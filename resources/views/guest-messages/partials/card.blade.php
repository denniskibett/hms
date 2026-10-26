{{--
    Recent messages with a Send button. Needs messageOpen in the parent x-data,
    $messages, $messageGuest (User) and optionally $messageStay; include the
    send-modal partial in the same x-data.
--}}
<div class="rounded-2xl border border-gray-100 bg-white dark:border-gray-800 dark:bg-gray-900 print:hidden">
    <div class="flex items-center justify-between border-b border-gray-100 px-5 py-4 dark:border-gray-800">
        <h3 class="text-lg font-medium text-gray-800 dark:text-white/90">Messages</h3>
        <button type="button" @click="messageOpen = true" class="inline-flex items-center gap-1 text-sm text-primary hover:underline">
            <i class="fas fa-paper-plane"></i> Send message
        </button>
    </div>
    @include('guest-messages.partials.list', ['messages' => $messages, 'showGuest' => false])
</div>
