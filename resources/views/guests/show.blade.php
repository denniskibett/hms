@extends('layouts.app')

@section('content')
<!-- Breadcrumb Start -->
<div x-data="{ pageName: `{{ $guest->user->name ?? 'Guest Profile' }}` }">
    @include('partials.breadcrumb')
</div>
<!-- Breadcrumb End -->

<div class="space-y-6">
    <!-- Guest Status Card -->
    <div class="flex flex-col justify-between gap-6 rounded-2xl border border-gray-200 bg-white px-6 py-5 sm:flex-row sm:items-center dark:border-gray-800 dark:bg-white/3">
        <div class="flex flex-col gap-2.5 divide-gray-300 sm:flex-row sm:divide-x dark:divide-gray-700">
            <div class="flex items-center gap-2 sm:pr-3">
                <span class="text-base font-medium text-gray-700 dark:text-gray-400">
                    Guest ID : G{{ str_pad($guest->id, 5, '0', STR_PAD_LEFT) }}
                </span>
                @php
                    $guestStatus = 'Inactive';
                    $statusClass = 'bg-danger/10 text-danger dark:bg-danger-500/15 dark:text-danger-500';
                    
                    if ($currentStay = $guest->currentStay()) {
                        if ($currentStay->status === 'checked_in') {
                            $guestStatus = 'Active';
                            $statusClass = 'bg-success-50 text-success-600 dark:bg-success-500/15 dark:text-success-500';
                        } elseif ($currentStay->status === 'booked') {
                            $guestStatus = 'Booked';
                            $statusClass = 'bg-warning-50 text-warning-600 dark:bg-warning-500/15 dark:text-orange-400';
                        }
                    }
                @endphp
                <span class="{{ $statusClass }} inline-flex items-center justify-center gap-1 rounded-full px-2.5 py-0.5 text-sm font-medium">
                    {{ $guestStatus }}
                </span>
            </div>
            <p class="text-sm text-gray-500 sm:pl-3 dark:text-gray-400">
                Member since: {{ $guest->user->created_at->format(SystemHelper::dateFormat()) }}
            </p>
        </div>
        <div class="flex gap-3">
            @can('update', $guest)
            <a href="{{ route('guests.edit', $guest) }}" 
               class="bg-warning shadow-theme-xs hover:bg-warning/90 inline-flex items-center justify-center gap-2 rounded-lg px-4 py-3 text-sm font-medium text-white transition">
                Edit Profile
            </a>
            @endcan
            <a href="{{ route('guests.index') }}" 
               class="shadow-theme-xs inline-flex items-center justify-center gap-2 rounded-lg bg-white px-4 py-3 text-sm font-medium text-gray-700 ring-1 ring-gray-300 transition hover:bg-gray-50 dark:bg-gray-800 dark:text-gray-400 dark:ring-gray-700 dark:hover:bg-white/[0.03]">
                Back to List
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-12">
        <!-- Left Column - Guest Details & Preferences -->
        <div class="space-y-6 lg:col-span-4 2xl:col-span-3">
            <!-- Guest Details Card -->
            <div class="rounded-2xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-white/3">
                <h2 class="mb-5 text-lg font-semibold text-gray-800 dark:text-white/90">
                    Guest Details
                </h2>
                
                <!-- Profile Image -->
                <div class="mb-6 flex flex-col items-center">
                    <div class="relative mb-4">
                        @if($guest->user->avatar)
                            <img src="{{ asset('storage/' . $guest->user->avatar) }}" 
                                 alt="{{ $guest->user->name }}" 
                                 class="h-24 w-24 rounded-full border-4 border-white shadow-lg dark:border-gray-800">
                        @else
                            <div class="h-24 w-24 rounded-full border-4 border-white bg-primary/10 shadow-lg dark:border-gray-800 flex items-center justify-center">
                                <span class="text-3xl font-bold text-primary">
                                    {{ substr($guest->user->name, 0, 1) }}
                                </span>
                            </div>
                        @endif
                    </div>
                    
                    <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90">{{ $guest->user->name }}</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400">{{ $guest->user->email }}</p>
                </div>

                <!-- Guest Info -->
                <ul class="divide-y divide-gray-100 dark:divide-gray-800">
                    <li class="flex items-start gap-5 py-2.5">
                        <span class="w-1/2 text-sm text-gray-500 sm:w-1/3 dark:text-gray-400">
                            Phone
                        </span>
                        <span class="w-1/2 text-sm text-gray-700 sm:w-2/3 dark:text-gray-400">
                            {{ $guest->user->phone ?? 'Not provided' }}
                        </span>
                    </li>
                    <li class="flex items-start gap-5 py-2.5">
                        <span class="w-1/2 text-sm text-gray-500 sm:w-1/3 dark:text-gray-400">
                            ID Type
                        </span>
                        <span class="w-1/2 text-sm text-gray-700 sm:w-2/3 dark:text-gray-400">
                            @php
                                $idTypeOptions = App\Http\Controllers\GuestController::getIdTypeOptions();
                                echo $idTypeOptions[$guest->id_type] ?? ucfirst(str_replace('_', ' ', $guest->id_type));
                            @endphp
                        </span>
                    </li>
                    <li class="flex items-start gap-5 py-2.5">
                        <span class="w-1/2 text-sm text-gray-500 sm:w-1/3 dark:text-gray-400">
                            ID Number
                        </span>
                        <span class="w-1/2 text-sm text-gray-700 sm:w-2/2/3 dark:text-gray-400">
                            {{ $guest->id_number }}
                        </span>
                    </li>
                    <li class="flex items-start gap-5 py-2.5">
                        <span class="w-1/2 text-sm text-gray-500 sm:w-1/3 dark:text-gray-400">
                            Nationality
                        </span>
                        <span class="w-1/2 text-sm text-gray-700 sm:w-2/3 dark:text-gray-400">
                            {{ $guest->nationality ?? 'Not provided' }}
                        </span>
                    </li>
                    <li class="flex items-start gap-5 py-2.5">
                        <span class="w-1/2 text-sm text-gray-500 sm:w-1/3 dark:text-gray-400">
                            Address
                        </span>
                        <span class="w-1/2 text-sm text-gray-700 sm:w-2/3 dark:text-gray-400">
                            {{ $guest->address ?? 'Not provided' }}
                        </span>
                    </li>
                    <li class="flex items-start gap-5 py-2.5">
                        <span class="w-1/2 text-sm text-gray-500 sm:w-1/3 dark:text-gray-400">
                            Total Nights
                        </span>
                        <span class="w-1/2 text-sm font-medium text-gray-700 sm:w-2/3 dark:text-gray-400">
                            {{ $totalNights ?? 0 }}
                        </span>
                    </li>
                    <li class="flex items-start gap-5 py-2.5">
                        <span class="w-1/2 text-sm text-gray-500 sm:w-1/3 dark:text-gray-400">
                            Total Stays
                        </span>
                        <span class="w-1/2 text-sm font-medium text-gray-700 sm:w-2/3 dark:text-gray-400">
                            {{ $allStays->count() ?? 0 }}
                        </span>
                    </li>
                </ul>
            </div>

            <!-- Emergency Contact Card -->
            @php
                $emergencyContact = $guest->emergency_contact;
            @endphp
            @if($emergencyContact && (isset($emergencyContact['name']) || isset($emergencyContact['phone'])))
            <div class="rounded-2xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-white/3">
                <h2 class="mb-5 text-lg font-semibold text-gray-800 dark:text-white/90">
                    Emergency Contact
                </h2>
                <ul class="divide-y divide-gray-100 dark:divide-gray-800">
                    @if(isset($emergencyContact['name']))
                    <li class="flex items-start gap-5 py-2.5">
                        <span class="w-1/2 text-sm text-gray-500 sm:w-1/3 dark:text-gray-400">
                            Name
                        </span>
                        <span class="w-1/2 text-sm text-gray-700 sm:w-2/3 dark:text-gray-400">
                            {{ $emergencyContact['name'] }}
                        </span>
                    </li>
                    @endif
                    @if(isset($emergencyContact['phone']))
                    <li class="flex items-start gap-5 py-2.5">
                        <span class="w-1/2 text-sm text-gray-500 sm:w-1/3 dark:text-gray-400">
                            Phone
                        </span>
                        <span class="w-1/2 text-sm text-gray-700 sm:w-2/3 dark:text-gray-400">
                            {{ $emergencyContact['phone'] }}
                        </span>
                    </li>
                    @endif
                    @if(isset($emergencyContact['relationship']))
                    <li class="flex items-start gap-5 py-2.5">
                        <span class="w-1/2 text-sm text-gray-500 sm:w-1/3 dark:text-gray-400">
                            Relationship
                        </span>
                        <span class="w-1/2 text-sm text-gray-700 sm:w-2/3 dark:text-gray-400">
                            {{ $emergencyContact['relationship'] }}
                        </span>
                    </li>
                    @endif
                    @if(isset($emergencyContact['email']))
                    <li class="flex items-start gap-5 py-2.5">
                        <span class="w-1/2 text-sm text-gray-500 sm:w-1/3 dark:text-gray-400">
                            Email
                        </span>
                        <span class="w-1/2 text-sm text-gray-700 sm:w-2/3 dark:text-gray-400">
                            {{ $emergencyContact['email'] }}
                        </span>
                    </li>
                    @endif
                </ul>
            </div>
            @endif
        </div>

        <!-- Right Column - Stays Table & Preferences -->
        <div class="lg:col-span-8 2xl:col-span-9">
            <!-- Tabs Navigation -->
            <div class="mb-6">
                <div class="border-b border-gray-200 dark:border-gray-700">
                    <nav class="-mb-px flex space-x-8" aria-label="Tabs">
                        <button onclick="showTab('stays')" id="stays-tab" class="group relative min-w-0 flex-1 whitespace-nowrap border-b-2 px-1 py-4 text-center text-sm font-medium">
                            <span id="stays-tab-text" class="font-medium text-gray-500 group-hover:text-gray-700 dark:text-gray-400 dark:group-hover:text-gray-300">Stay History</span>
                            <span id="stays-tab-indicator" aria-hidden="true" class="absolute inset-x-0 -bottom-px h-0.5"></span>
                        </button>
                        <button onclick="showTab('preferences')" id="preferences-tab" class="group relative min-w-0 flex-1 whitespace-nowrap border-b-2 px-1 py-4 text-center text-sm font-medium">
                            <span id="preferences-tab-text" class="font-medium text-gray-500 group-hover:text-gray-700 dark:text-gray-400 dark:group-hover:text-gray-300">Preferences</span>
                            <span id="preferences-tab-indicator" aria-hidden="true" class="absolute inset-x-0 -bottom-px h-0.5"></span>
                        </button>
                    </nav>
                </div>
            </div>

            <!-- Stays Tab Content -->
            <div id="stays-tab-content" class="tab-content">
                @include('partials.table.table-stays', [
                    'stays' => $stays,
                    'guest' => $guest,
                    'allRooms' => $allRooms,
                    'totalNights' => $totalNights
                ])
            </div>

            <!-- Preferences Tab Content -->
            <div id="preferences-tab-content" class="tab-content hidden">
                <div class="rounded-2xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-white/3">
                    <h2 class="mb-5 text-lg font-semibold text-gray-800 dark:text-white/90">
                        Guest Preferences
                    </h2>
                    
                    @php
                        $preferences = $guest->preferences ?? [];
                    @endphp
                    
                    @if(!empty($preferences))
                    <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                        <!-- Room Preferences -->
                        @if(isset($preferences['room_preference']) && !empty($preferences['room_preference']))
                        <div class="rounded-lg border border-gray-200 p-4 dark:border-gray-700">
                            <h3 class="mb-3 text-sm font-semibold text-gray-800 dark:text-white/90">Room Preferences</h3>
                            <div class="flex flex-wrap gap-2">
                                @foreach($preferences['room_preference'] as $preference)
                                <span class="inline-flex items-center rounded-full bg-primary/10 px-2.5 py-0.5 text-xs font-medium text-primary">
                                    <i class="fas fa-bed mr-1"></i>
                                    {{ ucfirst(str_replace('_', ' ', $preference)) }}
                                </span>
                                @endforeach
                            </div>
                        </div>
                        @endif

                        <!-- Entertainment Preferences -->
                        @if(isset($preferences['entertainment']) && !empty($preferences['entertainment']))
                        <div class="rounded-lg border border-gray-200 p-4 dark:border-gray-700">
                            <h3 class="mb-3 text-sm font-semibold text-gray-800 dark:text-white/90">Entertainment</h3>
                            <div class="flex flex-wrap gap-2">
                                @foreach($preferences['entertainment'] as $preference)
                                <span class="inline-flex items-center rounded-full bg-success/10 px-2.5 py-0.5 text-xs font-medium text-success">
                                    <i class="fas fa-tv mr-1"></i>
                                    {{ ucfirst(str_replace('_', ' ', $preference)) }}
                                </span>
                                @endforeach
                            </div>
                        </div>
                        @endif

                        <!-- Room Service Preferences -->
                        @if(isset($preferences['room_service']) && !empty($preferences['room_service']))
                        <div class="rounded-lg border border-gray-200 p-4 dark:border-gray-700">
                            <h3 class="mb-3 text-sm font-semibold text-gray-800 dark:text-white/90">Room Service</h3>
                            <div class="flex flex-wrap gap-2">
                                @foreach($preferences['room_service'] as $preference)
                                <span class="inline-flex items-center rounded-full bg-warning/10 px-2.5 py-0.5 text-xs font-medium text-warning">
                                    <i class="fas fa-concierge-bell mr-1"></i>
                                    {{ ucfirst(str_replace('_', ' ', $preference)) }}
                                </span>
                                @endforeach
                            </div>
                        </div>
                        @endif

                        <!-- Restaurant Preferences -->
                        @if(isset($preferences['restaurant']) && !empty($preferences['restaurant']))
                        <div class="rounded-lg border border-gray-200 p-4 dark:border-gray-700">
                            <h3 class="mb-3 text-sm font-semibold text-gray-800 dark:text-white/90">Restaurant</h3>
                            <div class="flex flex-wrap gap-2">
                                @foreach($preferences['restaurant'] as $preference)
                                <span class="inline-flex items-center rounded-full bg-danger/10 px-2.5 py-0.5 text-xs font-medium text-danger">
                                    <i class="fas fa-utensils mr-1"></i>
                                    {{ ucfirst(str_replace('_', ' ', $preference)) }}
                                </span>
                                @endforeach
                            </div>
                        </div>
                        @endif

                        <!-- Check-in Time Preferences -->
                        @if(isset($preferences['checkin_time']) && !empty($preferences['checkin_time']))
                        <div class="rounded-lg border border-gray-200 p-4 dark:border-gray-700">
                            <h3 class="mb-3 text-sm font-semibold text-gray-800 dark:text-white/90">Check-in Time</h3>
                            <div class="flex flex-wrap gap-2">
                                @foreach($preferences['checkin_time'] as $preference)
                                <span class="inline-flex items-center rounded-full bg-purple-100 px-2.5 py-0.5 text-xs font-medium text-purple-800">
                                    <i class="fas fa-clock mr-1"></i>
                                    {{ ucfirst(str_replace('_', ' ', $preference)) }}
                                </span>
                                @endforeach
                            </div>
                        </div>
                        @endif

                        <!-- Other Preferences -->
                        @if(isset($preferences['other']) && !empty(trim($preferences['other'])))
                        <div class="md:col-span-2 rounded-lg border border-gray-200 p-4 dark:border-gray-700">
                            <h3 class="mb-3 text-sm font-semibold text-gray-800 dark:text-white/90">Other Preferences</h3>
                            <p class="text-sm text-gray-600 dark:text-gray-400">{{ $preferences['other'] }}</p>
                        </div>
                        @endif

                        <!-- Allergies -->
                        @if(isset($preferences['allergies']) && !empty(trim($preferences['allergies'])))
                        <div class="md:col-span-2 rounded-lg border border-gray-200 p-4 dark:border-gray-700">
                            <h3 class="mb-3 text-sm font-semibold text-gray-800 dark:text-white/90">Allergies / Dietary Restrictions</h3>
                            <p class="text-sm text-gray-600 dark:text-gray-400">{{ $preferences['allergies'] }}</p>
                        </div>
                        @endif
                    </div>
                    @else
                    <div class="rounded-lg border border-gray-200 p-8 text-center dark:border-gray-700">
                        <i class="fas fa-sliders-h text-4xl text-gray-400 mb-3"></i>
                        <p class="text-gray-500 dark:text-gray-400">No preferences set for this guest</p>
                        <p class="mt-1 text-sm text-gray-400">Add preferences when editing the guest profile</p>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
// Tab switching function
function showTab(tabName) {
    // Hide all tab contents
    document.querySelectorAll('.tab-content').forEach(tab => {
        tab.classList.add('hidden');
    });
    
    // Remove active state from all tabs
    document.querySelectorAll('[id$="-tab"]').forEach(tabBtn => {
        const indicator = document.getElementById(tabBtn.id.replace('-tab', '-tab-indicator'));
        const text = document.getElementById(tabBtn.id.replace('-tab', '-tab-text'));
        
        tabBtn.classList.remove('border-primary', 'text-primary');
        if (indicator) {
            indicator.classList.remove('bg-primary');
        }
        if (text) {
            text.classList.remove('text-primary', 'dark:text-white');
            text.classList.add('text-gray-500', 'dark:text-gray-400');
        }
    });
    
    // Show selected tab content
    document.getElementById(tabName + '-tab-content').classList.remove('hidden');
    
    // Activate selected tab button
    const activeTab = document.getElementById(tabName + '-tab');
    const activeIndicator = document.getElementById(tabName + '-tab-indicator');
    const activeText = document.getElementById(tabName + '-tab-text');
    
    if (activeTab) {
        activeTab.classList.add('border-primary', 'text-primary');
    }
    if (activeIndicator) {
        activeIndicator.classList.add('bg-primary');
    }
    if (activeText) {
        activeText.classList.remove('text-gray-500', 'dark:text-gray-400');
        activeText.classList.add('text-primary', 'dark:text-white');
    }
}

// Initialize first tab as active when page loads
document.addEventListener('DOMContentLoaded', function() {
    showTab('stays');
});


</script>
@endpush