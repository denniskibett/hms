@extends('layouts.app')

@use('App\Models\GuestMessage')

@php
    $input = 'h-10 rounded-lg border border-gray-300 bg-transparent px-3 text-sm text-gray-800 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90';
@endphp

@section('content')
<div x-data="{ pageName: `Guest Messages` }">
    @include('partials.breadcrumb')
</div>

<div class="space-y-6">
    <div>
        <h2 class="text-2xl font-bold text-gray-800 dark:text-white/90">Guest Messages</h2>
        <p class="text-gray-600 dark:text-gray-400">Every SMS and email sent to guests: automatic ones (booking confirmed, welcome, receipts) and ones typed by staff.</p>
    </div>

    @if (GuestMessage::inTestMode())
        <div class="rounded-lg border border-info-200 bg-info-50 px-4 py-3 text-sm text-info-700 dark:border-info-500/30 dark:bg-info-500/10 dark:text-info-400">
            <i class="fas fa-flask mr-1"></i> <strong>SMS test mode.</strong> Messages are recorded here but not sent to phones. They go out once the SMS account is connected.
        </div>
    @endif

    <div class="flex gap-2 overflow-x-auto border-b border-gray-200 dark:border-gray-800">
        <a href="{{ route('guest-messages.index') }}"
           class="-mb-px whitespace-nowrap border-b-2 px-4 py-2 text-sm font-medium {{ $tab === 'history' ? 'border-primary text-primary' : 'border-transparent text-gray-500 hover:text-gray-700 dark:text-gray-400' }}">
            Sent messages
            @if ($failedCount)<span class="ml-1 rounded-full bg-error-500 px-2 text-xs text-white">{{ $failedCount }} failed</span>@endif
        </a>
        <a href="{{ route('guest-messages.index', ['tab' => 'templates']) }}"
           class="-mb-px whitespace-nowrap border-b-2 px-4 py-2 text-sm font-medium {{ $tab === 'templates' ? 'border-primary text-primary' : 'border-transparent text-gray-500 hover:text-gray-700 dark:text-gray-400' }}">
            Automatic messages
        </a>
    </div>

    @if ($tab === 'history')
        <form method="GET" class="flex flex-wrap gap-2">
            <input type="search" name="search" value="{{ $filters['search'] ?? '' }}" placeholder="Guest, phone, email or text" class="{{ $input }} w-full sm:w-64">
            <select name="channel" class="{{ $input }}">
                <option value="">SMS and email</option>
                @foreach (GuestMessage::CHANNELS as $value => $label)
                    <option value="{{ $value }}" @selected(($filters['channel'] ?? '') === $value)>{{ $label }}</option>
                @endforeach
            </select>
            <select name="status" class="{{ $input }}">
                <option value="">Any status</option>
                @foreach (GuestMessage::STATUSES as $value => $label)
                    <option value="{{ $value }}" @selected(($filters['status'] ?? '') === $value)>{{ $label }}</option>
                @endforeach
            </select>
            <button type="submit" class="rounded-lg bg-primary px-4 text-sm font-medium text-white hover:bg-primary/90">Filter</button>
        </form>

        <div class="rounded-2xl border border-gray-100 bg-white dark:border-gray-800 dark:bg-gray-900">
            @include('guest-messages.partials.list', ['messages' => $messages, 'showGuest' => true])
        </div>

        @if ($messages->hasPages())
            <div>{{ $messages->links() }}</div>
        @endif
    @else
        <form method="POST" action="{{ route('guest-messages.templates') }}" class="space-y-4">
            @csrf
            @method('PUT')
            <p class="text-sm text-gray-600 dark:text-gray-400">
                These go out by SMS, and by email when the guest has an address. Words in braces are filled in for each guest:
                <code class="text-xs">{name} {hotel} {hotel_phone} {reference} {arrival} {departure} {nights} {total} {room} {reason} {amount} {method} {payment_ref} {balance}</code>.
                One SMS holds 160 characters; longer messages are sent in parts and cost more.
            </p>

            @foreach (GuestMessage::TEMPLATES as $key => $template)
                <div x-data="{ text: @js(old("templates.{$key}", GuestMessage::templateBody($key))) }"
                     class="rounded-2xl border border-gray-100 bg-white p-5 dark:border-gray-800 dark:bg-gray-900">
                    <div class="mb-2 flex flex-wrap items-center justify-between gap-2">
                        <label class="flex items-center gap-2 text-sm font-medium text-gray-800 dark:text-white/90">
                            <input type="checkbox" name="auto[{{ $key }}]" value="1" @checked(GuestMessage::autoSends($key)) @disabled(! $canEditTemplates) class="rounded border-gray-300">
                            {{ $template['label'] }}
                        </label>
                        <span class="text-xs text-gray-500" x-text="`${text.length} characters · ${Math.max(1, Math.ceil(text.length / 153))} SMS`"></span>
                    </div>
                    <textarea name="templates[{{ $key }}]" x-model="text" rows="3" maxlength="480" required @disabled(! $canEditTemplates)
                              class="w-full rounded-lg border border-gray-300 bg-transparent px-3 py-2 text-sm text-gray-800 dark:border-gray-700 dark:text-white/90"></textarea>
                    @if ($canEditTemplates)
                        <button type="button" @click="text = @js($template['body'])" class="mt-1 text-xs text-gray-500 hover:text-primary">Reset to original wording</button>
                    @endif
                </div>
            @endforeach

            @if ($canEditTemplates)
                <button type="submit" class="rounded-lg bg-primary px-5 py-2.5 text-sm font-medium text-white hover:bg-primary/90">Save</button>
            @else
                <p class="text-sm text-gray-500">Only a manager can change these.</p>
            @endif
        </form>
    @endif
</div>
@endsection
