<?php

namespace Tests\Feature;

use App\Http\Controllers\ReportController;
use App\Models\Department;
use App\Models\Facility;
use App\Models\InventoryItem;
use App\Models\MenuCategory;
use App\Models\MenuItem;
use App\Models\Payroll;
use App\Models\Role;
use App\Models\Room;
use App\Models\RoomType;
use App\Models\User;
use App\Services\BillingService;
use App\Services\BookingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportTest extends TestCase
{
    use RefreshDatabase;

    private User $manager;

    protected function setUp(): void
    {
        parent::setUp();

        $this->manager = $this->userWithRole('manager');
    }

    private function userWithRole(string $role): User
    {
        $user = User::factory()->create();
        $user->roles()->attach(Role::firstOrCreate(['name' => $role])->id);

        return $user;
    }

    /**
     * A bit of everything: two rooms, one booked for 2 nights at 5,000 and paid
     * in part; a restaurant order; an event; payroll; stock in and out.
     */
    private function seedActivity(): void
    {
        $type = RoomType::factory()->create(['name' => 'Deluxe', 'base_rate' => 5000, 'capacity' => 2]);
        $room = Room::factory()->create(['room_type_id' => $type->id]);
        Room::factory()->create(['room_type_id' => $type->id]);

        $stay = app(BookingService::class)->create([
            'guest_id' => User::factory()->create(['name' => 'Asha Guest'])->id,
            'arrival_date' => today()->toDateString(),
            'departure_date' => today()->addDays(2)->toDateString(),
            'meal_plan' => 'BB',
            'status' => 'booked',
            'rooms' => [['room_id' => $room->id, 'adults' => 2, 'children' => 0]],
        ], $this->manager);
        app(BillingService::class)->recordPayment($stay->invoices()->sole(), ['amount' => 4000, 'method' => 'mobile_money', 'payment_reference' => 'QAA1'], $this->manager);

        $dish = MenuItem::create(['name' => 'Pilau', 'category_id' => MenuCategory::create(['name' => 'Mains'])->id]);
        $variant = $dish->variants()->create(['name' => 'Regular', 'price' => 600]);
        $this->actingAs($this->manager)->postJson(route('kitchen.orders.store'), [
            'type' => 'takeaway', 'items' => [['menu_item_variant_id' => $variant->id, 'quantity' => 3]],
            'payment' => ['method' => 'cash'],
        ])->assertCreated();

        $hall = Facility::create(['name' => 'Hall A', 'code' => 'hall_a', 'capacity' => 50, 'base_rate' => 0, 'status' => 'available']);
        $this->actingAs($this->manager)->postJson(route('events.store'), [
            'facility_id' => $hall->id, 'title' => 'Board meeting', 'client_name' => 'Acme', 'contact_phone' => '0700',
            'start_date' => today()->toDateString(), 'end_date' => today()->toDateString(), 'pax' => 10, 'status' => 'confirmed',
            'items' => [['description' => 'Full day', 'charge_basis' => 'per_person', 'quantity' => 10, 'days' => 1, 'unit_price' => 2500]],
        ])->assertCreated();

        $payroll = new Payroll(['user_id' => $this->manager->id, 'period_month' => today()->startOfMonth()->toDateString(), 'basic_salary' => 50000, 'status' => 'approved']);
        $payroll->calculate()->save();

        $item = InventoryItem::create(['name' => 'Soap', 'sku' => 'SOAP', 'item_type' => 'cleaning', 'unit_of_measure' => 'pcs', 'reorder_level' => 5, 'status' => true]);
        $item->move('in', 20, $this->manager, ['unit_cost' => 50]);
        $item->move('out', -5, $this->manager, ['department_id' => Department::create(['name' => 'Housekeeping', 'code' => 'HK'])->id]);
        $item->move('damage', -1, $this->manager, ['notes' => 'Broken']);
    }

    public function test_every_report_renders_and_exports(): void
    {
        $this->seedActivity();

        foreach (array_keys(ReportController::REPORTS) as $report) {
            $this->actingAs($this->manager)->get(route('reports.show', $report))->assertOk();

            $csv = $this->actingAs($this->manager)->get(route('reports.show', [$report, 'export' => 'csv']));
            $csv->assertOk();
            $this->assertStringContainsString('text/csv', $csv->headers->get('Content-Type'));
        }

        $this->actingAs($this->manager)->get(route('reports.index'))->assertOk()->assertSee('Occupancy');
    }

    public function test_figures(): void
    {
        $this->seedActivity();
        $get = fn ($report) => $this->actingAs($this->manager)->get(route('reports.show', [$report, 'from' => today()->startOfMonth()->toDateString(), 'to' => today()->addDays(5)->toDateString()]))->viewData('data');

        // Billed: 10,000 room + 1,800 food + 25,000 event; collected 4,000 + 1,800
        $revenue = $get('revenue');
        $this->assertEquals(36800, $revenue['cards'][0][1]);
        $this->assertEquals(5800, $revenue['cards'][1][1]);

        // 2 nights sold, ADR 5,000
        $occupancy = $get('occupancy');
        $this->assertSame(2, $occupancy['cards'][1][1]);
        $this->assertEquals(5000, $occupancy['cards'][2][1]);

        // Debtors: the stay owes 6,000 and the event 25,000; nothing is late yet
        $debtors = $get('debtors');
        $this->assertEquals(31000, $debtors['cards'][0][1]);
        $this->assertEquals(0, $debtors['cards'][1][1]);

        $this->assertEquals(1800, $get('restaurant')['cards'][0][1]);
        $this->assertEquals(25000, $get('events')['cards'][1][1]);
        $this->assertEquals(5845.85, $get('payroll')['cards'][1][1]);

        $stock = $get('stock');
        $this->assertEquals(250, $stock['cards'][1][1]);   // 5 × 50
        $this->assertEquals(50, $stock['cards'][2][1]);    // 1 × 50
        $this->assertEquals(700, $stock['cards'][3][1]);   // 14 × 50
    }

    public function test_access(): void
    {
        $this->actingAs($this->userWithRole('finance'))->get(route('reports.show', 'tax'))->assertOk();
        $this->actingAs($this->userWithRole('receptionist'))->get(route('reports.show', 'tax'))->assertForbidden();
        $this->actingAs($this->manager)->get(route('reports.show', 'nope'))->assertNotFound();
    }
}
