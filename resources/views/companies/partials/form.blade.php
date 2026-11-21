{{-- Company fields. @include('companies.partials.form', ['company' => Company|null]) --}}
@php
    $company ??= null;
    $f = 'h-11 w-full rounded-lg border border-gray-300 bg-white px-4 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10';
    $l = 'mb-1.5 block text-sm font-medium text-gray-700';
    $v = fn ($key, $default = '') => old($key, $company?->{$key} ?? $default);
@endphp
<div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
    <label class="sm:col-span-2"><span class="{{ $l }}">Company name *</span><input type="text" name="name" required maxlength="255" value="{{ $v('name') }}" class="{{ $f }}"></label>
    <label><span class="{{ $l }}">KRA PIN</span><input type="text" name="kra_pin" maxlength="20" value="{{ $v('kra_pin') }}" class="{{ $f }} uppercase"></label>
    <label><span class="{{ $l }}">Contact person</span><input type="text" name="contact_name" maxlength="255" value="{{ $v('contact_name') }}" class="{{ $f }}"></label>
    <label><span class="{{ $l }}">Email (statements go here)</span><input type="email" name="email" maxlength="255" value="{{ $v('email') }}" class="{{ $f }}"></label>
    <label><span class="{{ $l }}">Phone</span><input type="tel" name="phone" maxlength="30" value="{{ $v('phone') }}" class="{{ $f }}"></label>
    <label class="sm:col-span-2"><span class="{{ $l }}">Address</span><input type="text" name="address" maxlength="255" value="{{ $v('address') }}" class="{{ $f }}"></label>
    <label><span class="{{ $l }}">Room rate discount %</span><input type="number" name="rate_discount_percent" step="0.01" min="0" max="90" value="{{ $v('rate_discount_percent', 0) }}" class="{{ $f }}"></label>
    <label><span class="{{ $l }}">Pays within (days)</span><input type="number" name="payment_terms_days" min="0" max="365" value="{{ $v('payment_terms_days', 30) }}" class="{{ $f }}"></label>
    <label class="sm:col-span-2"><span class="{{ $l }}">Credit limit ({{ SystemHelper::currencyCode() }}, 0 = no limit)</span><input type="number" name="credit_limit" step="0.01" min="0" value="{{ $v('credit_limit', 0) }}" class="{{ $f }}"></label>
    <label class="sm:col-span-2"><span class="{{ $l }}">Notes</span><textarea name="notes" rows="2" maxlength="2000" class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm shadow-theme-xs focus:border-brand-300 focus:outline-hidden">{{ $v('notes') }}</textarea></label>
    @if ($company)
        <label class="flex items-center gap-2 text-sm text-gray-700 sm:col-span-2"><input type="hidden" name="is_active" value="0"><input type="checkbox" name="is_active" value="1" @checked($v('is_active', true)) class="h-4 w-4 rounded border-gray-300"> Active (can be chosen on new bookings)</label>
    @endif
</div>
