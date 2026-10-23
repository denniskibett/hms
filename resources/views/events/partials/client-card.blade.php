{{-- Client contact card. @include('events.partials.client-card', ['event' => $event]) --}}
<div class="rounded-2xl border border-gray-100 bg-white dark:border-gray-800 dark:bg-gray-900">
    <div class="border-b border-gray-100 px-5 py-4 dark:border-gray-800">
        <h3 class="text-lg font-medium text-gray-800 dark:text-white/90">Client</h3>
    </div>
    <dl class="space-y-3 p-5 text-sm">
        <div><dt class="text-gray-500 dark:text-gray-400">Organisation / name</dt><dd class="font-medium text-gray-800 dark:text-white/90">{{ $event->client_name }}</dd></div>
        @if ($event->contact_name)
            <div><dt class="text-gray-500 dark:text-gray-400">Contact person</dt><dd class="text-gray-800 dark:text-white/90">{{ $event->contact_name }}</dd></div>
        @endif
        <div><dt class="text-gray-500 dark:text-gray-400">Phone</dt><dd><a href="tel:{{ $event->contact_phone }}" class="text-gray-800 hover:text-primary dark:text-white/90">{{ $event->contact_phone }}</a></dd></div>
        @if ($event->contact_email)
            <div><dt class="text-gray-500 dark:text-gray-400">Email</dt><dd><a href="mailto:{{ $event->contact_email }}" class="break-all text-gray-800 hover:text-primary dark:text-white/90">{{ $event->contact_email }}</a></dd></div>
        @endif
    </dl>
</div>
