@extends('layouts.app')

@section('title', 'Payment ' . $payment->payment_reference)

@section('content')
<div class="container-fluid px-0">
    <!-- Breadcrumb -->
    <div class="flex flex-wrap items-center justify-between gap-3 pb-6">
        <h2 class="text-xl font-semibold text-gray-800 dark:text-white/90">Payment Details</h2>
        <nav>
            <ol class="flex items-center gap-1.5">
                <li>
                    <a class="inline-flex items-center gap-1.5 text-sm text-gray-500 dark:text-gray-400" href="{{ route('finance.dashboard') }}">
                        Dashboard
                        <svg class="stroke-current" width="17" height="16" viewBox="0 0 17 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M6.0765 12.667L10.2432 8.50033L6.0765 4.33366" stroke="" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </a>
                </li>
                <li>
                    <a class="inline-flex items-center gap-1.5 text-sm text-gray-500 dark:text-gray-400" href="{{ route('finance.payments.index') }}">
                        Payments
                        <svg class="stroke-current" width="17" height="16" viewBox="0 0 17 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M6.0765 12.667L10.2432 8.50033L6.0765 4.33366" stroke="" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </a>
                </li>
                <li class="text-sm text-gray-800 dark:text-white/90">{{ $payment->payment_reference }}</li>
            </ol>
        </nav>
    </div>

    <!-- Payment Header -->
    <div class="flex flex-col justify-between gap-6 rounded-2xl border border-gray-200 bg-white px-6 py-5 sm:flex-row sm:items-center dark:border-gray-800 dark:bg-white/3">
        <div class="flex flex-col gap-2.5 divide-gray-300 sm:flex-row sm:divide-x dark:divide-gray-700">
            <div class="flex items-center gap-2 sm:pr-3">
                <span class="text-base font-medium text-gray-700 dark:text-gray-400">
                    Payment ID: {{ $payment->payment_reference }}
                </span>
                <span class="text-theme-xs rounded-full px-2 py-0.5 font-medium 
                    {{ $payment->status === 'completed' ? 'bg-success-50 text-success-600 dark:bg-success-500/15 dark:text-success-500' : 
                       ($payment->status === 'pending' ? 'bg-warning-50 dark:bg-warning-500/15 text-warning-700 dark:text-warning-500' : 
                       'bg-error-50 text-error-600 dark:bg-error-500/15 dark:text-error-500') }}">
                    {{ ucfirst($payment->status) }}
                </span>
            </div>
            <p class="text-sm text-gray-500 sm:pl-3 dark:text-gray-400">
                Date: {{ $payment->received_at->format('F d, Y') }}
            </p>
        </div>
        <div class="flex gap-3">
            <a href="{{ route('finance.payments.receipt', $payment) }}" target="_blank" class="bg-brand-500 shadow-theme-xs hover:bg-brand-600 inline-flex items-center justify-center gap-2 rounded-lg px-4 py-3 text-sm font-medium text-white transition">
                View Receipt
            </a>
            @if($payment->amount > 0)
            <button type="button" class="shadow-theme-xs inline-flex items-center justify-center gap-2 rounded-lg bg-white px-4 py-3 text-sm font-medium text-gray-700 ring-1 ring-gray-300 transition hover:bg-gray-50 dark:bg-gray-800 dark:text-gray-400 dark:ring-gray-700 dark:hover:bg-white/[0.03]" data-toggle="modal" data-target="#refundModal">
                Refund
            </button>
            @endif
            @if($payment->created_at->diffInHours(now()) <= 24)
            <form action="{{ route('finance.payments.destroy', $payment) }}" method="POST" class="inline">
                @csrf @method('DELETE')
                <button type="submit" class="shadow-theme-xs inline-flex items-center justify-center gap-2 rounded-lg bg-white px-4 py-3 text-sm font-medium text-error-600 ring-1 ring-error-200 transition hover:bg-error-50 dark:bg-gray-800 dark:text-error-400 dark:ring-error-800 dark:hover:bg-error-500/10" onclick="return confirm('Are you sure you want to delete this payment?')">
                    Delete
                </button>
            </form>
            @endif
        </div>
    </div>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-12 mt-6">
        <div class="lg:col-span-8 2xl:col-span-9">
            <!-- Payment Details -->
            <div class="rounded-2xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-white/3">
                <h2 class="mb-5 text-lg font-semibold text-gray-800 dark:text-white/90">Payment Details</h2>
                
                @if($payment->invoice)
                <!-- Invoice Details -->
                <div class="mb-6 overflow-hidden rounded-2xl border border-gray-100 dark:border-gray-800">
                    <div class="custom-scrollbar overflow-x-auto">
                        <table class="min-w-full text-left text-sm text-gray-700 dark:border-gray-800">
                            <thead class="bg-gray-50 dark:bg-gray-900">
                                <tr class="border-b border-gray-100 whitespace-nowrap dark:border-gray-800">
                                    <th class="px-5 py-4 text-sm font-medium whitespace-nowrap text-gray-700 dark:text-gray-400">S. No.</th>
                                    <th class="px-5 py-4 text-sm font-medium whitespace-nowrap text-gray-500 dark:text-gray-400">Description</th>
                                    <th class="px-5 py-4 text-sm font-medium whitespace-nowrap text-gray-700 dark:text-gray-400">Quantity</th>
                                    <th class="px-5 py-4 text-sm font-medium whitespace-nowrap text-gray-700 dark:text-gray-400">Unit Price</th>
                                    <th class="px-5 py-4 text-sm font-medium whitespace-nowrap text-gray-700 dark:text-gray-400">Type</th>
                                    <th class="px-5 py-4 text-sm font-medium whitespace-nowrap text-gray-700 dark:text-gray-400">Total</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 bg-white dark:divide-gray-800 dark:bg-white/[0.03]">
                                @foreach($payment->invoice->items as $item)
                                <tr>
                                    <td class="px-5 py-4 text-sm whitespace-nowrap text-gray-500 dark:text-gray-400">{{ $loop->iteration }}</td>
                                    <td class="px-5 py-4 text-sm font-medium whitespace-nowrap text-gray-800 dark:text-white/90">{{ $item->description }}</td>
                                    <td class="px-5 py-4 text-sm whitespace-nowrap text-gray-500 dark:text-gray-400">{{ number_format($item->quantity, 2) }}</td>
                                    <td class="px-5 py-4 text-sm whitespace-nowrap text-gray-500 dark:text-gray-400">KSH {{ number_format($item->unit_price, 2) }}</td>
                                    <td class="px-5 py-4 text-sm whitespace-nowrap text-gray-500 dark:text-gray-400">{{ ucfirst($item->source_type) }}</td>
                                    <td class="px-5 py-4 text-sm whitespace-nowrap text-gray-500 dark:text-gray-400">KSH {{ number_format($item->total, 2) }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="flex flex-wrap justify-between sm:justify-end">
                    <div class="mt-6 w-full space-y-1 text-right sm:w-[220px]">
                        <p class="mb-4 text-left text-sm font-medium text-gray-800 dark:text-white/90">Invoice Summary</p>
                        <ul class="space-y-2">
                            <li class="flex justify-between gap-5">
                                <span class="text-sm text-gray-500 dark:text-gray-400">Sub Total</span>
                                <span class="text-sm font-medium text-gray-700 dark:text-gray-400">KSH {{ number_format($payment->invoice->subtotal, 2) }}</span>
                            </li>
                            <li class="flex items-center justify-between">
                                <span class="text-sm text-gray-500 dark:text-gray-400">Tax (16%):</span>
                                <span class="text-sm font-medium text-gray-700 dark:text-gray-400">KSH {{ number_format($payment->invoice->tax_amount, 2) }}</span>
                            </li>
                            @if($payment->invoice->discount_amount > 0)
                            <li class="flex items-center justify-between">
                                <span class="text-sm text-gray-500 dark:text-gray-400">Discount:</span>
                                <span class="text-sm font-medium text-gray-700 dark:text-gray-400">-KSH {{ number_format($payment->invoice->discount_amount, 2) }}</span>
                            </li>
                            @endif
                            <li class="flex items-center justify-between">
                                <span class="font-medium text-gray-700 dark:text-gray-400">Total Invoice</span>
                                <span class="text-lg font-semibold text-gray-800 dark:text-white/90">KSH {{ number_format($payment->invoice->total, 2) }}</span>
                            </li>
                            <li class="flex items-center justify-between">
                                <span class="font-medium text-gray-700 dark:text-gray-400">Paid Amount</span>
                                <span class="text-lg font-semibold text-success-600 dark:text-success-500">KSH {{ number_format($payment->invoice->paid_amount, 2) }}</span>
                            </li>
                            <li class="flex items-center justify-between">
                                <span class="font-medium text-gray-700 dark:text-gray-400">Balance Due</span>
                                <span class="text-lg font-semibold text-error-600 dark:text-error-500">KSH {{ number_format($payment->invoice->due_amount, 2) }}</span>
                            </li>
                        </ul>
                    </div>
                </div>
                @else
                <!-- Standalone Payment Details -->
                <div class="text-center py-8">
                    <div class="mx-auto mb-4 flex h-20 w-20 items-center justify-center rounded-full bg-gray-100 dark:bg-gray-800">
                        <svg class="h-10 w-10 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <h3 class="mb-2 text-lg font-semibold text-gray-800 dark:text-white/90">Standalone Payment</h3>
                    <p class="text-gray-500 dark:text-gray-400">This payment is not linked to any invoice.</p>
                    
                    <div class="mt-6 grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div class="rounded-lg border border-gray-200 p-4 dark:border-gray-800">
                            <p class="text-sm text-gray-500 dark:text-gray-400">Payment Amount</p>
                            <p class="text-2xl font-semibold {{ $payment->amount >= 0 ? 'text-success-600 dark:text-success-500' : 'text-error-600 dark:text-error-500' }}">
                                {{ $payment->amount >= 0 ? '+' : '-' }} KSH {{ number_format(abs($payment->amount), 2) }}
                            </p>
                        </div>
                        <div class="rounded-lg border border-gray-200 p-4 dark:border-gray-800">
                            <p class="text-sm text-gray-500 dark:text-gray-400">Payment Method</p>
                            <p class="text-lg font-semibold text-gray-800 dark:text-white/90">{{ ucfirst(str_replace('_', ' ', $payment->method)) }}</p>
                        </div>
                    </div>
                </div>
                @endif
            </div>
        </div>

        <!-- Sidebar -->
        <div class="space-y-6 lg:col-span-4 2xl:col-span-3">
            <!-- Customer Details -->
            @if($payment->invoice && $payment->invoice->stay && $payment->invoice->stay->guest)
            <div class="rounded-2xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-white/3">
                <h2 class="mb-5 text-lg font-semibold text-gray-800 dark:text-white/90">Customer Details</h2>
                <ul class="divide-y divide-gray-100 dark:divide-gray-800">
                    <li class="flex items-start gap-5 py-2.5">
                        <span class="w-1/2 text-sm text-gray-500 sm:w-1/3 dark:text-gray-400">Name</span>
                        <span class="w-1/2 text-sm text-gray-700 sm:w-2/3 dark:text-gray-400">{{ $payment->invoice->stay->guest->name }}</span>
                    </li>
                    <li class="flex items-start gap-5 py-2.5">
                        <span class="w-1/2 text-sm text-gray-500 sm:w-1/3 dark:text-gray-400">Email</span>
                        <span class="w-1/2 text-sm text-gray-700 sm:w-2/3 dark:text-gray-400">{{ $payment->invoice->stay->guest->email }}</span>
                    </li>
                    <li class="flex items-start gap-5 py-2.5">
                        <span class="w-1/2 text-sm text-gray-500 sm:w-1/3 dark:text-gray-400">Phone</span>
                        <span class="w-1/2 text-sm text-gray-700 sm:w-2/3 dark:text-gray-400">{{ $payment->invoice->stay->guest->phone ?? 'N/A' }}</span>
                    </li>
                    @if($payment->invoice->stay->guest->guestProfile)
                    <li class="flex items-start gap-5 py-2.5">
                        <span class="w-1/2 text-sm text-gray-500 sm:w-1/3 dark:text-gray-400">ID Number</span>
                        <span class="w-1/2 text-sm text-gray-700 sm:w-2/3 dark:text-gray-400">{{ $payment->invoice->stay->guest->guestProfile->id_number }}</span>
                    </li>
                    @endif
                    <li class="flex items-start gap-5 py-2.5">
                        <span class="w-1/2 text-sm text-gray-500 sm:w-1/3 dark:text-gray-400">Stay Info</span>
                        <span class="w-1/2 text-sm text-gray-700 sm:w-2/3 dark:text-gray-400">
                            Stay #{{ $payment->invoice->stay->id }}<br>
                            Check-in: {{ $payment->invoice->stay->check_in_date->format('M d, Y') }}
                        </span>
                    </li>
                </ul>
            </div>
            @endif

            <!-- Payment Information -->
            <div class="rounded-2xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-white/3">
                <h2 class="mb-5 text-lg font-semibold text-gray-800 dark:text-white/90">Payment Information</h2>
                <ul class="divide-y divide-gray-100 dark:divide-gray-800">
                    <li class="flex items-start gap-5 py-2.5">
                        <span class="w-1/2 text-sm text-gray-500 sm:w-1/3 dark:text-gray-400">Reference</span>
                        <span class="w-1/2 text-sm text-gray-700 sm:w-2/3 dark:text-gray-400">{{ $payment->payment_reference }}</span>
                    </li>
                    <li class="flex items-start gap-5 py-2.5">
                        <span class="w-1/2 text-sm text-gray-500 sm:w-1/3 dark:text-gray-400">Date & Time</span>
                        <span class="w-1/2 text-sm text-gray-700 sm:w-2/3 dark:text-gray-400">
                            {{ $payment->received_at->format('M d, Y') }}<br>
                            <small class="text-gray-500">{{ $payment->received_at->format('h:i A') }}</small>
                        </span>
                    </li>
                    <li class="flex items-start gap-5 py-2.5">
                        <span class="w-1/2 text-sm text-gray-500 sm:w-1/3 dark:text-gray-400">Payment Method</span>
                        <span class="w-1/2 text-sm text-gray-700 sm:w-2/3 dark:text-gray-400">{{ ucfirst(str_replace('_', ' ', $payment->method)) }}</span>
                    </li>
                    <li class="flex items-start gap-5 py-2.5">
                        <span class="w-1/2 text-sm text-gray-500 sm:w-1/3 dark:text-gray-400">Status</span>
                        <span class="w-1/2 text-sm text-gray-700 sm:w-2/3 dark:text-gray-400">
                            <span class="text-theme-xs rounded-full px-2 py-0.5 font-medium 
                                {{ $payment->status === 'completed' ? 'bg-success-50 dark:bg-success-500/15 text-success-700 dark:text-success-500' : 
                                   ($payment->status === 'pending' ? 'bg-warning-50 dark:bg-warning-500/15 text-warning-700 dark:text-warning-500' : 
                                   'bg-error-50 text-error-600 dark:bg-error-500/15 dark:text-error-500') }}">
                                {{ ucfirst($payment->status) }}
                            </span>
                        </span>
                    </li>
                    <li class="flex items-start gap-5 py-2.5">
                        <span class="w-1/2 text-sm text-gray-500 sm:w-1/3 dark:text-gray-400">Received By</span>
                        <span class="w-1/2 text-sm text-gray-700 sm:w-2/3 dark:text-gray-400">{{ $payment->receiver->name ?? 'System' }}</span>
                    </li>
                    @if($payment->payment_details)
                    <li class="flex items-start gap-5 py-2.5">
                        <span class="w-1/2 text-sm text-gray-500 sm:w-1/3 dark:text-gray-400">Payment Details</span>
                        <span class="w-1/2 text-sm text-gray-700 sm:w-2/3 dark:text-gray-400">
                            @php
                                $details = json_decode(json_encode($payment->payment_details), true);
                            @endphp
                            @if(is_array($details))
                                @foreach($details as $key => $value)
                                    {{ ucfirst(str_replace('_', ' ', $key)) }}: {{ $value }}<br>
                                @endforeach
                            @else
                                {{ $payment->payment_details }}
                            @endif
                        </span>
                    </li>
                    @endif
                    @if($payment->notes)
                    <li class="flex items-start gap-5 py-2.5">
                        <span class="w-1/2 text-sm text-gray-500 sm:w-1/3 dark:text-gray-400">Notes</span>
                        <span class="w-1/2 text-sm text-gray-700 sm:w-2/3 dark:text-gray-400">{{ $payment->notes }}</span>
                    </li>
                    @endif
                </ul>
            </div>

            <!-- Related Invoice -->
            @if($payment->invoice)
            <div class="rounded-2xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-white/3">
                <h2 class="mb-5 text-lg font-semibold text-gray-800 dark:text-white/90">Related Invoice</h2>
                <div class="space-y-4">
                    <div>
                        <h3 class="mb-2 font-medium text-gray-800 dark:text-white/90">
                            <a href="{{ route('finance.invoices.show', $payment->invoice) }}" class="hover:underline">
                                {{ $payment->invoice->invoice_number }}
                            </a>
                        </h3>
                        <div class="space-y-2">
                            <div class="flex justify-between">
                                <span class="text-sm text-gray-500 dark:text-gray-400">Total Amount</span>
                                <span class="text-sm font-medium text-gray-700 dark:text-gray-400">KSH {{ number_format($payment->invoice->total, 2) }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-sm text-gray-500 dark:text-gray-400">Paid Amount</span>
                                <span class="text-sm font-medium text-success-600 dark:text-success-500">KSH {{ number_format($payment->invoice->paid_amount, 2) }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-sm text-gray-500 dark:text-gray-400">Balance Due</span>
                                <span class="text-sm font-medium text-error-600 dark:text-error-500">KSH {{ number_format($payment->invoice->due_amount, 2) }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="progress-group">
                        <div class="flex justify-between text-sm">
                            <span class="font-medium text-gray-700 dark:text-gray-400">Payment Progress</span>
                            <span class="font-medium text-gray-700 dark:text-gray-400">
                                {{ number_format(($payment->invoice->paid_amount / $payment->invoice->total) * 100, 0) }}%
                            </span>
                        </div>
                        <div class="mt-2 h-2 w-full rounded-full bg-gray-200 dark:bg-gray-700">
                            <div class="h-2 rounded-full bg-success-500" style="width: {{ min(100, ($payment->invoice->paid_amount / $payment->invoice->total) * 100) }}%"></div>
                        </div>
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>

<!-- Refund Modal -->
@if($payment->amount > 0)
<div class="modal fade" id="refundModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Record Refund - {{ $payment->payment_reference }}</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <form action="{{ route('finance.payments.refund', $payment) }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="alert alert-warning">
                        <strong>Maximum Refundable Amount:</strong> KSH {{ number_format($payment->amount, 2) }}
                    </div>
                    
                    <div class="form-group">
                        <label>Refund Amount (KSH)</label>
                        <input type="number" class="form-control" name="amount" 
                               step="0.01" min="0.01" max="{{ $payment->amount }}" required>
                    </div>
                    <div class="form-group">
                        <label>Reason for Refund</label>
                        <textarea class="form-control" name="reason" rows="3" required></textarea>
                    </div>
                    <div class="form-group">
                        <label>Refund Method</label>
                        <select class="form-control" name="method" required>
                            <option value="cash">Cash</option>
                            <option value="bank_transfer">Bank Transfer</option>
                            <option value="cheque">Cheque</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Refund Details (Optional)</label>
                        <textarea class="form-control" name="refund_details" rows="2" placeholder="Reference number, etc."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">Record Refund</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif
@endsection

@push('styles')
<style>
.progress-group {
    margin-top: 1rem;
}
</style>
@endpush