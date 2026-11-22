@extends('layouts.app')

{{-- One expense: what it was for, its receipt, who asked / approved / paid, and the next step (as in Breve). --}}
@php
    $money = fn ($value) => SystemHelper::currencySymbol() . ' ' . number_format((float) $value, 2);
    $user = auth()->user();
    $canApprove = \App\Http\Controllers\ExpenseController::canApprove($user);
    $canPay = \App\Http\Controllers\ExpenseController::canPay($user);
    $tones = ['warning' => 'bg-warning-50 text-warning-700', 'info' => 'bg-info-50 text-info-700', 'success' => 'bg-success-50 text-success-700', 'error' => 'bg-error-50 text-error-700', 'gray' => 'bg-gray-100 text-gray-600'];
    $input = 'h-11 w-full rounded-lg border border-gray-300 bg-white px-4 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10';
    $isImage = $expense->receipt_path && preg_match('/\.(jpe?g|png|webp)$/i', $expense->receipt_path);
@endphp

@section('content')
@include('partials.page-header', ['title' => 'Expense', 'crumbs' => ['Expenses' => route('expenses.index')]])

<div class="grid grid-cols-1 gap-6 xl:grid-cols-3">
    <div class="space-y-6 xl:col-span-2">
        <div class="rounded-2xl border border-gray-200 bg-white p-6">
            <div class="flex flex-wrap items-start justify-between gap-4">
                <div>
                    <p class="text-sm text-gray-500">{{ $expense->expense_number }} · {{ $expense->category_label }}</p>
                    <h3 class="mt-1 text-xl font-semibold text-gray-800">{{ $expense->description }}</h3>
                    <span class="mt-2 inline-block rounded-full px-2.5 py-0.5 text-xs font-medium {{ $tones[\App\Models\Expense::STATUS_TONES[$expense->status] ?? 'gray'] }}">{{ $expense->status_label }}</span>
                </div>
                <p class="text-3xl font-bold text-gray-800">{{ $money($expense->amount) }}</p>
            </div>

            <dl class="mt-6 grid grid-cols-1 gap-4 text-sm sm:grid-cols-2">
                <div><dt class="text-gray-500">Date of the bill</dt><dd class="font-medium text-gray-800">{{ $expense->spent_on->format('d M Y') }}</dd></div>
                <div><dt class="text-gray-500">Department</dt><dd class="font-medium text-gray-800">{{ $expense->department?->name ?? 'Whole hotel' }}</dd></div>
                @if ($expense->supplier)
                    <div><dt class="text-gray-500">Supplier</dt><dd class="font-medium text-gray-800">{{ $expense->supplier->name }}{{ $expense->purchaseOrder ? ' · ' . $expense->purchaseOrder->po_number : '' }}</dd></div>
                @endif
                @if ($expense->status === 'paid')
                    <div><dt class="text-gray-500">Paid by</dt><dd class="font-medium text-gray-800">{{ $expense->method_label }}{{ $expense->reference ? ' · ' . $expense->reference : '' }}</dd></div>
                @endif
                @if (in_array($expense->status, ['rejected', 'void']) && $expense->void_reason)
                    <div class="sm:col-span-2"><dt class="text-gray-500">{{ $expense->status === 'rejected' ? 'Why it was rejected' : 'Why it was voided' }}</dt><dd class="font-medium text-error-600">{{ $expense->void_reason }}</dd></div>
                @endif
            </dl>
        </div>

        <div class="rounded-2xl border border-gray-200 bg-white p-6">
            <h4 class="mb-4 font-semibold text-gray-800">Receipt</h4>
            @if ($expense->receipt_path)
                @if ($isImage)
                    <a href="{{ route('expenses.receipt', $expense) }}" target="_blank" data-no-spa><img src="{{ route('expenses.receipt', $expense) }}" alt="Receipt for {{ $expense->expense_number }}" class="max-h-96 rounded-lg border border-gray-200"></a>
                @else
                    <a href="{{ route('expenses.receipt', $expense) }}" target="_blank" class="inline-flex items-center gap-2 rounded-lg border border-gray-300 px-4 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50"><i class="fas fa-file-pdf text-error-500"></i> Open the receipt (PDF)</a>
                @endif
            @else
                <p class="text-sm text-gray-500">No receipt attached.</p>
            @endif
        </div>
    </div>

    <div class="space-y-6">
        <div class="rounded-2xl border border-gray-200 bg-white p-6">
            <h4 class="mb-4 font-semibold text-gray-800">History</h4>
            <ol class="space-y-3 text-sm">
                <li><i class="fas fa-circle-plus mr-2 text-gray-400"></i>Recorded by <strong>{{ $expense->creator?->name }}</strong>, {{ $expense->created_at->format('d M Y H:i') }}</li>
                @if ($expense->approved_at && $expense->status !== 'rejected')
                    <li><i class="fas fa-circle-check mr-2 text-success-500"></i>Approved by <strong>{{ $expense->approver?->name }}</strong>, {{ $expense->approved_at->format('d M Y H:i') }}</li>
                @endif
                @if ($expense->status === 'rejected')
                    <li><i class="fas fa-circle-xmark mr-2 text-error-500"></i>Rejected by <strong>{{ $expense->approver?->name }}</strong>, {{ $expense->approved_at?->format('d M Y H:i') }}</li>
                @endif
                @if ($expense->paid_at)
                    <li><i class="fas fa-money-bill-wave mr-2 text-success-500"></i>Paid {{ $expense->paid_at->format('d M Y H:i') }}</li>
                @endif
            </ol>
        </div>

        @if ($expense->status === 'pending' && $canApprove)
            <div class="rounded-2xl border border-gray-200 bg-white p-6" x-data="{ rejecting: false }">
                <h4 class="mb-4 font-semibold text-gray-800">Approve this expense?</h4>
                <form method="POST" action="{{ route('expenses.approve', $expense) }}" class="space-y-3">
                    @csrf
                    <input type="hidden" name="decision" :value="rejecting ? 'reject' : 'approve'">
                    <textarea x-show="rejecting" x-cloak name="reason" rows="2" maxlength="255" :required="rejecting" placeholder="Why is it rejected?" class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm"></textarea>
                    <div class="flex gap-2">
                        <button type="submit" x-show="!rejecting" class="flex-1 rounded-lg bg-success-500 px-4 py-2.5 text-sm font-medium text-white hover:bg-success-600">Approve</button>
                        <button type="button" x-show="!rejecting" @click="rejecting = true" class="rounded-lg border border-gray-300 px-4 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50">Reject</button>
                        <button type="submit" x-show="rejecting" x-cloak class="flex-1 rounded-lg bg-error-500 px-4 py-2.5 text-sm font-medium text-white hover:bg-error-600">Reject</button>
                        <button type="button" x-show="rejecting" x-cloak @click="rejecting = false" class="rounded-lg border border-gray-300 px-4 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50">Back</button>
                    </div>
                </form>
            </div>
        @endif

        @if ($canPay && ($expense->status === 'approved' || ($expense->status === 'pending' && $canApprove)))
            <div class="rounded-2xl border border-gray-200 bg-white p-6" x-data="{ method: 'mobile_money' }">
                <h4 class="mb-1 font-semibold text-gray-800">Pay it</h4>
                @if ($expense->status === 'pending')<p class="mb-3 text-xs text-gray-500">Paying it also approves it.</p>@endif
                <form method="POST" action="{{ route('expenses.pay', $expense) }}" class="space-y-3">
                    @csrf
                    <select name="method" x-model="method" class="{{ $input }}">
                        @foreach ($methods as $value => $label)<option value="{{ $value }}">{{ $label }}</option>@endforeach
                    </select>
                    <input type="text" name="reference" maxlength="100" x-show="!['cash', 'credit_card'].includes(method)" :required="!['cash', 'credit_card'].includes(method)" placeholder="M-Pesa code / cheque no." class="{{ $input }} uppercase">
                    <button type="submit" class="w-full rounded-lg bg-primary px-4 py-2.5 text-sm font-medium text-white hover:bg-primary/90">Mark paid {{ $money($expense->amount) }}</button>
                </form>
            </div>
        @endif

        @if ($canPay && $expense->status !== 'void')
            <div class="rounded-2xl border border-gray-200 bg-white p-6" x-data="{ open: false }">
                <button type="button" @click="open = !open" class="text-sm text-error-600 hover:underline">Void this expense (entered by mistake)</button>
                <form x-show="open" x-cloak method="POST" action="{{ route('expenses.void', $expense) }}" class="mt-3 flex gap-2">
                    @csrf
                    <input type="text" name="reason" required maxlength="255" placeholder="Why?" class="{{ $input }}">
                    <button type="submit" class="rounded-lg bg-error-500 px-4 text-sm font-medium text-white hover:bg-error-600">Void</button>
                </form>
            </div>
        @endif
    </div>
</div>
@endsection
