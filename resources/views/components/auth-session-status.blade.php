@props(['status'])
@if ($status)
    <div {{ $attributes->merge(['class' => 'mb-4 rounded-lg bg-success-50 px-4 py-3 text-sm text-success-700']) }} role="status">{{ $status }}</div>
@endif
