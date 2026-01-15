<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\InventoryItem;
use App\Models\PurchaseOrder;
use App\Services\OperationsService;
use App\Services\FinanceService;

class InventoryController extends Controller
{
    public function __construct(
        private OperationsService $operationsService,
        private FinanceService $financeService
    ) {}
    
    public function index(Request $request)
    {
        // $this->authorize('viewAny', InventoryItem::class);
        
        $query = InventoryItem::query();
        
        // Apply filters
        if ($request->has('item_type')) {
            $query->where('item_type', $request->input('item_type'));
        }
        
        if ($request->has('category')) {
            $query->where('category', $request->input('category'));
        }
        
        if ($request->has('low_stock')) {
            $query->whereColumn('quantity', '<=', 'reorder_level');
        }
        
        if ($request->has('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->input('search') . '%')
                  ->orWhere('sku', 'like', '%' . $request->input('search') . '%');
            });
        }
        
        $items = $query->latest()->paginate(20);
        $categories = InventoryItem::pluck('category');
        $locations = InventoryItem::pluck('location');
        
        return view('inventory.index', compact('items', 'categories', 'locations'));
    }
    
    public function lowStock()
    {
        $this->authorize('viewAny', InventoryItem::class);
        
        $items = $this->operationsService->getLowStockItems();
        
        return view('inventory.low-stock', compact('items'));
    }
    
    public function transactions(Request $request)
    {
        $this->authorize('viewTransactions', InventoryItem::class);
        
        $transactions = $this->operationsService->getInventoryTransactions($request->all());
        
        return view('inventory.transactions', compact('transactions'));
    }
    
    public function updateStock(InventoryItem $item, Request $request)
    {
        $this->authorize('update', $item);
        
        $request->validate([
            'operation' => 'required|in:add,subtract,set',
            'quantity' => 'required|numeric|min:0.01',
            'reason' => 'required|string|max:255',
        ]);
        
        try {
            $transaction = $this->operationsService->updateInventoryStock(
                $item,
                $request->input('operation'),
                $request->input('quantity'),
                $request->input('reason')
            );
            
            return redirect()->route('inventory.show', $item)
                ->with('success', 'Stock updated successfully.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Error updating stock: ' . $e->getMessage());
        }
    }
    
    public function purchaseOrders(Request $request)
    {
        $this->authorize('viewAny', PurchaseOrder::class);
        
        $query = PurchaseOrder::with(['supplier', 'requester']);
        
        if ($request->has('status')) {
            $query->where('status', $request->input('status'));
        }
        
        $orders = $query->latest()->paginate(20);
        
        return view('inventory.purchase-orders.index', compact('orders'));
    }
    
    public function approvePurchaseOrder(PurchaseOrder $order)
    {
        $this->authorize('approve', $order);
        
        try {
            $order = $this->operationsService->approvePurchaseOrder($order, auth()->user());
            
            return redirect()->route('inventory.purchase-orders.show', $order)
                ->with('success', 'Purchase order approved successfully.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Error approving purchase order: ' . $e->getMessage());
        }
    }
    
    public function receivePurchaseOrder(PurchaseOrder $order, Request $request)
    {
        $this->authorize('receive', $order);
        
        try {
            $order = $this->operationsService->receivePurchaseOrder($order, $request->input('received_items', []));
            
            return redirect()->route('inventory.purchase-orders.show', $order)
                ->with('success', 'Purchase order received successfully.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Error receiving purchase order: ' . $e->getMessage());
        }
    }
}