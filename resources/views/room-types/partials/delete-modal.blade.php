{{-- Delete a room type (slide-over). Room types that still have rooms can't be deleted (the controller says so). --}}
<div x-show="isDeleteModalOpen" x-transition.opacity x-cloak class="slideover" @keydown.escape.window="closeModal()">
    <div class="fixed inset-0 modal-overlay" @click="closeModal()"></div>

    <form method="POST" :action="`/room-types/${deleteData.id}`" class="slideover-panel space-y-5">
        @csrf
        @method('DELETE')
        <div class="flex items-start justify-between gap-4">
            <h3 class="text-lg font-semibold text-gray-800">Delete room type</h3>
            <button type="button" @click="closeModal()" class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-gray-100 text-gray-500 hover:bg-gray-200 hover:text-gray-700" aria-label="Close"><i class="fas fa-times"></i></button>
        </div>
        <p class="text-sm text-gray-600">Delete <strong class="text-gray-800" x-text="deleteData.name"></strong>? This can't be undone. A room type that still has rooms can't be deleted: move or delete its rooms first.</p>
        <div class="flex justify-end gap-3 border-t border-gray-100 pt-5">
            <button type="button" @click="closeModal()" class="rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50">Cancel</button>
            <button type="submit" class="rounded-lg bg-error-500 px-5 py-2.5 text-sm font-medium text-white hover:bg-error-600">Delete</button>
        </div>
    </form>
</div>
