@extends('layouts.app')

@section('content')
<div class="p-4 mx-auto max-w-screen-2xl md:p-6">
    <!-- Header -->
    <div class="mb-6">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-2xl font-bold text-gray-800 dark:text-white/90">Transaction Details</h2>
                <p class="text-gray-600 dark:text-gray-400">View transaction information</p>
            </div>
            <div class="flex items-center space-x-2">
                @can('update', $transaction)
                <a href="{{ route('finance.edit', $transaction) }}" class="px-4 py-2 bg-warning text-white rounded hover:bg-warning/90">
                    Edit Transaction
                </a>
                @endcan
                <a href="{{ route('finance.index') }}" class="px-4 py-2 border border-gray-300 dark:border-gray-700 rounded text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800">
                    Back to List
                </a>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-12 gap-6">
        <!-- Transaction Details -->
        <div class="col-span-12 lg:col-span-8">
            <div class="bg-white rounded-lg border border-gray-200 dark:bg-gray-800 dark:border-gray-700 shadow-sm mb-6">
                <div class="p-6">
                    <div class="flex items-center justify-between mb-6">
                        <div>
                            <h3 class="text-xl font-bold text-gray-800 dark:text-white">Transaction #{{ $transaction->id }}</h3>
                            <p class="text-gray-600 dark:text-gray-400">{{ $transaction->date->format('F d, Y') }}</p>
                        </div>
                        <div class="text-right">
                            <p class="text-3xl font-bold 
                                @if($transaction->type === 'income') text-success 
                                @elseif($transaction->type === 'expense') text-danger 
                                @else text-warning @endif">
                                @if($transaction->type === 'income')+ @elseif($transaction->type === 'expense')- @endif${{ number_format($transaction->amount, 2) }}
                            </p>
                            <span class="px-3 py-1 text-sm rounded-full 
                                @if($transaction->type === 'income') bg-success/10 text-success 
                                @elseif($transaction->type === 'expense') bg-danger/10 text-danger 
                                @else bg-warning/10 text-warning @endif">
                                {{ ucfirst($transaction->type) }}
                            </span>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-600 dark:text-gray-400 mb-1">Category</label>
                            <p class="text-gray-800 dark:text-white font-medium">{{ $transaction->category }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-600 dark:text-gray-400 mb-1">Status</label>
                            <span class="px-3 py-1 text-sm rounded-full 
                                @if($transaction->status === 'completed') bg-success/10 text-success 
                                @elseif($transaction->status === 'pending') bg-warning/10 text-warning 
                                @else bg-danger/10 text-danger @endif">
                                {{ ucfirst($transaction->status) }}
                            </span>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-600 dark:text-gray-400 mb-1">Payment Method</label>
                            <p class="text-gray-800 dark:text-white font-medium">{{ $transaction->payment_method ?? 'Not specified' }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-600 dark:text-gray-400 mb-1">Reference</label>
                            <p class="text-gray-800 dark:text-white font-medium">{{ $transaction->reference ?? 'N/A' }}</p>
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-600 dark:text-gray-400 mb-1">Description</label>
                            <p class="text-gray-800 dark:text-white">{{ $transaction->description }}</p>
                        </div>
                    </div>

                    <!-- Timestamps -->
                    <div class="mt-8 pt-6 border-t border-gray-200 dark:border-gray-700">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
                            <div>
                                <label class="block text-gray-600 dark:text-gray-400">Created At</label>
                                <p class="text-gray-800 dark:text-white">{{ $transaction->created_at->format('M d, Y h:i A') }}</p>
                            </div>
                            <div>
                                <label class="block text-gray-600 dark:text-gray-400">Updated At</label>
                                <p class="text-gray-800 dark:text-white">{{ $transaction->updated_at->format('M d, Y h:i A') }}</p>
                            </div>
                            <div>
                                <label class="block text-gray-600 dark:text-gray-400">Created By</label>
                                <p class="text-gray-800 dark:text-white">{{ $transaction->user->name ?? 'System' }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Attachment -->
            @if($transaction->attachment)
            <div class="bg-white rounded-lg border border-gray-200 dark:bg-gray-800 dark:border-gray-700 shadow-sm">
                <div class="p-6">
                    <h4 class="text-lg font-semibold text-gray-800 dark:text-white/90 mb-4">Attachment</h4>
                    <div class="flex items-center p-4 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
                        <i class="fas fa-file-pdf text-danger text-2xl mr-3"></i>
                        <div>
                            <p class="text-gray-800 dark:text-white font-medium">Invoice_{{ $transaction->id }}.pdf</p>
                            <p class="text-sm text-gray-600 dark:text-gray-400">Uploaded on {{ $transaction->created_at->format('M d, Y') }}</p>
                        </div>
                        <a href="{{ asset('storage/' . $transaction->attachment) }}" target="_blank" class="ml-auto text-primary hover:text-primary/80">
                            <i class="fas fa-download"></i> Download
                        </a>
                    </div>
                </div>
            </div>
            @endif
        </div>

        <!-- Sidebar -->
        <div class="col-span-12 lg:col-span-4">
            <!-- Quick Actions -->
            <div class="bg-white rounded-lg border border-gray-200 dark:bg-gray-800 dark:border-gray-700 shadow-sm p-6 mb-6">
                <h4 class="text-lg font-semibold text-gray-800 dark:text-white/90 mb-4">Quick Actions</h4>
                <div class="space-y-3">
                    @can('update', $transaction)
                    <a href="{{ route('finance.edit', $transaction) }}" class="flex items-center p-3 bg-warning/10 text-warning rounded hover:bg-warning/20">
                        <i class="fas fa-edit mr-2"></i>
                        <span>Edit Transaction</span>
                    </a>
                    @endcan
                    
                    <a href="{{ route('finance.index', ['type' => $transaction->type]) }}" class="flex items-center p-3 bg-primary/10 text-primary rounded hover:bg-primary/20">
                        <i class="fas fa-list mr-2"></i>
                        <span>View Similar Transactions</span>
                    </a>
                    
                    <button onclick="window.print()" class="w-full flex items-center p-3 bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-white rounded hover:bg-gray-200 dark:hover:bg-gray-600">
                        <i class="fas fa-print mr-2"></i>
                        <span>Print Receipt</span>
                    </button>
                </div>
            </div>

            <!-- Danger Zone -->
            @can('delete', $transaction)
            <div class="bg-white rounded-lg border border-red-200 dark:bg-gray-800 dark:border-red-900 shadow-sm">
                <div class="p-6">
                    <h4 class="text-lg font-semibold text-red-700 dark:text-red-400 mb-2">Danger Zone</h4>
                    <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
                        Deleting this transaction is permanent and cannot be undone.
                    </p>
                    <form action="{{ route('finance.destroy', $transaction) }}" method="POST" 
                          onsubmit="return confirm('Are you sure you want to delete this transaction? This action cannot be undone.');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="w-full px-4 py-2 bg-danger text-white rounded hover:bg-danger/90">
                            <i class="fas fa-trash mr-1"></i> Delete Transaction
                        </button>
                    </form>
                </div>
            </div>
            @endcan
        </div>
    </div>
</div>
@endsection