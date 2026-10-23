@extends('layouts.app')

@php
    $inputClass = 'h-10 w-full rounded-lg border border-gray-300 bg-transparent px-3 py-2 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30';
    $money = fn ($value) => SystemHelper::currencySymbol() . ' ' . number_format((float) $value, 2);
    $shown = isset($filters['category_id']) ? $categories->where('id', (int) $filters['category_id']) : $categories;
@endphp

@section('content')
<div x-data="{ pageName: `Menu` }">
    @include('partials.breadcrumb')
</div>

<div class="space-y-6">
    @include('kitchen.partials.header', ['title' => 'Menu', 'subtitle' => 'Dishes, portions and prices (prices include tax)', 'hideNewOrder' => true])

    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <form method="GET" action="{{ route('kitchen.menu.index') }}" class="flex flex-1 gap-2 sm:max-w-xl">
            <input type="search" name="search" value="{{ $filters['search'] ?? '' }}" placeholder="Search dishes" class="{{ $inputClass }}">
            <select name="category_id" class="{{ $inputClass }}" onchange="this.form.submit()">
                <option value="">All categories</option>
                @foreach ($categories as $category)
                    <option value="{{ $category->id }}" @selected((int) ($filters['category_id'] ?? 0) === $category->id)>{{ $category->name }}</option>
                @endforeach
            </select>
        </form>
        <div class="flex gap-2">
            <button type="button" @click="$dispatch('open-category-form')"
                    class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 shadow-theme-xs hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300">
                <i class="fas fa-folder-plus"></i> Category
            </button>
            <button type="button" @click="$dispatch('open-dish-form')" @disabled($categories->isEmpty())
                    class="inline-flex items-center gap-2 rounded-lg bg-primary px-4 py-2.5 text-sm font-medium text-white shadow-theme-xs hover:bg-primary/90 disabled:opacity-50">
                <i class="fas fa-plus"></i> Dish
            </button>
        </div>
    </div>

    @forelse ($shown as $category)
        @php $dishes = $items->get($category->id, collect()); @endphp
        <div class="rounded-2xl border border-gray-100 bg-white dark:border-gray-800 dark:bg-gray-900 {{ $category->is_active ? '' : 'opacity-70' }}">
            <div class="flex items-center justify-between border-b border-gray-100 px-5 py-4 dark:border-gray-800">
                <h3 class="text-lg font-medium text-gray-800 dark:text-white/90">
                    {{ $category->name }}
                    @unless ($category->is_active)
                        <span class="ml-1 rounded bg-gray-100 px-1.5 py-0.5 text-[10px] uppercase text-gray-500 dark:bg-gray-800 dark:text-gray-400">hidden</span>
                    @endunless
                </h3>
                <button type="button" @click="$dispatch('open-category-form', @js($category->formData()))" class="text-sm text-gray-500 hover:text-primary"><i class="fas fa-edit"></i> Edit</button>
            </div>
            <ul class="divide-y divide-gray-100 dark:divide-gray-800">
                @forelse ($dishes as $dish)
                    <li class="flex flex-wrap items-center justify-between gap-3 px-5 py-3 {{ $dish->is_active ? '' : 'opacity-60' }}">
                        <div>
                            <p class="font-medium text-gray-800 dark:text-white/90">
                                {{ $dish->name }}
                                @unless ($dish->is_active)
                                    <span class="ml-1 rounded bg-gray-100 px-1.5 py-0.5 text-[10px] uppercase text-gray-500 dark:bg-gray-800 dark:text-gray-400">hidden</span>
                                @endunless
                            </p>
                            @if ($dish->description)<p class="text-xs text-gray-500 dark:text-gray-400">{{ $dish->description }}</p>@endif
                        </div>
                        <div class="flex flex-wrap items-center gap-2">
                            @foreach ($dish->variants as $variant)
                                <span class="rounded-lg bg-gray-100 px-2.5 py-1 text-xs text-gray-700 dark:bg-gray-800 dark:text-gray-300 {{ $variant->is_active ? '' : 'line-through opacity-60' }}">
                                    @if ($dish->variants->count() > 1){{ $variant->name }} · @endif{{ $money($variant->price) }}
                                </span>
                            @endforeach
                            <button type="button" @click="$dispatch('open-dish-form', @js($dish->formData()))" class="ml-1 text-sm text-gray-500 hover:text-primary" title="Edit"><i class="fas fa-edit"></i></button>
                        </div>
                    </li>
                @empty
                    <li class="px-5 py-4 text-sm text-gray-500 dark:text-gray-400">No dishes{{ ($filters['search'] ?? null) ? ' match' : ' yet' }}.</li>
                @endforelse
            </ul>
        </div>
    @empty
        <div class="rounded-2xl border border-dashed border-gray-300 p-10 text-center text-gray-500 dark:border-gray-700 dark:text-gray-400">
            <i class="fas fa-book-open mb-3 text-4xl"></i>
            <p class="text-lg">The menu is empty</p>
            <p class="text-sm">Start with a category such as Breakfast, Main Course or Drinks.</p>
        </div>
    @endforelse
</div>

@include('kitchen.partials.category-modal')
@include('kitchen.partials.dish-modal')
@endsection
