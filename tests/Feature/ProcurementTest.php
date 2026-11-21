<?php

namespace Tests\Feature;

use App\Enums\PurchaseOrderStatus;
use App\Models\InventoryItem;
use App\Models\PurchaseOrder;
use App\Models\Role;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ProcurementTest extends TestCase
{
    use RefreshDatabase;

    private User $buyer;
    private User $manager;
    private Supplier $supplier;
    private InventoryItem $rice;
    private InventoryItem $oil;

    protected function setUp(): void
    {
        parent::setUp();

        $this->buyer = $this->userWithRole('procurement');
        $this->manager = $this->userWithRole('manager');
        $this->supplier = Supplier::create(['name' => 'Wholesale Ltd', 'status' => 'active', 'payment_terms' => '30 days']);
        $this->rice = $this->stockItem('Rice', 'kg', 10, 20, 100);
        $this->oil = $this->stockItem('Cooking oil', 'litre', 30, 10, 250);
    }

    private function userWithRole(string $role): User
    {
        $user = User::factory()->create();
        $user->roles()->attach(Role::firstOrCreate(['name' => $role])->id);

        return $user;
    }

    private function stockItem(string $name, string $unit, float $qty, float $reorder, float $cost): InventoryItem
    {
        $item = InventoryItem::create([
            'name' => $name, 'sku' => strtoupper(substr($name, 0, 4)), 'item_type' => 'food', 'unit_of_measure' => $unit,
            'reorder_level' => $reorder, 'primary_supplier_id' => $this->supplier->id, 'status' => true,
        ]);
        $item->move('in', $qty, $this->buyer, ['unit_cost' => $cost]);

        return $item->fresh();
    }

    private function draft(): PurchaseOrder
    {
        $this->actingAs($this->buyer)->postJson(route('purchase-orders.store'), [
            'supplier_id' => $this->supplier->id,
            'delivery_date' => today()->addDays(3)->toDateString(),
            'items' => [
                ['inventory_item_id' => $this->rice->id, 'quantity' => 50, 'unit_price' => 110],
                ['inventory_item_id' => $this->oil->id, 'quantity' => 20, 'unit_price' => 240],
            ],
        ])->assertCreated();

        return PurchaseOrder::latest('id')->first();
    }

    private function moveTo(PurchaseOrder $order, string $status, User $by, array $extra = [])
    {
        return $this->actingAs($by)->post(route('purchase-orders.status', $order), ['status' => $status] + $extra);
    }

    public function test_order_goes_through_approval(): void
    {
        $order = $this->draft();
        $this->assertMatchesRegularExpression('/^PO-\d{4}-\d{5}$/', $order->po_number);
        $this->assertSame('10300.00', $order->total);
        $this->assertSame(PurchaseOrderStatus::Draft, $order->status);

        $this->moveTo($order, 'submitted', $this->buyer)->assertSessionHas('success');

        // Buyers can't approve or edit a submitted order
        $this->moveTo($order, 'approved', $this->buyer)->assertSessionHas('error');
        $this->actingAs($this->buyer)->putJson(route('purchase-orders.update', $order), ['supplier_id' => $this->supplier->id, 'items' => [['inventory_item_id' => $this->rice->id, 'quantity' => 1, 'unit_price' => 1]]])->assertUnprocessable();

        // Sent back needs a reason, then it is editable again
        $this->moveTo($order, 'draft', $this->manager)->assertSessionHasErrors('note');
        $this->moveTo($order, 'draft', $this->manager, ['note' => 'Too much oil'])->assertSessionHas('success');
        $this->assertStringContainsString('Too much oil', $order->fresh()->notes);

        $this->moveTo($order, 'submitted', $this->buyer);
        $this->moveTo($order, 'approved', $this->manager)->assertSessionHas('success');
        $this->moveTo($order, 'ordered', $this->buyer)->assertSessionHas('success');

        $order->refresh();
        $this->assertSame(PurchaseOrderStatus::Ordered, $order->status);
        $this->assertSame($this->manager->id, $order->approved_by);
        $this->assertNotNull($order->ordered_at);
    }

    public function test_receiving_in_parts_puts_stock_in_at_the_price_paid(): void
    {
        $order = $this->draft();
        $this->moveTo($order, 'approved', $this->manager);
        [$riceLine, $oilLine] = $order->items()->orderBy('id')->get()->all();

        // Can't receive more than ordered
        $this->actingAs($this->buyer)->post(route('purchase-orders.receive', $order), ['lines' => [$riceLine->id => ['quantity' => 60]]])
            ->assertSessionHasErrors("lines.{$riceLine->id}.quantity");

        // First delivery: all the rice at a better price, no oil
        $this->actingAs($this->buyer)->post(route('purchase-orders.receive', $order), [
            'lines' => [$riceLine->id => ['quantity' => 50, 'unit_price' => 100], $oilLine->id => ['quantity' => 0]],
            'supplier_reference' => 'INV-778',
        ])->assertSessionHas('success');

        $this->assertSame(PurchaseOrderStatus::PartiallyReceived, $order->fresh()->status);
        $this->assertSame('60.00', $this->rice->fresh()->quantity);
        $this->assertSame('100.00', $this->rice->fresh()->unit_cost);   // (10 × 100 + 50 × 100) / 60
        $this->assertEquals(100, DB::table('supplier_prices')->where('inventory_item_id', $this->rice->id)->value('unit_price'));

        // Second delivery: the oil
        $this->actingAs($this->buyer)->post(route('purchase-orders.receive', $order), ['lines' => [$oilLine->id => ['quantity' => 20]]]);

        $order->refresh();
        $this->assertSame(PurchaseOrderStatus::Received, $order->status);
        $this->assertNotNull($order->received_at);
        $this->assertSame('INV-778', $order->supplier_reference);
        $this->assertSame('50.00', $this->oil->fresh()->quantity);
        $this->assertSame(2, $order->receipts()->count());

        // Received orders can't be cancelled or received again
        $this->moveTo($order, 'cancelled', $this->manager, ['note' => 'x'])->assertSessionHas('error');
        $this->actingAs($this->buyer)->post(route('purchase-orders.receive', $order), ['lines' => [$oilLine->id => ['quantity' => 1]]])->assertSessionHas('error');
    }

    public function test_drafts_cannot_be_received(): void
    {
        $order = $this->draft();
        $line = $order->items()->first();

        $this->actingAs($this->buyer)->post(route('purchase-orders.receive', $order), ['lines' => [$line->id => ['quantity' => 1]]])->assertSessionHas('error');
        $this->assertSame('10.00', $this->rice->fresh()->quantity);
    }

    public function test_reorder_low_stock(): void
    {
        // Rice: 10 kg with reorder at 20 → order up to 40; oil is fine
        $this->actingAs($this->buyer)->post(route('purchase-orders.reorder'), ['supplier_id' => $this->supplier->id])->assertRedirect();

        $order = PurchaseOrder::sole();
        $line = $order->items()->sole();
        $this->assertSame($this->rice->id, $line->inventory_item_id);
        $this->assertSame('30.00', $line->quantity);
        $this->assertSame('100.00', $line->unit_price);
    }

    public function test_suppliers_and_access(): void
    {
        $this->actingAs($this->buyer)->postJson(route('suppliers.store'), ['name' => 'Fresh Farm', 'kra_pin' => 'p051234567x', 'status' => 'active'])->assertCreated();
        $this->assertSame('P051234567X', Supplier::where('name', 'Fresh Farm')->value('kra_pin'));

        $order = $this->draft();
        $finance = $this->userWithRole('finance');
        $this->actingAs($finance)->get(route('purchase-orders.index'))->assertOk()->assertSee($order->po_number);
        $this->actingAs($finance)->get(route('purchase-orders.show', $order))->assertOk();
        $this->actingAs($finance)->post(route('purchase-orders.status', $order), ['status' => 'submitted'])->assertForbidden();
        $this->actingAs($this->userWithRole('housekeeping'))->get(route('purchase-orders.index'))->assertForbidden();

        $this->actingAs($this->buyer)->get(route('suppliers.index'))->assertOk()->assertSee('Wholesale Ltd');
        $this->actingAs($this->buyer)->get(route('suppliers.show', $this->supplier))->assertOk()->assertSee('Order low stock');
        $this->actingAs($this->buyer)->get(route('purchase-orders.show', $order))->assertOk()->assertSee('Rice');
        $this->actingAs($this->buyer)->get(route('dashboard'))->assertOk();
        $this->actingAs($this->buyer)->get(route('inventory.show', $this->rice))->assertOk();
    }
}
