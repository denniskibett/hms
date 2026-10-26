{{-- Draft notice and validation errors for the JSON modals (reads Alpine `draftRestored`, `errors`). --}}
<div x-show="draftRestored" x-cloak
     class="flex flex-wrap items-center justify-between gap-2 rounded-lg border border-info-200 bg-info-50 px-4 py-3 text-sm text-info-700 dark:border-info-500/30 dark:bg-info-500/10 dark:text-info-400">
    <span><i class="fas fa-history mr-1"></i> We kept what you typed last time. Check it, then save.</span>
    <button type="button" @click="discardDraft()" class="text-xs font-medium underline">Discard and start again</button>
</div>

<div x-show="Object.keys(errors).length" x-cloak
     class="rounded-lg border border-error-200 bg-error-50 p-4 text-sm text-error-700 dark:border-error-500/30 dark:bg-error-500/10 dark:text-error-400">
    <p class="mb-1 font-medium">Please fix the following:</p>
    <ul class="list-inside list-disc space-y-0.5">
        <template x-for="(messages, field) in errors" :key="field">
            <li x-text="messages[0]"></li>
        </template>
    </ul>
</div>
