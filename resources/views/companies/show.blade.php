@extends('layouts.app')

@php
    $money = fn ($v) => SystemHelper::currencySymbol() . ' ' . number_format((float) $v, 2);
    $canMoney = auth()->user()->hasAnyRole(['admin', 'manager', 'finance']);
    $f = 'h-11 w-full rounded-lg border border-gray-300 bg-white px-4 text-sm shadow-theme-xs focus:border-brand-300 focus:outline-hidden';
@endphp

@section('content')
<div x-data="{ pageName: @js($company->name) }">@include('partials.breadcrumb')</div>

<div class="space-y-6" x-data="{ dialog: @js(old('amount') !== null ? 'pay' : ($errors->any() ? 'edit' : null)) }">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
        <div>
            <a href="{{ route('companies.index') }}" class="text-sm text-gray-500 hover:text-brand-600"><i class="fas fa-arrow-left text-xs"></i> Company accounts</a>
            <h2 class="mt-1 text-2xl font-bold text-gray-800">{{ $company->name }} @unless ($company->is_active)<span class="ml-2 rounded-full bg-gray-100 px-2 py-0.5 text-xs font-medium text-gray-600">Inactive</span>@endunless</h2>
            <p class="text-sm text-gray-600">{{ collect([$company->contact_name, $company->phone, $company->email, $company->kra_pin ? 'KRA PIN ' . $company->kra_pin : null])->filter()->join(' · ') }}</p>
        </div>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('companies.statement', $company) }}" target="_blank" class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50"><i class="fas fa-print"></i> Statement</a>
            @if ($canMoney)
                <button type="button" @click="dialog = 'edit'" class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50"><i class="fas fa-pen"></i> Edit</button>
                @if ($balance > 0)
                    <button type="button" @click="dialog = 'pay'" class="inline-flex items-center gap-2 rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-medium text-white hover:bg-brand-600"><i class="fas fa-money-bill-wave"></i> Record payment</button>
                @endif
            @endif
        </div>
    </div>

    <div class="grid grid-cols-2 gap-4 lg:grid-cols-4">
        @include('partials.stat-card', ['label' => 'Owes', 'value' => $money($balance), 'icon' => 'fa-scale-balanced', 'tone' => $balance > 0 ? 'warning' : 'success'])
        @include('partials.stat-card', ['label' => 'Credit limit', 'value' => $company->hasCreditLimit() ? $money($company->credit_limit) : 'No limit', 'icon' => 'fa-shield-halved', 'tone' => $company->hasCreditLimit() && $balance > (float) $company->credit_limit ? 'error' : 'info'])
        @include('partials.stat-card', ['label' => 'Pays within', 'value' => $company->payment_terms_days . ' days', 'icon' => 'fa-calendar-days', 'tone' => 'info'])
        @include('partials.stat-card', ['label' => 'Room rate', 'value' => (float) $company->rate_discount_percent > 0 ? rtrim(rtrim(number_format((float) $company->rate_discount_percent, 2), '0'), '.') . '% off' : 'Rack rate', 'icon' => 'fa-tag', 'tone' => 'primary'])
    </div>

    <section class="rounded-2xl border border-gray-200 bg-white p-5">
        <h3 class="mb-3 text-sm font-semibold text-gray-700">Ageing</h3>
        <div class="grid grid-cols-2 gap-3 sm:grid-cols-5">
            @foreach ($ageing as $label => $amount)
                <div class="rounded-xl border border-gray-100 p-3 {{ $amount > 0 && $label !== 'Not yet due' ? 'bg-error-50/50' : '' }}">
                    <p class="text-xs text-gray-500">{{ $label }}</p>
                    <p class="mt-1 font-semibold text-gray-800">{{ $money($amount) }}</p>
                </div>
            @endforeach
        </div>
    </section>

    <section class="overflow-x-auto rounded-2xl border border-gray-200 bg-white">
        <h3 class="border-b border-gray-100 px-5 py-3 text-sm font-semibold text-gray-700">Invoices billed to {{ $company->name }}</h3>
        <table class="w-full min-w-[720px] text-sm">
            <thead class="text-left text-gray-500"><tr>
                <th class="px-5 py-2.5 font-medium">Invoice</th><th class="px-5 py-2.5 font-medium">For</th><th class="px-5 py-2.5 font-medium">Issued</th><th class="px-5 py-2.5 font-medium">Due</th>
                <th class="px-5 py-2.5 text-right font-medium">Total</th><th class="px-5 py-2.5 text-right font-medium">Balance</th>
            </tr></thead>
            <tbody class="divide-y divide-gray-100">
                @forelse ($invoices as $invoice)
                    <tr>
                        <td class="px-5 py-2.5 font-medium text-gray-800">@if ($invoice->source_url)<a href="{{ $invoice->source_url }}" class="hover:text-brand-600">{{ $invoice->invoice_number }}</a>@else{{ $invoice->invoice_number }}@endif</td>
                        <td class="px-5 py-2.5 text-gray-600">{{ $invoice->stay ? 'Booking #' . $invoice->stay_id . ' · ' . ($invoice->stay->guest?->name ?? '') : ($invoice->facilityBooking?->title ?? '—') }}</td>
                        <td class="px-5 py-2.5 text-gray-600">{{ $invoice->issue_date?->format('d M Y') }}</td>
                        <td class="px-5 py-2.5 {{ $invoice->balance > 0 && $invoice->due_date?->isPast() ? 'font-medium text-error-600' : 'text-gray-600' }}">{{ $invoice->due_date?->format('d M Y') }}</td>
                        <td class="px-5 py-2.5 text-right text-gray-600">{{ $money($invoice->total) }}</td>
                        <td class="px-5 py-2.5 text-right font-semibold {{ $invoice->balance > 0 ? 'text-gray-900' : 'text-success-600' }}">{{ $invoice->balance > 0 ? $money($invoice->balance) : 'Paid' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-5 py-10 text-center text-gray-500">Nothing billed to this company yet. Choose it under "Bill to" on a booking.</td></tr>
                @endforelse
            </tbody>
        </table>
    </section>
    {{ $invoices->links() }}

    @if ($canMoney)
        <div x-show="dialog" x-cloak class="fixed inset-0 z-99999 flex items-center justify-center p-5" @keydown.escape.window="dialog = null">
            <div class="fixed inset-0 bg-gray-400/50 backdrop-blur-xs" @click="dialog = null"></div>

            <form x-show="dialog === 'edit'" method="POST" action="{{ route('companies.update', $company) }}" class="relative max-h-[90vh] w-full max-w-2xl overflow-y-auto rounded-2xl bg-white p-6">
                @csrf @method('PUT')
                <h3 class="mb-4 text-lg font-semibold text-gray-800">Edit {{ $company->name }}</h3>
                @if ($errors->any() && old('amount') === null)<div class="mb-4 rounded-lg bg-error-50 p-3 text-sm text-error-700">@foreach ($errors->all() as $e)<p>{{ $e }}</p>@endforeach</div>@endif
                @include('companies.partials.form', ['company' => $company])
                <div class="mt-6 flex justify-end gap-3">
                    <button type="button" @click="dialog = null" class="rounded-lg border border-gray-300 px-4 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50">Cancel</button>
                    <button class="rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-medium text-white hover:bg-brand-600">Save</button>
                </div>
            </form>

            @if ($balance > 0)
                <form x-show="dialog === 'pay'" method="POST" action="{{ route('companies.pay', $company) }}" class="relative w-full max-w-md rounded-2xl bg-white p-6" x-data="{ method: @js(old('method', 'bank_transfer')) }">
                    @csrf
                    <h3 class="text-lg font-semibold text-gray-800">Record company payment</h3>
                    <p class="mt-1 text-sm text-gray-500">Owes {{ $money($balance) }}. The payment clears the oldest invoices first.</p>
                    @if ($errors->any() && old('amount') !== null)<div class="mt-3 rounded-lg bg-error-50 p-3 text-sm text-error-700">@foreach ($errors->all() as $e)<p>{{ $e }}</p>@endforeach</div>@endif
                    <div class="mt-4 space-y-4">
                        <label class="block"><span class="mb-1.5 block text-sm font-medium text-gray-700">Amount *</span><input type="number" name="amount" step="0.01" min="1" max="{{ $balance }}" value="{{ old('amount', $balance) }}" required class="{{ $f }}"></label>
                        <label class="block"><span class="mb-1.5 block text-sm font-medium text-gray-700">Method *</span>
                            <select name="method" x-model="method" class="{{ $f }}">@foreach ($methods as $key => $label)<option value="{{ $key }}">{{ $label }}</option>@endforeach</select></label>
                        <label class="block" x-show="method !== 'cash'"><span class="mb-1.5 block text-sm font-medium text-gray-700">Reference *</span><input type="text" name="payment_reference" maxlength="40" value="{{ old('payment_reference') }}" placeholder="Cheque, transfer or M-Pesa code" class="{{ $f }}"></label>
                        <label class="block"><span class="mb-1.5 block text-sm font-medium text-gray-700">Notes</span><input type="text" name="notes" maxlength="500" value="{{ old('notes') }}" class="{{ $f }}"></label>
                    </div>
                    <div class="mt-6 flex justify-end gap-3">
                        <button type="button" @click="dialog = null" class="rounded-lg border border-gray-300 px-4 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50">Cancel</button>
                        <button class="rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-medium text-white hover:bg-brand-600">Save payment</button>
                    </div>
                </form>
            @endif
        </div>
    @endif
</div>
@endsection
