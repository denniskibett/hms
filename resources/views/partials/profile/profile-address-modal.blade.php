<div
  x-show="isProfileAddressModal"
  x-transition:enter="transition ease-out duration-300"
  x-transition:enter-start="opacity-0"
  x-transition:enter-end="opacity-100"
  x-transition:leave="transition ease-in duration-200"
  x-transition:leave-start="opacity-100"
  x-transition:leave-end="opacity-0"
  class="fixed inset-0 z-[999] flex items-center justify-center p-5 overflow-y-auto"
>
  <!-- Backdrop -->
  <div 
    @click="isProfileAddressModal = false"
    class="fixed inset-0 h-full w-full bg-gray-400/50 backdrop-blur-[32px] cursor-pointer"
  ></div>

  <!-- Modal Content -->
  <div
    x-show="isProfileAddressModal"
    x-transition:enter="transition ease-out duration-300"
    x-transition:enter-start="opacity-0 scale-95"
    x-transition:enter-end="opacity-100 scale-100"
    x-transition:leave="transition ease-in duration-200"
    x-transition:leave-start="opacity-100 scale-100"
    x-transition:leave-end="opacity-0 scale-95"
    @click.outside="isProfileAddressModal = false"
    class="relative w-full max-w-[700px] overflow-y-auto rounded-3xl bg-white p-6 shadow-xl dark:bg-gray-900 lg:p-11"
  >
    <!-- Close Button -->
    <button
      @click="isProfileAddressModal = false"
      class="absolute right-5 top-5 z-[999] flex h-11 w-11 items-center justify-center rounded-full bg-gray-100 text-gray-400 transition-colors hover:bg-gray-200 hover:text-gray-600 dark:bg-gray-700 dark:text-gray-400 dark:hover:bg-gray-600 dark:hover:text-gray-300"
    >
      <svg class="h-6 w-6 fill-current" viewBox="0 0 24 24">
        <path
          fill-rule="evenodd"
          clip-rule="evenodd"
          d="M6.04289 16.5418C5.65237 16.9323 5.65237 17.5655 6.04289 17.956C6.43342 18.3465 7.06658 18.3465 7.45711 17.956L11.9987 13.4144L16.5408 17.9565C16.9313 18.347 17.5645 18.347 17.955 17.9565C18.3455 17.566 18.3455 16.9328 17.955 16.5423L13.4129 12.0002L17.955 7.45808C18.3455 7.06756 18.3455 6.43439 17.955 6.04387C17.5645 5.65335 16.9313 5.65335 16.5408 6.04387L11.9987 10.586L7.45711 6.04439C7.06658 5.65386 6.43342 5.65386 6.04289 6.04439C5.65237 6.43491 5.65237 7.06808 6.04289 7.4586L10.5845 12.0002L6.04289 16.5418Z"
        />
      </svg>
    </button>

    <!-- Modal Header -->
    <div class="px-2 pr-14">
      <h4 class="mb-2 text-2xl font-semibold text-gray-800 dark:text-white/90">
        Edit Address
      </h4>
      <p class="mb-6 text-sm text-gray-500 dark:text-gray-400 lg:mb-7">
        Update your address details.
      </p>
    </div>

    <!-- Form -->
    <form class="flex flex-col">
      <div class="px-2 overflow-y-auto">
        <div class="grid grid-cols-1 gap-x-6 gap-y-5 lg:grid-cols-2">
          <!-- Country -->
          <div>
            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
              Country
            </label>
            <input
              type="text"
              value="United Kingdom"
              class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-sm placeholder:text-gray-400 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500/10 dark:border-gray-700 dark:bg-gray-800 dark:text-white/90 dark:placeholder:text-gray-500 dark:focus:border-blue-500"
            />
          </div>

          <!-- City/State -->
          <div>
            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
              City/State
            </label>
            <input
              type="text"
              value="Leeds, East London"
              class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-sm placeholder:text-gray-400 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500/10 dark:border-gray-700 dark:bg-gray-800 dark:text-white/90 dark:placeholder:text-gray-500 dark:focus:border-blue-500"
            />
          </div>

          <!-- Postal Code -->
          <div>
            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
              Postal Code
            </label>
            <input
              type="text"
              value="ERT 2489"
              class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-sm placeholder:text-gray-400 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500/10 dark:border-gray-700 dark:bg-gray-800 dark:text-white/90 dark:placeholder:text-gray-500 dark:focus:border-blue-500"
            />
          </div>

          <!-- TAX ID -->
          <div>
            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
              TAX ID
            </label>
            <input
              type="text"
              value="AS4568384"
              class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-sm placeholder:text-gray-400 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500/10 dark:border-gray-700 dark:bg-gray-800 dark:text-white/90 dark:placeholder:text-gray-500 dark:focus:border-blue-500"
            />
          </div>
        </div>
      </div>

      <!-- Form Actions -->
      <div class="mt-6 flex items-center justify-end gap-3">
        <button
          @click="isProfileAddressModal = false"
          type="button"
          class="rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-gray-700"
        >
          Close
        </button>
        <button
          type="submit"
          class="rounded-lg bg-blue-600 px-4 py-2.5 text-sm font-medium text-white shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:focus:ring-offset-gray-900"
        >
          Save Changes
        </button>
      </div>
    </form>
  </div>
</div>