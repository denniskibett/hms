<?php

namespace Tests\Feature;

use App\Enums\KitchenOrderStatus;
use App\Models\KitchenOrder;
use App\Models\MenuCategory;
use App\Models\MenuItem;
use App\Models\MenuItemVariant;
use App\Models\Role;
use App\Models\Room;
use App\Models\RoomType;
use App\Models\Stay;
use App\Models\User;
use App\Services\BookingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class KitchenTest extends TestCase
{
    use RefreshDatabase;

    private User $chef;
    private User $clerk;
    private MenuItemVariant $stewHalf;
    private MenuItemVariant $tea;

    protected function setUp(): void
    {
        parent::setUp();

        $this->chef = $this->userWithRole('kitchen');
        $this->clerk = $this->userWithRole('receptionist');

        $mains = MenuCategory::create(['name' => 'Main Course', 'sort_order' => 1]);
        $drinks = MenuCategory::create(['name' => 'Drinks', 'sort_order' => 2]);
        $stew = MenuItem::create(['name' => 'Beef Stew', 'category_id' => $mains->id]);
        $stew->variants()->create(['name' => 'Quarter', 'price' => 450]);
        $this->stewHalf = $stew->variants()->create(['name' => 'Half', 'price' => 800]);
        $this->tea = MenuItem::create(['name' => 'Tea', 'category_id' => $drinks->id])->variants()->create(['name' => 'Regular', 'price' => 150]);
    }

    private function userWithRole(string $role): User
    {
        $user = User::factory()->create();
        $user->roles()->attach(Role::firstOrCreate(['name' => $role])->id);

        return $user;
    }

    private function checkedInStay(): Stay
    {
        $bookings = app(BookingService::class);
        $room = Room::factory()->create(['room_type_id' => RoomType::factory()->create(['base_rate' => 5000, 'capacity' => 2])->id]);

        $stay = $bookings->create([
            'guest_id' => User::factory()->create()->id,
            'arrival_date' => today()->toDateString(),
            'departure_date' => today()->addDays(2)->toDateString(),
            'meal_plan' => 'BB',
            'status' => 'booked',
            'rooms' => [['room_id' => $room->id, 'adults' => 2, 'children' => 0]],
        ], $this->clerk);

        return $bookings->checkIn($stay, $this->clerk);
    }

    private function order(array $overrides = []): KitchenOrder
    {
        $this->actingAs($this->chef)->postJson(route('kitchen.orders.store'), array_merge([
            'type' => 'dine_in',
            'table_number' => '4',
            'items' => [
                ['menu_item_variant_id' => $this->stewHalf->id, 'quantity' => 2],
                ['menu_item_variant_id' => $this->tea->id, 'quantity' => 1, 'notes' => 'No sugar'],
            ],
        ], $overrides))->assertCreated();

        return KitchenOrder::latest('id')->first();
    }

    public function test_walk_in_order_paid_now_gets_a_paid_invoice(): void
    {
        $response = $this->actingAs($this->chef)->postJson(route('kitchen.orders.store'), [
            'type' => 'takeaway',
            'customer_name' => 'John',
            'items' => [['menu_item_variant_id' => $this->stewHalf->id, 'quantity' => 2]],
            'payment' => ['method' => 'mobile_money', 'payment_reference' => 'QAB12CD34E'],
        ])->assertCreated();

        $order = KitchenOrder::sole();
        $invoice = $order->invoices()->sole();

        $this->assertMatchesRegularExpression('/^KO-\d{5}$/', $order->order_number);
        $this->assertSame(KitchenOrderStatus::Pending, $order->status);
        $this->assertSame('1600.00', $invoice->total);
        $this->assertSame('paid', $invoice->status);
        $this->assertNotNull($response->json('receipt_url'));
        $this->actingAs($this->chef)->get($response->json('receipt_url'))->assertOk()->assertSee('John');
    }

    public function test_unpaid_walk_in_order_can_be_paid_later(): void
    {
        $order = $this->order();
        $invoice = $order->invoices()->sole();
        $this->assertSame('1750.00', $invoice->total);
        $this->assertSame('sent', $invoice->status);

        $this->actingAs($this->clerk)
            ->post(route('kitchen.orders.payments.store', $order), ['amount' => 1750, 'method' => 'cash'])
            ->assertSessionHas('receipt_url');

        $this->assertSame('paid', $invoice->fresh()->status);
    }

    public function test_room_service_goes_on_the_guest_bill_and_comes_off_when_cancelled(): void
    {
        $stay = $this->checkedInStay();
        $before = (float) $stay->currentInvoice()->total;

        $order = $this->order(['type' => 'room_service', 'stay_id' => $stay->id, 'charge_to_room' => true]);

        $this->assertSame(0, $order->invoices()->count());
        $invoice = $stay->currentInvoice();
        $this->assertSame($before + 1750, (float) $invoice->total);
        $this->assertSame(1, $invoice->items()->where('source_type', 'food')->count());

        // Rebuilding the stay invoice keeps the food line
        app(\App\Services\BillingService::class)->syncStayInvoice($stay->fresh());
        $this->assertSame($before + 1750, (float) $stay->currentInvoice()->total);

        $this->actingAs($this->chef)
            ->post(route('kitchen.orders.update-status', $order), ['status' => 'cancelled', 'reason' => 'Guest changed mind'])
            ->assertSessionHas('success');

        $this->assertSame($before, (float) $stay->currentInvoice()->total);
    }

    public function test_only_checked_in_guests_can_be_charged_and_room_charges_are_not_paid_now(): void
    {
        $stay = $this->checkedInStay();
        app(BookingService::class)->checkOut($stay, $this->clerk);

        $this->actingAs($this->chef)->postJson(route('kitchen.orders.store'), [
            'type' => 'room_service', 'stay_id' => $stay->id, 'charge_to_room' => true,
            'items' => [['menu_item_variant_id' => $this->tea->id, 'quantity' => 1]],
        ])->assertJsonValidationErrors('stay_id');

        $this->actingAs($this->chef)->postJson(route('kitchen.orders.store'), [
            'type' => 'room_service',
            'items' => [['menu_item_variant_id' => $this->tea->id, 'quantity' => 1]],
        ])->assertJsonValidationErrors('stay_id');
    }

    public function test_hidden_dishes_cannot_be_ordered(): void
    {
        $this->tea->update(['is_active' => false]);

        $this->actingAs($this->chef)->postJson(route('kitchen.orders.store'), [
            'type' => 'takeaway',
            'items' => [['menu_item_variant_id' => $this->tea->id, 'quantity' => 1]],
        ])->assertJsonValidationErrors('items.0.menu_item_variant_id');
    }

    public function test_order_moves_through_the_kitchen(): void
    {
        $order = $this->order();

        foreach (['preparing', 'ready', 'delivered'] as $status) {
            $this->actingAs($this->chef)->post(route('kitchen.orders.update-status', $order), ['status' => $status])->assertSessionHas('success');
        }

        $order->refresh();
        $this->assertSame(KitchenOrderStatus::Delivered, $order->status);
        $this->assertNotNull($order->preparation_started_at);
        $this->assertNotNull($order->ready_at);
        $this->assertNotNull($order->delivered_at);

        // Served food isn't cancelled, and steps can't be skipped
        $this->actingAs($this->chef)->post(route('kitchen.orders.update-status', $order), ['status' => 'cancelled', 'reason' => 'x'])->assertSessionHas('error');
        $other = $this->order();
        $this->actingAs($this->chef)->post(route('kitchen.orders.update-status', $other), ['status' => 'delivered'])->assertSessionHas('error');
    }

    public function test_cancelling_an_unpaid_walk_in_order_cancels_its_invoice(): void
    {
        $order = $this->order();

        $this->actingAs($this->chef)->post(route('kitchen.orders.update-status', $order), ['status' => 'cancelled'])->assertSessionHasErrors('reason');
        $this->actingAs($this->chef)->post(route('kitchen.orders.update-status', $order), ['status' => 'cancelled', 'reason' => 'Walked out']);

        $this->assertSame('cancelled', $order->invoices()->sole()->status);
    }

    public function test_menu_management(): void
    {
        $manager = $this->userWithRole('manager');
        $category = MenuCategory::where('name', 'Drinks')->first();

        $this->actingAs($this->clerk)->get(route('kitchen.menu.index'))->assertForbidden();

        $this->actingAs($manager)->postJson(route('kitchen.menu.items.store'), [
            'name' => 'Fresh Juice', 'category_id' => $category->id,
            'variants' => [['name' => 'Glass', 'price' => 200], ['name' => 'Jug', 'price' => 600]],
        ])->assertCreated();

        $juice = MenuItem::where('name', 'Fresh Juice')->with('variants')->first();
        $this->assertCount(2, $juice->variants);

        // Drop the jug (never ordered: deleted), rename the glass
        $glass = $juice->variants->firstWhere('name', 'Glass');
        $this->actingAs($manager)->putJson(route('kitchen.menu.items.update', $juice), [
            'name' => 'Fresh Juice', 'category_id' => $category->id,
            'variants' => [['id' => $glass->id, 'name' => 'Glass 300ml', 'price' => 250]],
        ])->assertOk();
        $this->assertSame(['Glass 300ml'], $juice->variants()->pluck('name')->all());

        // A dish that has been ordered stays for history; its category can't be deleted while it has dishes
        $this->order();
        $stew = $this->stewHalf->menuItem;
        $this->actingAs($manager)->deleteJson(route('kitchen.menu.items.destroy', $stew))->assertUnprocessable();
        $this->actingAs($manager)->deleteJson(route('kitchen.menu.categories.destroy', $stew->category_id))->assertUnprocessable();

        $this->actingAs($manager)->putJson(route('kitchen.menu.items.update', $stew), [
            'name' => 'Beef Stew', 'category_id' => $stew->category_id,
            'variants' => [['name' => 'Full', 'price' => 1500]],
        ])->assertOk();
        $this->assertFalse($this->stewHalf->fresh()->is_active);
    }

    public function test_pages_render(): void
    {
        $stay = $this->checkedInStay();
        $walkIn = $this->order();
        $roomOrder = $this->order(['type' => 'room_service', 'stay_id' => $stay->id, 'charge_to_room' => true]);
        $finance = $this->userWithRole('finance');

        $this->actingAs($this->chef)->get(route('kitchen.index'))->assertOk()->assertSee($walkIn->order_number)->assertSee('No sugar');
        $this->actingAs($this->chef)->get(route('kitchen.orders.index'))->assertOk()->assertSee($roomOrder->order_number);
        $this->actingAs($this->chef)->get(route('kitchen.orders.show', $walkIn))->assertOk()->assertSee('Record Payment');
        $this->actingAs($this->chef)->get(route('kitchen.orders.show', $roomOrder))->assertOk()->assertSee('Charged to the room');
        $this->actingAs($this->chef)->get(route('kitchen.orders.invoice', $walkIn))->assertOk();
        $this->actingAs($this->chef)->get(route('kitchen.menu.index'))->assertOk()->assertSee('Beef Stew');
        $this->actingAs($this->chef)->get(route('dashboard'))->assertOk();
        $this->actingAs($this->clerk)->get(route('kitchen.index'))->assertOk();
        $this->actingAs($this->clerk)->get(route('stays.show', $stay))->assertOk()->assertSee($roomOrder->order_number);
        $this->actingAs($finance)->get(route('invoices.show', $walkIn->invoices()->sole()))->assertOk()->assertSee($walkIn->order_number);
        $this->actingAs($finance)->get(route('invoices.index'))->assertOk()->assertSee('Walk-in customer');
        $this->actingAs($this->userWithRole('housekeeping'))->get(route('kitchen.index'))->assertForbidden();
    }
}
