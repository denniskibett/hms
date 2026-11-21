{{-- Enquiry form; posts to contact.submit. @include('public.partials.contact-form', ['topics' => [...]?, 'topic' => ?]) --}}
@php
    $topics ??= ['Room booking', 'Company account', 'Conference or event', 'Restaurant table', 'Something else'];
    $topic = old('topic', $topic ?? request('topic'));
@endphp
<form method="POST" action="{{ route('contact.submit') }}" class="grid grid-cols-1 gap-4 rounded-xl border border-line bg-white p-6 md:grid-cols-2 md:p-8" aria-label="Contact {{ SystemHelper::appName() }}">
    @csrf
    @if (session('success'))
        <p class="rounded-md bg-green-50 p-3 text-sm text-green-800 md:col-span-2" role="status">{{ session('success') }}</p>
    @endif
    @if (session('error') || $errors->any())
        <div class="rounded-md bg-red-50 p-3 text-sm text-red-700 md:col-span-2" role="alert">
            {{ session('error') }}
            @foreach ($errors->all() as $message)<p>{{ $message }}</p>@endforeach
        </div>
    @endif
    <div class="hidden" aria-hidden="true"><input type="text" name="website" tabindex="-1" autocomplete="off"></div>
    <label class="site-label">Name *<input type="text" name="name" value="{{ old('name') }}" required maxlength="100" autocomplete="name" class="site-field"></label>
    <label class="site-label">Phone<input type="tel" name="phone" value="{{ old('phone') }}" maxlength="30" autocomplete="tel" class="site-field"></label>
    <label class="site-label md:col-span-2">Email *<input type="email" name="email" value="{{ old('email') }}" required maxlength="255" autocomplete="email" class="site-field"></label>
    <label class="site-label md:col-span-2">About
        <select name="topic" class="site-field">
            @foreach ($topics as $option)<option @selected($topic === $option)>{{ $option }}</option>@endforeach
        </select>
    </label>
    <label class="site-label md:col-span-2">Message *<textarea name="message" rows="5" required maxlength="5000" class="site-field">{{ old('message') }}</textarea></label>
    <button type="submit" class="site-btn md:col-span-2">Send message</button>
</form>
