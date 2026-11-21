{{-- Site logo. Inline SVG (white over photos, brand colours once the header is solid) when an SVG logo exists, otherwise the uploaded image. --}}
@php
    $svgPath = public_path('images/site/logo.svg');
    $class ??= 'h-16 w-16';
@endphp
@if (is_file($svgPath))
    <span class="site-logo inline-block {{ $class }}" aria-hidden="true">{!! file_get_contents($svgPath) !!}</span>
@else
    <img src="{{ SystemHelper::logoUrl() }}" alt="" class="{{ $class }} object-contain">
@endif
