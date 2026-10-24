<?php

namespace App\Services;

use App\Models\KitchenOrder;

/**
 * Read-only kitchen queries still used by DashboardController. Orders are
 * placed and moved in KitchenController; remove this when the dashboards are rebuilt.
 */
class KitchenService
{
    public function getPendingOrdersCount(): array
    {
        return [
            'pending' => KitchenOrder::where('status', 'pending')->count(),
            'preparing' => KitchenOrder::where('status', 'preparing')->count(),
            'ready' => KitchenOrder::where('status', 'ready')->count(),
            'completed' => KitchenOrder::where('status', 'delivered')->count(),
            'total_today' => KitchenOrder::whereDate('created_at', today())->count(),
        ];
    }

    public function getPreparingOrdersCount(): int
    {
        return KitchenOrder::where('status', 'preparing')->count();
    }

    public function getPendingOrders()
    {
        return KitchenOrder::with(['stay.guest', 'stay.roomAllocations.room', 'items.variant.menuItem', 'placer'])
            ->where('status', 'pending')
            ->latest()
            ->get();
    }

    public function getPreparingOrders()
    {
        return KitchenOrder::with(['stay.guest', 'stay.roomAllocations.room', 'items.variant.menuItem', 'placer'])
            ->where('status', 'preparing')
            ->latest()
            ->get();
    }

    public function getCompletedOrdersCount($date = null): int
    {
        return KitchenOrder::where('status', 'delivered')
            ->when($date, fn ($q) => $q->whereDate('created_at', $date))
            ->count();
    }
}
