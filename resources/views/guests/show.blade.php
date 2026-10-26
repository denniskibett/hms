@extends('layouts.app')

@php
    $canDelete = auth()->user()->hasAnyRole(['admin', 'manager']);
    $currency = SystemHelper::currencySymbol();
    $user = $guest->user;
    $contact = array_filter($guest->emergency_contact ?? []);
    $tags = $guest->preferences['tags'] ?? [];
    $allergies = $guest->preferences['allergies'] ?? null;
    $notes = $guest->preferences['notes'] ?? null;
@endphp

@section('content')
<div x-data="{ pageName: @js($guest->name) }">
    @include('partials.breadcrumb')
</div>

<div class="space-y-6"
     x-data="guestPage(@js(['destroyUrl' => route('guests.destroy', $guest), 'indexUrl' => route('guests.index')]))"
     x-on:guest-saved.window="window.location.reload()">
    <!-- Header -->
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <a href="{{ route('guests.index') }}" class="inline-flex items-center gap-2 text-sm text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300">
                <i class="fas fa-arrow-left"></i> Back to Guests
            </a>
            <div class="mt-2 flex items-center gap-3">
                <div class="flex h-12 w-12 items-center justify-center rounded-full bg-primary/10 text-lg font-semibold text-primary">
                    {{ Str::upper(Str::substr($guest->name, 0, 1)) }}
                </div>
                <div>
                    <h2 class="text-2xl font-bold text-gray-800 dark:text-white/90">{{ $guest->name }}</h2>
                    <p class="text-gray-600 dark:text-gray-400">Guest since {{ $guest->created_at?->format(SystemHelper::dateFormat()) ?? '—' }}</p>
                </div>
            </div>
        </div>

        <div class="flex flex-wrap items-center gap-3">
            <button type="button" @click="$dispatch('open-guest-form', @js($guest->formData()))"
                    class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-800 shadow-theme-xs hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-white/[0.03]">
                <i class="fas fa-edit"></i> Edit
            </button>
            @if ($canDelete && $stays->isEmpty())
                <button type="button" @click="destroy()" :disabled="isLoading"
                        class="inline-flex items-center gap-2 rounded-lg border border-error-300 bg-white px-4 py-2.5 text-sm font-medium text-error-600 shadow-theme-xs hover:bg-error-50 dark:border-error-500/30 dark:bg-gray-800 dark:text-error-500">
                    <i class="fas fa-trash"></i> Delete
                </button>
            @endif
            <a href="{{ route('stays.create', ['guest_id' => $guest->user_id]) }}"
               class="inline-flex items-center gap-2 rounded-lg bg-primary px-4 py-2.5 text-sm font-medium text-white shadow-theme-xs hover:bg-primary/90">
                <i class="fas fa-calendar-plus"></i> New Booking
            </a>
        </div>
    </div>

    <!-- Totals (cancelled stays excluded) -->
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
        @include('partials.stat-card', ['label' => 'Stays', 'value' => $totals['stays'], 'icon' => 'fa-calendar-check', 'tone' => 'primary'])
        @include('partials.stat-card', ['label' => 'Nights', 'value' => $totals['nights'], 'icon' => 'fa-moon', 'tone' => 'info'])
        @include('partials.stat-card', ['label' => 'Total Billed', 'value' => $currency . ' ' . number_format($totals['billed'], 2), 'icon' => 'fa-file-invoice', 'tone' => 'gray'])
        @include('partials.stat-card', ['label' => 'Balance Due', 'value' => $currency . ' ' . number_format($totals['balance'], 2), 'icon' => 'fa-wallet', 'tone' => $totals['balance'] > 0 ? 'error' : 'success'])
    </div>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        <!-- Stay history -->
        <div class="space-y-6 lg:col-span-2">
            <div class="rounded-2xl border border-gray-100 bg-white dark:border-gray-800 dark:bg-gray-900">
                <div class="border-b border-gray-100 px-5 py-4 dark:border-gray-800">
                    <h3 class="text-lg font-medium text-gray-800 dark:text-white/90">Bookings</h3>
                </div>
                @include('stays.partials.table', ['stays' => $stays, 'showGuest' => false, 'empty' => 'No bookings yet'])
            </div>

            @if ($guest->user)
                @include('guest-messages.partials.card', ['messageGuest' => $guest->user])
            @endif
        </div>

        <!-- Profile -->
        <div class="space-y-6">
            <div class="rounded-2xl border border-gray-100 bg-white dark:border-gray-800 dark:bg-gray-900">
                <div class="border-b border-gray-100 px-5 py-4 dark:border-gray-800">
                    <h3 class="text-lg font-medium text-gray-800 dark:text-white/90">Contact & ID</h3>
                </div>
                <dl class="space-y-3 p-5 text-sm">
                    <div>
                        <dt class="text-gray-500 dark:text-gray-400">Phone</dt>
                        <dd>@if ($user?->phone)<a href="tel:{{ $user->phone }}" class="text-gray-800 hover:text-primary dark:text-white/90">{{ $user->phone }}</a>@else — @endif</dd>
                    </div>
                    <div>
                        <dt class="text-gray-500 dark:text-gray-400">Email</dt>
                        <dd>@if ($user?->email)<a href="mailto:{{ $user->email }}" class="break-all text-gray-800 hover:text-primary dark:text-white/90">{{ $user->email }}</a>@else — @endif</dd>
                    </div>
                    <div>
                        <dt class="text-gray-500 dark:text-gray-400">{{ $guest->id_type_label ?? 'ID' }}</dt>
                        <dd class="text-gray-800 dark:text-white/90">
                            @if ($guest->id_number)
                                {{ $guest->id_number }}
                            @else
                                <span class="text-warning-600 dark:text-warning-500"><i class="fas fa-exclamation-circle"></i> Not captured — needed at check-in</span>
                            @endif
                        </dd>
                    </div>
                    <div>
                        <dt class="text-gray-500 dark:text-gray-400">Nationality</dt>
                        <dd class="text-gray-800 dark:text-white/90">{{ $guest->nationality ?? '—' }}</dd>
                    </div>
                    @if ($user?->dob)
                        <div>
                            <dt class="text-gray-500 dark:text-gray-400">Date of birth</dt>
                            <dd class="text-gray-800 dark:text-white/90">{{ \Carbon\Carbon::parse($user->dob)->format(SystemHelper::dateFormat()) }}</dd>
                        </div>
                    @endif
                    @if ($guest->address)
                        <div>
                            <dt class="text-gray-500 dark:text-gray-400">Address</dt>
                            <dd class="text-gray-800 dark:text-white/90">{{ $guest->address }}</dd>
                        </div>
                    @endif
                </dl>
            </div>

            <div class="rounded-2xl border border-gray-100 bg-white dark:border-gray-800 dark:bg-gray-900">
                <div class="border-b border-gray-100 px-5 py-4 dark:border-gray-800">
                    <h3 class="text-lg font-medium text-gray-800 dark:text-white/90">Emergency Contact</h3>
                </div>
                <div class="p-5 text-sm">
                    @if ($contact)
                        <p class="font-medium text-gray-800 dark:text-white/90">{{ $contact['name'] ?? '—' }}</p>
                        @if (! empty($contact['relationship']))
                            <p class="text-gray-500 dark:text-gray-400">{{ Str::headline($contact['relationship']) }}</p>
                        @endif
                        @if (! empty($contact['phone']))
                            <a href="tel:{{ $contact['phone'] }}" class="mt-1 inline-block text-gray-800 hover:text-primary dark:text-white/90">{{ $contact['phone'] }}</a>
                        @endif
                    @else
                        <p class="text-gray-500 dark:text-gray-400">None recorded</p>
                    @endif
                </div>
            </div>

            <div class="rounded-2xl border border-gray-100 bg-white dark:border-gray-800 dark:bg-gray-900">
                <div class="border-b border-gray-100 px-5 py-4 dark:border-gray-800">
                    <h3 class="text-lg font-medium text-gray-800 dark:text-white/90">Preferences</h3>
                </div>
                <div class="space-y-4 p-5 text-sm">
                    @if ($tags)
                        <div class="flex flex-wrap gap-1.5">
                            @foreach ($tags as $tag)
                                <span class="rounded-full bg-primary/10 px-2.5 py-1 text-xs font-medium text-primary">{{ $tag }}</span>
                            @endforeach
                        </div>
                    @endif
                    @if ($allergies)
                        <div>
                            <p class="font-medium text-error-600 dark:text-error-500"><i class="fas fa-allergies"></i> Allergies / dietary</p>
                            <p class="mt-1 text-gray-800 dark:text-white/90">{{ $allergies }}</p>
                        </div>
                    @endif
                    @if ($notes)
                        <div>
                            <p class="font-medium text-gray-500 dark:text-gray-400">Notes</p>
                            <p class="mt-1 whitespace-pre-line text-gray-800 dark:text-white/90">{{ $notes }}</p>
                        </div>
                    @endif
                    @unless ($tags || $allergies || $notes)
                        <p class="text-gray-500 dark:text-gray-400">None recorded</p>
                    @endunless
                </div>
            </div>
        </div>
    </div>

    @if ($guest->user)
        @include('guest-messages.partials.send-modal', ['messageGuest' => $guest->user])
    @endif
</div>

@include('guests.partials.modal-form')

<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('guestPage', (config) => ({
        isLoading: false,
        messageOpen: @js($errors->has('body') || $errors->has('channels')),

        async destroy() {
            if (!confirm(@js("Delete {$guest->name}? This cannot be undone."))) return;
            this.isLoading = true;

            const response = await fetch(config.destroyUrl, {
                method: 'DELETE',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                },
            });
            const data = await response.json().catch(() => ({}));
            this.isLoading = false;

            if (response.ok && data.success !== false) {
                window.location.href = config.indexUrl;
            } else {
                alert(data.message || `Request failed (${response.status})`);
            }
        },
    }));
});
</script>
@endsection
