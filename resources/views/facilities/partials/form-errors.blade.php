{{-- Validation errors box for the JSON modals (reads Alpine `errors`). --}}
<div x-show="Object.keys(errors).length" x-cloak
     class="rounded-lg border border-error-200 bg-error-50 p-4 text-sm text-error-700 dark:border-error-500/30 dark:bg-error-500/10 dark:text-error-400">
    <p class="mb-1 font-medium">Please fix the following:</p>
    <ul class="list-inside list-disc space-y-0.5">
        <template x-for="(messages, field) in errors" :key="field">
            <li x-text="messages[0]"></li>
        </template>
    </ul>
</div>
