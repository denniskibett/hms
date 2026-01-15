@extends('layouts.app')

@section('title', 'Finance Dashboard')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-gray-800 dark:text-white/90">
                Finance Dashboard
            </h1>
            <p class="text-sm text-gray-500 dark:text-gray-400">
                Overview of your financial performance
            </p>
        </div>
        
        <div class="flex items-center gap-3">
            <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#addTransactionModal">
                <i class="fas fa-plus mr-2"></i> Add Transaction
            </button>
            
            <div class="relative">
                <button
                    class="hover:text-dark-900 shadow-theme-xs relative flex h-11 items-center justify-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-3 whitespace-nowrap text-gray-700 transition-colors hover:bg-gray-100 hover:text-gray-700 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-400 dark:hover:bg-gray-800 dark:hover:text-white"
                    data-toggle="dropdown"
                >
                    <i class="fas fa-download mr-2"></i> Export
                </button>
                <div class="dropdown-menu dropdown-menu-right">
                    <a href="#" class="dropdown-item"><i class="fas fa-file-pdf mr-2"></i> PDF</a>
                    <a href="#" class="dropdown-item"><i class="fas fa-file-excel mr-2"></i> Excel</a>
                    <a href="#" class="dropdown-item"><i class="fas fa-file-csv mr-2"></i> CSV</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4">
        <!-- Today's Revenue -->
        <div class="rounded-xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Today's Revenue</p>
                    <p class="text-2xl font-semibold text-gray-800 dark:text-white/90 mt-1">
                        KSH {{ number_format($stats['today_revenue'], 2) }}
                    </p>
                </div>
                <div class="rounded-full bg-blue-50 p-3 dark:bg-blue-500/10">
                    <i class="fas fa-money-bill-wave text-blue-600 dark:text-blue-400 text-xl"></i>
                </div>
            </div>
            <a href="{{ route('payments.index') }}" class="mt-4 inline-flex items-center text-sm text-blue-600 hover:text-blue-700 dark:text-blue-400">
                View details
                <i class="fas fa-arrow-right ml-1.5"></i>
            </a>
        </div>

        <!-- Today's Payments -->
        <div class="rounded-xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Today's Payments</p>
                    <p class="text-2xl font-semibold text-gray-800 dark:text-white/90 mt-1">
                        KSH {{ number_format($stats['today_payments'], 2) }}
                    </p>
                </div>
                <div class="rounded-full bg-green-50 p-3 dark:bg-green-500/10">
                    <i class="fas fa-credit-card text-green-600 dark:text-green-400 text-xl"></i>
                </div>
            </div>
            <a href="{{ route('payments.index') }}" class="mt-4 inline-flex items-center text-sm text-green-600 hover:text-green-700 dark:text-green-400">
                View details
                <i class="fas fa-arrow-right ml-1.5"></i>
            </a>
        </div>

        <!-- Pending Invoices -->
        <div class="rounded-xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Pending Invoices</p>
                    <p class="text-2xl font-semibold text-gray-800 dark:text-white/90 mt-1">
                        KSH {{ number_format($stats['pending_invoices'], 2) }}
                    </p>
                </div>
                <div class="rounded-full bg-yellow-50 p-3 dark:bg-yellow-500/10">
                    <i class="fas fa-file-invoice-dollar text-yellow-600 dark:text-yellow-400 text-xl"></i>
                </div>
            </div>
            <a href="{{ route('invoices.index') }}" class="mt-4 inline-flex items-center text-sm text-yellow-600 hover:text-yellow-700 dark:text-yellow-400">
                View details
                <i class="fas fa-arrow-right ml-1.5"></i>
            </a>
        </div>

        <!-- Overdue Invoices -->
        <div class="rounded-xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Overdue Invoices</p>
                    <p class="text-2xl font-semibold text-gray-800 dark:text-white/90 mt-1">
                        KSH {{ number_format($stats['overdue_invoices'], 2) }}
                    </p>
                </div>
                <div class="rounded-full bg-red-50 p-3 dark:bg-red-500/10">
                    <i class="fas fa-exclamation-triangle text-red-600 dark:text-red-400 text-xl"></i>
                </div>
            </div>
            <a href="{{ route('invoices.index') }}?status=overdue" class="mt-4 inline-flex items-center text-sm text-red-600 hover:text-red-700 dark:text-red-400">
                View details
                <i class="fas fa-arrow-right ml-1.5"></i>
            </a>
        </div>
    </div>

    <!-- Main Content -->
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
        <!-- Account Balances -->
        <div class="rounded-xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="border-b border-gray-200 px-5 py-4 dark:border-gray-800">
                <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90">
                    Account Balances
                </h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="border-b border-gray-200 dark:border-gray-800">
                            <th class="p-4 text-left text-sm font-medium text-gray-500 dark:text-gray-400">Account</th>
                            <th class="p-4 text-left text-sm font-medium text-gray-500 dark:text-gray-400">Type</th>
                            <th class="p-4 text-left text-sm font-medium text-gray-500 dark:text-gray-400">Balance</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-800">
                        @foreach($accounts as $account)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-900 transition-colors">
                            <td class="p-4">
                                <p class="font-medium text-gray-700 dark:text-gray-300">{{ $account->name }}</p>
                                <p class="text-sm text-gray-500 dark:text-gray-400">{{ $account->code }}</p>
                            </td>
                            <td class="p-4">
                                <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium
                                    {{ $account->account_type == 'asset' ? 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300' : 
                                       ($account->account_type == 'liability' ? 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-300' : 
                                       'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300') }}">
                                    {{ ucfirst($account->account_type) }}
                                </span>
                            </td>
                            <td class="p-4">
                                <p class="font-medium {{ $account->balance >= 0 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">
                                    KSH {{ number_format($account->balance, 2) }}
                                </p>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="bg-gray-50 dark:bg-gray-900">
                        <tr class="border-t border-gray-200 dark:border-gray-800">
                            <td class="p-4 font-medium text-gray-700 dark:text-gray-300" colspan="2">Total Balance</td>
                            <td class="p-4 font-medium text-gray-700 dark:text-gray-300">
                                KSH {{ number_format($accounts->sum('balance'), 2) }}
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        <!-- Recent Transactions -->
        <div class="rounded-xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="border-b border-gray-200 px-5 py-4 dark:border-gray-800">
                <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90">
                    Recent Transactions
                </h3>
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    Latest financial transactions
                </p>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="border-b border-gray-200 dark:border-gray-800">
                            <th class="p-4 text-left text-sm font-medium text-gray-500 dark:text-gray-400">Date</th>
                            <th class="p-4 text-left text-sm font-medium text-gray-500 dark:text-gray-400">Description</th>
                            <th class="p-4 text-left text-sm font-medium text-gray-500 dark:text-gray-400">Amount</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-800">
                        @foreach($recentTransactions as $transaction)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-900 transition-colors">
                            <td class="p-4">
                                <p class="text-sm text-gray-700 dark:text-gray-300">
                                    {{ $transaction->transaction_date->format('M d') }}
                                </p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">
                                    {{ $transaction->transaction_date->format('Y') }}
                                </p>
                            </td>
                            <td class="p-4">
                                <p class="font-medium text-gray-700 dark:text-gray-300">{{ $transaction->description }}</p>
                                <p class="text-sm text-gray-500 dark:text-gray-400">
                                    {{ $transaction->account->name }}
                                    @if($transaction->category)
                                    • {{ ucfirst($transaction->category) }}
                                    @endif
                                </p>
                            </td>
                            <td class="p-4">
                                <span class="font-medium {{ $transaction->type == 'income' || $transaction->type == 'capital' ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">
                                    {{ $transaction->type == 'income' || $transaction->type == 'capital' ? '+' : '-' }}
                                    KSH {{ number_format($transaction->amount, 2) }}
                                </span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="border-t border-gray-200 px-5 py-4 dark:border-gray-800">
                <div class="flex justify-center gap-3">
                    <a href="{{ route('payments.index') }}" class="btn btn-outline">
                        <i class="fas fa-chart-bar mr-2"></i> View Reports
                    </a>
                    <a href="{{ route('payments.index') }}" class="btn btn-outline">
                        <i class="fas fa-list mr-2"></i> All Transactions
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="rounded-xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
        <div class="border-b border-gray-200 px-5 py-4 dark:border-gray-800">
            <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90">
                Quick Actions
            </h3>
            <p class="text-sm text-gray-500 dark:text-gray-400">
                Common financial operations
            </p>
        </div>
        <div class="p-5">
            <div class="grid grid-cols-2 gap-4 md:grid-cols-4">
                <button type="button" 
                        class="flex flex-col items-center justify-center gap-3 rounded-lg border border-gray-200 p-6 text-center transition-colors hover:border-gray-300 hover:bg-gray-50 dark:border-gray-800 dark:hover:border-gray-700 dark:hover:bg-gray-900"
                        data-toggle="modal" 
                        data-target="#createInvoiceModal">
                    <div class="rounded-full bg-blue-50 p-4 dark:bg-blue-500/10">
                        <i class="fas fa-file-invoice-dollar text-2xl text-blue-600 dark:text-blue-400"></i>
                    </div>
                    <p class="font-medium text-gray-700 dark:text-gray-300">Create Invoice</p>
                </button>

                <a href="{{ route('finance.create') }}" 
                   class="flex flex-col items-center justify-center gap-3 rounded-lg border border-gray-200 p-6 text-center transition-colors hover:border-gray-300 hover:bg-gray-50 dark:border-gray-800 dark:hover:border-gray-700 dark:hover:bg-gray-900">
                    <div class="rounded-full bg-red-50 p-4 dark:bg-red-500/10">
                        <i class="fas fa-receipt text-2xl text-red-600 dark:text-red-400"></i>
                    </div>
                    <p class="font-medium text-gray-700 dark:text-gray-300">Record Expense</p>
                </a>

                <button type="button" 
                        class="flex flex-col items-center justify-center gap-3 rounded-lg border border-gray-200 p-6 text-center transition-colors hover:border-gray-300 hover:bg-gray-50 dark:border-gray-800 dark:hover:border-gray-700 dark:hover:bg-gray-900"
                        data-toggle="modal" 
                        data-target="#recordInvestmentModal">
                    <div class="rounded-full bg-green-50 p-4 dark:bg-green-500/10">
                        <i class="fas fa-hand-holding-usd text-2xl text-green-600 dark:text-green-400"></i>
                    </div>
                    <p class="font-medium text-gray-700 dark:text-gray-300">Record Investment</p>
                </button>

                <button type="button" 
                        class="flex flex-col items-center justify-center gap-3 rounded-lg border border-gray-200 p-6 text-center transition-colors hover:border-gray-300 hover:bg-gray-50 dark:border-gray-800 dark:hover:border-gray-700 dark:hover:bg-gray-900"
                        data-toggle="modal" 
                        data-target="#transferFundsModal">
                    <div class="rounded-full bg-yellow-50 p-4 dark:bg-yellow-500/10">
                        <i class="fas fa-exchange-alt text-2xl text-yellow-600 dark:text-yellow-400"></i>
                    </div>
                    <p class="font-medium text-gray-700 dark:text-gray-300">Transfer Funds</p>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modals (Keep existing modals, just update styling if needed) -->
{{-- @include('finance.modals.add-transaction')
@include('finance.modals.create-invoice')
@include('finance.modals.record-investment')
@include('finance.modals.transfer-funds') --}}
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Initialize Select2
    $('.select2').select2({
        theme: 'bootstrap4',
        placeholder: 'Select...'
    });
    
    // Add invoice item
    let itemCount = 1;
    $('#add-item-btn').click(function() {
        const itemHtml = `
        <div class="invoice-item row mb-2">
            <div class="col-md-5">
                <input type="text" class="form-control" name="items[${itemCount}][description]" placeholder="Description" required>
            </div>
            <div class="col-md-2">
                <input type="number" class="form-control" name="items[${itemCount}][quantity]" placeholder="Qty" step="0.01" min="0.01" required>
            </div>
            <div class="col-md-2">
                <input type="number" class="form-control" name="items[${itemCount}][unit_price]" placeholder="Unit Price" step="0.01" min="0" required>
            </div>
            <div class="col-md-2">
                <select class="form-control" name="items[${itemCount}][source_type]" required>
                    <option value="room">Room</option>
                    <option value="food">Food</option>
                    <option value="service">Service</option>
                    <option value="other">Other</option>
                </select>
            </div>
            <div class="col-md-1">
                <button type="button" class="btn btn-danger remove-item"><i class="fas fa-trash"></i></button>
            </div>
        </div>`;
        $('#invoice-items-container').append(itemHtml);
        itemCount++;
    });
    
    // Remove invoice item
    $(document).on('click', '.remove-item', function() {
        $(this).closest('.invoice-item').remove();
    });
});
</script>
@endpush