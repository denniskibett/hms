<x-guest-layout title="Sign in" subtitle="Staff and guests: use the email and password you were given.">
    <x-auth-session-status :status="session('status')" />
    @if (session('error'))
        <div class="mb-4 rounded-lg bg-error-50 px-4 py-3 text-sm text-error-700" role="alert">{{ session('error') }}</div>
    @endif

    <form method="POST" action="{{ route('login') }}" class="space-y-5" x-data="{ show: false, busy: false }" @submit="busy = true">
        @csrf
        <div>
            <x-input-label for="email" value="Email" />
            <x-text-input id="email" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" placeholder="you@example.com" />
            <x-input-error :messages="$errors->get('email')" />
        </div>

        <div>
            <div class="mb-1.5 flex items-center justify-between">
                <x-input-label for="password" value="Password" class="mb-0" />
                @if (Route::has('password.request'))
                    <a href="{{ route('password.request') }}" class="text-sm font-medium text-brand-600 hover:text-brand-700">Forgot password?</a>
                @endif
            </div>
            <div class="relative">
                <x-text-input id="password" x-bind:type="show ? 'text' : 'password'" type="password" name="password" required autocomplete="current-password" class="pr-12" />
                <button type="button" @click="show = !show" class="absolute inset-y-0 right-0 flex w-12 items-center justify-center text-gray-400 hover:text-gray-600" :aria-label="show ? 'Hide password' : 'Show password'">
                    <i class="fa-regular" :class="show ? 'fa-eye-slash' : 'fa-eye'"></i>
                </button>
            </div>
            <x-input-error :messages="$errors->get('password')" />
        </div>

        <label class="flex items-center gap-2 text-sm text-gray-600">
            <input type="checkbox" name="remember" class="h-4 w-4 rounded border-gray-300 text-brand-500 focus:ring-brand-500/20">
            Keep me signed in on this device
        </label>

        <x-primary-button x-bind:disabled="busy">
            <span x-show="!busy">Sign in</span>
            <span x-show="busy" x-cloak><i class="fa-solid fa-spinner fa-spin mr-2"></i>Signing in…</span>
        </x-primary-button>
    </form>

    @if (Route::has('register'))
        <p class="mt-6 text-center text-sm text-gray-500">
            Staying with us and don't have an account?
            <a href="{{ route('register') }}" class="font-medium text-brand-600 hover:text-brand-700">Create one</a>
        </p>
    @endif
</x-guest-layout>
