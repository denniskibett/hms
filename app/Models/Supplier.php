<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Supplier extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'contact_person',
        'phone',
        'email',
        'kra_pin',
        'address',
        'payment_terms',
        'notes',
        'status',
    ];

    public function purchaseOrders()
    {
        return $this->hasMany(PurchaseOrder::class);
    }

    public function prices()
    {
        return $this->hasMany(SupplierPrice::class);
    }

    /**
     * Items this supplier is the usual source of.
     */
    public function items()
    {
        return $this->hasMany(InventoryItem::class, 'primary_supplier_id');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active');
    }

    public function scopeSearch(Builder $query, string $term): Builder
    {
        return $query->where(fn ($q) => $q
            ->where('name', 'like', "%{$term}%")
            ->orWhere('contact_person', 'like', "%{$term}%")
            ->orWhere('phone', 'like', "%{$term}%"));
    }

    /**
     * Latest price paid for each item: [inventory_item_id => unit_price].
     */
    public function latestPrices(): array
    {
        return $this->prices()
            ->orderBy('effective_from')
            ->get()
            ->mapWithKeys(fn (SupplierPrice $p) => [$p->inventory_item_id => (float) $p->unit_price])
            ->all();
    }

    public function formData(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'contact_person' => $this->contact_person ?? '',
            'phone' => $this->phone ?? '',
            'email' => $this->email ?? '',
            'kra_pin' => $this->kra_pin ?? '',
            'address' => $this->address ?? '',
            'payment_terms' => $this->payment_terms ?? '',
            'notes' => $this->notes ?? '',
            'status' => $this->status,
        ];
    }
}
