@extends('layouts.app')

@php
    $currency = SystemHelper::currencySymbol();
    $format = function ($value, string $type) use ($currency) {
        if ($value === null || $value === '') {
            return '';
        }

        return match ($type) {
            'money' => ($value < 0 ? '− ' : '') . number_format(abs((float) $value), 2),
            'number' => number_format((float) $value, (float) $value == (int) $value ? 0 : 2),
            'percent' => number_format((float) $value, 1) . '%',
            'date' => \Carbon\Carbon::parse($value)->format('D d M Y'),
            default => (string) $value,
        };
    };
    $cardFormat = fn ($value, $type) => $type === 'money' ? $currency . ' ' . $format($value, 'money') : $format($value, $type);
    $alignRight = fn ($type) => in_array($type, ['money', 'number', 'percent'], true);
    $presets = [
        'This month' => [now()->startOfMonth(), today()],
        'Last month' => [now()->subMonthNoOverflow()->startOfMonth(), now()->subMonthNoOverflow()->endOfMonth()],
        'This year' => [now()->startOfYear(), today()],
        'Last 7 days' => [today()->subDays(6), today()],
    ];
    $inputClass = 'h-10 rounded-lg border border-gray-300 bg-transparent px-3 text-sm text-gray-800 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90';
@endphp

@section('content')
<div x-data="{ pageName: @js($title) }" class="print:hidden">
    @include('partials.breadcrumb')
</div>

<div class="space-y-6">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
        <div>
            <a href="{{ route('reports.index') }}" class="inline-flex items-center gap-2 text-sm text-gray-500 hover:text-gray-700 dark:text-gray-400 print:hidden"><i class="fas fa-arrow-left"></i> All reports</a>
            <h2 class="mt-2 text-2xl font-bold text-gray-800 dark:text-white/90">{{ $title }}</h2>
            <p class="text-gray-600 dark:text-gray-400">
                {{ $snapshot ? 'As at ' . today()->format('d M Y') : $from->format('d M Y') . ' – ' . $to->format('d M Y') }}
                <span class="print:hidden">· {{ $description }}</span>
            </p>
        </div>
        <div class="flex flex-wrap items-end gap-2 print:hidden">
            @unless ($snapshot)
                <form method="GET" action="{{ route('reports.show', $report) }}" class="flex flex-wrap items-end gap-2">
                    <input type="date" name="from" value="{{ $from->toDateString() }}" class="{{ $inputClass }}">
                    <input type="date" name="to" value="{{ $to->toDateString() }}" class="{{ $inputClass }}">
                    <button type="submit" class="h-10 rounded-lg bg-primary px-4 text-sm font-medium text-white hover:bg-primary/90">Show</button>
                </form>
            @endunless
            <button type="button" onclick="window.print()" class="inline-flex h-10 items-center gap-2 rounded-lg border border-gray-300 px-3 text-sm text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300"><i class="fas fa-print"></i> Print</button>
            <a href="{{ route('reports.show', [$report, 'from' => $from->toDateString(), 'to' => $to->toDateString(), 'export' => 'csv']) }}"
               class="inline-flex h-10 items-center gap-2 rounded-lg border border-gray-300 px-3 text-sm text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300"><i class="fas fa-file-csv"></i> Excel (CSV)</a>
        </div>
    </div>

    @unless ($snapshot)
        <div class="flex flex-wrap gap-2 print:hidden">
            @foreach ($presets as $label => [$start, $end])
                @php $active = $from->isSameDay($start) && $to->isSameDay($end); @endphp
                <a href="{{ route('reports.show', [$report, 'from' => $start->toDateString(), 'to' => $end->toDateString()]) }}"
                   class="rounded-full border px-3 py-1 text-xs font-medium {{ $active ? 'border-primary bg-primary/10 text-primary' : 'border-gray-300 text-gray-600 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-400' }}">{{ $label }}</a>
            @endforeach
        </div>
    @endunless

    <div class="grid grid-cols-2 gap-4 lg:grid-cols-4">
        @foreach ($data['cards'] as [$label, $value, $type])
            <div class="rounded-2xl border border-gray-100 bg-white p-5 dark:border-gray-800 dark:bg-gray-900">
                <p class="text-sm text-gray-500 dark:text-gray-400">{{ $label }}</p>
                <p class="mt-1 text-xl font-semibold text-gray-800 dark:text-white/90">{{ $cardFormat($value, $type) }}</p>
            </div>
        @endforeach
    </div>

    @foreach ($data['sections'] as $section)
        <div class="break-inside-avoid rounded-2xl border border-gray-100 bg-white dark:border-gray-800 dark:bg-gray-900">
            <div class="border-b border-gray-100 px-5 py-4 dark:border-gray-800">
                <h3 class="text-lg font-medium text-gray-800 dark:text-white/90">{{ $section['title'] }}</h3>
                @if (! empty($section['note']))<p class="text-xs text-gray-500 dark:text-gray-400">{{ $section['note'] }}</p>@endif
            </div>
            <div class="custom-scrollbar overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-gray-50 dark:bg-gray-900">
                        <tr class="whitespace-nowrap border-b border-gray-100 text-gray-700 dark:border-gray-800 dark:text-gray-400">
                            @foreach ($section['columns'] as [$label, $type])
                                <th class="px-5 py-3 font-medium {{ $alignRight($type) ? 'text-right' : 'text-left' }}">{{ $label }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        @forelse ($section['rows'] as $row)
                            <tr>
                                @foreach ($section['columns'] as $i => [$label, $type])
                                    <td class="px-5 py-2.5 {{ $alignRight($type) ? 'whitespace-nowrap text-right' : '' }} {{ $i === 0 ? 'text-gray-800 dark:text-white/90' : 'text-gray-700 dark:text-gray-300' }}">{{ $format($row[$i] ?? null, $type) }}</td>
                                @endforeach
                            </tr>
                        @empty
                            <tr><td colspan="{{ count($section['columns']) }}" class="px-5 py-6 text-center text-gray-500 dark:text-gray-400">Nothing in this period.</td></tr>
                        @endforelse
                    </tbody>
                    @if (! empty($section['footer']) && count($section['rows']))
                        <tfoot class="border-t-2 border-gray-200 font-semibold text-gray-800 dark:border-gray-700 dark:text-white/90">
                            <tr>
                                @foreach ($section['columns'] as $i => [$label, $type])
                                    <td class="whitespace-nowrap px-5 py-3 {{ $alignRight($type) ? 'text-right' : '' }}">{{ $i === 0 ? $section['footer'][0] : $format($section['footer'][$i] ?? null, $type) }}</td>
                                @endforeach
                            </tr>
                        </tfoot>
                    @endif
                </table>
            </div>
        </div>
    @endforeach

    <p class="text-xs text-gray-500 dark:text-gray-400">Amounts in {{ $currency }}. Printed {{ now()->format('d M Y H:i') }} by {{ auth()->user()->name }}.</p>
</div>
@endsection
