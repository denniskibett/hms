{{--
    Folio: invoice lines, tax breakdown, payments, and the dialogs to
    record a payment, add an extra charge or set a discount.
    Expects $stay with invoices.items and invoices.payments loaded, or another owner:
      @include('stays.partials.folio', ['folioOwner' => $event, 'folioRoutePrefix' => 'events'])
      @include('stays.partials.folio', ['folioOwner' => $order, 'folioRoutePrefix' => 'kitchen.orders'])
    Used on stays.show / events.show (front desk) and invoices.show (finance).
--}}
@php
    $owner = $folioOwner ?? $stay;
    $routePrefix = $folioRoutePrefix ?? 'stays';
    [$ownerLabel, $ownerCancelled, $chargeSuggestions] = match (true) {
        $owner instanceof \App\Models\FacilityBooking => ['Event', $owner->status === \App\Enums\FacilityBookingStatus::Cancelled,
            ['Extra tea break', 'Extra lunch', 'Projector hire', 'PA system', 'Decoration', 'Damages']],
        $owner instanceof \App\Models\KitchenOrder => ['Order', $owner->status === \App\Enums\KitchenOrderStatus::Cancelled,
            ['Packaging', 'Delivery', 'Corkage', 'Damages']],
        default => ['Booking', $owner->state === \App\Enums\StayStatus::Cancelled,
            ['Laundry', 'Minibar', 'Room service', 'Airport transfer', 'Extra bed', 'Late check-out', 'Damages']],
    };
    $currency = SystemHelper::currencySymbol();
    $money = fn ($value) => $currency . ' ' . number_format((float) $value, 2);
    $invoice = $owner->invoices->where('status', '!=', 'cancelled')->sortBy('id')->first();
    $methods = \App\Models\Payment::METHODS;
    $canManageMoney = auth()->user()->hasAnyRole(['admin', 'manager', 'finance']);
    $balance = $invoice ? round((float) $invoice->total - (float) $invoice->paid_amount, 2) : 0;
    $charges = $invoice ? round((float) $invoice->items->sum(fn ($i) => (float) $i->quantity * (float) $i->unit_price), 2) : 0;
    $inputClass = 'h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90';
    $labelClass = 'mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400';
    $secondaryButton = 'inline-flex items-center gap-2 rounded-lg border border-gray-300 px-3 py-2 text-sm text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-white/[0.03]';
    // Reopen the dialog that failed validation
    $openDialog = old('amount') !== null ? 'pay' : (old('unit_price') !== null ? 'charge' : (old('discount_amount') !== null ? 'discount' : null));
@endphp

<div x-data="{ dialog: @js($openDialog) }" class="rounded-2xl border border-gray-100 bg-white dark:border-gray-800 dark:bg-gray-900">
    <div class="flex flex-wrap items-center justify-between gap-3 border-b border-gray-100 px-5 py-4 dark:border-gray-800">
        <div>
            <h3 class="text-lg font-medium text-gray-800 dark:text-white/90">Folio</h3>
            @if ($invoice)
                <p class="flex items-center gap-2 text-xs text-gray-500 dark:text-gray-400">
                    Invoice {{ $invoice->invoice_number }} @include('partials.status-badge', ['status' => $invoice->display_status])
                </p>
            @endif
        </div>
        @if ($invoice)
            <div class="flex flex-wrap gap-2 print:hidden">
                <a href="{{ route("{$routePrefix}.invoice", $owner) }}" target="_blank" class="{{ $secondaryButton }}">
                    <i class="fas fa-print"></i> Invoice
                </a>
                <button type="button" @click="dialog = 'charge'" class="{{ $secondaryButton }}">
                    <i class="fas fa-plus"></i> Charge
                </button>
                @if ($canManageMoney)
                    <button type="button" @click="dialog = 'discount'" class="{{ $secondaryButton }}">
                        <i class="fas fa-percent"></i> Discount
                    </button>
                @endif
                @if ($balance > 0)
                    <button type="button" @click="dialog = 'pay'"
                            class="inline-flex items-center gap-2 rounded-lg bg-primary px-3 py-2 text-sm font-medium text-white hover:bg-primary/90">
                        <i class="fas fa-money-bill-wave"></i> Record Payment
                    </button>
                @endif
            </div>
        @endif
    </div>

    @if (! $invoice)
        <p class="p-5 text-sm text-gray-500 dark:text-gray-400">
            {{ $ownerCancelled ? $ownerLabel . ' cancelled; no charges.' : 'No invoice yet.' }}
        </p>
    @else
        <!-- Lines -->
        <table class="min-w-full text-sm">
            <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                @foreach ($invoice->items as $item)
                    <tr>
                        <td class="px-5 py-2 text-gray-700 dark:text-gray-300">
                            {{ $item->description }}
                            @if ($item->source_type === 'other')
                                <span class="ml-1 rounded bg-gray-100 px-1.5 py-0.5 text-[10px] uppercase text-gray-500 dark:bg-gray-800 dark:text-gray-400">extra</span>
                            @endif
                        </td>
                        <td class="whitespace-nowrap px-5 py-2 text-right text-gray-500 dark:text-gray-400">{{ rtrim(rtrim(number_format((float) $item->quantity, 2), '0'), '.') }} × {{ number_format((float) $item->unit_price, 2) }}</td>
                        <td class="whitespace-nowrap px-5 py-2 text-right text-gray-800 dark:text-white/90">
                            {{ $money((float) $item->quantity * (float) $item->unit_price) }}
                            @if ($canManageMoney && $item->source_type === 'other')
                                <form method="POST" action="{{ route("{$routePrefix}.charges.destroy", [$owner, $item]) }}" class="inline print:hidden"
                                      onsubmit="return confirm(@js("Remove the charge \"{$item->description}\"?"))">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" title="Remove charge" class="ml-2 text-gray-400 hover:text-error-500"><i class="fas fa-times"></i></button>
                                </form>
                            @endif
                        </td>
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
                            {{ $money($payment->amount) }} · {{ $payment->method_label }}
                        </p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">
                            {{ $payment->payment_reference }} · {{ $payment->received_at?->format('d M Y, H:i') }}
                            @if ($payment->status !== 'completed') · <span class="text-error-500">{{ $payment->status === 'cancelled' ? 'Voided' : ucfirst($payment->status) }}</span>@endif
                        </p>
                    </div>
                    @if ($payment->status === 'completed')
                        <div class="flex items-center gap-2 print:hidden">
                            <a href="{{ route("{$routePrefix}.receipt", [$owner, $payment]) }}" target="_blank" class="text-xs text-primary hover:underline">Receipt</a>
                            @if ($canManageMoney)
                                <button type="button" @click="voidOpen = !voidOpen" class="text-xs text-error-500 hover:underline">Void</button>
                            @endif
                        </div>
                        @if ($canManageMoney)
                            <form x-show="voidOpen" x-cloak method="POST" action="{{ route("{$routePrefix}.payments.void", [$owner, $payment]) }}" class="flex w-full gap-2">
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

        @if ($invoice->notes)
            <div class="border-t border-gray-100 px-5 py-4 text-xs text-gray-500 dark:border-gray-800 dark:text-gray-400">
                <p class="mb-1 font-medium">Notes</p>
                <p class="whitespace-pre-line">{{ $invoice->notes }}</p>
            </div>
        @endif

        <!-- Dialogs -->
        <div x-show="dialog" x-cloak x-transition class="fixed inset-0 z-[99999] flex items-center justify-center p-5" @keydown.escape.window="dialog = null">
            <div class="fixed inset-0 bg-gray-400/50 backdrop-blur-sm dark:bg-gray-900/80" @click="dialog = null"></div>

            @if ($balance > 0)
                <form x-show="dialog === 'pay'" method="POST" action="{{ route("{$routePrefix}.payments.store", $owner) }}" x-data="{ method: @js(old('method', 'cash')) }"
                      class="relative w-full max-w-md rounded-2xl bg-white p-6 dark:bg-gray-900">
                    @csrf
                    <h4 class="text-lg font-medium text-gray-800 dark:text-white/90">Record payment</h4>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Balance on {{ $invoice->invoice_number }}: <strong>{{ $money($balance) }}</strong></p>

                    <div class="mt-4 space-y-4">
                        <div>
                            <label class="{{ $labelClass }}">Amount ({{ $currency }}) *</label>
                            <input type="number" name="amount" step="0.01" min="0.01" max="{{ $balance }}" required
                                   value="{{ old('amount', $balance) }}" class="{{ $inputClass }}">
                        </div>
                        <div>
                            <label class="{{ $labelClass }}">Method *</label>
                            <select name="method" x-model="method" class="{{ $inputClass }}">
                                @foreach ($methods as $value => $label)
                                    <option value="{{ $value }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div x-show="method !== 'cash' && method !== 'credit_card'">
                            <label class="{{ $labelClass }}">Transaction code *</label>
                            <input type="text" name="payment_reference" maxlength="50" value="{{ old('payment_reference') }}"
                                   :required="method !== 'cash' && method !== 'credit_card'"
                                   placeholder="e.g. M-Pesa code QJK3XYZ12A" class="{{ $inputClass }} uppercase">
                        </div>
                        <div>
                            <label class="{{ $labelClass }}">Notes</label>
                            <input type="text" name="notes" maxlength="500" value="{{ old('notes') }}" placeholder="e.g. Deposit" class="{{ $inputClass }}">
                        </div>
                    </div>

                    <div class="mt-6 flex justify-end gap-3">
                        <button type="button" @click="dialog = null" class="rounded-lg border border-gray-300 px-4 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-white/[0.03]">Cancel</button>
                        <button type="submit" class="rounded-lg bg-primary px-4 py-2.5 text-sm font-medium text-white hover:bg-primary/90">Save payment</button>
                    </div>
                </form>
            @endif

            <form x-show="dialog === 'charge'" method="POST" action="{{ route("{$routePrefix}.charges.store", $owner) }}"
                  x-data="{ qty: @js((float) old('quantity', 1)), price: @js(old('unit_price', '')) }"
                  class="relative w-full max-w-md rounded-2xl bg-white p-6 dark:bg-gray-900">
                @csrf
                <h4 class="text-lg font-medium text-gray-800 dark:text-white/90">Add charge</h4>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Extras such as {{ Str::lower(implode(', ', array_slice($chargeSuggestions, 0, 2))) }} or damages. Prices include tax.</p>

                <div class="mt-4 space-y-4">
                    <div>
                        <label class="{{ $labelClass }}">Description *</label>
                        <input type="text" name="description" required maxlength="255" value="{{ old('description') }}"
                               list="folio-charge-suggestions" placeholder="e.g. Laundry" class="{{ $inputClass }}">
                        <datalist id="folio-charge-suggestions">
                            @foreach ($chargeSuggestions as $suggestion)
                                <option value="{{ $suggestion }}">
                            @endforeach
                        </datalist>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="{{ $labelClass }}">Quantity *</label>
                            <input type="number" name="quantity" x-model.number="qty" step="0.01" min="0.01" required class="{{ $inputClass }}">
                        </div>
                        <div>
                            <label class="{{ $labelClass }}">Unit price ({{ $currency }}) *</label>
                            <input type="number" name="unit_price" x-model="price" step="0.01" min="0.01" required class="{{ $inputClass }}">
                        </div>
                    </div>
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        Line total: <strong class="text-gray-800 dark:text-white/90" x-text="@js($currency) + ' ' + ((qty || 0) * (price || 0)).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })"></strong>
                    </p>
                </div>

                <div class="mt-6 flex justify-end gap-3">
                    <button type="button" @click="dialog = null" class="rounded-lg border border-gray-300 px-4 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-white/[0.03]">Cancel</button>
                    <button type="submit" class="rounded-lg bg-primary px-4 py-2.5 text-sm font-medium text-white hover:bg-primary/90">Add charge</button>
                </div>
            </form>

            @if ($canManageMoney)
                <form x-show="dialog === 'discount'" method="POST" action="{{ route("{$routePrefix}.discount", $owner) }}"
                      class="relative w-full max-w-md rounded-2xl bg-white p-6 dark:bg-gray-900">
                    @csrf
                    <h4 class="text-lg font-medium text-gray-800 dark:text-white/90">Discount</h4>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                        Amount off the total (charges {{ $money($charges) }}). Taxes are recalculated. Set 0 to remove.
                    </p>

                    <div class="mt-4 space-y-4">
                        <div>
                            <label class="{{ $labelClass }}">Discount ({{ $currency }}) *</label>
                            <input type="number" name="discount_amount" step="0.01" min="0" max="{{ $charges }}" required
                                   value="{{ old('discount_amount', (float) $invoice->discount_amount) }}" class="{{ $inputClass }}">
                        </div>
                        <div>
                            <label class="{{ $labelClass }}">Reason</label>
                            <input type="text" name="reason" maxlength="255" value="{{ old('reason') }}" placeholder="e.g. Returning guest, manager approved" class="{{ $inputClass }}">
                        </div>
                    </div>

                    <div class="mt-6 flex justify-end gap-3">
                        <button type="button" @click="dialog = null" class="rounded-lg border border-gray-300 px-4 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-white/[0.03]">Cancel</button>
                        <button type="submit" class="rounded-lg bg-primary px-4 py-2.5 text-sm font-medium text-white hover:bg-primary/90">Save discount</button>
                    </div>
                </form>
            @endif
        </div>
    @endif
</div>
