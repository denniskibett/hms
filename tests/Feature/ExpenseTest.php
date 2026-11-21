<?php

namespace Tests\Feature;

use App\Models\Expense;
use App\Models\InventoryItem;
use App\Models\PurchaseOrder;
use App\Models\Role;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExpenseTest extends TestCase
{
    use RefreshDatabase;

    private User $finance;
    private Supplier $supplier;

    protected function setUp(): void
    {
        parent::setUp();

        $this->finance = $this->userWithRole('finance');
        $this->supplier = Supplier::create(['name' => 'Wholesale Ltd', 'status' => 'active']);
    }

    private function userWithRole(string $role): User
    {
        $user = User::factory()->create();
        $user->roles()->attach(Role::firstOrCreate(['name' => $role])->id);

        return $user;
    }

    /**
     * A purchase order with 10 × 100 = 1,000 received into stock.
     */
    private function receivedOrder(): PurchaseOrder
    {
        $buyer = $this->userWithRole('manager');
        $item = InventoryItem::create(['name' => 'Rice', 'sku' => 'RICE', 'item_type' => 'food', 'unit_of_measure' => 'kg', 'reorder_level' => 0, 'status' => true]);

        $this->actingAs($buyer)->postJson(route('purchase-orders.store'), [
            'supplier_id' => $this->supplier->id,
            'items' => [['inventory_item_id' => $item->id, 'quantity' => 10, 'unit_price' => 100]],
        ])->assertCreated();
        $order = PurchaseOrder::latest('id')->first();
        $this->actingAs($buyer)->post(route('purchase-orders.status', $order), ['status' => 'approved']);
        $this->actingAs($buyer)->post(route('purchase-orders.receive', $order), ['lines' => [$order->items()->first()->id => ['quantity' => 10]]]);

        return $order->fresh();
    }

    private function record(array $data)
    {
        return $this->actingAs($this->finance)->postJson(route('expenses.store'), array_merge([
            'spent_on' => today()->toDateString(),
            'category' => 'utilities',
            'description' => 'KPLC tokens',
            'amount' => 5000,
            'method' => 'mobile_money',
            'reference' => 'QXY123',
        ], $data));
    }

    public function test_recording_and_voiding_an_expense(): void
    {
        $this->record([])->assertCreated();
        $expense = Expense::sole();
        $this->assertMatchesRegularExpression('/^EXP-\d{4}-\d{5}$/', $expense->expense_number);

        // M-Pesa needs a code; can't be in the future
        $this->record(['reference' => null])->assertJsonValidationErrors('reference');
        $this->record(['spent_on' => today()->addDay()->toDateString()])->assertJsonValidationErrors('spent_on');

        $this->actingAs($this->finance)->post(route('expenses.void', $expense), [])->assertSessionHasErrors('reason');
        $this->actingAs($this->finance)->post(route('expenses.void', $expense), ['reason' => 'Entered twice'])->assertSessionHas('success');
        $this->assertSame('void', $expense->fresh()->status);
        $this->assertSame(0.0, (float) Expense::paid()->sum('amount'));
    }

    public function test_supplier_payments_settle_what_was_received(): void
    {
        $order = $this->receivedOrder();
        $this->assertSame(1000.0, $order->amount_owed);
        $this->assertSame(1000.0, $this->supplier->balance());

        // Supplier payments need a supplier, and can't exceed what is owed on the order
        $this->record(['category' => 'supplier_payment'])->assertJsonValidationErrors('supplier_id');
        $this->record(['category' => 'supplier_payment', 'supplier_id' => $this->supplier->id, 'purchase_order_id' => $order->id, 'amount' => 1500])
            ->assertJsonValidationErrors('amount');

        $other = Supplier::create(['name' => 'Other', 'status' => 'active']);
        $this->record(['category' => 'supplier_payment', 'supplier_id' => $other->id, 'purchase_order_id' => $order->id, 'amount' => 100])
            ->assertJsonValidationErrors('purchase_order_id');

        $this->record(['category' => 'supplier_payment', 'supplier_id' => $this->supplier->id, 'purchase_order_id' => $order->id, 'amount' => 600, 'description' => 'Part payment'])
            ->assertCreated();

        $this->assertSame(400.0, $order->fresh()->amount_owed);
        $this->assertSame(400.0, $this->supplier->fresh()->balance());
    }

    public function test_pages_report_and_access(): void
    {
        $order = $this->receivedOrder();
        $this->record([])->assertCreated();
        $this->record(['category' => 'supplier_payment', 'supplier_id' => $this->supplier->id, 'amount' => 300, 'description' => 'Advance'])->assertCreated();

        $this->actingAs($this->finance)->get(route('expenses.index'))->assertOk()->assertSee('KPLC tokens')->assertSee('Wholesale Ltd');
        $this->actingAs($this->finance)->get(route('expenses.index', ['pay_supplier' => $this->supplier->id, 'po' => $order->id]))->assertOk();
        $this->actingAs($this->finance)->get(route('suppliers.show', $this->supplier))->assertOk()->assertSee('We owe');
        $this->actingAs($this->finance)->get(route('purchase-orders.show', $order))->assertOk()->assertSee('Owed to supplier');

        $report = $this->actingAs($this->finance)->get(route('reports.show', 'expenses'))->assertOk()->viewData('data');
        $this->assertEquals(5300, $report['cards'][0][1]);
        $this->assertEquals(700, $report['cards'][3][1]);   // 1,000 received − 300 paid

        $this->actingAs($this->userWithRole('procurement'))->get(route('expenses.index'))->assertForbidden();
    }
}
