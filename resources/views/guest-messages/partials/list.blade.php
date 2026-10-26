{{--
    Messages as a list. $messages (with guest, sender loaded), $showGuest (bool).
--}}
<ul class="divide-y divide-gray-100 dark:divide-gray-800">
    @forelse ($messages as $message)
        <li class="px-5 py-3 text-sm">
            <div class="flex flex-wrap items-center gap-2">
                <i class="fas {{ $message->channel === 'sms' ? 'fa-sms' : 'fa-envelope' }} text-gray-400" title="{{ \App\Models\GuestMessage::CHANNELS[$message->channel] }}"></i>
                @if ($showGuest && $message->guest?->guestProfile)
                    <a href="{{ route('guests.show', $message->guest->guestProfile) }}" class="font-medium text-gray-800 hover:text-primary dark:text-white/90">{{ $message->guest->name }}</a>
                @elseif ($showGuest && $message->guest)
                    <span class="font-medium text-gray-800 dark:text-white/90">{{ $message->guest->name }}</span>
                @endif
                <span class="text-gray-500 dark:text-gray-400">{{ $message->recipient }}</span>
                <span class="rounded-full px-2 py-0.5 text-xs font-medium {{ $message->status_classes }}">{{ \App\Models\GuestMessage::STATUSES[$message->status] }}</span>
                <span class="ml-auto text-xs text-gray-500">{{ $message->template_label }}{{ $message->sender ? ' · ' . $message->sender->name : '' }} · {{ $message->created_at->format('d M, H:i') }}</span>
            </div>
            @if ($message->subject)
                <p class="mt-1 font-medium text-gray-700 dark:text-gray-300">{{ $message->subject }}</p>
            @endif
            <p class="mt-1 whitespace-pre-line text-gray-700 dark:text-gray-300">{{ $message->body }}</p>
            @if ($message->status === 'failed')
                <div class="mt-1 flex flex-wrap items-center gap-3 text-xs">
                    <span class="text-error-600 dark:text-error-500">{{ $message->error }}</span>
                    <form method="POST" action="{{ route('guest-messages.retry', $message) }}">
                        @csrf
                        <button type="submit" class="font-medium text-primary hover:underline">Send again</button>
                    </form>
                </div>
            @endif
        </li>
    @empty
        <li class="px-5 py-8 text-center text-sm text-gray-500 dark:text-gray-400">No messages yet.</li>
    @endforelse
</ul>
