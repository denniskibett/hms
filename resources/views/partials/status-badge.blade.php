{{--
    Status pill for any status enum that has label() and badgeClasses().
    Usage: @include('partials.status-badge', ['status' => $room->status])
--}}
@if ($status)
    <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium {{ $status->badgeClasses() }}">
        {{ $status->label() }}
    </span>
@endif
