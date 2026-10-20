{{--
    Stay folio: invoice lines, tax breakdown, payments, record-payment dialog.
    Expects $stay with invoices.items and invoices.payments loaded.
    Sits inside an element whose x-data has payOpen.
--}}
@php
    $currency = SystemHelper::currencySymbol();
    $money = fn ($value) => $currency . ' ' . number_format((float) $value, 2);
    $invoice = $stay->invoices->where('status', '!=', 'cancelled')->sortBy('id')->first();
    $methods = \App\Http\Controllers\StayBillingController::METHODS;
    $canVoid = auth()->user()->hasAnyRole(['admin', 'manager', 'finance']);
    $balance = $invoice ? round((float) $invoice->total - (float) $invoice->paid_amount, 2) : 0;
    $inputClass = 'h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90';
@endphp

<div class="rounded-2xl border border-gray-100 bg-white dark:border-gray-800 dark:bg-gray-900">
    <div class="flex flex-wrap items-center justify-between gap-3 border-b border-gray-100 px-5 py-4 dark:border-gray-800">
        <div>
            <h3 class="text-lg font-medium text-gray-800 dark:text-white/90">Folio</h3>
            @if ($invoice)
                <p class="text-xs text-gray-500 dark:text-gray-400">Invoice {{ $invoice->invoice_number }} • {{ ucfirst($invoice->status) }}</p>
            @endif
        </div>
        @if ($invoice)
            <div class="flex gap-2 print:hidden">
                <a href="{{ route('stays.invoice', $stay) }}" target="_blank"
                   class="inline-flex items-center gap-2 rounded-lg border border-gray-300 px-3 py-2 text-sm text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-white/[0.03]">
                    <i class="fas fa-print"></i> Invoice
                </a>
                @if ($balance > 0)
                    <button type="button" @click="payOpen = true"
                            class="inline-flex items-center gap-2 rounded-lg bg-primary px-3 py-2 text-sm font-medium text-white hover:bg-primary/90">
                        <i class="fas fa-money-bill-wave"></i> Record Payment
                    </button>
                @endif
            </div>
        @endif
    </div>

    @if (! $invoice)
        <p class="p-5 text-sm text-gray-500 dark:text-gray-400">
            {{ $stay->state === \App\Enums\StayStatus::Cancelled ? 'Booking cancelled; no charges.' : 'No invoice yet.' }}
        </p>
    @else
        <!-- Lines -->
        <table class="min-w-full text-sm">
            <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                @foreach ($invoice->items as $item)
                    <tr>
                        <td class="px-5 py-2 text-gray-700 dark:text-gray-300">{{ $item->description }}</td>
                        <td class="whitespace-nowrap px-5 py-2 text-right text-gray-500 dark:text-gray-400">{{ rtrim(rtrim(number_format((float) $item->quantity, 2), '0'), '.') }} × {{ number_format((float) $item->unit_price, 2) }}</td>
                        <td class="whitespace-nowrap px-5 py-2 text-right text-gray-800 dark:text-white/90">{{ $money((float) $item->quantity * (float) $item->unit_price) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <!-- Totals -->
        <dl class="space-y-1.5 border-t border-gray-100 px-5 py-4 text-sm dark:border-gray-800">
            @if ((float) $invoice->discount_amount > 0)
                <div class="flex justify-between"><dt class="text-gray-500 dark:text-gray-400">Discount</dt><dd class="text-gray-800 dark:text-white/90">− {{ $money($invoice->discount_amount) }}</dd></div>
            @endif
            <div class="flex justify-between text-base font-semibold"><dt class="text-gray-800 dark:text-white/90">Total</dt><dd class="text-gray-800 dark:text-white/90">{{ $money($invoice->total) }}</dd></div>
            <div class="flex justify-between text-xs text-gray-500 dark:text-gray-400">
                <dt>{{ $invoice->tax_inclusive ? 'Includes' : 'Plus' }} VAT {{ (float) $invoice->vat_rate }}%</dt><dd>{{ $money($invoice->vat_amount) }}</dd>
            </div>
            <div class="flex justify-between text-xs text-gray-500 dark:text-gray-400">
                <dt>{{ $invoice->tax_inclusive ? 'Includes' : 'Plus' }} Tourism Levy {{ (float) $invoice->levy_rate }}%</dt><dd>{{ $money($invoice->levy_amount) }}</dd>
            </div>
            <div class="flex justify-between pt-1"><dt class="text-gray-500 dark:text-gray-400">Paid</dt><dd class="text-success-600 dark:text-success-500">{{ $money($invoice->paid_amount) }}</dd></div>
            <div class="flex justify-between font-semibold">
                <dt class="text-gray-800 dark:text-white/90">Balance</dt>
                <dd class="{{ $balance > 0 ? 'text-error-600 dark:text-error-400' : 'text-success-600 dark:text-success-500' }}">{{ $money($balance) }}</dd>
            </div>
        </dl>

        <!-- Payments -->
        <div class="border-t border-gray-100 px-5 py-4 dark:border-gray-800">
            <p class="mb-2 text-sm font-medium text-gray-700 dark:text-gray-400">Payments</p>
            @forelse ($invoice->payments->sortByDesc('received_at') as $payment)
                <div x-data="{ voidOpen: false }" class="flex flex-wrap items-center justify-between gap-2 rounded-lg px-2 py-2 text-sm {{ $payment->status !== 'completed' ? 'opacity-60' : '' }}">
                    <div>
                        <p class="font-medium text-gray-800 dark:text-white/90 {{ $payment->status !== 'completed' ? 'line-through' : '' }}">
                            {{ $money($payment->amount) }} · {{ $methods[$payment->method] ?? ucfirst(str_replace('_', ' ', $payment->method)) }}
                        </p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">
                            {{ $payment->payment_reference }} · {{ $payment->received_at?->format('d M Y, H:i') }}
                            @if ($payment->status !== 'completed') · <span class="text-error-500">{{ $payment->status === 'cancelled' ? 'Voided' : ucfirst($payment->status) }}</span>@endif
                        </p>
                    </div>
                    @if ($payment->status === 'completed')
                        <div class="flex items-center gap-2 print:hidden">
                            <a href="{{ route('stays.receipt', [$stay, $payment]) }}" target="_blank" class="text-xs text-primary hover:underline">Receipt</a>
                            @if ($canVoid)
                                <button type="button" @click="voidOpen = !voidOpen" class="text-xs text-error-500 hover:underline">Void</button>
                            @endif
                        </div>
                        @if ($canVoid)
                            <form x-show="voidOpen" x-cloak method="POST" action="{{ route('stays.payments.void', [$stay, $payment]) }}" class="flex w-full gap-2">
                                @csrf
                                <input type="text" name="reason" required maxlength="255" placeholder="Why is this payment being voided?" class="{{ $inputClass }} h-9">
                                <button type="submit" class="shrink-0 rounded-lg bg-error-500 px-3 text-xs font-medium text-white hover:bg-error-600">Void payment</button>
                            </form>
                        @endif
                    @endif
                </div>
            @empty
                <p class="text-sm text-gray-500 dark:text-gray-400">No payments yet.</p>
            @endforelse
        </div>
    @endif
</div>

@if ($invoice && $balance > 0)
    <!-- Record payment dialog -->
    <div x-show="payOpen" x-cloak x-transition class="fixed inset-0 z-[99999] flex items-center justify-center p-5">
        <div class="fixed inset-0 bg-gray-400/50 backdrop-blur-sm dark:bg-gray-900/80" @click="payOpen = false"></div>

        <form method="POST" action="{{ route('stays.payments.store', $stay) }}" x-data="{ method: @js(old('method', 'cash')) }"
              class="relative w-full max-w-md rounded-2xl bg-white p-6 dark:bg-gray-900">
            @csrf
            <h4 class="text-lg font-medium text-gray-800 dark:text-white/90">Record payment</h4>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Balance on {{ $invoice->invoice_number }}: <strong>{{ $money($balance) }}</strong></p>

            <div class="mt-4 space-y-4">
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Amount ({{ $currency }}) *</label>
                    <input type="number" name="amount" step="0.01" min="0.01" max="{{ $balance }}" required
                           value="{{ old('amount', $balance) }}" class="{{ $inputClass }}">
                </div>
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Method *</label>
                    <select name="method" x-model="method" class="{{ $inputClass }}">
                        @foreach ($methods as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div x-show="method !== 'cash' && method !== 'credit_card'">
                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Transaction code *</label>
                    <input type="text" name="payment_reference" maxlength="50" value="{{ old('payment_reference') }}"
                           :required="method !== 'cash' && method !== 'credit_card'"
                           placeholder="e.g. M-Pesa code QJK3XYZ12A" class="{{ $inputClass }} uppercase">
                </div>
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Notes</label>
                    <input type="text" name="notes" maxlength="500" value="{{ old('notes') }}" placeholder="e.g. Deposit" class="{{ $inputClass }}">
                </div>
            </div>

            <div class="mt-6 flex justify-end gap-3">
                <button type="button" @click="payOpen = false" class="rounded-lg border border-gray-300 px-4 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-white/[0.03]">Cancel</button>
                <button type="submit" class="rounded-lg bg-primary px-4 py-2.5 text-sm font-medium text-white hover:bg-primary/90">Save payment</button>
            </div>
        </form>
    </div>
@endif
