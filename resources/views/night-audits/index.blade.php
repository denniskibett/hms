@extends('layouts.app')

@php $money = fn ($v) => SystemHelper::currencySymbol() . ' ' . number_format((float) $v, 2); @endphp

@section('content')
<div x-data="{ pageName: `Night audit` }">@include('partials.breadcrumb')</div>

<div class="space-y-6">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
        <div>
            <h2 class="text-2xl font-bold text-gray-800">Night audit</h2>
            <p class="text-gray-600">Closes each day: marks no-shows, lists overstays and keeps the day's figures. Runs by itself at 03:00 for the day before.</p>
        </div>
        <form method="POST" action="{{ route('night-audits.store') }}" class="flex flex-wrap items-end gap-2"
              onsubmit="return confirm('Close this day? Guests due that day who never checked in will be marked no-show.')">
            @csrf
            <label><span class="mb-1 block text-xs font-medium text-gray-600">Day to close</span>
                <input type="date" name="business_date" value="{{ today()->toDateString() }}" max="{{ today()->toDateString() }}" required class="h-11 rounded-lg border border-gray-300 bg-white px-3 text-sm shadow-theme-xs"></label>
            <button class="h-11 rounded-lg bg-brand-500 px-4 text-sm font-medium text-white hover:bg-brand-600"><i class="fas fa-moon mr-1"></i> Run night audit</button>
        </form>
    </div>

    <div class="overflow-x-auto rounded-2xl border border-gray-200 bg-white">
        <table class="w-full min-w-[820px] text-sm">
            <thead class="border-b border-gray-100 text-left text-gray-500"><tr>
                <th class="px-5 py-3 font-medium">Day</th><th class="px-5 py-3 text-right font-medium">Occupancy</th><th class="px-5 py-3 text-right font-medium">Rooms sold</th>
                <th class="px-5 py-3 text-right font-medium">Room revenue</th><th class="px-5 py-3 text-right font-medium">ADR</th><th class="px-5 py-3 text-right font-medium">Money taken</th>
                <th class="px-5 py-3 font-medium">Flags</th><th class="px-5 py-3 font-medium">Closed by</th>
            </tr></thead>
            <tbody class="divide-y divide-gray-100">
                @forelse ($audits as $audit)
                    <tr class="hover:bg-gray-50">
                        <td class="px-5 py-3"><a href="{{ route('night-audits.show', $audit) }}" class="font-medium text-gray-800 hover:text-brand-600">{{ $audit->business_date->format('D d M Y') }}</a></td>
                        <td class="px-5 py-3 text-right">{{ $audit->figure('occupancy_percent') }}%</td>
                        <td class="px-5 py-3 text-right">{{ $audit->figure('rooms_sold') }} / {{ $audit->figure('rooms_sellable') }}</td>
                        <td class="px-5 py-3 text-right">{{ $money($audit->figure('room_revenue')) }}</td>
                        <td class="px-5 py-3 text-right">{{ $money($audit->figure('adr')) }}</td>
                        <td class="px-5 py-3 text-right font-semibold">{{ $money($audit->figure('payments_total')) }}</td>
                        <td class="px-5 py-3 text-xs">
                            @if (count($audit->figure('no_shows', [])))<span class="mr-1 rounded-full bg-orange-50 px-2 py-0.5 font-medium text-orange-700">{{ count($audit->figure('no_shows', [])) }} no-show</span>@endif
                            @if (count($audit->figure('overstays', [])))<span class="rounded-full bg-error-50 px-2 py-0.5 font-medium text-error-700">{{ count($audit->figure('overstays', [])) }} overstay</span>@endif
                        </td>
                        <td class="px-5 py-3 text-gray-500">{{ $audit->runner?->name ?? 'Automatic' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="px-5 py-10 text-center text-gray-500">No days closed yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    {{ $audits->links() }}
</div>
@endsection
