@extends('layouts.app')

@php
    $inputClass = 'h-10 w-full rounded-lg border border-gray-300 bg-transparent px-3 py-2 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30';
    $money = fn ($value) => SystemHelper::currencySymbol() . ' ' . number_format((float) $value, 2);
    $owedTotal = $owed->sum('balance');
    $supplierTotal = (float) ($byCategory['supplier_payment'] ?? 0);
    // Suppliers and purchase orders link here with ?pay_supplier=ID&po=ID to open the payment form
    $payFor = null;
    if (request()->integer('pay_supplier')) {
        $po = collect($openOrders)->firstWhere('id', request()->integer('po'));
        $payFor = array_filter([
            'category' => 'supplier_payment',
            'supplier_id' => (string) request()->integer('pay_supplier'),
            'purchase_order_id' => $po ? (string) $po['id'] : null,
            'amount' => $po['owed'] ?? null,
            'description' => $po ? 'Payment for ' . $po['label'] : null,
        ]);
    }
@endphp

@section('content')
<div x-data="{ pageName: `Expenses` }">
    @include('partials.breadcrumb')
</div>

<div class="space-y-6" x-data x-init="@js($payFor) && $nextTick(() => $dispatch('open-expense-form', @js($payFor)))">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="text-2xl font-bold text-gray-800 dark:text-white/90">Expenses</h2>
            <p class="text-gray-600 dark:text-gray-400">Money paid out: running costs and supplier payments</p>
        </div>
        <div class="flex flex-wrap gap-2">
            <button type="button" @click="$dispatch('open-expense-form', { category: 'supplier_payment' })" class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 shadow-theme-xs hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300"><i class="fas fa-truck"></i> Pay a supplier</button>
            <button type="button" @click="$dispatch('open-expense-form')" class="inline-flex items-center gap-2 rounded-lg bg-primary px-4 py-2.5 text-sm font-medium text-white shadow-theme-xs hover:bg-primary/90"><i class="fas fa-plus"></i> Record expense</button>
        </div>
    </div>

    <div class="grid grid-cols-2 gap-4 lg:grid-cols-4">
        @include('partials.stat-card', ['label' => 'Paid out in period', 'value' => $money($total), 'icon' => 'fa-money-bill-wave', 'tone' => 'primary'])
        @include('partials.stat-card', ['label' => 'Running costs', 'value' => $money($total - $supplierTotal), 'icon' => 'fa-bolt', 'tone' => 'info'])
        @include('partials.stat-card', ['label' => 'Paid to suppliers', 'value' => $money($supplierTotal), 'icon' => 'fa-truck', 'tone' => 'success'])
        @include('partials.stat-card', ['label' => 'Owed to suppliers now', 'value' => $money($owedTotal), 'icon' => 'fa-file-invoice-dollar', 'tone' => $owedTotal > 0 ? 'warning' : 'gray'])
    </div>

    <form method="GET" action="{{ route('expenses.index') }}" class="grid grid-cols-1 gap-3 rounded-2xl border border-gray-100 bg-white p-4 sm:grid-cols-2 lg:grid-cols-6 dark:border-gray-800 dark:bg-gray-900">
        <input type="date" name="from" value="{{ $from }}" class="{{ $inputClass }}">
        <input type="date" name="to" value="{{ $to }}" class="{{ $inputClass }}">
        <select name="category" class="{{ $inputClass }}">
            <option value="">All kinds</option>
            @foreach (\App\Models\Expense::CATEGORIES as $value => $label)
                <option value="{{ $value }}" @selected(($filters['category'] ?? '') === $value)>{{ $label }}</option>
            @endforeach
        </select>
        <select name="supplier_id" class="{{ $inputClass }}">
            <option value="">Any supplier</option>
            @foreach ($suppliers as $supplier)
                <option value="{{ $supplier->id }}" @selected((int) ($filters['supplier_id'] ?? 0) === $supplier->id)>{{ $supplier->name }}</option>
            @endforeach
        </select>
        <input type="search" name="search" value="{{ $filters['search'] ?? '' }}" placeholder="No., description, ref." class="{{ $inputClass }}">
        <div class="flex items-center gap-2">
            <button type="submit" class="h-10 flex-1 rounded-lg bg-primary px-3 text-sm font-medium text-white hover:bg-primary/90">Show</button>
            <label class="flex items-center gap-1 text-xs text-gray-600 dark:text-gray-400"><input type="checkbox" name="void" value="1" @checked($filters['void'] ?? false) class="rounded border-gray-300"> Void</label>
        </div>
    </form>

    <div class="grid grid-cols-1 gap-6 xl:grid-cols-3">
        <div class="rounded-2xl border border-gray-100 bg-white xl:col-span-2 dark:border-gray-800 dark:bg-gray-900">
            <div class="custom-scrollbar overflow-x-auto">
                <table class="min-w-full text-left text-sm">
                    <thead class="bg-gray-50 dark:bg-gray-900">
                        <tr class="whitespace-nowrap border-b border-gray-100 text-gray-700 dark:border-gray-800 dark:text-gray-400">
                            <th class="px-5 py-3 font-medium">Expense</th>
                            <th class="px-5 py-3 font-medium">Kind</th>
                            <th class="px-5 py-3 font-medium">Paid by</th>
                            <th class="px-5 py-3 text-right font-medium">Amount</th>
                            <th class="px-5 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        @forelse ($expenses as $expense)
                            <tr x-data="{ voiding: false }" class="{{ $expense->status === 'void' ? 'opacity-60' : '' }}">
                                <td class="px-5 py-3">
                                    <p class="font-medium text-gray-800 dark:text-white/90 {{ $expense->status === 'void' ? 'line-through' : '' }}">{{ $expense->description }}</p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">
                                        {{ $expense->expense_number }} · {{ $expense->spent_on->format('d M Y') }}
                                        @if ($expense->supplier) · <a href="{{ route('suppliers.show', $expense->supplier) }}" class="hover:text-primary">{{ $expense->supplier->name }}</a>@endif
                                        @if ($expense->purchaseOrder) · <a href="{{ route('purchase-orders.show', $expense->purchaseOrder) }}" class="hover:text-primary">{{ $expense->purchaseOrder->po_number }}</a>@endif
                                        @if ($expense->department) · {{ $expense->department->name }}@endif
                                    </p>
                                    @if ($expense->status === 'void')<p class="text-xs text-error-500">Void: {{ $expense->void_reason }}</p>@endif
                                </td>
                                <td class="whitespace-nowrap px-5 py-3 text-gray-700 dark:text-gray-300">{{ $expense->category_label }}</td>
                                <td class="whitespace-nowrap px-5 py-3 text-gray-700 dark:text-gray-300">
                                    {{ $expense->method_label }}
                                    @if ($expense->reference)<p class="font-mono text-xs text-gray-500">{{ $expense->reference }}</p>@endif
                                </td>
                                <td class="whitespace-nowrap px-5 py-3 text-right font-medium text-gray-800 dark:text-white/90">{{ $money($expense->amount) }}</td>
                                <td class="whitespace-nowrap px-5 py-3 text-right">
                                    @if ($expense->status === 'paid')
                                        <button type="button" @click="voiding = !voiding" class="text-xs text-error-500 hover:underline">Void</button>
                                        <form x-show="voiding" x-cloak method="POST" action="{{ route('expenses.void', $expense) }}" class="mt-2 flex gap-1">
                                            @csrf
                                            <input type="text" name="reason" required maxlength="255" placeholder="Why?" class="h-8 w-32 rounded border border-gray-300 px-2 text-xs dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                                            <button type="submit" class="rounded bg-error-500 px-2 text-xs text-white">Void</button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="px-5 py-10 text-center text-gray-500 dark:text-gray-400"><i class="fas fa-receipt mb-3 text-4xl"></i><p>No expenses in this period</p></td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if ($expenses->hasPages())
                <div class="border-t border-gray-100 px-5 py-4 dark:border-gray-800">{{ $expenses->links() }}</div>
            @endif
        </div>

        <div class="space-y-6">
            @component('dashboard.partials.panel', ['title' => 'By kind', 'href' => route('reports.show', ['expenses', 'from' => $from, 'to' => $to]), 'link' => 'Report'])
                <ul class="divide-y divide-gray-100 text-sm dark:divide-gray-800">
                    @forelse ($byCategory as $category => $amount)
                        <li class="flex justify-between px-5 py-2.5">
                            <span class="text-gray-800 dark:text-white/90">{{ \App\Models\Expense::CATEGORIES[$category] ?? $category }}</span>
                            <span class="text-gray-700 dark:text-gray-300">{{ $money($amount) }}</span>
                        </li>
                    @empty
                        <li class="px-5 py-3 text-gray-500 dark:text-gray-400">Nothing yet.</li>
                    @endforelse
                </ul>
            @endcomponent

            @component('dashboard.partials.panel', ['title' => 'Owed to suppliers'])
                <ul class="divide-y divide-gray-100 text-sm dark:divide-gray-800">
                    @forelse ($owed as $row)
                        <li class="flex flex-wrap items-center justify-between gap-2 px-5 py-2.5">
                            <span>
                                <a href="{{ route('suppliers.show', $row['supplier']) }}" class="text-gray-800 hover:text-primary dark:text-white/90">{{ $row['supplier']->name }}</a>
                                @if ($row['supplier']->payment_terms)<span class="block text-xs text-gray-500">{{ $row['supplier']->payment_terms }}</span>@endif
                            </span>
                            <span class="flex items-center gap-2">
                                <span class="font-medium text-warning-600 dark:text-warning-400">{{ $money($row['balance']) }}</span>
                                <button type="button" @click="$dispatch('open-expense-form', @js(['category' => 'supplier_payment', 'supplier_id' => (string) $row['supplier']->id, 'amount' => $row['balance'], 'description' => 'Payment to ' . $row['supplier']->name]))" class="text-xs text-primary hover:underline">Pay</button>
                            </span>
                        </li>
                    @empty
                        <li class="px-5 py-3 text-gray-500 dark:text-gray-400">Nothing owed.</li>
                    @endforelse
                </ul>
            @endcomponent
        </div>
    </div>
</div>

@include('expenses.partials.modal-form')
@endsection
