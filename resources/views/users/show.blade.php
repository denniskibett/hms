@extends('layouts.app')

@section('content')
    <div class="p-4 mx-auto max-w-screen-2xl md:p-6">
        <!-- Header -->
        <div class="mb-6">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h2 class="text-2xl font-bold text-gray-800 dark:text-white/90">User Details</h2>
                    <p class="text-gray-600 dark:text-gray-400">View user information and profile</p>
                </div>
                <div class="flex items-center space-x-2">
                    <a href="{{ route('users.edit', $user) }}" class="px-4 py-2 bg-warning text-white rounded hover:bg-warning/90">
                        Edit User
                    </a>
                    <a href="{{ route('users.index') }}" class="px-4 py-2 border border-gray-300 dark:border-gray-700 rounded text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800">
                        Back to List
                    </a>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-12 gap-6">
            <!-- Left Column - User Info -->
            <div class="col-span-12 lg:col-span-4">
                <!-- Profile Card -->
<div class="bg-white rounded-lg border border-gray-200 dark:bg-gray-800 dark:border-gray-700 shadow-sm">
    <div class="p-6">
        <!-- Profile Header -->
        <div class="flex flex-col items-center mb-6">
            <div class="relative mb-4">
                @if($user->avatar)
                    <img src="{{ asset('storage/' . $user->avatar) }}" alt="{{ $user->name }}" 
                         class="w-32 h-32 rounded-full border-4 border-white dark:border-gray-800 shadow">
                @else
                    <div class="w-32 h-32 rounded-full border-4 border-white dark:border-gray-800 shadow bg-primary/10 flex items-center justify-center">
                        <span class="text-4xl font-bold text-primary">{{ substr($user->name, 0, 1) }}</span>
                    </div>
                @endif
                
                <!-- Status Indicator -->
                <div class="absolute bottom-2 right-2">
                    @if($user->is_active)
                        <span class="block w-4 h-4 bg-success rounded-full border-2 border-white dark:border-gray-800"></span>
                    @else
                        <span class="block w-4 h-4 bg-danger rounded-full border-2 border-white dark:border-gray-800"></span>
                    @endif
                </div>
            </div>
            
            <h3 class="text-xl font-bold text-gray-800 dark:text-white/90">{{ $user->name }}</h3>
            <p class="text-gray-600 dark:text-gray-400">{{ $user->email }}</p>
            
            <!-- Role Badge -->
            <div class="mt-2">
                <span class="px-3 py-1 text-xs font-medium rounded-full 
                    @if($user->role === 'admin') bg-primary/10 text-primary 
                    @elseif($user->role === 'manager') bg-warning/10 text-warning 
                    @else bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300 @endif">
                    {{ ucfirst($user->role) }}
                </span>
            </div>
        </div>

        <!-- User Stats -->
        <div class="grid grid-cols-3 gap-4 mb-6">
            <div class="text-center p-3 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
                <p class="text-2xl font-bold text-gray-800 dark:text-white">{{ $user->id }}</p>
                <p class="text-sm text-gray-600 dark:text-gray-400">ID</p>
            </div>
            <div class="text-center p-3 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
                <p class="text-2xl font-bold text-gray-800 dark:text-white">
                    {{ $user->created_at->format('d') }}
                </p>
                <p class="text-sm text-gray-600 dark:text-gray-400">Joined</p>
            </div>
            <div class="text-center p-3 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
                <p class="text-2xl font-bold text-gray-800 dark:text-white">
                    @if($user->email_verified_at)
                        <i class="fas fa-check text-success"></i>
                    @else
                        <i class="fas fa-times text-danger"></i>
                    @endif
                </p>
                <p class="text-sm text-gray-600 dark:text-gray-400">Verified</p>
            </div>
        </div>

        <!-- Quick Info -->
        <div class="space-y-4">
            <div class="flex items-center justify-between">
                <span class="text-gray-600 dark:text-gray-400">Status</span>
                <span class="font-medium @if($user->is_active) text-success @else text-danger @endif">
                    @if($user->is_active) Active @else Inactive @endif
                </span>
            </div>
            
            <div class="flex items-center justify-between">
                <span class="text-gray-600 dark:text-gray-400">Last Login</span>
                <span class="font-medium text-gray-800 dark:text-white">
                    {{ $user->last_login_at ? $user->last_login_at->diffForHumans() : 'Never' }}
                </span>
            </div>
            
            <div class="flex items-center justify-between">
                <span class="text-gray-600 dark:text-gray-400">Member Since</span>
                <span class="font-medium text-gray-800 dark:text-white">
                    {{ $user->created_at->format('M d, Y') }}
                </span>
            </div>
        </div>
    </div>
</div>
            </div>

            <!-- Right Column - Details -->
            <div class="col-span-12 lg:col-span-8">
                <!-- Personal Information Card -->
                <div class="bg-white rounded-lg border border-gray-200 dark:bg-gray-800 dark:border-gray-700 shadow-sm mb-6">
                    <div class="p-6">
                        <h4 class="text-lg font-semibold text-gray-800 dark:text-white/90 mb-4">Personal Information</h4>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-600 dark:text-gray-400 mb-1">Full Name</label>
                                <p class="text-gray-800 dark:text-white font-medium">{{ $user->name }}</p>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-600 dark:text-gray-400 mb-1">Email Address</label>
                                <p class="text-gray-800 dark:text-white font-medium">{{ $user->email }}</p>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-600 dark:text-gray-400 mb-1">Phone Number</label>
                                <p class="text-gray-800 dark:text-white font-medium">
                                    {{ $user->phone ?? 'Not provided' }}
                                </p>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-600 dark:text-gray-400 mb-1">Date of Birth</label>
                                <p class="text-gray-800 dark:text-white font-medium">
                                    {{ $user->date_of_birth ? $user->date_of_birth->format('M d, Y') : 'Not provided' }}
                                </p>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-600 dark:text-gray-400 mb-1">Gender</label>
                                <p class="text-gray-800 dark:text-white font-medium">
                                    {{ $user->gender ? ucfirst($user->gender) : 'Not specified' }}
                                </p>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-600 dark:text-gray-400 mb-1">Country</label>
                                <p class="text-gray-800 dark:text-white font-medium">
                                    {{ $user->country ?? 'Not specified' }}
                                </p>
                            </div>
                        </div>
                        
                        @if($user->address)
                        <div class="mt-6">
                            <label class="block text-sm font-medium text-gray-600 dark:text-gray-400 mb-1">Address</label>
                            <p class="text-gray-800 dark:text-white">{{ $user->address }}</p>
                        </div>
                        @endif
                        
                        @if($user->bio)
                        <div class="mt-6">
                            <label class="block text-sm font-medium text-gray-600 dark:text-gray-400 mb-1">Bio</label>
                            <p class="text-gray-800 dark:text-white">{{ $user->bio }}</p>
                        </div>
                        @endif
                    </div>
                </div>

                <!-- Account Information Card -->
                <div class="bg-white rounded-lg border border-gray-200 dark:bg-gray-800 dark:border-gray-700 shadow-sm mb-6">
                    <div class="p-6">
                        <h4 class="text-lg font-semibold text-gray-800 dark:text-white/90 mb-4">Account Information</h4>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-600 dark:text-gray-400 mb-1">User Role</label>
                                <div class="flex items-center">
                                    <span class="px-3 py-1 text-sm font-medium rounded-full 
                                        @if($user->role === 'admin') bg-primary/10 text-primary 
                                        @elseif($user->role === 'manager') bg-warning/10 text-warning 
                                        @else bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300 @endif">
                                        {{ ucfirst($user->role) }}
                                    </span>
                                </div>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-600 dark:text-gray-400 mb-1">Account Status</label>
                                <div class="flex items-center">
                                    @if($user->is_active)
                                        <span class="px-3 py-1 text-sm font-medium rounded-full bg-success/10 text-success">
                                            <i class="fas fa-circle text-xs mr-1"></i> Active
                                        </span>
                                    @else
                                        <span class="px-3 py-1 text-sm font-medium rounded-full bg-danger/10 text-danger">
                                            <i class="fas fa-circle text-xs mr-1"></i> Inactive
                                        </span>
                                    @endif
                                </div>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-600 dark:text-gray-400 mb-1">Email Verification</label>
                                <div class="flex items-center">
                                    @if($user->email_verified_at)
                                        <span class="px-3 py-1 text-sm font-medium rounded-full bg-success/10 text-success">
                                            <i class="fas fa-check-circle mr-1"></i> Verified
                                        </span>
                                    @else
                                        <span class="px-3 py-1 text-sm font-medium rounded-full bg-warning/10 text-warning">
                                            <i class="fas fa-exclamation-circle mr-1"></i> Not Verified
                                        </span>
                                    @endif
                                </div>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-600 dark:text-gray-400 mb-1">Two-Factor Auth</label>
                                <div class="flex items-center">
                                    @if($user->two_factor_enabled)
                                        <span class="px-3 py-1 text-sm font-medium rounded-full bg-success/10 text-success">
                                            <i class="fas fa-shield-alt mr-1"></i> Enabled
                                        </span>
                                    @else
                                        <span class="px-3 py-1 text-sm font-medium rounded-full bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300">
                                            <i class="fas fa-shield-alt mr-1"></i> Disabled
                                        </span>
                                    @endif
                                </div>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-600 dark:text-gray-400 mb-1">Created At</label>
                                <p class="text-gray-800 dark:text-white font-medium">
                                    {{ $user->created_at->format('F d, Y \a\t h:i A') }}
                                </p>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-600 dark:text-gray-400 mb-1">Last Updated</label>
                                <p class="text-gray-800 dark:text-white font-medium">
                                    {{ $user->updated_at->format('F d, Y \a\t h:i A') }}
                                </p>
                            </div>
                        </div>
                        
                        <!-- Last Login Info -->
                        <div class="mt-6 pt-6 border-t border-gray-200 dark:border-gray-700">
                            <label class="block text-sm font-medium text-gray-600 dark:text-gray-400 mb-1">Last Login</label>
                            <p class="text-gray-800 dark:text-white">
                                @if($user->last_login_at)
                                    {{ $user->last_login_at->format('F d, Y \a\t h:i A') }}
                                    <span class="text-gray-600 dark:text-gray-400 ml-2">
                                        ({{ $user->last_login_at->diffForHumans() }})
                                    </span>
                                @else
                                    <span class="text-gray-500 italic">No login recorded</span>
                                @endif
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Activity Log Card (Optional - if you have activity logs) -->
                @if($user->activities && $user->activities->count() > 0)
                <div class="bg-white rounded-lg border border-gray-200 dark:bg-gray-800 dark:border-gray-700 shadow-sm">
                    <div class="p-6">
                        <h4 class="text-lg font-semibold text-gray-800 dark:text-white/90 mb-4">Recent Activity</h4>
                        
                        <div class="space-y-4">
                            @foreach($user->activities->take(5) as $activity)
                            <div class="flex items-start">
                                <div class="flex-shrink-0 mt-1">
                                    @if($activity->type === 'login')
                                        <i class="fas fa-sign-in-alt text-primary"></i>
                                    @elseif($activity->type === 'update')
                                        <i class="fas fa-edit text-warning"></i>
                                    @elseif($activity->type === 'create')
                                        <i class="fas fa-plus-circle text-success"></i>
                                    @else
                                        <i class="fas fa-history text-gray-400"></i>
                                    @endif
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm text-gray-800 dark:text-white">
                                        {{ $activity->description }}
                                    </p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                        {{ $activity->created_at->diffForHumans() }}
                                    </p>
                                </div>
                            </div>
                            @endforeach
                        </div>
                        
                        @if($user->activities->count() > 5)
                        <div class="mt-4 text-center">
                            <a href="{{ route('users.activity', $user) }}" class="text-primary hover:text-primary/80 text-sm font-medium">
                                View All Activity
                            </a>
                        </div>
                        @endif
                    </div>
                </div>
                @endif
            </div>
        </div>

        <!-- Danger Zone Card -->
        <div class="mt-8 bg-white rounded-lg border border-red-200 dark:bg-gray-800 dark:border-red-900 shadow-sm">
            <div class="p-6">
                <h4 class="text-lg font-semibold text-red-700 dark:text-red-400 mb-2">Danger Zone</h4>
                <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
                    These actions are irreversible. Please proceed with caution.
                </p>
                
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h5 class="font-medium text-gray-800 dark:text-white">Delete User Account</h5>
                        <p class="text-sm text-gray-600 dark:text-gray-400">
                            Permanently delete this user account and all associated data.
                        </p>
                    </div>
                    
                    <form action="{{ route('users.destroy', $user) }}" method="POST" class="mt-4 sm:mt-0" 
                          onsubmit="return confirm('Are you sure you want to delete this user? This action cannot be undone.');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="px-4 py-2 bg-danger text-white rounded hover:bg-danger/90">
                            <i class="fas fa-trash mr-1"></i> Delete User
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal for confirmation (optional, can be added if needed) -->
    <div id="deleteModal" class="fixed inset-0 bg-gray-500 bg-opacity-75 hidden z-50">
        <!-- Modal content would go here -->
    </div>

@endsection

@push('scripts')
<script>
    // Add any JavaScript needed for the user show page
    document.addEventListener('DOMContentLoaded', function() {
        // Example: Toggle password visibility if you have password fields
        const togglePasswordButtons = document.querySelectorAll('.toggle-password');
        
        togglePasswordButtons.forEach(button => {
            button.addEventListener('click', function() {
                const target = document.querySelector(this.getAttribute('data-target'));
                const type = target.getAttribute('type') === 'password' ? 'text' : 'password';
                target.setAttribute('type', type);
                
                // Toggle icon
                this.querySelector('i').classList.toggle('fa-eye');
                this.querySelector('i').classList.toggle('fa-eye-slash');
            });
        });
    });
</script>
@endpush