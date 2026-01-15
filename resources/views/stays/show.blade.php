@extends('layouts.app')

@section('content')
<div x-data="{ pageName: `Stay #S{{ str_pad($stay->id, 5, '0', STR_PAD_LEFT) }}` }">
    @include('partials.breadcrumb')
</div>

<div class="space-y-6">
    <!-- Header Card -->
    <div class="flex flex-col justify-between gap-6 rounded-2xl border border-gray-200 bg-white px-6 py-5 sm:flex-row sm:items-center dark:border-gray-800 dark:bg-white/3">
        <div class="flex flex-col gap-2.5 divide-gray-300 sm:flex-row sm:divide-x dark:divide-gray-700">
            <div class="flex items-center gap-2 sm:pr-3">
                <span class="text-base font-medium text-gray-700 dark:text-gray-400">
                    Stay ID: #S{{ str_pad($stay->id, 5, '0', STR_PAD_LEFT) }}
                </span>
                <span class="bg-success-50 text-success-600 dark:bg-success-500/15 dark:text-success-500 inline-flex items-center justify-center gap-1 rounded-full px-2.5 py-0.5 text-sm font-medium">
                    {{ ucfirst($stay->status) }}
                </span>
                @if($stay->is_checked_in)
                <span class="bg-blue-50 text-blue-600 dark:bg-blue-500/15 dark:text-blue-500 inline-flex items-center justify-center gap-1 rounded-full px-2.5 py-0.5 text-sm font-medium">
                    <i class="fas fa-door-open mr-1"></i> Checked-in
                </span>
                @endif
            </div>
            <p class="text-sm text-gray-500 sm:pl-3 dark:text-gray-400">
                Stay: {{ $stay->arrival_date?->format('M d') }} - {{ $stay->departure_date?->format('D, ' . SystemHelper::dateFormat()) }}
            </p>
        </div>
        <div class="flex gap-3">
            @can('update', $stay)
            <button onclick="openEditStayModal()"
               class="shadow-theme-xs inline-flex items-center justify-center gap-2 rounded-lg bg-white px-4 py-3 text-sm font-medium text-gray-700 ring-1 ring-gray-300 transition hover:bg-gray-50 dark:bg-gray-800 dark:text-gray-400 dark:ring-gray-700 dark:hover:bg-white/[0.03]">
                <i class="fas fa-edit mr-1"></i> Edit Stay
            </button>
            @endcan
            
            @if($stay->status === 'checked_in' && !$stay->is_checked_out)
            <a href="{{ route('stays.create', $stay) }}" 
               class="bg-brand-500 shadow-theme-xs hover:bg-brand-600 inline-flex items-center justify-center gap-2 rounded-lg px-4 py-3 text-sm font-medium text-white transition">
                <i class="fas fa-door-closed mr-1"></i> Check-out
            </a>
            @endif
            
            <button onclick="printStay()" 
                    class="shadow-theme-xs inline-flex items-center justify-center gap-2 rounded-lg bg-white px-4 py-3 text-sm font-medium text-gray-700 ring-1 ring-gray-300 transition hover:bg-gray-50 dark:bg-gray-800 dark:text-gray-400 dark:ring-gray-700 dark:hover:bg-white/[0.03]">
                <i class="fas fa-print mr-1"></i> Print
            </button>
        </div>
    </div>

    <!-- Main Content Area -->
    <div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/3">
        <!-- Tabs Navigation -->
        <div class="border-b border-gray-200 dark:border-gray-800">
            <nav class="flex space-x-1 px-6 overflow-x-auto">
                <button onclick="showTab('details')" id="details-tab" 
                        class="px-4 py-3 text-sm font-medium border-b-2 border-primary text-primary whitespace-nowrap">
                    <i class="fas fa-info-circle mr-2"></i> Stay Details
                </button>
                <button onclick="showTab('billing')" id="billing-tab" 
                        class="px-4 py-3 text-sm font-medium border-b-2 border-transparent text-gray-500 hover:text-gray-700 dark:hover:text-gray-300 whitespace-nowrap">
                    <i class="fas fa-file-invoice-dollar mr-2"></i> Billing
                </button>
                <button onclick="showTab('services')" id="services-tab" 
                        class="px-4 py-3 text-sm font-medium border-b-2 border-transparent text-gray-500 hover:text-gray-700 dark:hover:text-gray-300 whitespace-nowrap">
                    <i class="fas fa-concierge-bell mr-2"></i> Services
                </button>
                <button onclick="showTab('documents')" id="documents-tab" 
                        class="px-4 py-3 text-sm font-medium border-b-2 border-transparent text-gray-500 hover:text-gray-700 dark:hover:text-gray-300 whitespace-nowrap">
                    <i class="fas fa-file-alt mr-2"></i> Documents
                </button>
            </nav>
        </div>

        <!-- Tab Content Container -->
        <div class="p-6">
            <!-- Stay Details Tab -->
            <div id="details-tab-content">
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <!-- Guest Information Card -->
                    <div class="lg:col-span-2">
                        <div class="rounded-xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-gray-900">
                            <div class="mb-4 flex items-center justify-between">
                                <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90">
                                    Guest Information
                                </h3>
                                <a href="{{ route('guests.show', $stay->guest) }}" 
                                   class="text-sm text-primary hover:text-primary/80">
                                    <i class="fas fa-external-link-alt mr-1"></i> View Full Profile
                                </a>
                            </div>
                            
                            <div class="space-y-4">
                                <div class="flex items-center">
                                    <div class="mr-3">
                                        @if($stay->guest->avatar)
                                        <img src="{{ asset('storage/' . $stay->guest->avatar) }}" 
                                             alt="{{ $stay->guest->name }}" 
                                             class="h-12 w-12 rounded-full">
                                        @else
                                        <div class="h-12 w-12 rounded-full bg-primary/10 flex items-center justify-center">
                                            <span class="text-primary font-bold text-lg">
                                                {{ substr($stay->guest->name, 0, 1) }}
                                            </span>
                                        </div>
                                        @endif
                                    </div>
                                    <div>
                                        <h4 class="font-medium text-gray-800 dark:text-white/90">
                                            {{ $stay->guest->name }}
                                        </h4>
                                        <p class="text-sm text-gray-500 dark:text-gray-400">
                                            {{ $stay->guest->email }}
                                        </p>
                                    </div>
                                </div>
                                
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-xs text-gray-500 dark:text-gray-400 mb-1">
                                            Phone Number
                                        </label>
                                        <p class="text-sm text-gray-800 dark:text-white/90">
                                            {{ $stay->guest->phone ?? 'N/A' }}
                                        </p>
                                    </div>
                                    <div>
                                        <label class="block text-xs text-gray-500 dark:text-gray-400 mb-1">
                                            ID Type/Number
                                        </label>
                                        <p class="text-sm text-gray-800 dark:text-white/90">
                                            {{ ucfirst($stay->guest->guest->id_type ?? 'N/A') }}: {{ $stay->guest->guest->id_number ?? 'N/A' }}
                                        </p>
                                    </div>
                                    <div>
                                        <label class="block text-xs text-gray-500 dark:text-gray-400 mb-1">
                                            Nationality
                                        </label>
                                        <p class="text-sm text-gray-800 dark:text-white/90">
                                            {{ $stay->guest->guest->nationality ?? 'N/A' }}
                                        </p>
                                    </div>
                                    <div>
                                        <label class="block text-xs text-gray-500 dark:text-gray-400 mb-1">
                                            Guest Since
                                        </label>
                                        <p class="text-sm text-gray-800 dark:text-white/90">
                                            {{ $stay->guest->guest->created_at?->format('D, ' . SystemHelper::dateFormat()) ?? 'N/A' }}
                                        </p>
                                    </div>
                                </div>
                                
                                @if($stay->guest->guest->preferences && is_array(json_decode($stay->guest->guest->preferences, true)))
                                <div class="pt-4 border-t border-gray-200 dark:border-gray-800">
                                    <h5 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                        Guest Preferences
                                    </h5>
                                    @php
                                        $preferences = json_decode($stay->guest->guest->preferences, true);
                                    @endphp
                                    <div class="flex flex-wrap gap-2">
                                        @foreach($preferences as $key => $value)
                                            @if(!empty($value) && !is_array($value))
                                                <span class="px-2 py-1 text-xs rounded-full bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300">
                                                    {{ ucfirst(str_replace('_', ' ', $key)) }}: {{ $value }}
                                                </span>
                                            @endif
                                        @endforeach
                                    </div>
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Stay Timeline Card -->
                    <div class="lg:col-span-1">
                        <div class="rounded-xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-gray-900">
                            <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90 mb-4">
                                Stay Timeline
                            </h3>
                            
                            <div class="space-y-4">
                                <!-- Stay booking created -->
                                <div class="flex items-start">
                                    <div class="mr-3">
                                        <div class="p-2 rounded-full bg-primary/10 text-primary">
                                            <i class="fas fa-calendar-plus"></i>
                                        </div>
                                    </div>
                                    <div class="flex-1">
                                        <p class="text-sm text-gray-800 dark:text-white">Stay booking created</p>
                                        <p class="text-xs text-gray-500 mt-1">
                                            <span>{{ optional($stay->created_by_user)->name ?? 'System' }}</span> •
                                            <span>{{ optional($stay->created_at)?->diffForHumans() ?? 'N/A' }}</span>
                                        </p>
                                    </div>
                                </div>

                                <!-- Check-in timeline -->
                                @if($stay->actual_check_in)
                                <div class="flex items-start">
                                    <div class="mr-3">
                                        <div class="p-2 rounded-full bg-success/10 text-success">
                                            <i class="fas fa-door-open"></i>
                                        </div>
                                    </div>
                                    <div class="flex-1">
                                        <p class="text-sm text-gray-800 dark:text-white">Guest checked in</p>
                                        <p class="text-xs text-gray-500 mt-1">
                                            <span>{{ optional($stay->checked_in_by_user)->name ?? 'System' }}</span> •
                                            <span>{{ optional($stay->actual_check_in)?->diffForHumans() ?? 'N/A' }}</span>
                                        </p>
                                    </div>
                                </div>
                                @endif

                                <!-- Check-out timeline -->
                                @if($stay->actual_check_out)
                                <div class="flex items-start">
                                    <div class="mr-3">
                                        <div class="p-2 rounded-full bg-warning/10 text-warning">
                                            <i class="fas fa-door-closed"></i>
                                        </div>
                                    </div>
                                    <div class="flex-1">
                                        <p class="text-sm text-gray-800 dark:text-white">Guest checked out</p>
                                        <p class="text-xs text-gray-500 mt-1">
                                            <span>{{ optional($stay->checked_out_by_user)->name ?? 'System' }}</span> •
                                            <span>{{ optional($stay->actual_check_out)?->diffForHumans() ?? 'N/A' }}</span>
                                        </p>
                                    </div>
                                </div>
                                @endif

                                <!-- Last update -->
                                @if($stay->updated_at && $stay->updated_at != $stay->created_at)
                                <div class="flex items-start">
                                    <div class="mr-3">
                                        <div class="p-2 rounded-full bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-400">
                                            <i class="fas fa-edit"></i>
                                        </div>
                                    </div>
                                    <div class="flex-1">
                                        <p class="text-sm text-gray-800 dark:text-white">Stay information updated</p>
                                        <p class="text-xs text-gray-500 mt-1">
                                            <span>{{ optional($stay->updated_by_user)->name ?? 'System' }}</span> •
                                            <span>{{ optional($stay->updated_at)?->diffForHumans() ?? 'N/A' }}</span>
                                        </p>
                                    </div>
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Room Information Card with Actions -->
                    <div class="lg:col-span-3">
                        <div class="rounded-xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-gray-900">
                            <div class="mb-4 flex items-center justify-between">
                                <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90">
                                    Room Information
                                </h3>
                                @can('update', $stay)
                                <button onclick="openAddRoomModal()"
                                   class="text-sm text-primary hover:text-primary/80">
                                    <i class="fas fa-plus mr-1"></i> Add Room
                                </button>
                                @endcan
                            </div>
                            
                            <div class="overflow-hidden rounded-lg border border-gray-100 dark:border-gray-800">
                                <div class="custom-scrollbar overflow-x-auto">
                                    <table class="min-w-full text-left text-sm text-gray-700 dark:border-gray-800">
                                        <thead class="bg-gray-50 dark:bg-gray-800">
                                            <tr class="border-b border-gray-100 whitespace-nowrap dark:border-gray-700">
                                                <th class="px-5 py-3 text-sm font-medium whitespace-nowrap text-gray-700 dark:text-gray-400">
                                                    Room
                                                </th>
                                                <th class="px-5 py-3 text-sm font-medium whitespace-nowrap text-gray-500 dark:text-gray-400">
                                                    Type
                                                </th>
                                                <th class="px-5 py-3 text-sm font-medium whitespace-nowrap text-gray-700 dark:text-gray-400">
                                                    Check-in
                                                </th>
                                                <th class="px-5 py-3 text-sm font-medium whitespace-nowrap text-gray-700 dark:text-gray-400">
                                                    Check-out
                                                </th>
                                                <th class="px-5 py-3 text-sm font-medium whitespace-nowrap text-gray-700 dark:text-gray-400">
                                                    Nights
                                                </th>
                                                <th class="px-5 py-3 text-sm font-medium whitespace-nowrap text-gray-700 dark:text-gray-400">
                                                    Rate/Night
                                                </th>
                                                <th class="px-5 py-3 text-sm font-medium whitespace-nowrap text-gray-700 dark:text-gray-400">
                                                    Total
                                                </th>
                                                <th class="px-5 py-3 text-sm font-medium whitespace-nowrap text-gray-700 dark:text-gray-400">
                                                    Actions
                                                </th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-gray-100 bg-white dark:divide-gray-800 dark:bg-white/[0.03]">
                                            @foreach($stay->roomAllocations as $allocation)
                                            <tr>
                                                <td class="px-5 py-3 text-sm whitespace-nowrap text-gray-500 dark:text-gray-400">
                                                    Room {{ $allocation->room->room_number ?? 'N/A' }}
                                                </td>
                                                <td class="px-5 py-3 text-sm font-medium whitespace-nowrap text-gray-800 dark:text-white/90">
                                                    {{ $allocation->room->roomType->name ?? 'N/A' }}
                                                </td>
                                                <td class="px-5 py-3 text-sm whitespace-nowrap text-gray-500 dark:text-gray-400">
                                                    {{ $allocation->from_date?->format('D, ' . SystemHelper::dateFormat()) ?? $stay->arrival_date?->format('D, ' . SystemHelper::dateFormat()) }}
                                                </td>
                                                <td class="px-5 py-3 text-sm whitespace-nowrap text-gray-500 dark:text-gray-400">
                                                    {{ $allocation->to_date?->format('D, ' . SystemHelper::dateFormat()) ?? $stay->departure_date?->format('D, ' . SystemHelper::dateFormat()) }}
                                                </td>
                                                <td class="px-5 py-3 text-sm whitespace-nowrap text-gray-500 dark:text-gray-400">
                                                    @php
                                                        $from = $allocation->from_date ?? $stay->arrival_date;
                                                        $to = $allocation->to_date ?? $stay->departure_date;
                                                        $nights = $from && $to ? $from->diffInDays($to) : $stay->nights;
                                                    @endphp
                                                    {{ $nights }}
                                                </td>
                                                <td class="px-5 py-3 text-sm whitespace-nowrap text-gray-500 dark:text-gray-400">
                                                    {{ SystemHelper::currencySymbol() }} {{ number_format($allocation->rate_applied, 2) }}
                                                </td>
                                                <td class="px-5 py-3 text-sm whitespace-nowrap text-gray-500 dark:text-gray-400">
                                                    {{ SystemHelper::currencySymbol() }} {{ number_format($allocation->rate_applied * $nights, 2) }}
                                                </td>
                                                <td class="px-5 py-3 text-sm whitespace-nowrap">
                                                    <div class="relative" x-data="{ open: false }">
                                                        <button @click="open = !open" 
                                                                class="inline-flex items-center text-gray-500 hover:text-gray-700 dark:hover:text-gray-300">
                                                            <i class="fas fa-ellipsis-v"></i>
                                                        </button>
                                                        
                                                        <div x-show="open" 
                                                             @click.outside="open = false" 
                                                             x-transition
                                                             class="absolute right-0 mt-2 w-48 rounded-lg border border-gray-200 bg-white shadow-lg dark:border-gray-700 dark:bg-gray-800 z-10"
                                                             x-cloak>
                                                            <div class="py-1">
                                                                <button onclick="editRoomAllocation({{ $allocation->id }})"
                                                                        class="block w-full px-4 py-2 text-left text-sm text-gray-700 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-700">
                                                                    <i class="fas fa-edit mr-2"></i> Edit Room
                                                                </button>
                                                                <button onclick="deleteRoomAllocation({{ $allocation->id }})"
                                                                        class="block w-full px-4 py-2 text-left text-sm text-danger hover:bg-gray-100 dark:text-danger-400 dark:hover:bg-gray-700">
                                                                    <i class="fas fa-trash mr-2"></i> Delete Room
                                                                </button>
                                                                <a href="{{ route('rooms.show', $allocation->room_id) }}"
                                                                   class="block w-full px-4 py-2 text-left text-sm text-gray-700 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-700">
                                                                    <i class="fas fa-eye mr-2"></i> View Room
                                                                </a>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                        <tfoot class="bg-gray-50 dark:bg-gray-800">
                                            <tr>
                                                <td colspan="6" class="px-5 py-3 text-right text-sm font-medium text-gray-700 dark:text-gray-400">
                                                    Room Charges Total:
                                                </td>
                                                <td class="px-5 py-3 text-sm font-bold text-gray-800 dark:text-white/90">
                                                    {{ SystemHelper::currencySymbol() }} {{ number_format($roomCharges, 2) }}
                                                </td>
                                                <td></td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            </div>
                            
                            <!-- Room Amenities -->
                            @if($stay->roomAllocations->first()?->room?->roomType?->amenities)
                            <div class="mt-4 pt-4 border-t border-gray-200 dark:border-gray-800">
                                <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Room Amenities</h4>
                                <div class="flex flex-wrap gap-2">
                                    @foreach(json_decode($stay->roomAllocations->first()->room->roomType->amenities, true) ?? [] as $amenity)
                                    <span class="px-2 py-1 text-xs rounded-full bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300">
                                        {{ $amenity }}
                                    </span>
                                    @endforeach
                                </div>
                            </div>
                            @endif
                        </div>
                    </div>

                    <!-- Special Requests -->
                    @if($stay->special_requests)
                    <div class="lg:col-span-3">
                        <div class="rounded-xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-gray-900">
                            <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90 mb-3">
                                Special Requests & Notes
                            </h3>
                            <div class="p-3 bg-gray-50 dark:bg-gray-800 rounded-lg">
                                <p class="text-sm text-gray-700 dark:text-gray-300">
                                    {{ $stay->special_requests }}
                                </p>
                            </div>
                        </div>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Billing Tab -->
            <div id="billing-tab-content" class="hidden">
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <!-- Invoice Details Card -->
                    <div class="lg:col-span-2">
                        <div class="rounded-xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-gray-900">
                            <div class="mb-4 flex items-center justify-between">
                                <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90">
                                    Invoice Details
                                </h3>
                                <a href="{{ route('invoices.show', $stay) }}" 
                                   class="text-sm text-primary hover:text-primary/80">
                                    <i class="fas fa-external-link-alt mr-1"></i> View Full Invoice
                                </a>
                            </div>
                            
                            <div class="overflow-hidden rounded-lg border border-gray-100 dark:border-gray-800 mb-6">
                                <div class="custom-scrollbar overflow-x-auto">
                                    <table class="min-w-full text-left text-sm text-gray-700 dark:border-gray-800">
                                        <thead class="bg-gray-50 dark:bg-gray-800">
                                            <tr class="border-b border-gray-100 whitespace-nowrap dark:border-gray-700">
                                                <th class="px-5 py-3 text-sm font-medium whitespace-nowrap text-gray-700 dark:text-gray-400">
                                                    Description
                                                </th>
                                                <th class="px-5 py-3 text-sm font-medium whitespace-nowrap text-gray-500 dark:text-gray-400">
                                                    Quantity
                                                </th>
                                                <th class="px-5 py-3 text-sm font-medium whitespace-nowrap text-gray-700 dark:text-gray-400">
                                                    Unit Price
                                                </th>
                                                <th class="px-5 py-3 text-sm font-medium whitespace-nowrap text-gray-700 dark:text-gray-400">
                                                    Discount
                                                </th>
                                                <th class="px-5 py-3 text-sm font-medium whitespace-nowrap text-gray-700 dark:text-gray-400">
                                                    Total
                                                </th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-gray-100 bg-white dark:divide-gray-800 dark:bg-white/[0.03]">
                                            <!-- Room Charges -->
                                            <tr>
                                                <td class="px-5 py-3 text-sm font-medium whitespace-nowrap text-gray-800 dark:text-white/90">
                                                    Room Accommodation
                                                </td>
                                                <td class="px-5 py-3 text-sm whitespace-nowrap text-gray-500 dark:text-gray-400">
                                                    {{ $stay->nights }} night(s)
                                                </td>
                                                <td class="px-5 py-3 text-sm whitespace-nowrap text-gray-500 dark:text-gray-400">
                                                    {{ SystemHelper::currencySymbol() }} {{ number_format($roomCharges / $stay->nights, 2) }}
                                                </td>
                                                <td class="px-5 py-3 text-sm whitespace-nowrap text-gray-500 dark:text-gray-400">
                                                    0%
                                                </td>
                                                <td class="px-5 py-3 text-sm whitespace-nowrap text-gray-500 dark:text-gray-400">
                                                    {{ SystemHelper::currencySymbol() }} {{ number_format($roomCharges, 2) }}
                                                </td>
                                            </tr>
                                            
                                            <!-- Additional Services -->
                                            @if($stay->services && $stay->services->count() > 0)
                                            @foreach($stay->services as $service)
                                            <tr>
                                                <td class="px-5 py-3 text-sm font-medium whitespace-nowrap text-gray-800 dark:text-white/90">
                                                    {{ $service->service->name }}
                                                </td>
                                                <td class="px-5 py-3 text-sm whitespace-nowrap text-gray-500 dark:text-gray-400">
                                                    {{ $service->quantity }}
                                                </td>
                                                <td class="px-5 py-3 text-sm whitespace-nowrap text-gray-500 dark:text-gray-400">
                                                    {{ SystemHelper::currencySymbol() }} {{ number_format($service->rate, 2) }}
                                                </td>
                                                <td class="px-5 py-3 text-sm whitespace-nowrap text-gray-500 dark:text-gray-400">
                                                    0%
                                                </td>
                                                <td class="px-5 py-3 text-sm whitespace-nowrap text-gray-500 dark:text-gray-400">
                                                    {{ SystemHelper::currencySymbol() }} {{ number_format($service->total_amount, 2) }}
                                                </td>
                                            </tr>
                                            @endforeach
                                            @endif
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <!-- Billing Summary -->
                            <div class="flex flex-wrap justify-end">
                                <div class="mt-6 w-full space-y-1 text-right sm:w-[250px]">
                                    <p class="mb-4 text-left text-sm font-medium text-gray-800 dark:text-white/90">
                                        Invoice Summary
                                    </p>
                                    <ul class="space-y-2">
                                        <li class="flex justify-between gap-5">
                                            <span class="text-sm text-gray-500 dark:text-gray-400">
                                                Sub Total
                                            </span>
                                            <span class="text-sm font-medium text-gray-700 dark:text-gray-400">
                                                {{ SystemHelper::currencySymbol() }} {{ number_format($totalAmount - ($stay->tax_amount ?? 0), 2) }}
                                            </span>
                                        </li>
                                        
                                        @if($stay->discount > 0)
                                        <li class="flex justify-between gap-5">
                                            <span class="text-sm text-gray-500 dark:text-gray-400">
                                                Discount ({{ $stay->discount }}%)
                                            </span>
                                            <span class="text-sm font-medium text-success-600 dark:text-success-500">
                                                -{{ SystemHelper::currencySymbol() }} {{ number_format($stay->discount_amount, 2) }}
                                            </span>
                                        </li>
                                        @endif
                                        
                                        <li class="flex items-center justify-between">
                                            <span class="text-sm text-gray-500 dark:text-gray-400">
                                                Tax ({{ $stay->tax_rate ?? 0 }}%)
                                            </span>
                                            <span class="text-sm font-medium text-gray-700 dark:text-gray-400">
                                                {{ SystemHelper::currencySymbol() }} {{ number_format($stay->tax_amount ?? 0, 2) }}
                                            </span>
                                        </li>
                                        
                                        <li class="flex items-center justify-between pt-3 border-t border-gray-200 dark:border-gray-700">
                                            <span class="font-medium text-gray-700 dark:text-gray-400">
                                                Total Amount
                                            </span>
                                            <span class="text-lg font-semibold text-gray-800 dark:text-white/90">
                                                {{ SystemHelper::currencySymbol() }} {{ number_format($totalAmount, 2) }}
                                            </span>
                                        </li>
                                        
                                        <li class="flex items-center justify-between">
                                            <span class="text-sm text-gray-500 dark:text-gray-400">
                                                Amount Paid
                                            </span>
                                            <span class="text-sm font-medium text-success-600 dark:text-success-500">
                                                {{ SystemHelper::currencySymbol() }} {{ number_format($amountPaid, 2) }}
                                            </span>
                                        </li>
                                        
                                        <li class="flex items-center justify-between">
                                            <span class="text-sm text-gray-500 dark:text-gray-400">
                                                Balance Due
                                            </span>
                                            <span class="text-sm font-medium text-warning-600 dark:text-warning-500">
                                                {{ SystemHelper::currencySymbol() }} {{ number_format($balanceDue, 2) }}
                                            </span>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Payment History Card -->
                    <div class="lg:col-span-1">
                        <div class="rounded-xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-gray-900">
                            <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90 mb-4">
                                Payment History
                            </h3>
                            
                            @if($payments && $payments->count() > 0)
                            <div class="space-y-3">
                                @foreach($payments as $payment)
                                <div class="p-3 bg-gray-50 dark:bg-gray-800 rounded-lg">
                                    <div class="flex items-center justify-between mb-2">
                                        <span class="text-sm font-medium text-gray-700 dark:text-gray-300">
                                            {{ SystemHelper::currencySymbol() }} {{ number_format($payment->amount, 2) }}
                                        </span>
                                        <span class="text-xs px-2 py-1 rounded-full 
                                            @if($payment->status === 'completed') bg-success/10 text-success 
                                            @elseif($payment->status === 'pending') bg-warning/10 text-warning 
                                            @else bg-danger/10 text-danger @endif">
                                            {{ ucfirst($payment->status) }}
                                        </span>
                                    </div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400">
                                        <div class="flex items-center justify-between">
                                            <span>{{ ucfirst(str_replace('_', ' ', $payment->method)) }}</span>
                                            <span>{{ $payment->received_at?->format('D, ' . SystemHelper::dateFormat()) }}</span>
                                        </div>
                                        <div class="mt-1 text-xs">
                                            Ref: {{ $payment->payment_reference }}
                                        </div>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                            @else
                            <div class="text-center py-8 text-gray-500 dark:text-gray-400">
                                <i class="fas fa-credit-card text-3xl mb-3"></i>
                                <p>No payments recorded</p>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Services Tab -->
            <div id="services-tab-content" class="hidden">
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <!-- Services Used -->
                    <div class="lg:col-span-2">
                        <div class="rounded-xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-gray-900">
                            <div class="mb-4 flex items-center justify-between">
                                <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90">
                                    Services Used During Stay
                                </h3>
                                @if($stay->status === 'active')
                                <a href="{{ route('stays.add-service', $stay) }}" 
                                   class="text-sm text-primary hover:text-primary/80">
                                    <i class="fas fa-plus mr-1"></i> Add Service
                                </a>
                                @endif
                            </div>
                            
                            @if($stay->services && $stay->services->count() > 0)
                            <div class="overflow-hidden rounded-lg border border-gray-100 dark:border-gray-800">
                                <div class="custom-scrollbar overflow-x-auto">
                                    <table class="min-w-full text-left text-sm text-gray-700 dark:border-gray-800">
                                        <thead class="bg-gray-50 dark:bg-gray-800">
                                            <tr class="border-b border-gray-100 whitespace-nowrap dark:border-gray-700">
                                                <th class="px-5 py-3 text-sm font-medium whitespace-nowrap text-gray-700 dark:text-gray-400">
                                                    Service
                                                </th>
                                                <th class="px-5 py-3 text-sm font-medium whitespace-nowrap text-gray-500 dark:text-gray-400">
                                                    Date
                                                </th>
                                                <th class="px-5 py-3 text-sm font-medium whitespace-nowrap text-gray-700 dark:text-gray-400">
                                                    Quantity
                                                </th>
                                                <th class="px-5 py-3 text-sm font-medium whitespace-nowrap text-gray-700 dark:text-gray-400">
                                                    Rate
                                                </th>
                                                <th class="px-5 py-3 text-sm font-medium whitespace-nowrap text-gray-700 dark:text-gray-400">
                                                    Total
                                                </th>
                                                <th class="px-5 py-3 text-sm font-medium whitespace-nowrap text-gray-700 dark:text-gray-400">
                                                    Status
                                                </th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-gray-100 bg-white dark:divide-gray-800 dark:bg-white/[0.03]">
                                            @foreach($stay->services as $service)
                                            <tr>
                                                <td class="px-5 py-3 text-sm font-medium whitespace-nowrap text-gray-800 dark:text-white/90">
                                                    {{ $service->service->name }}
                                                </td>
                                                <td class="px-5 py-3 text-sm whitespace-nowrap text-gray-500 dark:text-gray-400">
                                                    {{ $service->service_date?->format('M d, Y H:i') }}
                                                </td>
                                                <td class="px-5 py-3 text-sm whitespace-nowrap text-gray-500 dark:text-gray-400">
                                                    {{ $service->quantity }}
                                                </td>
                                                <td class="px-5 py-3 text-sm whitespace-nowrap text-gray-500 dark:text-gray-400">
                                                    {{ SystemHelper::currencySymbol() }}  {{ number_format($service->rate, 2) }}
                                                </td>
                                                <td class="px-5 py-3 text-sm whitespace-nowrap text-gray-500 dark:text-gray-400">
                                                    {{ SystemHelper::currencySymbol() }}  {{ number_format($service->total_amount, 2) }}
                                                </td>
                                                <td class="px-5 py-3 text-sm whitespace-nowrap">
                                                    <span class="px-2 py-1 text-xs rounded-full 
                                                        @if($service->status === 'charged') bg-success/10 text-success 
                                                        @elseif($service->status === 'pending') bg-warning/10 text-warning 
                                                        @else bg-danger/10 text-danger @endif">
                                                        {{ ucfirst($service->status) }}
                                                    </span>
                                                </td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                        <tfoot class="bg-gray-50 dark:bg-gray-800">
                                            <tr>
                                                <td colspan="4" class="px-5 py-3 text-right text-sm font-medium text-gray-700 dark:text-gray-400">
                                                    Total Services:
                                                </td>
                                                <td class="px-5 py-3 text-sm font-bold text-gray-800 dark:text-white/90">
                                                    {{ SystemHelper::currencySymbol() }}  {{ number_format($stay->services->sum('total_amount'), 2) }}
                                                </td>
                                                <td></td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            </div>
                            @else
                            <div class="text-center py-8 text-gray-500 dark:text-gray-400">
                                <i class="fas fa-concierge-bell text-3xl mb-3"></i>
                                <p>No services used during this stay</p>
                            </div>
                            @endif
                        </div>
                    </div>

                    <!-- Available Services -->
                    <div class="lg:col-span-1">
                        <div class="rounded-xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-gray-900">
                            <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90 mb-4">
                                Available Services
                            </h3>
                            
                            <div class="space-y-3">
                                <div class="p-3 bg-blue-50 dark:bg-blue-900/20 rounded-lg">
                                    <div class="flex items-center justify-between mb-1">
                                        <span class="text-sm font-medium text-gray-700 dark:text-gray-300">
                                            Room Service
                                        </span>
                                        <span class="text-xs font-medium text-primary">
                                            {{ SystemHelper::currencySymbol() }}  25
                                        </span>
                                    </div>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">
                                        24/7 dining service
                                    </p>
                                </div>
                                
                                <div class="p-3 bg-blue-50 dark:bg-blue-900/20 rounded-lg">
                                    <div class="flex items-center justify-between mb-1">
                                        <span class="text-sm font-medium text-gray-700 dark:text-gray-300">
                                            Laundry Service
                                        </span>
                                        <span class="text-xs font-medium text-primary">
                                            {{ SystemHelper::currencySymbol() }}  15
                                        </span>
                                    </div>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">
                                        Same day laundry
                                    </p>
                                </div>
                                
                                <div class="p-3 bg-blue-50 dark:bg-blue-900/20 rounded-lg">
                                    <div class="flex items-center justify-between mb-1">
                                        <span class="text-sm font-medium text-gray-700 dark:text-gray-300">
                                            Airport Transfer
                                        </span>
                                        <span class="text-xs font-medium text-primary">
                                            {{ SystemHelper::currencySymbol() }}  50
                                        </span>
                                    </div>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">
                                        Luxury car service
                                    </p>
                                </div>
                                
                                <div class="p-3 bg-blue-50 dark:bg-blue-900/20 rounded-lg">
                                    <div class="flex items-center justify-between mb-1">
                                        <span class="text-sm font-medium text-gray-700 dark:text-gray-300">
                                            Spa Services
                                        </span>
                                        <span class="text-xs font-medium text-primary">
                                            {{ SystemHelper::currencySymbol() }}  75+
                                        </span>
                                    </div>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">
                                        Massage & treatments
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Documents Tab -->
            <div id="documents-tab-content" class="hidden">
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <!-- Stay Documents -->
                    <div class="lg:col-span-2">
                        <div class="rounded-xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-gray-900">
                            <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90 mb-4">
                                Stay Documents
                            </h3>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <!-- Check-in Document -->
                                <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-4">
                                    <div class="flex items-start">
                                        <div class="p-2 bg-gray-100 dark:bg-gray-700 rounded mr-3">
                                            <i class="fas fa-file-signature text-primary"></i>
                                        </div>
                                        <div class="flex-1">
                                            <h5 class="font-medium text-gray-800 dark:text-white mb-1">Check-in Form</h5>
                                            <p class="text-sm text-gray-600 dark:text-gray-400 mb-2">
                                                Guest registration and agreement
                                            </p>
                                            <p class="text-xs text-gray-500">
                                                @if($stay->checkin_document)
                                                Uploaded: {{ $stay->checkin_document_uploaded_at?->format('D, ' . SystemHelper::dateFormat()) }}
                                                @else
                                                Not uploaded
                                                @endif
                                            </p>
                                        </div>
                                        @if($stay->checkin_document)
                                        <a href="{{ asset('storage/' . $stay->checkin_document) }}" target="_blank" 
                                           class="text-primary hover:text-primary/80 ml-4">
                                            <i class="fas fa-download"></i>
                                        </a>
                                        @endif
                                    </div>
                                </div>

                                <!-- ID Copy -->
                                <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-4">
                                    <div class="flex items-start">
                                        <div class="p-2 bg-gray-100 dark:bg-gray-700 rounded mr-3">
                                            <i class="fas fa-id-card text-primary"></i>
                                        </div>
                                        <div class="flex-1">
                                            <h5 class="font-medium text-gray-800 dark:text-white mb-1">Guest ID Copy</h5>
                                            <p class="text-sm text-gray-600 dark:text-gray-400 mb-2">
                                                Identification document copy
                                            </p>
                                            <p class="text-xs text-gray-500">
                                                @if($stay->id_copy)
                                                Uploaded: {{ $stay->id_copy_uploaded_at?->format('D, ' . SystemHelper::dateFormat()) }}
                                                @else
                                                Not uploaded
                                                @endif
                                            </p>
                                        </div>
                                        @if($stay->id_copy)
                                        <a href="{{ asset('storage/' . $stay->id_copy) }}" target="_blank" 
                                           class="text-primary hover:text-primary/80 ml-4">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        @endif
                                    </div>
                                </div>

                                <!-- Invoice -->
                                <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-4">
                                    <div class="flex items-start">
                                        <div class="p-2 bg-gray-100 dark:bg-gray-700 rounded mr-3">
                                            <i class="fas fa-file-invoice-dollar text-primary"></i>
                                        </div>
                                        <div class="flex-1">
                                            <h5 class="font-medium text-gray-800 dark:text-white mb-1">Final Invoice</h5>
                                            <p class="text-sm text-gray-600 dark:text-gray-400 mb-2">
                                                Complete billing statement
                                            </p>
                                            <p class="text-xs text-gray-500">
                                                @if($stay->invoice_document)
                                                Generated: {{ $stay->invoice_generated_at?->format('D, ' . SystemHelper::dateFormat()) }}
                                                @else
                                                Not generated
                                                @endif
                                            </p>
                                        </div>
                                        @if($stay->invoice_document)
                                        <a href="{{ asset('storage/' . $stay->invoice_document) }}" target="_blank" 
                                           class="text-primary hover:text-primary/80 ml-4">
                                            <i class="fas fa-download"></i>
                                        </a>
                                        @else
                                        <a href="{{ route('invoices.create', $stay) }}" 
                                           class="text-primary hover:text-primary/80 ml-4">
                                            <i class="fas fa-file-export"></i> Generate
                                        </a>
                                        @endif
                                    </div>
                                </div>

                                <!-- Receipt -->
                                <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-4">
                                    <div class="flex items-start">
                                        <div class="p-2 bg-gray-100 dark:bg-gray-700 rounded mr-3">
                                            <i class="fas fa-receipt text-primary"></i>
                                        </div>
                                        <div class="flex-1">
                                            <h5 class="font-medium text-gray-800 dark:text-white mb-1">Payment Receipt</h5>
                                            <p class="text-sm text-gray-600 dark:text-gray-400 mb-2">
                                                Payment confirmation receipt
                                            </p>
                                            <p class="text-xs text-gray-500">
                                                @if($stay->receipt_document)
                                                Generated: {{ $stay->receipt_generated_at?->format('D, ' . SystemHelper::dateFormat()) }}
                                                @else
                                                Not generated
                                                @endif
                                            </p>
                                        </div>
                                        @if($stay->receipt_document)
                                        <a href="{{ asset('storage/' . $stay->receipt_document) }}" target="_blank" 
                                           class="text-primary hover:text-primary/80 ml-4">
                                            <i class="fas fa-download"></i>
                                        </a>
                                        @elseif($stay->amount_paid > 0)
                                        <a href="{{ route('stays.generate-receipt', $stay) }}" 
                                           class="text-primary hover:text-primary/80 ml-4">
                                            <i class="fas fa-file-export"></i> Generate
                                        </a>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Document Actions -->
                    <div class="lg:col-span-1">
                        <div class="rounded-xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-gray-900">
                            <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90 mb-4">
                                Document Actions
                            </h3>
                            
                            <div class="space-y-3">
                                <button class="w-full text-left p-3 bg-gray-50 dark:bg-gray-800 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition">
                                    <div class="flex items-center">
                                        <i class="fas fa-upload text-gray-500 dark:text-gray-400 mr-3"></i>
                                        <div>
                                            <p class="text-sm font-medium text-gray-700 dark:text-gray-300">Upload Document</p>
                                            <p class="text-xs text-gray-500 dark:text-gray-400">Upload check-in form or ID copy</p>
                                        </div>
                                    </div>
                                </button>
                                
                                <button class="w-full text-left p-3 bg-gray-50 dark:bg-gray-800 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition">
                                    <div class="flex items-center">
                                        <i class="fas fa-print text-gray-500 dark:text-gray-400 mr-3"></i>
                                        <div>
                                            <p class="text-sm font-medium text-gray-700 dark:text-gray-300">Print Stay Summary</p>
                                            <p class="text-xs text-gray-500 dark:text-gray-400">Print complete stay details</p>
                                        </div>
                                    </div>
                                </button>
                                
                                <button class="w-full text-left p-3 bg-gray-50 dark:bg-gray-800 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition">
                                    <div class="flex items-center">
                                        <i class="fas fa-envelope text-gray-500 dark:text-gray-400 mr-3"></i>
                                        <div>
                                            <p class="text-sm font-medium text-gray-700 dark:text-gray-300">Email Documents</p>
                                            <p class="text-xs text-gray-500 dark:text-gray-400">Send all documents to guest</p>
                                        </div>
                                    </div>
                                </button>
                                
                                <button class="w-full text-left p-3 bg-gray-50 dark:bg-gray-800 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition">
                                    <div class="flex items-center">
                                        <i class="fas fa-archive text-gray-500 dark:text-gray-400 mr-3"></i>
                                        <div>
                                            <p class="text-sm font-medium text-gray-700 dark:text-gray-300">Archive Stay</p>
                                            <p class="text-xs text-gray-500 dark:text-gray-400">Archive completed stay documents</p>
                                        </div>
                                    </div>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Edit Stay Modal -->
<div id="editStayModal" class="fixed inset-0 z-[2147483647] hidden isolate" aria-labelledby="editStayModalLabel" aria-modal="true">
    <div class="flex min-h-screen items-center justify-center p-4">
                <div class="modal-close-btn fixed inset-0 h-full w-full bg-gray-400/50 backdrop-blur-[32px]" onclick="closeEditStayModal()"></div>

        <div class="relative w-full max-w-4xl rounded-2xl bg-white shadow-2xl dark:bg-gray-800">
            <!-- Modal Header -->
            <div class="flex items-center justify-between border-b border-gray-200 p-6 dark:border-gray-700">
                <div>
                    <h3 class="text-xl font-semibold text-gray-800 dark:text-white/90">Edit Stay #S{{ str_pad($stay->id, 5, '0', STR_PAD_LEFT) }}</h3>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Update stay information</p>
                </div>
                <button onclick="closeEditStayModal()" type="button"
                        class="rounded-lg p-2 hover:bg-gray-100 dark:hover:bg-gray-700">
                    <i class="fas fa-times text-lg text-gray-500 dark:text-gray-400"></i>
                </button>
            </div>
            
            <!-- Modal Body -->
            <form id="editStayForm" onsubmit="updateStay(event)">
                <div class="max-h-[calc(100vh-200px)] overflow-y-auto p-6">
                    <!-- Hidden CSRF Token -->
                    @csrf
                    @method('PUT')
                    
                    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                        <!-- Guest Selection -->
                        <div class="sm:col-span-2">
                            <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-400">
                                Guest <span class="text-danger">*</span>
                            </label>
                            <select id="edit_guest_id" name="guest_id" required
                                    class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full appearance-none rounded-lg border border-gray-300 bg-transparent bg-none px-4 py-2.5 pr-11 pl-4 text-sm text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30">
                                <!-- Options will be populated by JavaScript -->
                            </select>
                        </div>
                        
                        <!-- Arrival Date -->
                        <div>
                            <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-400">
                                Arrival Date <span class="text-danger">*</span>
                            </label>
                            <input type="date" id="edit_arrival_date" name="arrival_date" required
                                   class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full appearance-none rounded-lg border border-gray-300 bg-transparent bg-none px-4 py-2.5 pr-11 pl-4 text-sm text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30">
                        </div>
                        
                        <!-- Departure Date -->
                        <div>
                            <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-400">
                                Departure Date <span class="text-danger">*</span>
                            </label>
                            <input type="date" id="edit_departure_date" name="departure_date" required
                                   class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full appearance-none rounded-lg border border-gray-300 bg-transparent bg-none px-4 py-2.5 pr-11 pl-4 text-sm text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30">
                        </div>
                        
                        <!-- Status -->
                        <div>
                            <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-400">
                                Status <span class="text-danger">*</span>
                            </label>
                            <select id="edit_status" name="status" required
                                    class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full appearance-none rounded-lg border border-gray-300 bg-transparent bg-none px-4 py-2.5 pr-11 pl-4 text-sm text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30">
                                <option value="reserved">Reserved</option>
                                <option value="booked">Booked</option>
                                <option value="checked_in">Checked-in</option>
                                <option value="checked_out">Checked-out</option>
                                <option value="cancelled">Cancelled</option>
                            </select>
                        </div>
                        
                        <!-- Adults -->
                        <div>
                            <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-400">
                                Adults <span class="text-danger">*</span>
                            </label>
                            <input type="number" id="edit_adults" name="adults" min="1" max="10" required
                                   class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full appearance-none rounded-lg border border-gray-300 bg-transparent bg-none px-4 py-2.5 pr-11 pl-4 text-sm text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30">
                        </div>
                        
                        <!-- Children -->
                        <div>
                            <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-400">
                                Children
                            </label>
                            <input type="number" id="edit_children" name="children" min="0" max="10"
                                   class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full appearance-none rounded-lg border border-gray-300 bg-transparent bg-none px-4 py-2.5 pr-11 pl-4 text-sm text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30">
                        </div>
                        
                        <!-- Special Requests -->
                        <div class="sm:col-span-2">
                            <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-400">
                                Special Requests
                            </label>
                            <textarea id="edit_special_requests" name="special_requests" rows="3"
                                      class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 w-full appearance-none rounded-lg border border-gray-300 bg-transparent bg-none px-4 py-2.5 text-sm text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30"
                                      placeholder="Any special requests or notes..."></textarea>
                        </div>
                    </div>
                    
                    <!-- Room Allocations Section -->
                    <div class="mt-6 pt-6 border-t border-gray-200 dark:border-gray-700">
                        <div class="mb-4 flex items-center justify-between">
                            <h4 class="text-lg font-semibold text-gray-800 dark:text-white/90">Room Allocations</h4>
                            <button type="button" onclick="addRoomAllocation()"
                                    class="text-sm text-primary hover:text-primary/80">
                                <i class="fas fa-plus mr-1"></i> Add Room
                            </button>
                        </div>
                        
                        <div id="roomAllocationsContainer" class="space-y-4">
                            <!-- Room allocations will be added here by JavaScript -->
                        </div>
                    </div>
                </div>
                
                <!-- Modal Footer -->
                <div class="border-t border-gray-200 p-6 dark:border-gray-700">
                    <div class="flex justify-end gap-3">
                        <button type="button" onclick="closeEditStayModal()"
                                class="rounded-lg border border-gray-300 px-5 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:text-gray-400 dark:hover:bg-gray-700">
                            Cancel
                        </button>
                        <button
    type="submit"
    id="updateStayButton"
    class="rounded-lg bg-blue-600 px-5 py-2.5 text-sm font-medium text-white hover:bg-blue-700 disabled:cursor-not-allowed disabled:opacity-50"
>
    <span id="updateStayButtonText">Update Stay</span>
    <span id="updateStayButtonLoading" class="hidden">
        <i class="fas fa-spinner fa-spin mr-2"></i> Updating...
    </span>
</button>

                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Add/Edit Room Allocation Modal -->
<div id="roomAllocationModal" class="fixed inset-0 z-[2147483647] hidden isolate" aria-labelledby="roomAllocationModalLabel" aria-modal="true">
    <div class="fixed inset-0 flex items-center justify-center p-5">
        <div class="modal-close-btn fixed inset-0 h-full w-full bg-gray-400/50 backdrop-blur-[32px]" onclick="closeRoomAllocationModal()"></div>
        
        <div class="relative w-full max-w-[480px] rounded-3xl bg-white p-6 dark:bg-gray-900 lg:p-8">
            <!-- Close Button -->
            <button
                onclick="closeRoomAllocationModal()"
                class="group absolute right-3 top-3 z-999 flex h-9.5 w-9.5 items-center justify-center rounded-full bg-gray-200 text-gray-500 transition-colors hover:bg-gray-300 hover:text-gray-500 dark:bg-gray-800 dark:hover:bg-gray-700 sm:right-6 sm:top-6 sm:h-11 sm:w-11"
            >
                <svg
                    class="transition-colors fill-current group-hover:text-gray-600 dark:group-hover:text-gray-200"
                    width="24"
                    height="24"
                    viewBox="0 0 24 24"
                    fill="none"
                    xmlns="http://www.w3.org/2000/svg"
                >
                    <path
                        fill-rule="evenodd"
                        clip-rule="evenodd"
                        d="M6.04289 16.5413C5.65237 16.9318 5.65237 17.565 6.04289 17.9555C6.43342 18.346 7.06658 18.346 7.45711 17.9555L11.9987 13.4139L16.5408 17.956C16.9313 18.3466 17.5645 18.3466 17.955 17.956C18.3455 17.5655 18.3455 16.9323 17.955 16.5418L13.4129 11.9997L17.955 7.4576C18.3455 7.06707 18.3455 6.43391 17.955 6.04338C17.5645 5.65286 16.9313 5.65286 16.5408 6.04338L11.9987 10.5855L7.45711 6.0439C7.06658 5.65338 6.43342 5.65338 6.04289 6.0439C5.65237 6.43442 5.65237 7.06759 6.04289 7.45811L10.5845 11.9997L6.04289 16.5413Z"
                        fill=""
                    />
                </svg>
            </button>

            <!-- Modal Content -->
            <div class="pt-2">
                <h4 class="mb-2 text-lg font-medium text-gray-800 dark:text-white/90" id="roomAllocationModalTitle">Add Room</h4>
                <p class="mb-6 text-sm text-gray-500 dark:text-gray-400">Assign a room to this stay</p>
                
                <form id="roomAllocationForm" onsubmit="saveRoomAllocation(event)" class="space-y-4">
                    @csrf
                    <input type="hidden" id="room_allocation_id" name="id">
                    
                    <!-- Room Selection -->
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                            Room <span class="text-red-500">*</span>
                        </label>
                        <select id="room_id" name="room_id" required
                                class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800">
                            <!-- Options will be populated by JavaScript -->
                        </select>
                    </div>
                    
                    <!-- Rate Applied -->
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                            Rate Applied <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <div class="pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 text-gray-500 dark:text-gray-400">
                                {{ SystemHelper::currencySymbol() }}
                            </div>
                            <input type="number" id="rate_applied" name="rate_applied" step="0.01" min="0" required
                                   class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 pl-10 pr-4 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800"
                                   placeholder="0.00">
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-2 gap-4">
                        <!-- Adults -->
                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                                Adults <span class="text-red-500">*</span>
                            </label>
                            <input type="number" id="room_adults" name="adults" min="1" max="10" required
                                   class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800"
                                   placeholder="1">
                        </div>
                        
                        <!-- Children -->
                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                                Children
                            </label>
                            <input type="number" id="room_children" name="children" min="0" max="10"
                                   class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800"
                                   placeholder="0">
                        </div>
                    </div>
                    
                    <!-- Guest Notes -->
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                            Guest Notes
                        </label>
                        <input type="text" id="guest_notes" name="guest_notes"
                               class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800"
                               placeholder="Any notes for this room...">
                    </div>
                    
                    <div class="grid grid-cols-2 gap-4">
                        <!-- From Date -->
                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                                From Date <span class="text-red-500">*</span>
                            </label>
                            <input type="date" id="from_date" name="from_date" required
                                   class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800">
                        </div>
                        
                        <!-- To Date -->
                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                                To Date <span class="text-red-500">*</span>
                            </label>
                            <input type="date" id="to_date" name="to_date" required
                                   class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800">
                        </div>
                    </div>
                    
                    <!-- Modal Footer -->
                    <div class="flex items-center justify-end w-full gap-3 pt-6 border-t border-gray-100 dark:border-gray-800">
                        <button
                            onclick="closeRoomAllocationModal()"
                            type="button"
                            class="flex w-full justify-center rounded-lg border border-gray-300 bg-white px-4 py-3 text-sm font-medium text-gray-700 shadow-theme-xs transition-colors hover:bg-gray-50 hover:text-gray-800 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03] dark:hover:text-gray-200 sm:w-auto"
                        >
                            Cancel
                        </button>
                        <button
                            type="submit"
                            id="saveRoomButton"
                            class="flex justify-center w-full px-4 py-3 text-sm font-medium text-white rounded-lg bg-brand-500 shadow-theme-xs hover:bg-brand-600 sm:w-auto"
                        >
                            Save Room
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
<!-- Print Styles -->
<style>
@media print {
    .no-print { display: none !important; }
    body { background: white; }
    .container { max-width: none; }
    .shadow-sm, .rounded-2xl, .border { box-shadow: none !important; border: 1px solid #ddd !important; }
    .dark\:bg-white\/3 { background: white !important; }
    .dark\:text-white\/90 { color: black !important; }
    .dark\:text-gray-400 { color: #666 !important; }
}
</style>

<script>
// Global variables
let stayData = {};
let availableRooms = [];
let guests = [];
let roomAllocations = [];

// Tab switching functionality
function showTab(tabName) {
    // Hide all tab contents
    document.getElementById('details-tab-content').classList.add('hidden');
    document.getElementById('billing-tab-content').classList.add('hidden');
    document.getElementById('services-tab-content').classList.add('hidden');
    document.getElementById('documents-tab-content').classList.add('hidden');
    
    // Remove active class from all tabs
    document.getElementById('details-tab').classList.remove('border-primary', 'text-primary');
    document.getElementById('details-tab').classList.add('border-transparent', 'text-gray-500');
    document.getElementById('billing-tab').classList.remove('border-primary', 'text-primary');
    document.getElementById('billing-tab').classList.add('border-transparent', 'text-gray-500');
    document.getElementById('services-tab').classList.remove('border-primary', 'text-primary');
    document.getElementById('services-tab').classList.add('border-transparent', 'text-gray-500');
    document.getElementById('documents-tab').classList.remove('border-primary', 'text-primary');
    document.getElementById('documents-tab').classList.add('border-transparent', 'text-gray-500');
    
    // Show selected tab content
    document.getElementById(tabName + '-tab-content').classList.remove('hidden');
    
    // Add active class to selected tab
    document.getElementById(tabName + '-tab').classList.remove('border-transparent', 'text-gray-500');
    document.getElementById(tabName + '-tab').classList.add('border-primary', 'text-primary');
}

// Edit Stay Modal Functions
async function openEditStayModal() {
    try {
        // Show loading state
        document.getElementById('editStayModal').classList.remove('hidden');
        
        // Fetch stay data
        const response = await fetch('{{ route("stays.edit", $stay) }}', {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        });
        
        const data = await response.json();
        
        if (data.success) {
            stayData = data.stay;
            availableRooms = data.availableRooms;
            guests = data.guests;
            roomAllocations = data.stay.room_allocations;
            
            // Populate form fields
            populateStayForm();
            
            // Populate room allocations
            populateRoomAllocations();
        }
    } catch (error) {
        console.error('Error loading stay data:', error);
        alert('Error loading stay data. Please try again.');
        closeEditStayModal();
    }
}

function closeEditStayModal() {
    document.getElementById('editStayModal').classList.add('hidden');
}

function populateStayForm() {
    // Guest dropdown
    const guestSelect = document.getElementById('edit_guest_id');
    guestSelect.innerHTML = '';
    
    guests.forEach(guest => {
        const option = document.createElement('option');
        option.value = guest.id;
        option.textContent = `${guest.name} (${guest.email})`;
        if (guest.id === stayData.guest_id) {
            option.selected = true;
        }
        guestSelect.appendChild(option);
    });
    
    // Other fields
    document.getElementById('edit_arrival_date').value = stayData.arrival_date;
    document.getElementById('edit_departure_date').value = stayData.departure_date;
    document.getElementById('edit_status').value = stayData.status;
    document.getElementById('edit_adults').value = stayData.adults;
    document.getElementById('edit_children').value = stayData.children || 0;
    document.getElementById('edit_special_requests').value = stayData.special_requests || '';
}

function populateRoomAllocations() {
    const container = document.getElementById('roomAllocationsContainer');
    container.innerHTML = '';
    
    roomAllocations.forEach((allocation, index) => {
        const allocationDiv = createRoomAllocationElement(allocation, index);
        container.appendChild(allocationDiv);
    });
}

function createRoomAllocationElement(allocation, index) {
    const div = document.createElement('div');
    div.className = 'rounded-lg border border-gray-200 p-4 dark:border-gray-700';
    div.innerHTML = `
        <div class="mb-4 flex items-center justify-between">
            <h4 class="font-medium text-gray-800 dark:text-white/90">
                Room ${index + 1}
            </h4>
            <button type="button" onclick="removeRoomAllocation(${allocation.id || index})"
                    class="rounded-lg p-1 text-danger hover:bg-danger/10">
                <i class="fas fa-trash"></i>
            </button>
        </div>
        
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            <input type="hidden" name="room_allocations[${index}][id]" value="${allocation.id || ''}">
            
            <!-- Room Selection -->
            <div>
                <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-400">
                    Room <span class="text-danger">*</span>
                </label>
                <select name="room_allocations[${index}][room_id]" required
                        class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full appearance-none rounded-lg border border-gray-300 bg-transparent bg-none px-4 py-2.5 pr-11 pl-4 text-sm text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30">
                    ${getRoomOptions(allocation.room_id)}
                </select>
            </div>
            
            <!-- Rate Applied -->
            <div>
                <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-400">
                    Rate Applied <span class="text-danger">*</span>
                </label>
                <div class="relative">
                    <div class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-500">
                        {{ SystemHelper::currencySymbol() }} 
                    </div>
                    <input type="number" name="room_allocations[${index}][rate_applied]" 
                           value="${allocation.rate_applied}" step="0.01" min="0" required
                           class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full appearance-none rounded-lg border border-gray-300 bg-transparent bg-none px-4 py-2.5 pr-11 pl-10 text-sm text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30">
                </div>
            </div>
            
            <!-- Adults -->
            <div>
                <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-400">
                    Adults <span class="text-danger">*</span>
                </label>
                <input type="number" name="room_allocations[${index}][adults]" 
                       value="${allocation.adults}" min="1" max="10" required
                       class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full appearance-none rounded-lg border border-gray-300 bg-transparent bg-none px-4 py-2.5 pr-11 pl-4 text-sm text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30">
            </div>
            
            <!-- Children -->
            <div>
                <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-400">
                    Children
                </label>
                <input type="number" name="room_allocations[${index}][children]" 
                       value="${allocation.children || 0}" min="0" max="10"
                       class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full appearance-none rounded-lg border border-gray-300 bg-transparent bg-none px-4 py-2.5 pr-11 pl-4 text-sm text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30">
            </div>
            
            <!-- Guest Notes -->
            <div>
                <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-400">
                    Guest Notes
                </label>
                <input type="text" name="room_allocations[${index}][guest_notes]" 
                       value="${allocation.guest_notes || ''}"
                       class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full appearance-none rounded-lg border border-gray-300 bg-transparent bg-none px-4 py-2.5 pr-11 pl-4 text-sm text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30">
            </div>
            
            <!-- From Date -->
            <div>
                <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-400">
                    From Date <span class="text-danger">*</span>
                </label>
                <input type="date" name="room_allocations[${index}][from_date]" 
                       value="${allocation.from_date || stayData.arrival_date}" required
                       class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full appearance-none rounded-lg border border-gray-300 bg-transparent bg-none px-4 py-2.5 pr-11 pl-4 text-sm text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30">
            </div>
            
            <!-- To Date -->
            <div>
                <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-400">
                    To Date <span class="text-danger">*</span>
                </label>
                <input type="date" name="room_allocations[${index}][to_date]" 
                       value="${allocation.to_date || stayData.departure_date}" required
                       class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full appearance-none rounded-lg border border-gray-300 bg-transparent bg-none px-4 py-2.5 pr-11 pl-4 text-sm text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30">
            </div>
        </div>
    `;
    return div;
}

function getRoomOptions(selectedRoomId) {
    let options = '<option value="">Select Room</option>';
    availableRooms.forEach(room => {
        const selected = room.id == selectedRoomId ? 'selected' : '';
        options += `<option value="${room.id}" ${selected}>Room ${room.room_number} - ${room.room_type?.name || 'N/A'} ({{ SystemHelper::currencySymbol() }} ${room.room_type?.base_rate || 0})</option>`;
    });
    return options;
}

function addRoomAllocation() {
    const container = document.getElementById('roomAllocationsContainer');
    const index = container.children.length;
    const newAllocation = {
        id: '',
        room_id: '',
        rate_applied: '',
        adults: 1,
        children: 0,
        guest_notes: '',
        from_date: stayData.arrival_date,
        to_date: stayData.departure_date
    };
    
    const allocationDiv = createRoomAllocationElement(newAllocation, index);
    container.appendChild(allocationDiv);
}

function removeRoomAllocation(id) {
    if (confirm('Are you sure you want to remove this room allocation?')) {
        if (typeof id === 'number' && id > 0) {
            // Remove from roomAllocations array
            const index = roomAllocations.findIndex(a => a.id == id);
            if (index > -1) {
                roomAllocations.splice(index, 1);
            }
        }
        
        // Re-render room allocations
        populateRoomAllocations();
    }
}

async function updateStay(event) {
    event.preventDefault();
    
    const form = document.getElementById('editStayForm');
    const formData = new FormData(form);
    const submitButton = document.getElementById('updateStayButton');
    const buttonText = document.getElementById('updateStayButtonText');
    const buttonLoading = document.getElementById('updateStayButtonLoading');
    
    // Disable button and show loading
    submitButton.disabled = true;
    buttonText.classList.add('hidden');
    buttonLoading.classList.remove('hidden');
    
    try {
        // Collect room allocations data
        const roomAllocationsData = [];
        const allocationElements = document.querySelectorAll('[name^="room_allocations"]');
        
        // Group by index
        const allocationsByIndex = {};
        allocationElements.forEach(element => {
            const name = element.getAttribute('name');
            const match = name.match(/room_allocations\[(\d+)\]\[(\w+)\]/);
            if (match) {
                const index = match[1];
                const field = match[2];
                const value = element.value;
                
                if (!allocationsByIndex[index]) {
                    allocationsByIndex[index] = {};
                }
                allocationsByIndex[index][field] = value;
            }
        });
        
        // Convert to array and validate
        Object.values(allocationsByIndex).forEach(allocation => {
            if (allocation.room_id && allocation.rate_applied) {
                roomAllocationsData.push(allocation);
            }
        });
        
        // Add room allocations to form data
        formData.delete('room_allocations');
        roomAllocationsData.forEach((allocation, index) => {
            Object.keys(allocation).forEach(key => {
                formData.append(`room_allocations[${index}][${key}]`, allocation[key]);
            });
        });
        
        // Send update request
        const response = await fetch('{{ route("stays.update", $stay) }}', {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: formData
        });
        
        const data = await response.json();
        
        if (data.success) {
            // Show success message
            showNotification('Stay updated successfully', 'success');
            
            // Redirect to stay page after a short delay
            setTimeout(() => {
                if (data.redirect) {
                    window.location.href = data.redirect;
                } else {
                    window.location.reload();
                }
            }, 1500);
        } else {
            throw new Error(data.message || 'Failed to update stay');
        }
    } catch (error) {
        console.error('Error updating stay:', error);
        showNotification(error.message || 'Error updating stay', 'error');
        
        // Re-enable button
        submitButton.disabled = false;
        buttonText.classList.remove('hidden');
        buttonLoading.classList.add('hidden');
    }
}

// Room Allocation Modal Functions
async function openAddRoomModal() {
    try {
        // Set modal title
        document.getElementById('roomAllocationModalTitle').textContent = 'Add Room';
        
        // Reset form
        document.getElementById('roomAllocationForm').reset();
        document.getElementById('room_allocation_id').value = '';
        
        // Load available rooms
        const response = await fetch('{{ route("stays.edit", $stay) }}', {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        });
        
        const data = await response.json();
        
        if (data.success) {
            // Populate room dropdown
            const roomSelect = document.getElementById('room_id');
            roomSelect.innerHTML = '<option value="">Select Room</option>';
            
            data.availableRooms.forEach(room => {
                const option = document.createElement('option');
                option.value = room.id;
                option.textContent = `Room ${room.room_number} - ${room.room_type?.name || 'N/A'} ({{ SystemHelper::currencySymbol() }} ${room.room_type?.base_rate || 0})`;
                roomSelect.appendChild(option);
            });
            
            // Set default dates
            document.getElementById('from_date').value = '{{ $stay->arrival_date?->format("Y-m-d") }}';
            document.getElementById('to_date').value = '{{ $stay->departure_date?->format("Y-m-d") }}';
            
            // Show modal
            document.getElementById('roomAllocationModal').classList.remove('hidden');
        }
    } catch (error) {
        console.error('Error loading room data:', error);
        alert('Error loading room data. Please try again.');
    }
}

async function editRoomAllocation(allocationId) {
    try {
        // Set modal title
        document.getElementById('roomAllocationModalTitle').textContent = 'Edit Room';
        
        // Reset form
        document.getElementById('roomAllocationForm').reset();
        
        // Fetch allocation data
        const response = await fetch(`/room-allocations/${allocationId}/edit`, {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        });
        
        if (!response.ok) {
            throw new Error('Failed to fetch allocation data');
        }
        
        const data = await response.json();
        
        if (data.success) {
            // Set form values
            document.getElementById('room_allocation_id').value = data.allocation.id;
            document.getElementById('room_id').value = data.allocation.room_id;
            document.getElementById('rate_applied').value = data.allocation.rate_applied;
            document.getElementById('room_adults').value = data.allocation.adults;
            document.getElementById('room_children').value = data.allocation.children || 0;
            document.getElementById('guest_notes').value = data.allocation.guest_notes || '';
            document.getElementById('from_date').value = data.allocation.from_date;
            document.getElementById('to_date').value = data.allocation.to_date;
            
            // Load available rooms
            const roomsResponse = await fetch('{{ route("stays.edit", $stay) }}', {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });
            
            const roomsData = await roomsResponse.json();
            
            if (roomsData.success) {
                // Populate room dropdown
                const roomSelect = document.getElementById('room_id');
                roomSelect.innerHTML = '<option value="">Select Room</option>';
                
                roomsData.availableRooms.forEach(room => {
                    const option = document.createElement('option');
                    option.value = room.id;
                    option.textContent = `Room ${room.room_number} - ${room.room_type?.name || 'N/A'} ({{ SystemHelper::currencySymbol() }} ${room.room_type?.base_rate || 0})`;
                    if (room.id == data.allocation.room_id) {
                        option.selected = true;
                    }
                    roomSelect.appendChild(option);
                });
            }
            
            // Show modal
            document.getElementById('roomAllocationModal').classList.remove('hidden');
        }
    } catch (error) {
        console.error('Error loading room allocation:', error);
        alert('Error loading room allocation. Please try again.');
    }
}

function closeRoomAllocationModal() {
    document.getElementById('roomAllocationModal').classList.add('hidden');
}

async function saveRoomAllocation(event) {
    event.preventDefault();
    
    const form = document.getElementById('roomAllocationForm');
    const formData = new FormData(form);
    const allocationId = document.getElementById('room_allocation_id').value;
    
    try {
        // Determine the URL based on whether we're updating or creating
        const url = allocationId 
            ? `/room-allocations/${allocationId}`
            : '{{ route("stays.store") }}';
        
        const method = allocationId ? 'PUT' : 'POST';
        
        // Add stay_id to form data for new allocations
        if (!allocationId) {
            formData.append('stay_id', '{{ $stay->id }}');
        }
        
        const response = await fetch(url, {
            method: method,
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: formData
        });
        
        const data = await response.json();
        
        if (data.success) {
            // Show success message
            showNotification('Room allocation saved successfully', 'success');
            
            // Close modal and refresh page
            closeRoomAllocationModal();
            setTimeout(() => {
                window.location.reload();
            }, 1000);
        } else {
            throw new Error(data.message || 'Failed to save room allocation');
        }
    } catch (error) {
        console.error('Error saving room allocation:', error);
        showNotification(error.message || 'Error saving room allocation', 'error');
    }
}

async function deleteRoomAllocation(allocationId) {
    if (!confirm('Are you sure you want to delete this room allocation?')) {
        return;
    }
    
    try {
        const response = await fetch(`/room-allocations/${allocationId}`, {
            method: 'DELETE',
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        });
        
        const data = await response.json();
        
        if (data.success) {
            // Show success message
            showNotification('Room allocation deleted successfully', 'success');
            
            // Refresh page
            setTimeout(() => {
                window.location.reload();
            }, 1000);
        } else {
            throw new Error(data.message || 'Failed to delete room allocation');
        }
    } catch (error) {
        console.error('Error deleting room allocation:', error);
        showNotification(error.message || 'Error deleting room allocation', 'error');
    }
}

// Helper Functions
function showNotification(message, type = 'success') {
    // You can use your existing notification system or create a simple alert
    alert(`${type === 'success' ? '✓' : '✗'} ${message}`);
}

function printStay() {
    // Store current tab
    const activeTab = document.querySelector('[class*="border-primary"]').id.replace('-tab', '');
    
    // Switch to details tab for printing
    showTab('details');
    
    // Wait a moment for tab switch, then print
    setTimeout(() => {
        window.print();
        
        // Restore previous tab after printing
        setTimeout(() => {
            showTab(activeTab);
        }, 100);
    }, 100);
}

// Initialize date restrictions
document.addEventListener('DOMContentLoaded', function() {
    // Set today's date as min for arrival date
    const today = new Date().toISOString().split('T')[0];
    const arrivalInput = document.getElementById('edit_arrival_date');
    const departureInput = document.getElementById('edit_departure_date');
    
    if (arrivalInput) {
        arrivalInput.min = today;
        arrivalInput.addEventListener('change', function() {
            if (departureInput) {
                departureInput.min = this.value;
                if (departureInput.value && departureInput.value < this.value) {
                    departureInput.value = this.value;
                }
            }
        });
    }
    
    // Auto-calculate rate based on selected room
    const roomSelect = document.getElementById('room_id');
    if (roomSelect) {
        roomSelect.addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            if (selectedOption.text.includes('{{ SystemHelper::currencySymbol() }} ')) {
                const rateMatch = selectedOption.text.match(/{{ SystemHelper::currencySymbol() }} (\d+(\.\d+)?)/);
                if (rateMatch) {
                    document.getElementById('rate_applied').value = rateMatch[1];
                }
            }
        });
    }
});

// Add CSRF token to all fetch requests
const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
if (csrfToken) {
    window.fetch = new Proxy(window.fetch, {
        apply(target, thisArg, args) {
            const [resource, config = {}] = args;
            
            // Add CSRF token to headers for non-GET requests
            if (config.method && config.method !== 'GET') {
                config.headers = {
                    ...config.headers,
                    'X-CSRF-TOKEN': csrfToken
                };
            }
            
            return target.apply(thisArg, [resource, config]);
        }
    });
}
</script>