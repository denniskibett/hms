{{--
    Lifecycle buttons for one stay (check in / check out / edit / cancel / delete).
    Needs to sit inside an element with x-data="{ cancelOpen: false }" for the cancel dialog.
--}}
@use('App\Enums\StayStatus')
@php
    $state = $stay->state;
    $btn = 'inline-flex items-center gap-2 rounded-lg px-4 py-2.5 text-sm font-medium shadow-theme-xs transition';
@endphp

<div class="flex flex-wrap items-center gap-2 print:hidden">
    @if ($state->canTransitionTo(StayStatus::CheckedIn))
        @if ($stay->arrival_date->lte(today()))
            <form method="POST" action="{{ route('stays.check-in', $stay) }}">
                @csrf
                <button type="submit" class="{{ $btn }} bg-success-500 text-white hover:bg-success-600">
                    <i class="fas fa-sign-in-alt"></i> Check In
                </button>
            </form>
        @else
            <span class="{{ $btn }} cursor-not-allowed border border-gray-200 text-gray-400 dark:border-gray-800" title="Arrives {{ $stay->arrival_date->toFormattedDateString() }}">
                <i class="fas fa-sign-in-alt"></i> Check In
            </span>
        @endif
    @endif

    @if ($state->canTransitionTo(StayStatus::CheckedOut))
        <form method="POST" action="{{ route('stays.check-out', $stay) }}"
              onsubmit="return confirm(@js($stay->departure_date->gt(today())
                  ? "This guest is leaving early (booked until {$stay->departure_date->toFormattedDateString()}). The stay will be shortened to today. Check out?"
                  : 'Check out this stay?'))">
            @csrf
            <button type="submit" class="{{ $btn }} bg-warning-500 text-white hover:bg-warning-600">
                <i class="fas fa-sign-out-alt"></i> Check Out
            </button>
        </form>
    @endif

    @if ($state->isEditable())
        <a href="{{ route('stays.edit', $stay) }}" class="{{ $btn }} border border-gray-300 bg-white text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-white/[0.03]">
            <i class="fas fa-edit"></i> {{ $state === StayStatus::CheckedIn ? 'Extend / Shorten' : 'Edit' }}
        </a>
    @endif

    @if ($state->canTransitionTo(StayStatus::Cancelled))
        <button type="button" @click="cancelOpen = true" class="{{ $btn }} border border-error-300 text-error-600 hover:bg-error-50 dark:border-error-500/40 dark:text-error-400 dark:hover:bg-error-500/10">
            <i class="fas fa-ban"></i> Cancel Booking
        </button>
    @endif

    @if ($state === StayStatus::Cancelled && auth()->user()->hasAnyRole(['admin', 'manager']))
        <form method="POST" action="{{ route('stays.destroy', $stay) }}" onsubmit="return confirm('Delete this cancelled booking permanently?')">
            @csrf
            @method('DELETE')
            <button type="submit" class="{{ $btn }} bg-error-500 text-white hover:bg-error-600">
                <i class="fas fa-trash"></i> Delete
            </button>
        </form>
    @endif
</div>
