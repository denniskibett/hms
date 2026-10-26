<?php

namespace Tests\Feature;

use App\Helpers\SystemHelper;
use App\Mail\GuestMessageMail;
use App\Models\BookingRequest;
use App\Models\GuestMessage;
use App\Models\Role;
use App\Models\Room;
use App\Models\RoomType;
use App\Models\Stay;
use App\Models\System;
use App\Models\User;
use App\Services\BookingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class GuestMessageTest extends TestCase
{
    use RefreshDatabase;

    private User $guest;
    private User $receptionist;
    private Room $room;

    protected function setUp(): void
    {
        parent::setUp();

        $this->guest = $this->userWithRole('guest');
        $this->guest->update(['name' => 'Otieno Ouma', 'phone' => '0712 345 678', 'email' => 'otieno@example.com']);
        $this->guest->guestProfile()->create(['nationality' => 'Kenya']);
        $this->receptionist = $this->userWithRole('receptionist');
        $this->room = Room::factory()->create(['room_number' => '104', 'room_type_id' => RoomType::factory()->create(['base_rate' => 5000, 'capacity' => 2])->id]);
    }

    private function userWithRole(string $role): User
    {
        $user = User::factory()->create();
        $user->roles()->attach(Role::firstOrCreate(['name' => $role])->id);

        return $user;
    }

    private function stayPayload(array $overrides = []): array
    {
        return array_merge([
            'guest_id' => $this->guest->id,
            'arrival_date' => today()->toDateString(),
            'departure_date' => today()->addDays(2)->toDateString(),
            'meal_plan' => 'BB',
            'status' => 'booked',
            'rooms' => [['room_id' => $this->room->id, 'adults' => 2, 'children' => 0]],
        ], $overrides);
    }

    private function makeStay(): Stay
    {
        return app(BookingService::class)->create($this->stayPayload(), $this->receptionist);
    }

    private function setSetting(string $group, array $values): void
    {
        $system = System::settings();
        $system->update(['settings' => array_merge($system->settings ?? [], [$group => $values])]);
        SystemHelper::clearCache();
    }

    public function test_phone_numbers_are_normalised(): void
    {
        $this->assertSame('254712345678', GuestMessage::normalisePhone('0712 345 678'));
        $this->assertSame('254712345678', GuestMessage::normalisePhone('+254 712-345-678'));
        $this->assertSame('254112345678', GuestMessage::normalisePhone('112345678'));
        $this->assertSame('447700900123', GuestMessage::normalisePhone('+44 7700 900123'));
        $this->assertNull(GuestMessage::normalisePhone('12345'));
        $this->assertNull(GuestMessage::normalisePhone(null));
    }

    public function test_website_request_gets_an_sms_and_email_in_test_mode(): void
    {
        $this->post(route('book.submit'), [
            'arrival_date' => today()->addDay()->toDateString(),
            'departure_date' => today()->addDays(3)->toDateString(),
            'adults' => 2,
            'rooms' => 1,
            'meal_plan' => 'BB',
            'name' => 'Wanjiru Kamau',
            'phone' => '0722 000 111',
            'email' => 'wanjiru@example.com',
        ])->assertRedirect(route('book.thanks'));

        $request = BookingRequest::sole();
        $sms = GuestMessage::where('channel', 'sms')->sole();
        $this->assertSame('254722000111', $sms->recipient);
        $this->assertSame('test', $sms->status);
        $this->assertSame($request->id, $sms->booking_request_id);
        $this->assertStringContainsString("Hi Wanjiru, thank you for your booking request {$request->reference}", $sms->body);

        $email = GuestMessage::where('channel', 'email')->sole();
        $this->assertSame('wanjiru@example.com', $email->recipient);
        $this->assertSame('We have your booking request', $email->subject);
    }

    public function test_declining_a_request_tells_the_guest_why(): void
    {
        $request = BookingRequest::create([
            'name' => 'Wanjiru Kamau', 'phone' => '0722000111',
            'arrival_date' => today()->addDay(), 'departure_date' => today()->addDays(2),
            'adults' => 2, 'rooms' => 1, 'meal_plan' => 'BB',
        ]);

        $this->actingAs($this->receptionist)->post(route('booking-requests.decline', $request), ['reason' => 'we are fully booked']);

        $sms = GuestMessage::sole();
        $this->assertSame('request_declined', $sms->template);
        $this->assertStringContainsString('we are fully booked', $sms->body);
        $this->assertSame($this->receptionist->id, $sms->sent_by);
    }

    public function test_firm_bookings_are_confirmed_but_reservations_are_not(): void
    {
        $this->actingAs($this->receptionist)->post(route('stays.store'), $this->stayPayload(['status' => 'reserved']));
        $this->assertSame(0, GuestMessage::count());

        $this->post(route('stays.store'), $this->stayPayload([
            'arrival_date' => today()->addDays(5)->toDateString(),
            'departure_date' => today()->addDays(7)->toDateString(),
        ]))->assertSessionHasNoErrors();

        $stay = Stay::latest('id')->first();
        $sms = GuestMessage::where('channel', 'sms')->sole();
        $this->assertSame('booking_confirmed', $sms->template);
        $this->assertSame($stay->id, $sms->stay_id);
        $this->assertSame($this->guest->id, $sms->guest_id);
        $this->assertStringContainsString("your booking #{$stay->id}", $sms->body);
        $this->assertStringContainsString('2 nights', $sms->body);
        $this->assertSame(2, GuestMessage::count()); // and an email
    }

    public function test_check_in_sends_a_welcome_with_the_room(): void
    {
        $stay = $this->makeStay();

        $this->actingAs($this->receptionist)->post(route('stays.check-in', $stay))->assertSessionHas('success');

        $sms = GuestMessage::where('channel', 'sms')->sole();
        $this->assertSame('checked_in', $sms->template);
        $this->assertStringContainsString('You are in room 104', $sms->body);
    }

    public function test_payment_sends_a_receipt_with_the_balance(): void
    {
        $stay = $this->makeStay();
        $total = (float) $stay->currentInvoice()->total;

        $this->actingAs($this->receptionist)
            ->post(route('stays.payments.store', $stay), ['amount' => 2500, 'method' => 'mobile_money', 'payment_reference' => 'QJK3XYZ12A'])
            ->assertSessionHas('success');

        $sms = GuestMessage::where('channel', 'sms')->sole();
        $this->assertSame('payment_received', $sms->template);
        $this->assertStringContainsString('QJK3XYZ12A', $sms->body);
        $this->assertStringContainsString(number_format($total - 2500, 2), $sms->body);
    }

    public function test_switched_off_messages_are_not_sent_and_edited_wording_is_used(): void
    {
        $this->setSetting('messages', [
            'auto' => ['checked_in' => false],
            'templates' => ['booking_confirmed' => 'Karibu {name}! Booking {reference} is on.'],
        ]);

        $this->actingAs($this->receptionist)->post(route('stays.store'), $this->stayPayload());
        $stay = Stay::latest('id')->first();
        $this->assertSame("Karibu Otieno! Booking #{$stay->id} is on.", GuestMessage::where('channel', 'sms')->sole()->body);

        $this->post(route('stays.check-in', $stay));
        $this->assertSame(0, GuestMessage::where('template', 'checked_in')->count());
    }

    public function test_staff_can_send_a_message_by_hand(): void
    {
        $stay = $this->makeStay();

        $this->actingAs($this->receptionist)->post(route('guest-messages.store'), [
            'guest_id' => $this->guest->id,
            'stay_id' => $stay->id,
            'channels' => ['sms', 'email'],
            'subject' => 'Airport pickup',
            'body' => 'Your driver will meet you at 2pm.',
        ])->assertSessionHas('success');

        $this->assertSame(2, GuestMessage::where('stay_id', $stay->id)->whereNull('template')->count());
        $this->assertSame('Airport pickup', GuestMessage::where('channel', 'email')->sole()->subject);

        $this->guest->update(['phone' => '123']);
        $this->post(route('guest-messages.store'), [
            'guest_id' => $this->guest->id,
            'channels' => ['sms'],
            'body' => 'Hello',
        ])->assertSessionHas('error');
    }

    public function test_email_is_sent_when_real_mail_is_set_up(): void
    {
        Mail::fake();
        config(['mail.default' => 'smtp']);

        GuestMessage::queue('email', 'otieno@example.com', "Line one\nLine two", ['subject' => 'Hello'])->deliver();

        Mail::assertSent(GuestMessageMail::class, fn ($mail) => $mail->hasTo('otieno@example.com')
            && str_contains($mail->render(), 'Line one<br />'));
        $this->assertSame('sent', GuestMessage::sole()->status);
    }

    public function test_failed_messages_can_be_sent_again(): void
    {
        config(['services.sms.driver' => 'not-set-up']);

        $this->actingAs($this->receptionist)->post(route('guest-messages.store'), [
            'guest_id' => $this->guest->id,
            'channels' => ['sms'],
            'body' => 'Hello',
        ]);

        $message = GuestMessage::sole();
        $this->assertSame('failed', $message->status);
        $this->assertNotNull($message->error);

        config(['services.sms.driver' => 'log']);
        $this->post(route('guest-messages.retry', $message))->assertSessionHas('success');
        $this->assertSame('test', $message->fresh()->status);
    }

    public function test_pages_render_and_only_managers_edit_templates(): void
    {
        $stay = $this->makeStay();
        GuestMessage::forStay($stay, 'booking_confirmed');

        $this->actingAs($this->receptionist);
        $this->get(route('guest-messages.index'))->assertOk()->assertSee('Otieno Ouma');
        $this->get(route('guest-messages.index', ['tab' => 'templates']))->assertOk()->assertSee('Only a manager can change these.');
        $this->get(route('stays.show', $stay))->assertOk()->assertSee('Send message');
        $this->get(route('guests.show', $this->guest->guestProfile))->assertOk()->assertSee('Send message');

        $templates = collect(GuestMessage::TEMPLATES)->map(fn ($t) => $t['body'])->all();
        $this->put(route('guest-messages.templates'), ['templates' => $templates])->assertForbidden();

        $templates['checked_in'] = 'Welcome {name}, room {room}.';
        $this->actingAs($this->userWithRole('manager'))
            ->put(route('guest-messages.templates'), ['templates' => $templates, 'auto' => ['checked_in' => 1]])
            ->assertRedirect();

        $this->assertSame('Welcome {name}, room {room}.', GuestMessage::templateBody('checked_in'));
        $this->assertTrue(GuestMessage::autoSends('checked_in'));
        $this->assertFalse(GuestMessage::autoSends('payment_received'));
    }
}
