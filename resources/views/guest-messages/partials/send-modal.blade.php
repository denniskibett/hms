{{--
    "Send message" dialog for one guest. Opened by setting messageOpen = true in
    the parent x-data. Needs $messageGuest (User); $messageStay (Stay) optional.
--}}
@php
    $canSms = (bool) \App\Models\GuestMessage::normalisePhone($messageGuest->phone);
    $canEmail = (bool) $messageGuest->email;
@endphp

<div x-show="messageOpen" x-cloak x-transition class="fixed inset-0 z-[99999] flex items-center justify-center p-5">
    <div class="fixed inset-0 bg-gray-400/50 backdrop-blur-sm dark:bg-gray-900/80" @click="messageOpen = false"></div>

    <form method="POST" action="{{ route('guest-messages.store') }}"
          x-data="{ body: @js(old('body', '')), email: @js(in_array('email', old('channels', []))) }"
          class="relative w-full max-w-lg rounded-2xl bg-white p-6 dark:bg-gray-900">
        @csrf
        <input type="hidden" name="guest_id" value="{{ $messageGuest->id }}">
        @isset($messageStay)
            <input type="hidden" name="stay_id" value="{{ $messageStay->id }}">
        @endisset

        <h4 class="text-lg font-medium text-gray-800 dark:text-white/90">Message {{ $messageGuest->name }}</h4>

        <div class="mt-4 flex flex-wrap gap-4 text-sm text-gray-700 dark:text-gray-300">
            <label class="flex items-center gap-2 {{ $canSms ? '' : 'opacity-50' }}">
                <input type="checkbox" name="channels[]" value="sms" @checked($canSms && in_array('sms', old('channels', ['sms']))) @disabled(! $canSms) class="rounded border-gray-300">
                SMS {{ $canSms ? 'to ' . $messageGuest->phone : '(no phone number)' }}
            </label>
            <label class="flex items-center gap-2 {{ $canEmail ? '' : 'opacity-50' }}">
                <input type="checkbox" name="channels[]" value="email" x-model="email" @disabled(! $canEmail) class="rounded border-gray-300">
                Email {{ $canEmail ? 'to ' . $messageGuest->email : '(no email address)' }}
            </label>
        </div>

        <div x-show="email" x-cloak class="mt-4">
            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Email subject</label>
            <input type="text" name="subject" value="{{ old('subject') }}" maxlength="150" placeholder="A message from {{ SystemHelper::appName() }}"
                   class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 dark:border-gray-700 dark:text-white/90">
        </div>

        <div class="mt-4">
            <div class="mb-1.5 flex justify-between text-sm">
                <label class="font-medium text-gray-700 dark:text-gray-400">Message</label>
                <span class="text-xs text-gray-500" x-text="`${body.length} characters · ${Math.max(1, Math.ceil(body.length / 153))} SMS`"></span>
            </div>
            <textarea name="body" x-model="body" rows="5" maxlength="918" required
                      class="w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 dark:border-gray-700 dark:text-white/90"
                      placeholder="e.g. Hi, your airport pickup is confirmed for 2pm."></textarea>
        </div>

        <div class="mt-6 flex justify-end gap-3">
            <button type="button" @click="messageOpen = false"
                    class="rounded-lg border border-gray-300 px-4 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300">
                Close
            </button>
            <button type="submit" @disabled(! $canSms && ! $canEmail) class="rounded-lg bg-primary px-4 py-2.5 text-sm font-medium text-white hover:bg-primary/90 disabled:opacity-50">
                <i class="fas fa-paper-plane mr-1"></i> Send
            </button>
        </div>
    </form>
</div>
