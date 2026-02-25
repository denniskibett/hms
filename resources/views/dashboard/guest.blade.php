
        <!-- Stats Cards -->
        <div class="grid grid-cols-1 gap-4 mb-6 sm:grid-cols-2 lg:grid-cols-3">
            <!-- Current Stays -->
            <div class="bg-white rounded-lg border border-gray-200 dark:border-gray-800 dark:bg-white/[0.03] p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Current Stays</p>
                        <p class="mt-1 text-2xl font-semibold text-gray-900 dark:text-white">{{ count($currentStays) }}</p>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Active reservations</p>
                    </div>
                    <div class="p-3 rounded-full bg-primary/10">
                        <svg class="w-6 h-6 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Past Stays -->
            <div class="bg-white rounded-lg border border-gray-200 dark:border-gray-800 dark:bg-white/[0.03] p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Past Stays</p>
                        <p class="mt-1 text-2xl font-semibold text-gray-900 dark:text-white">{{ count($pastStays) }}</p>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Previous visits</p>
                    </div>
                    <div class="p-3 rounded-full bg-success/10">
                        <svg class="w-6 h-6 text-success" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Loyalty Points -->
            <div class="bg-white rounded-lg border border-gray-200 dark:border-gray-800 dark:bg-white/[0.03] p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Loyalty Points</p>
                        <p class="mt-1 text-2xl font-semibold text-gray-900 dark:text-white">1,250</p>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Earn rewards</p>
                    </div>
                    <div class="p-3 rounded-full bg-warning/10">
                        <svg class="w-6 h-6 text-warning" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z" />
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-12 gap-4 md:gap-6">
            <!-- Current Stays -->
            <div class="col-span-12 lg:col-span-7">
                <div class="bg-white rounded-lg border border-gray-200 dark:border-gray-800 dark:bg-white/[0.03] p-6">
                    <div class="flex items-center justify-between mb-6">
                        <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90">Current Stays</h3>
                        @if(count($currentStays) > 0)
                            <a href="{{ route('guests.index', $user) }}" class="text-sm text-primary hover:underline">View All</a>
                        @endif

                    </div>
                    <div class="space-y-4">
                        @forelse($currentStays as $stay)
                        <div class="p-4 rounded-lg border border-gray-200 dark:border-gray-800">
                            <div class="flex items-start justify-between">
                                <div class="flex-1">
                                    <div class="flex items-center justify-between mb-2">
                                        <h4 class="font-medium text-gray-900 dark:text-white">Stay #{{ $stay->id }}</h4>
                                        <span class="px-2 py-1 text-xs font-semibold rounded-full {{ 
                                            $stay->status === 'checked_in' ? 'bg-success/10 text-success' : 'bg-primary/10 text-primary'
                                        }}">
                                            {{ ucfirst(str_replace('_', ' ', $stay->status)) }}
                                        </span>
                                    </div>
                                    
                                    <!-- Room Information -->
                                    <div class="mb-3">
                                        @foreach($stay->roomAllocations as $allocation)
                                        <div class="flex items-center text-sm text-gray-600 dark:text-gray-400 mb-1">
                                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                                            </svg>
                                            Room {{ $allocation->room->room_number }} • {{ $allocation->room->roomType->name }}
                                        </div>
                                        @endforeach
                                    </div>

                                    <!-- Stay Dates -->
                                    <div class="flex items-center justify-between mb-3">
                                        <div class="flex items-center space-x-4">
                                            <div class="flex items-center text-sm text-gray-600 dark:text-gray-400">
                                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                                </svg>
                                                Check-in: {{ $stay->arrival_date->format('M d, Y') }}
                                            </div>
                                            <div class="flex items-center text-sm text-gray-600 dark:text-gray-400">
                                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                                </svg>
                                                Check-out: {{ $stay->departure_date->format('M d, Y') }}
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Actions -->
                                    <div class="flex items-center justify-between">
                                        <div class="text-sm text-gray-500 dark:text-gray-400">
                                            {{ $stay->adults }} Adult(s), {{ $stay->children }} Child(ren)
                                        </div>
                                        <div class="flex items-center space-x-2">
                                            <a href="{{ route('stays.show', $stay) }}" class="px-3 py-1 text-sm bg-primary text-white rounded hover:bg-primary/90">
                                                View Details
                                            </a>
                                            @if($stay->status === 'booked')
                                            <a href="{{ route('guest.check-in', $stay) }}" class="px-3 py-1 text-sm bg-success text-white rounded hover:bg-success/90">
                                                Check-in
                                            </a>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @empty
                        <div class="text-center py-8">
                            <p class="text-gray-500 dark:text-gray-400 mb-4">You don't have any current stays.</p>
                            <a href="{{ route('guests.create') }}" class="px-4 py-2 bg-primary text-white rounded hover:bg-primary/90">
                                Book a Stay
                            </a>
                        </div>
                        @endforelse
                    </div>
                </div>
            </div>

            <!-- Quick Actions & Past Stays -->
            <div class="col-span-12 lg:col-span-5 space-y-6">
                <!-- Quick Actions -->
                <div class="bg-white rounded-lg border border-gray-200 dark:border-gray-800 dark:bg-white/[0.03] p-6">
                    <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90 mb-4">Quick Actions</h3>
                    <div class="grid grid-cols-2 gap-3">
                        <a href="{{ route('guests.create') }}" class="p-4 rounded-lg border border-gray-200 dark:border-gray-800 hover:bg-gray-50 dark:hover:bg-gray-800 text-center">
                            <div class="p-2 rounded-full bg-primary/10 inline-block mb-2">
                                <svg class="w-6 h-6 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                </svg>
                            </div>
                            <p class="font-medium text-gray-900 dark:text-white">Book a Room</p>
                        </a>
                        <a href="{{ route('kitchen.index') }}" class="p-4 rounded-lg border border-gray-200 dark:border-gray-800 hover:bg-gray-50 dark:hover:bg-gray-800 text-center">
                            <div class="p-2 rounded-full bg-success/10 inline-block mb-2">
                                <svg class="w-6 h-6 text-success" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v13m0-13V6a2 2 0 112 2h-2zm0 0V5.5A2.5 2.5 0 109.5 8H12zm-7 4h14M5 12a2 2 0 110-4h14a2 2 0 110 4M5 12v7a2 2 0 002 2h10a2 2 0 002-2v-7" />
                                </svg>
                            </div>
                            <p class="font-medium text-gray-900 dark:text-white">Order Food</p>
                        </a>
                        <a href="{{ route('profile.edit') }}" class="p-4 rounded-lg border border-gray-200 dark:border-gray-800 hover:bg-gray-50 dark:hover:bg-gray-800 text-center">
                            <div class="p-2 rounded-full bg-warning/10 inline-block mb-2">
                                <svg class="w-6 h-6 text-warning" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                </svg>
                            </div>
                            <p class="font-medium text-gray-900 dark:text-white">Edit Profile</p>
                        </a>
                        <a href="#" class="p-4 rounded-lg border border-gray-200 dark:border-gray-800 hover:bg-gray-50 dark:hover:bg-gray-800 text-center">
                            <div class="p-2 rounded-full bg-info/10 inline-block mb-2">
                                <svg class="w-6 h-6 text-info" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                                </svg>
                            </div>
                            <p class="font-medium text-gray-900 dark:text-white">Payment</p>
                        </a>
                    </div>
                </div>

                <!-- Recent Past Stays -->
                <div class="bg-white rounded-lg border border-gray-200 dark:border-gray-800 dark:bg-white/[0.03] p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90">Recent Past Stays</h3>
                        @if(count($pastStays) > 0)
                            <a href="{{ route('guests.index', $user) }}" class="text-sm text-primary hover:underline">View All</a>
                        @endif
                    </div>
                    <div class="space-y-3">
                        @forelse($pastStays as $stay)
                        <div class="flex items-center justify-between p-3 rounded-lg bg-gray-50 dark:bg-gray-800">
                            <div>
                                @foreach($currentStays as $stay)
                                <p class="font-medium text-gray-900 dark:text-white">Stay #{{ $stay->id }}</p>
                                <p class="text-sm text-gray-500 dark:text-gray-400">
                                    {{ \App\Helpers\SystemHelper::dateFormat($stay->arrival_date) }} - {{ \App\Helpers\SystemHelper::dateFormat($stay->departure_date) }}
                                    @foreach($stay->roomAllocations as $allocation)
                                        • Room {{ $allocation->room->room_number }}
                                    @endforeach
                                </p>
                                @endforeach
                            </div>
                            <span class="px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 dark:bg-gray-800 text-gray-800 dark:text-gray-300">
                                Completed
                            </span>
                        </div>
                        @empty
                        <p class="text-gray-500 dark:text-gray-400 text-center py-4">No past stays yet.</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
