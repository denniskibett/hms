<x-guest-layout title="Reset your password" subtitle="Enter your email and we'll send you a link to choose a new password.">
    <x-auth-session-status :status="session('status')" />
    <form method="POST" action="{{ route('password.email') }}" class="space-y-5">
        @csrf
        <div>
            <x-input-label for="email" value="Email" />
            <x-text-input id="email" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" />
        </div>
        <x-primary-button>Email me a reset link</x-primary-button>
    </form>
    <p class="mt-6 text-center text-sm text-gray-500">
        Remembered it? <a href="{{ route('login') }}" class="font-medium text-brand-600 hover:text-brand-700">Back to sign in</a>
    </p>
</x-guest-layout>
