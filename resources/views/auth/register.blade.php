<x-guest-layout title="Create your guest account" subtitle="See your bookings and bills, and pay your balance online.">
    <form method="POST" action="{{ route('register') }}" class="space-y-5" x-data="{ show: false, busy: false }" @submit="busy = true">
        @csrf
        <div>
            <x-input-label for="name" value="Full name" />
            <x-text-input id="name" type="text" name="name" :value="old('name')" required autofocus autocomplete="name" />
            <x-input-error :messages="$errors->get('name')" />
        </div>
        <div>
            <x-input-label for="email" value="Email" />
            <x-text-input id="email" type="email" name="email" :value="old('email')" required autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" />
        </div>
        <div>
            <x-input-label for="password" value="Password" />
            <div class="relative">
                <x-text-input id="password" x-bind:type="show ? 'text' : 'password'" type="password" name="password" required autocomplete="new-password" class="pr-12" />
                <button type="button" @click="show = !show" class="absolute inset-y-0 right-0 flex w-12 items-center justify-center text-gray-400 hover:text-gray-600" :aria-label="show ? 'Hide password' : 'Show password'">
                    <i class="fa-regular" :class="show ? 'fa-eye-slash' : 'fa-eye'"></i>
                </button>
            </div>
            <x-input-error :messages="$errors->get('password')" />
        </div>
        <div>
            <x-input-label for="password_confirmation" value="Confirm password" />
            <x-text-input id="password_confirmation" x-bind:type="show ? 'text' : 'password'" type="password" name="password_confirmation" required autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password_confirmation')" />
        </div>
        <x-primary-button x-bind:disabled="busy">
            <span x-show="!busy">Create account</span>
            <span x-show="busy" x-cloak><i class="fa-solid fa-spinner fa-spin mr-2"></i>Creating…</span>
        </x-primary-button>
    </form>
    <p class="mt-6 text-center text-sm text-gray-500">
        Already have an account? <a href="{{ route('login') }}" class="font-medium text-brand-600 hover:text-brand-700">Sign in</a>
    </p>
</x-guest-layout>
