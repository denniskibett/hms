<?php

namespace Tests\Feature;

use App\Exceptions\StockException;
use App\Models\Department;
use App\Models\InventoryItem;
use App\Models\Role;
use App\Models\Task;
use App\Models\TaskType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InventoryTest extends TestCase
{
    use RefreshDatabase;

    private User $storekeeper;
    private User $housekeeper;
    private Department $housekeeping;

    protected function setUp(): void
    {
        parent::setUp();

        $this->storekeeper = $this->userWithRole('procurement');
        $this->housekeeper = $this->userWithRole('housekeeping');
        $this->housekeeping = Department::create(['name' => 'Housekeeping', 'code' => 'HK']);
    }

    private function userWithRole(string $role): User
    {
        $user = User::factory()->create();
        $user->roles()->attach(Role::firstOrCreate(['name' => $role])->id);

        return $user;
    }

    private function item(array $overrides = []): InventoryItem
    {
        $this->actingAs($this->storekeeper)->postJson(route('inventory.store'), array_merge([
            'name' => 'Toilet paper',
            'item_type' => 'cleaning',
            'unit_of_measure' => 'roll',
            'reorder_level' => 20,
            'opening_quantity' => 50,
            'opening_unit_cost' => 40,
        ], $overrides))->assertCreated();

        return InventoryItem::latest('id')->first();
    }

    public function test_new_item_gets_a_code_and_opening_stock(): void
    {
        $item = $this->item();

        $this->assertSame('CLE-0001', $item->sku);
        $this->assertSame('50.00', $item->quantity);
        $this->assertSame('40.00', $item->unit_cost);
        $this->assertSame(1, $item->transactions()->count());
        $this->assertSame('CLE-0002', $this->item(['name' => 'Bleach'])->sku);
    }

    public function test_receiving_updates_the_average_cost(): void
    {
        $item = $this->item();

        $this->actingAs($this->storekeeper)->post(route('inventory.move', $item), ['action' => 'in', 'quantity' => 50, 'unit_cost' => 60])->assertSessionHas('success');

        $item->refresh();
        $this->assertSame('100.00', $item->quantity);
        $this->assertSame('50.00', $item->unit_cost); // (50 × 40 + 50 × 60) / 100
        $this->assertSame(5000.0, $item->stock_value);
    }

    public function test_issuing_writing_off_and_counting(): void
    {
        $item = $this->item();

        $this->actingAs($this->housekeeper)->post(route('inventory.move', $item), ['action' => 'out', 'quantity' => 10])->assertSessionHasErrors('department_id');
        $this->actingAs($this->housekeeper)->post(route('inventory.move', $item), ['action' => 'out', 'quantity' => 10, 'department_id' => $this->housekeeping->id])->assertSessionHas('success');
        $this->actingAs($this->housekeeper)->post(route('inventory.move', $item), ['action' => 'damage', 'quantity' => 2, 'notes' => 'Wet'])->assertSessionHas('success');
        $this->assertSame('38.00', $item->fresh()->quantity);

        // Can't issue more than there is
        $this->actingAs($this->housekeeper)->post(route('inventory.move', $item), ['action' => 'out', 'quantity' => 100, 'department_id' => $this->housekeeping->id])->assertSessionHasErrors('quantity');

        // Housekeeping can't receive or count; the store can
        $this->actingAs($this->housekeeper)->post(route('inventory.move', $item), ['action' => 'count', 'quantity' => 30, 'notes' => 'x'])->assertForbidden();
        $this->actingAs($this->storekeeper)->post(route('inventory.move', $item), ['action' => 'count', 'quantity' => 35, 'notes' => 'Monthly count'])->assertSessionHas('success');

        $item->refresh();
        $this->assertSame('35.00', $item->quantity);
        $last = $item->transactions()->latest('id')->first();
        $this->assertSame('adjustment', $last->type);
        $this->assertSame('-3.00', $last->quantity);
        $this->assertSame('35.00', $last->balance_after);
        $this->assertSame(4, $item->transactions()->count());
    }

    public function test_low_stock(): void
    {
        $item = $this->item(['opening_quantity' => 15]);

        $this->assertTrue($item->is_low);
        $this->assertSame(1, InventoryItem::lowStock()->count());
        $this->actingAs($this->storekeeper)->get(route('inventory.index', ['stock' => 'low']))->assertOk()->assertSee('Toilet paper');
    }

    public function test_stock_used_on_a_task(): void
    {
        $item = $this->item();
        $type = TaskType::create(['name' => 'Clean', 'code' => 'CLEAN', 'category' => 'cleaning']);
        $task = Task::create(['task_type_id' => $type->id, 'title' => 'Clean lobby', 'assigned_to' => $this->housekeeper->id, 'due_date' => now(), 'created_by' => $this->housekeeper->id]);

        $this->actingAs($this->housekeeper)->post(route('tasks.items.store', $task), ['inventory_item_id' => $item->id, 'quantity' => 3])->assertSessionHas('success');
        $this->actingAs($this->housekeeper)->post(route('tasks.items.store', $task), ['inventory_item_id' => $item->id, 'quantity' => 2]);

        $this->assertSame('45.00', $item->fresh()->quantity);
        $line = $task->items()->sole();
        $this->assertSame('5.00', $line->quantity_used);
        $this->assertSame(200.0, $line->total_cost);
        $this->assertSame('task', $item->transactions()->latest('id')->first()->reference_type);

        $this->actingAs($this->housekeeper)->post(route('tasks.items.store', $task), ['inventory_item_id' => $item->id, 'quantity' => 500])->assertSessionHasErrors('quantity');
        $this->actingAs($this->housekeeper)->get(route('tasks.show', $task))->assertOk()->assertSee('Toilet paper');
    }

    public function test_move_refuses_negative_balances_directly(): void
    {
        $item = $this->item(['opening_quantity' => 1]);

        $this->expectException(StockException::class);
        $item->move('out', -2, $this->storekeeper);
    }

    public function test_access_and_pages(): void
    {
        $item = $this->item();

        $this->actingAs($this->housekeeper)->postJson(route('inventory.store'), ['name' => 'X', 'item_type' => 'other', 'unit_of_measure' => 'pcs', 'reorder_level' => 0])->assertForbidden();
        $this->actingAs($this->userWithRole('finance'))->get(route('inventory.index'))->assertForbidden();

        $this->actingAs($this->housekeeper)->get(route('inventory.index'))->assertOk()->assertSee('Toilet paper');
        $this->actingAs($this->housekeeper)->get(route('inventory.show', $item))->assertOk()->assertSee('Opening stock');
        $this->actingAs($this->storekeeper)->get(route('inventory.movements'))->assertOk()->assertSee('Toilet paper');
        $this->actingAs($this->storekeeper)->get(route('dashboard'))->assertOk();
    }
}
