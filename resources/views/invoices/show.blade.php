@extends('layouts.app')

@section('title', 'Invoice ' . $invoice->invoice_number)

@section('content')
<div class="container-fluid px-0">
    <!-- Breadcrumb -->
    <div class="flex flex-wrap items-center justify-between gap-3 pb-6">
        <h2 class="text-xl font-semibold text-gray-800 dark:text-white/90">Invoice Details</h2>
        <nav>
            <ol class="flex items-center gap-1.5">
                <li>
                    <a class="inline-flex items-center gap-1.5 text-sm text-gray-500 dark:text-gray-400" href="{{ route('index') }}">
                        Dashboard
                        <svg class="stroke-current" width="17" height="16" viewBox="0 0 17 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M6.0765 12.667L10.2432 8.50033L6.0765 4.33366" stroke="" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </a>
                </li>
                <li>
                    <a class="inline-flex items-center gap-1.5 text-sm text-gray-500 dark:text-gray-400" href="{{ route('invoices.index') }}">
                        Invoices
                        <svg class="stroke-current" width="17" height="16" viewBox="0 0 17 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M6.0765 12.667L10.2432 8.50033L6.0765 4.33366" stroke="" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </a>
                </li>
                <li class="text-sm text-gray-800 dark:text-white/90">{{ $invoice->invoice_number }}</li>
            </ol>
        </nav>
    </div>

    <!-- Invoice Header -->
    <div class="flex flex-col justify-between gap-6 rounded-2xl border border-gray-200 bg-white px-6 py-5 sm:flex-row sm:items-center dark:border-gray-800 dark:bg-white/3">
        <div class="flex flex-col gap-2.5 divide-gray-300 sm:flex-row sm:divide-x dark:divide-gray-700">
            <div class="flex items-center gap-2 sm:pr-3">
                <span class="text-base font-medium text-gray-700 dark:text-gray-400">
                    Invoice ID: {{ $invoice->invoice_number }}
                </span>
                <span :class="{
                    'bg-success-50 text-success-600 dark:bg-success-500/15 dark:text-success-500': '{{ $invoice->status }}' === 'paid',
                    'bg-error-50 text-error-600 dark:bg-error-500/15 dark:text-error-500': '{{ $invoice->status }}' === 'sent' || '{{ $invoice->status }}' === 'partial',
                    'bg-warning-50 dark:bg-warning-500/15 text-warning-700 dark:text-warning-500': '{{ $invoice->status }}' === 'draft',
                    'bg-gray-100 text-gray-600 dark:bg-gray-500/15 dark:text-gray-400': '{{ $invoice->status }}' === 'overdue'
                }" class="inline-flex items-center justify-center gap-1 rounded-full px-2.5 py-0.5 text-sm font-medium">
                    @if($invoice->is_overdue && $invoice->status !== 'paid')
                        Overdue
                    @else
                        {{ ucfirst($invoice->status) }}
                    @endif
                </span>
            </div>
            <p class="text-sm text-gray-500 sm:pl-3 dark:text-gray-400">
                Due date: {{ $invoice->due_date?->format('D, ' . SystemHelper::dateFormat()) }}
            </p>
        </div>
        <div class="flex gap-3">
            @if($invoice->status !== 'paid' && $invoice->due_amount > 0)
            <button type="button" class="bg-brand-500 shadow-theme-xs hover:bg-brand-600 inline-flex items-center justify-center gap-2 rounded-lg px-4 py-3 text-sm font-medium text-white transition" data-toggle="modal" data-target="#processPaymentModal">
                Process Payment
            </button>
            @endif
            <a href="{{ route('invoices.show', $invoice) }}" target="_blank" class="shadow-theme-xs inline-flex items-center justify-center gap-2 rounded-lg bg-white px-4 py-3 text-sm font-medium text-gray-700 ring-1 ring-gray-300 transition hover:bg-gray-50 dark:bg-gray-800 dark:text-gray-400 dark:ring-gray-700 dark:hover:bg-white/[0.03]">
                Download PDF
            </a>
            @if($invoice->paid_amount > 0)
            <button type="button" class="shadow-theme-xs inline-flex items-center justify-center gap-2 rounded-lg bg-white px-4 py-3 text-sm font-medium text-gray-700 ring-1 ring-gray-300 transition hover:bg-gray-50 dark:bg-gray-800 dark:text-gray-400 dark:ring-gray-700 dark:hover:bg-white/[0.03]" data-toggle="modal" data-target="#recordRefundModal">
                Refund
            </button>
            @endif
        </div>
    </div>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-12 mt-6">
        <div class="lg:col-span-8 2xl:col-span-9">
            <!-- Invoice Details -->
            <div class="rounded-2xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-white/3">
                <div class="w-full rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
                    <div class="flex items-center justify-between border-b border-gray-200 px-6 py-4 dark:border-gray-800">
                        <h3 class="text-theme-xl font-medium text-gray-800 dark:text-white/90">Invoice</h3>
                        <h4 class="text-base font-medium text-gray-700 dark:text-gray-400">ID: {{ $invoice->invoice_number }}</h4>
                    </div>

                    <div class="p-5 xl:p-8">
                        <div class="mb-9 flex flex-col gap-6 sm:flex-row sm:items-center sm:justify-between">
                            <div>
                                <span class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-400">From</span>
                                <h5 class="mb-2 text-base font-semibold text-gray-800 dark:text-white/90">The Willis Hotel</h5>
                                <p class="mb-4 text-sm text-gray-500 dark:text-gray-400">
                                    Nairobi, Kenya<br>
                                    Phone: +254 700 000 000<br>
                                    Email: accounts@willishotel.com
                                </p>
                                <span class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Issued On:</span>
                                <span class="block text-sm text-gray-500 dark:text-gray-400">{{ $invoice->issue_date?->format('D, ' . SystemHelper::dateFormat()) }}</span>
                            </div>

                            <div class="h-px w-full bg-gray-200 sm:h-[158px] sm:w-px dark:bg-gray-800"></div>

                            <div class="sm:text-right">
                                <span class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-400">To</span>
                                <h5 class="mb-2 text-base font-semibold text-gray-800 dark:text-white/90">{{ $invoice->stay->guest->name ?? 'N/A' }}</h5>
                                <p class="mb-4 text-sm text-gray-500 dark:text-gray-400">
                                    @if($invoice->stay->guest->guestProfile && $invoice->stay->guest->guestProfile->address)
                                        {{ $invoice->stay->guest->guestProfile->address }}<br>
                                    @endif
                                    Phone: {{ $invoice->stay->guest->phone ?? 'N/A' }}<br>
                                    Email: {{ $invoice->stay->guest->email ?? 'N/A' }}
                                </p>
                                <span class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Due On:</span>
                                <span class="block text-sm text-gray-500 dark:text-gray-400">{{ $invoice->due_date?->format('D, ' . SystemHelper::dateFormat()) }}</span>
                            </div>
                        </div>

                        <!-- Invoice Table -->
                        <div>
                            <div class="overflow-x-auto rounded-xl border border-gray-100 dark:border-gray-800">
                                <table class="min-w-full text-left text-gray-700 dark:text-gray-400">
                                    <thead class="bg-gray-50 dark:bg-gray-900">
                                        <tr class="border-b border-gray-100 dark:border-gray-800">
                                            <th class="px-5 py-3 text-sm font-medium whitespace-nowrap text-gray-700 dark:text-gray-400">S.No.#</th>
                                            <th class="px-5 py-3 text-xs font-medium whitespace-nowrap text-gray-500 dark:text-gray-400">Description</th>
                                            <th class="px-5 py-3 text-center text-sm font-medium whitespace-nowrap text-gray-700 dark:text-gray-400">Quantity</th>
                                            <th class="px-5 py-3 text-center text-sm font-medium whitespace-nowrap text-gray-700 dark:text-gray-400">Unit Price</th>
                                            <th class="px-5 py-3 text-center text-sm font-medium whitespace-nowrap text-gray-700 dark:text-gray-400">Type</th>
                                            <th class="px-5 py-3 text-right text-sm font-medium whitespace-nowrap text-gray-700 dark:text-gray-400">Total</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                                        @foreach($invoice->items as $item)
                                        <tr>
                                            <td class="px-5 py-3 text-sm whitespace-nowrap text-gray-500 dark:text-gray-400">{{ $loop->iteration }}</td>
                                            <td class="px-5 py-3 text-sm font-medium whitespace-nowrap text-gray-800 dark:text-white/90">{{ $item->description }}</td>
                                            <td class="px-5 py-3 text-center text-sm whitespace-nowrap text-gray-500 dark:text-gray-400">{{ number_format($item->quantity, 2) }}</td>
                                            <td class="px-5 py-3 text-center text-sm whitespace-nowrap text-gray-500 dark:text-gray-400">KSH {{ number_format($item->unit_price, 2) }}</td>
                                            <td class="px-5 py-3 text-center text-sm whitespace-nowrap text-gray-500 dark:text-gray-400">{{ ucfirst($item->source_type) }}</td>
                                            <td class="px-5 py-3 text-right text-sm text-gray-500 dark:text-gray-400">KSH {{ number_format($item->total, 2) }}</td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div class="my-6 flex justify-end border-b border-gray-100 pb-6 text-right dark:border-gray-800">
                            <div class="w-[220px]">
                                <p class="mb-4 text-left text-sm font-medium text-gray-800 dark:text-white/90">Order summary</p>
                                <ul class="space-y-2">
                                    <li class="flex justify-between gap-5">
                                        <span class="text-sm text-gray-500 dark:text-gray-400">Sub Total</span>
                                        <span class="text-sm font-medium text-gray-700 dark:text-gray-400">KSH {{ number_format($invoice->subtotal, 2) }}</span>
                                    </li>
                                    <li class="flex items-center justify-between">
                                        <span class="text-sm text-gray-500 dark:text-gray-400">Tax (16%):</span>
                                        <span class="text-sm font-medium text-gray-700 dark:text-gray-400">KSH {{ number_format($invoice->tax_amount, 2) }}</span>
                                    </li>
                                    @if($invoice->discount_amount > 0)
                                    <li class="flex items-center justify-between">
                                        <span class="text-sm text-gray-500 dark:text-gray-400">Discount:</span>
                                        <span class="text-sm font-medium text-gray-700 dark:text-gray-400">-KSH {{ number_format($invoice->discount_amount, 2) }}</span>
                                    </li>
                                    @endif
                                    <li class="flex items-center justify-between">
                                        <span class="font-medium text-gray-700 dark:text-gray-400">Total</span>
                                        <span class="text-lg font-semibold text-gray-800 dark:text-white/90">KSH {{ number_format($invoice->total, 2) }}</span>
                                    </li>
                                    <li class="flex items-center justify-between">
                                        <span class="font-medium text-gray-700 dark:text-gray-400">Paid Amount</span>
                                        <span class="text-lg font-semibold text-success-600 dark:text-success-500">KSH {{ number_format($invoice->paid_amount, 2) }}</span>
                                    </li>
                                    <li class="flex items-center justify-between">
                                        <span class="font-medium text-gray-700 dark:text-gray-400">Balance Due</span>
                                        <span class="text-lg font-semibold text-error-600 dark:text-error-500">KSH {{ number_format($invoice->due_amount, 2) }}</span>
                                    </li>
                                </ul>
                            </div>
                        </div>

                        <div class="flex items-center justify-end gap-3">
                            @if($invoice->status !== 'paid' && $invoice->due_amount > 0)
                            <button type="button" class="shadow-theme-xs flex items-center justify-center rounded-lg border border-gray-300 bg-white px-4 py-3 text-sm font-medium text-gray-700 hover:bg-gray-50 hover:text-gray-800 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03] dark:hover:text-gray-200" data-toggle="modal" data-target="#processPaymentModal">
                                Proceed to payment
                            </button>
                            @endif
                            <button onclick="window.print()" class="bg-brand-500 shadow-theme-xs hover:bg-brand-600 flex items-center justify-center gap-2 rounded-lg px-4 py-3 text-sm font-medium text-white">
                                <svg class="fill-current" width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path fill-rule="evenodd" clip-rule="evenodd" d="M15.9375 4.67548V12.9375C15.9375 13.5588 15.4338 14.0625 14.8125 14.0625H3.1875C2.56618 14.0625 2.0625 13.5588 2.0625 12.9375V4.67548M2.80194 3.9375H15.1983C15.6066 3.9375 15.9375 4.26843 15.9376 4.67669C15.9376 4.91843 15.8195 5.14491 15.6212 5.28318L9.64374 9.45142C9.25711 9.72103 8.7434 9.72103 8.35676 9.45142L2.37912 5.28304C2.18095 5.14485 2.06282 4.91854 2.06274 4.67694C2.06261 4.2686 2.3936 3.9375 2.80194 3.9375Z" stroke="white" stroke-width="1.3" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                                Print
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="space-y-6 lg:col-span-4 2xl:col-span-3">
            <!-- Customer Details -->
            <div class="rounded-2xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-white/3">
                <h2 class="mb-5 text-lg font-semibold text-gray-800 dark:text-white/90">Customer Details</h2>
                <ul class="divide-y divide-gray-100 dark:divide-gray-800">
                    <li class="flex items-start gap-5 py-2.5">
                        <span class="w-1/2 text-sm text-gray-500 sm:w-1/3 dark:text-gray-400">Name</span>
                        <span class="w-1/2 text-sm text-gray-700 sm:w-2/3 dark:text-gray-400">{{ $invoice->stay->guest->name }}</span>
                    </li>
                    <li class="flex items-start gap-5 py-2.5">
                        <span class="w-1/2 text-sm text-gray-500 sm:w-1/3 dark:text-gray-400">Email</span>
                        <span class="w-1/2 text-sm text-gray-700 sm:w-2/3 dark:text-gray-400">{{ $invoice->stay->guest->email }}</span>
                    </li>
                    <li class="flex items-start gap-5 py-2.5">
                        <span class="w-1/2 text-sm text-gray-500 sm:w-1/3 dark:text-gray-400">Phone</span>
                        <span class="w-1/2 text-sm text-gray-700 sm:w-2/3 dark:text-gray-400">{{ $invoice->stay->guest->phone ?? 'N/A' }}</span>
                    </li>
                    @if($invoice->stay->guest->guestProfile)
                    <li class="flex items-start gap-5 py-2.5">
                        <span class="w-1/2 text-sm text-gray-500 sm:w-1/3 dark:text-gray-400">ID Number</span>
                        <span class="w-1/2 text-sm text-gray-700 sm:w-2/3 dark:text-gray-400">{{ $invoice->stay->guest->guestProfile->id_number }}</span>
                    </li>
                    @endif
                    <li class="flex items-start gap-5 py-2.5">
                        <span class="w-1/2 text-sm text-gray-500 sm:w-1/3 dark:text-gray-400">Stay Info</span>
                        <span class="w-1/2 text-sm text-gray-700 sm:w-2/3 dark:text-gray-400">
                            Stay #{{ $invoice->stay->id }}<br>
                            Check-in: {{ $invoice->stay->check_in_date?->format('D, ' . SystemHelper::dateFormat()) }}<br>
                            Check-out: {{ $invoice->stay->check_out_date?->format('D, ' . SystemHelper::dateFormat()) }}
                        </span>
                    </li>
                </ul>
            </div>

            <!-- Payment History -->
            <div class="rounded-2xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-white/3">
                <h2 class="mb-5 text-lg font-semibold text-gray-800 dark:text-white/90">Payment History</h2>
                @if($invoice->payments->count() > 0)
                @foreach($invoice->payments as $payment)
                <div class="relative pb-7 pl-11">
                    <div class="absolute top-0 left-0 z-10 flex h-12 w-12 items-center justify-center rounded-full border-2 border-gray-50 bg-white text-gray-700 ring ring-gray-200 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-400 dark:ring-gray-800">
                        @if($payment->amount >= 0)
                        <svg class="size-5" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 18 18" fill="none">
                            <path d="M15.9375 4.67548V12.9375C15.9375 13.5588 15.4338 14.0625 14.8125 14.0625H3.1875C2.56618 14.0625 2.0625 13.5588 2.0625 12.9375V4.67548M2.80194 3.9375H15.1983C15.6066 3.9375 15.9375 4.26843 15.9376 4.67669C15.9376 4.91843 15.8195 5.14491 15.6212 5.28318L9.64374 9.45142C9.25711 9.72103 8.7434 9.72103 8.35676 9.45142L2.37912 5.28304C2.18095 5.14485 2.06282 4.91854 2.06274 4.67694C2.06261 4.2686 2.3936 3.9375 2.80194 3.9375Z" stroke="currentColor" stroke-width="1.3" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                        @else
                        <svg class="size-5" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 18 18" fill="none">
                            <path d="M15.9375 4.67548V12.9375C15.9375 13.5588 15.4338 14.0625 14.8125 14.0625H3.1875C2.56618 14.0625 2.0625 13.5588 2.0625 12.9375V4.67548M2.80194 3.9375H15.1983C15.6066 3.9375 15.9375 4.26843 15.9376 4.67669C15.9376 4.91843 15.8195 5.14491 15.6212 5.28318L9.64374 9.45142C9.25711 9.72103 8.7434 9.72103 8.35676 9.45142L2.37912 5.28304C2.18095 5.14485 2.06282 4.91854 2.06274 4.67694C2.06261 4.2686 2.3936 3.9375 2.80194 3.9375Z" stroke="currentColor" stroke-width="1.3" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                        @endif
                    </div>
                    <div class="ml-4 flex justify-between">
                        <div>
                            <h4 class="font-medium text-gray-800 dark:text-white/90">
                                {{ $payment->amount >= 0 ? 'Payment Received' : 'Refund Issued' }}
                            </h4>
                            <p class="text-sm text-gray-500 dark:text-gray-400">
                                {{ ucfirst(str_replace('_', ' ', $payment->method)) }}
                            </p>
                        </div>
                        <div>
                            <span class="text-xs text-gray-500 dark:text-gray-400">{{ $payment->received_at->format('H:i') }}</span>
                            <p class="text-xs text-gray-500 dark:text-gray-400">{{ $payment->received_at?->format('D, ' . SystemHelper::dateFormat()) }}</p>
                        </div>
                    </div>
                    @if(!$loop.last)
                    <div class="absolute top-8 left-6 h-full w-px border border-dashed border-gray-300 dark:border-gray-700"></div>
                    @endif
                </div>
                @endforeach
                @else
                <div class="text-center py-4">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">No payments recorded yet.</p>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Process Payment Modal -->
<div class="modal fade" id="processPaymentModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Process Payment - {{ $invoice->invoice_number }}</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <form action="{{ route('invoices.create', $invoice) }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="alert alert-info">
                        <strong>Invoice Balance:</strong> KSH {{ number_format($invoice->due_amount, 2) }}
                    </div>
                    
                    <div class="form-group">
                        <label>Payment Amount (KSH)</label>
                        <input type="number" class="form-control" name="amount" 
                               value="{{ $invoice->due_amount }}" 
                               step="0.01" min="0.01" max="{{ $invoice->due_amount }}" required>
                    </div>
                    <div class="form-group">
                        <label>Payment Method</label>
                        <select class="form-control" name="method" required>
                            <option value="cash">Cash</option>
                            <option value="credit_card">Credit Card</option>
                            <option value="mobile_money">Mobile Money</option>
                            <option value="bank_transfer">Bank Transfer</option>
                            <option value="cheque">Cheque</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Payment Details (Optional)</label>
                        <textarea class="form-control" name="payment_details" rows="2" placeholder="Reference number, mobile number, etc."></textarea>
                    </div>
                    <div class="form-group">
                        <label>Notes (Optional)</label>
                        <textarea class="form-control" name="notes" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">Process Payment</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Record Refund Modal -->
<div class="modal fade" id="recordRefundModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Record Refund - {{ $invoice->invoice_number }}</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <form action="{{ route('invoices.create', $invoice) }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="alert alert-warning">
                        <strong>Maximum Refundable Amount:</strong> KSH {{ number_format($invoice->paid_amount, 2) }}
                    </div>
                    
                    <div class="form-group">
                        <label>Refund Amount (KSH)</label>
                        <input type="number" class="form-control" name="amount" 
                               step="0.01" min="0.01" max="{{ $invoice->paid_amount }}" required>
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
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">Record Refund</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection