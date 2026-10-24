<?php

namespace App\Models;

use App\Exceptions\StockException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

/**
 * Something the hotel keeps in stock. `quantity` is the running balance and
 * only changes through move(), which records an InventoryTransaction.
 * `unit_cost` is the weighted average cost of what is on hand.
 */
class InventoryItem extends Model
{
    use HasFactory;

    public const TYPES = [
        'cleaning' => 'Cleaning',
        'amenity' => 'Guest amenities',
        'linen' => 'Linen',
        'food' => 'Food',
        'beverage' => 'Beverages',
        'kitchen' => 'Kitchen supplies',
        'maintenance' => 'Maintenance',
        'office' => 'Office',
        'other' => 'Other',
    ];

    public const UNITS = ['pcs', 'kg', 'g', 'litre', 'ml', 'bottle', 'packet', 'box', 'roll', 'crate', 'bag', 'tray', 'set', 'pair'];

    protected $fillable = [
        'name',
        'sku',
        'barcode',
        'item_type',
        'category',
        'description',
        'unit_of_measure',
        'reorder_level',
        'primary_supplier_id',
        'location',
        'status',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'reorder_level' => 'decimal:2',
        'unit_cost' => 'decimal:2',
        'status' => 'boolean',
    ];

    public function primarySupplier()
    {
        return $this->belongsTo(Supplier::class, 'primary_supplier_id');
    }

    public function transactions()
    {
        return $this->hasMany(InventoryTransaction::class);
    }

    public function purchaseOrderItems()
    {
        return $this->hasMany(PurchaseOrderItem::class);
    }

    public function taskItems()
    {
        return $this->hasMany(TaskItem::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', true);
    }

    public function scopeLowStock(Builder $query): Builder
    {
        return $query->whereColumn('quantity', '<=', 'reorder_level');
    }

    public function scopeSearch(Builder $query, string $term): Builder
    {
        return $query->where(fn ($q) => $q
            ->where('name', 'like', "%{$term}%")
            ->orWhere('sku', 'like', "%{$term}%")
            ->orWhere('barcode', $term));
    }

    public function getIsLowAttribute(): bool
    {
        return (float) $this->quantity <= (float) $this->reorder_level;
    }

    public function getStockValueAttribute(): float
    {
        return round((float) $this->quantity * (float) $this->unit_cost, 2);
    }

    public function getTypeLabelAttribute(): string
    {
        return self::TYPES[$this->item_type] ?? ucfirst((string) $this->item_type);
    }

    /**
     * "12 kg", "3.5 litre"
     */
    public function qty(float|string|null $amount = null): string
    {
        $amount ??= $this->quantity;

        return rtrim(rtrim(number_format((float) $amount, 2), '0'), '.') . ' ' . $this->unit_of_measure;
    }

    /**
     * Change stock and record why. Positive quantity adds, negative takes away.
     * Stock-in at a cost updates the average cost. Refuses to go below zero.
     *
     * @param  array{unit_cost?: float, reference_type?: string, reference_id?: int, department_id?: int, notes?: string}  $details
     */
    public function move(string $type, float $quantity, User $by, array $details = []): InventoryTransaction
    {
        return DB::transaction(function () use ($type, $quantity, $by, $details) {
            $item = self::whereKey($this->id)->lockForUpdate()->firstOrFail();
            $quantity = round($quantity, 2);
            $balance = round((float) $item->quantity + $quantity, 2);

            if ($quantity == 0) {
                throw new StockException('The quantity can\'t be zero.');
            }
            if ($balance < 0) {
                throw new StockException("Only {$item->qty()} of {$item->name} in stock.");
            }

            $unitCost = (float) $item->unit_cost;
            if ($quantity > 0 && isset($details['unit_cost']) && $type === 'in') {
                // Weighted average cost of what is on hand after receiving
                $incoming = (float) $details['unit_cost'];
                $unitCost = $balance > 0
                    ? round(((float) $item->quantity * (float) $item->unit_cost + $quantity * $incoming) / $balance, 2)
                    : $incoming;
            }

            $item->forceFill(['quantity' => $balance, 'unit_cost' => $unitCost])->save();
            $this->setRawAttributes($item->getAttributes(), true);

            return $item->transactions()->create([
                'type' => $type,
                'quantity' => $quantity,
                'balance_after' => $balance,
                'unit_cost' => $details['unit_cost'] ?? $unitCost,
                'total_value' => round(abs($quantity) * ($details['unit_cost'] ?? $unitCost), 2),
                'reference_type' => $details['reference_type'] ?? 'manual',
                'reference_id' => $details['reference_id'] ?? null,
                'department_id' => $details['department_id'] ?? null,
                'notes' => $details['notes'] ?? null,
                'created_by' => $by->id,
            ]);
        });
    }

    public function formData(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'sku' => $this->sku,
            'barcode' => $this->barcode ?? '',
            'item_type' => $this->item_type,
            'unit_of_measure' => $this->unit_of_measure,
            'reorder_level' => (float) $this->reorder_level,
            'location' => $this->location ?? '',
            'description' => $this->description ?? '',
            'status' => $this->status,
        ];
    }
}
