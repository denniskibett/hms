{{-- Cancel booking dialog. Opened by setting cancelOpen = true in the parent x-data. --}}
<div x-show="cancelOpen" x-cloak x-transition class="fixed inset-0 z-[99999] flex items-center justify-center p-5">
    <div class="fixed inset-0 bg-gray-400/50 backdrop-blur-sm dark:bg-gray-900/80" @click="cancelOpen = false"></div>

    <form method="POST" action="{{ route('stays.cancel', $stay) }}"
          class="relative w-full max-w-md rounded-2xl bg-white p-6 dark:bg-gray-900">
        @csrf
        <h4 class="text-lg font-medium text-gray-800 dark:text-white/90">Cancel booking #{{ $stay->id }}?</h4>
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
            The rooms become available again for {{ $stay->arrival_date->toFormattedDateString() }} – {{ $stay->departure_date->toFormattedDateString() }}.
        </p>

        <label class="mb-1.5 mt-4 block text-sm font-medium text-gray-700 dark:text-gray-400">Reason (optional)</label>
        <textarea name="reason" rows="3" maxlength="500"
                  class="w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90"
                  placeholder="e.g. Guest changed plans"></textarea>

        <div class="mt-6 flex justify-end gap-3">
            <button type="button" @click="cancelOpen = false"
                    class="rounded-lg border border-gray-300 px-4 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-white/[0.03]">
                Keep booking
            </button>
            <button type="submit" class="rounded-lg bg-error-500 px-4 py-2.5 text-sm font-medium text-white hover:bg-error-600">
                Cancel booking
            </button>
        </div>
    </form>
</div>
