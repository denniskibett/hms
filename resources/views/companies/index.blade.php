@extends('layouts.app')

@php $money = fn ($v) => SystemHelper::currencySymbol() . ' ' . number_format((float) $v, 2); @endphp

@section('content')
<div x-data="{ pageName: `Company accounts` }">@include('partials.breadcrumb')</div>

<div class="space-y-6" x-data="{ open: @js($errors->any()) }">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="text-2xl font-bold text-gray-800">Company accounts</h2>
            <p class="text-gray-600">Companies whose staff stay or hold events here and pay later on a statement.</p>
        </div>
        @if (auth()->user()->hasAnyRole(['admin', 'manager', 'finance']))
            <button type="button" @click="open = true" class="inline-flex items-center gap-2 rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-medium text-white shadow-theme-xs hover:bg-brand-600"><i class="fas fa-plus"></i> Add company</button>
        @endif
    </div>

    <form method="GET" class="flex gap-2">
        <input type="search" name="search" value="{{ $search }}" placeholder="Name, KRA PIN or contact" class="h-11 w-full max-w-md rounded-lg border border-gray-300 bg-white px-4 text-sm shadow-theme-xs">
        <button class="rounded-lg border border-gray-300 bg-white px-4 text-sm font-medium text-gray-700 hover:bg-gray-50">Search</button>
    </form>

    <div class="overflow-x-auto rounded-2xl border border-gray-200 bg-white">
        <table class="w-full min-w-[720px] text-sm">
            <thead class="border-b border-gray-100 text-left text-gray-500"><tr>
                <th class="px-5 py-3 font-medium">Company</th><th class="px-5 py-3 font-medium">Terms</th><th class="px-5 py-3 font-medium">Rate</th>
                <th class="px-5 py-3 text-right font-medium">Credit limit</th><th class="px-5 py-3 text-right font-medium">Owes</th>
            </tr></thead>
            <tbody class="divide-y divide-gray-100">
                @forelse ($companies as $company)
                    @php $owes = (float) ($balances[$company->id] ?? 0); @endphp
                    <tr class="hover:bg-gray-50 {{ $company->is_active ? '' : 'opacity-60' }}">
                        <td class="px-5 py-3"><a href="{{ route('companies.show', $company) }}" class="font-medium text-gray-800 hover:text-brand-600">{{ $company->name }}</a>
                            <span class="block text-xs text-gray-500">{{ collect([$company->contact_name, $company->phone, $company->kra_pin])->filter()->join(' · ') }}{{ $company->is_active ? '' : ' · inactive' }}</span></td>
                        <td class="px-5 py-3 text-gray-600">{{ $company->payment_terms_days }} days</td>
                        <td class="px-5 py-3 text-gray-600">{{ (float) $company->rate_discount_percent > 0 ? rtrim(rtrim(number_format((float) $company->rate_discount_percent, 2), '0'), '.') . '% off' : 'Rack rate' }}</td>
                        <td class="px-5 py-3 text-right text-gray-600">{{ $company->hasCreditLimit() ? $money($company->credit_limit) : 'No limit' }}</td>
                        <td class="px-5 py-3 text-right font-semibold {{ $owes > 0 ? 'text-gray-900' : 'text-gray-400' }}">{{ $money($owes) }}
                            @if ($company->hasCreditLimit() && $owes > (float) $company->credit_limit)<span class="block text-xs font-medium text-error-600">Over limit</span>@endif</td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-5 py-10 text-center text-gray-500">No company accounts yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    {{ $companies->links() }}

    <div x-show="open" x-cloak class="fixed inset-0 z-99999 flex items-center justify-center p-5" @keydown.escape.window="open = false">
        <div class="fixed inset-0 bg-gray-400/50 backdrop-blur-xs" @click="open = false"></div>
        <form method="POST" action="{{ route('companies.store') }}" class="relative max-h-[90vh] w-full max-w-2xl overflow-y-auto rounded-2xl bg-white p-6">
            @csrf
            <h3 class="mb-4 text-lg font-semibold text-gray-800">Add company</h3>
            @if ($errors->any())<div class="mb-4 rounded-lg bg-error-50 p-3 text-sm text-error-700">@foreach ($errors->all() as $e)<p>{{ $e }}</p>@endforeach</div>@endif
            @include('companies.partials.form')
            <div class="mt-6 flex justify-end gap-3">
                <button type="button" @click="open = false" class="rounded-lg border border-gray-300 px-4 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50">Cancel</button>
                <button class="rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-medium text-white hover:bg-brand-600">Save company</button>
            </div>
        </form>
    </div>
</div>
@endsection
