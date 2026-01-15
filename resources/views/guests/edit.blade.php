@extends('layouts.app')

@section('content')
<div class="p-4 mx-auto max-w-screen-2xl md:p-6">
    <!-- Header -->
    <div class="mb-6">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-2xl font-bold text-gray-800 dark:text-white/90">Edit Guest Profile</h2>
                <p class="text-gray-600 dark:text-gray-400">Update guest information</p>
            </div>
            <div class="flex items-center space-x-2">
                <a href="{{ route('guests.show', $guest) }}" class="px-4 py-2 border border-gray-300 dark:border-gray-700 rounded text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800">
                    View Profile
                </a>
                <a href="{{ route('guests.index') }}" class="px-4 py-2 border border-gray-300 dark:border-gray-700 rounded text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800">
                    Back to List
                </a>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-12 gap-6">
        <!-- Form -->
        <div class="col-span-12 lg:col-span-8">
            <div class="bg-white rounded-lg border border-gray-200 dark:bg-gray-800 dark:border-gray-700 shadow-sm">
                <div class="p-6">
                    <form action="{{ route('guests.update', $guest) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
                        
                        <!-- Personal Information -->
                        <div class="mb-8">
                            <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90 mb-4">Personal Information</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">First Name *</label>
                                    <input type="text" name="first_name" required 
                                           class="w-full border border-gray-300 dark:border-gray-700 rounded px-3 py-2 dark:bg-gray-900" 
                                           value="{{ old('first_name', $guest->first_name) }}">
                                    @error('first_name')
                                    <p class="mt-1 text-sm text-danger">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Last Name *</label>
                                    <input type="text" name="last_name" required 
                                           class="w-full border border-gray-300 dark:border-gray-700 rounded px-3 py-2 dark:bg-gray-900" 
                                           value="{{ old('last_name', $guest->last_name) }}">
                                    @error('last_name')
                                    <p class="mt-1 text-sm text-danger">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Date of Birth</label>
                                    <input type="date" name="date_of_birth" 
                                           class="w-full border border-gray-300 dark:border-gray-700 rounded px-3 py-2 dark:bg-gray-900" 
                                           value="{{ old('date_of_birth', $guest->date_of_birth ? $guest->date_of_birth->format('Y-m-d') : '') }}">
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Gender</label>
                                    <select name="gender" class="w-full border border-gray-300 dark:border-gray-700 rounded px-3 py-2 dark:bg-gray-900">
                                        <option value="">Select Gender</option>
                                        <option value="male" {{ old('gender', $guest->gender) == 'male' ? 'selected' : '' }}>Male</option>
                                        <option value="female" {{ old('gender', $guest->gender) == 'female' ? 'selected' : '' }}>Female</option>
                                        <option value="other" {{ old('gender', $guest->gender) == 'other' ? 'selected' : '' }}>Other</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- Contact Information -->
                        <div class="mb-8">
                            <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90 mb-4">Contact Information</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Email *</label>
                                    <input type="email" name="email" required 
                                           class="w-full border border-gray-300 dark:border-gray-700 rounded px-3 py-2 dark:bg-gray-900" 
                                           value="{{ old('email', $guest->email) }}">
                                    @error('email')
                                    <p class="mt-1 text-sm text-danger">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Phone *</label>
                                    <input type="tel" name="phone" required 
                                           class="w-full border border-gray-300 dark:border-gray-700 rounded px-3 py-2 dark:bg-gray-900" 
                                           value="{{ old('phone', $guest->phone) }}">
                                    @error('phone')
                                    <p class="mt-1 text-sm text-danger">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div class="md:col-span-2">
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Address</label>
                                    <textarea name="address" rows="2" 
                                              class="w-full border border-gray-300 dark:border-gray-700 rounded px-3 py-2 dark:bg-gray-900">{{ old('address', $guest->address) }}</textarea>
                                </div>
                            </div>
                        </div>

                        <!-- Identification -->
                        <div class="mb-8">
                            <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90 mb-4">Identification</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Identification Type *</label>
                                    <select name="identification_type" required 
                                            class="w-full border border-gray-300 dark:border-gray-700 rounded px-3 py-2 dark:bg-gray-900">
                                        <option value="">Select Type</option>
                                        <option value="passport" {{ old('identification_type', $guest->identification_type) == 'passport' ? 'selected' : '' }}>Passport</option>
                                        <option value="national_id" {{ old('identification_type', $guest->identification_type) == 'national_id' ? 'selected' : '' }}>National ID</option>
                                        <option value="drivers_license" {{ old('identification_type', $guest->identification_type) == 'drivers_license' ? 'selected' : '' }}>Driver's License</option>
                                        <option value="other" {{ old('identification_type', $guest->identification_type) == 'other' ? 'selected' : '' }}>Other</option>
                                    </select>
                                    @error('identification_type')
                                    <p class="mt-1 text-sm text-danger">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Identification Number *</label>
                                    <input type="text" name="identification_number" required 
                                           class="w-full border border-gray-300 dark:border-gray-700 rounded px-3 py-2 dark:bg-gray-900" 
                                           value="{{ old('identification_number', $guest->identification_number) }}">
                                    @error('identification_number')
                                    <p class="mt-1 text-sm text-danger">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Nationality *</label>
                                    <input type="text" name="nationality" required 
                                           class="w-full border border-gray-300 dark:border-gray-700 rounded px-3 py-2 dark:bg-gray-900" 
                                           value="{{ old('nationality', $guest->nationality)">
                                    @error('nationality')
                                    <p class="mt-1 text-sm text-danger">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Issuing Country</label>
                                    <input type="text" name="issuing_country" 
                                           class="w-full border border-gray-300 dark:border-gray-700 rounded px-3 py-2 dark:bg-gray-900" 
                                           value="{{ old('issuing_country', $guest->issuing_country) }}">
                                </div>
                            </div>
                        </div>

                        <!-- Additional Information -->
                        <div class="mb-8">
                            <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90 mb-4">Additional Information</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Company</label>
                                    <input type="text" name="company" 
                                           class="w-full border border-gray-300 dark:border-gray-700 rounded px-3 py-2 dark:bg-gray-900" 
                                           value="{{ old('company', $guest->company) }}">
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Job Title</label>
                                    <input type="text" name="job_title" 
                                           class="w-full border border-gray-300 dark:border-gray-700 rounded px-3 py-2 dark:bg-gray-900" 
                                           value="{{ old('job_title', $guest->job_title) }}">
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">VIP Status</label>
                                    <div class="flex items-center space-x-4">
                                        <label class="inline-flex items-center">
                                            <input type="radio" name="vip_status" value="1" {{ old('vip_status', $guest->vip_status) == '1' ? 'checked' : '' }} class="text-primary">
                                            <span class="ml-2 text-gray-700 dark:text-gray-300">Yes</span>
                                        </label>
                                        <label class="inline-flex items-center">
                                            <input type="radio" name="vip_status" value="0" {{ old('vip_status', $guest->vip_status) == '0' ? 'checked' : '' }} class="text-primary">
                                            <span class="ml-2 text-gray-700 dark:text-gray-300">No</span>
                                        </label>
                                    </div>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Special Requirements</label>
                                    <textarea name="special_requirements" rows="2" 
                                              class="w-full border border-gray-300 dark:border-gray-700 rounded px-3 py-2 dark:bg-gray-900">{{ old('special_requirements', $guest->special_requirements) }}</textarea>
                                </div>
                            </div>
                        </div>

                        <!-- Profile Image -->
                        <div class="mb-8">
                            <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90 mb-4">Profile Image</h3>
                            <div class="flex items-center space-x-6">
                                @if($guest->profile_image)
                                <div class="flex-shrink-0">
                                    <img src="{{ asset('storage/' . $guest->profile_image) }}" alt="Current Profile" 
                                         class="w-20 h-20 rounded-full border-2 border-gray-300 dark:border-gray-700">
                                </div>
                                @endif
                                <div class="flex-1">
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Update Photo</label>
                                    <input type="file" name="profile_image" accept="image/*" 
                                           class="w-full border border-gray-300 dark:border-gray-700 rounded px-3 py-2 dark:bg-gray-900">
                                    <p class="mt-1 text-xs text-gray-500">Leave empty to keep current image</p>
                                </div>
                            </div>
                        </div>

                        <!-- Emergency Contact -->
                        <div class="mb-8">
                            <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90 mb-4">Emergency Contact</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Emergency Contact Name</label>
                                    <input type="text" name="emergency_contact_name" 
                                           class="w-full border border-gray-300 dark:border-gray-700 rounded px-3 py-2 dark:bg-gray-900" 
                                           value="{{ old('emergency_contact_name', $guest->emergency_contact_name) }}">
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Emergency Contact Phone</label>
                                    <input type="tel" name="emergency_contact_phone" 
                                           class="w-full border border-gray-300 dark:border-gray-700 rounded px-3 py-2 dark:bg-gray-900" 
                                           value="{{ old('emergency_contact_phone', $guest->emergency_contact_phone) }}">
                                </div>

                                <div class="md:col-span-2">
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Emergency Contact Relationship</label>
                                    <input type="text" name="emergency_contact_relationship" 
                                           class="w-full border border-gray-300 dark:border-gray-700 rounded px-3 py-2 dark:bg-gray-900" 
                                           value="{{ old('emergency_contact_relationship', $guest->emergency_contact_relationship) }}">
                                </div>
                            </div>
                        </div>

                        <!-- Submit Button -->
                        <div class="mt-8 flex justify-end space-x-3">
                            <a href="{{ route('guests.show', $guest) }}" class="px-6 py-2 border border-gray-300 dark:border-gray-700 rounded text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800">
                                Cancel
                            </a>
                            <button type="submit" class="px-6 py-2 bg-primary text-white rounded hover:bg-primary/90">
                                Update Guest Profile
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="col-span-12 lg:col-span-4">
            <!-- Guest Summary -->
            <div class="bg-white rounded-lg border border-gray-200 dark:bg-gray-800 dark:border-gray-700 shadow-sm p-6 mb-6">
                <h4 class="text-lg font-semibold text-gray-800 dark:text-white/90 mb-4">Guest Summary</h4>
                <div class="space-y-3">
                    <div class="flex justify-between">
                        <span class="text-gray-600 dark:text-gray-400">Guest ID:</span>
                        <span class="font-medium text-gray-800 dark:text-white">
                            G{{ str_pad($guest->id, 5, '0', STR_PAD_LEFT) }}
                        </span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600 dark:text-gray-400">Status:</span>
                        <span class="px-2 py-1 text-xs rounded-full 
                            @if($guest->status === 'active') bg-success/10 text-success 
                            @elseif($guest->status === 'checked_out') bg-danger/10 text-danger 
                            @else bg-warning/10 text-warning @endif">
                            {{ ucfirst(str_replace('_', ' ', $guest->status)) }}
                        </span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600 dark:text-gray-400">VIP Status:</span>
                        <span class="font-medium text-gray-800 dark:text-white">
                            {{ $guest->vip_status ? 'Yes' : 'No' }}
                        </span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600 dark:text-gray-400">Created:</span>
                        <span class="text-gray-800 dark:text-white">{{ $guest->created_at->format('M d, Y') }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600 dark:text-gray-400">Last Updated:</span>
                        <span class="text-gray-800 dark:text-white">{{ $guest->updated_at->diffForHumans() }}</span>
                    </div>
                </div>
            </div>

            <!-- Stay History -->
            @if($guest->stays->count() > 0)
            <div class="bg-white rounded-lg border border-gray-200 dark:bg-gray-800 dark:border-gray-700 shadow-sm p-6 mb-6">
                <h4 class="text-lg font-semibold text-gray-800 dark:text-white/90 mb-4">Stay History</h4>
                <div class="space-y-3">
                    <div class="flex justify-between">
                        <span class="text-gray-600 dark:text-gray-400">Total Stays:</span>
                        <span class="font-medium text-gray-800 dark:text-white">{{ $guest->stays->count() }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600 dark:text-gray-400">Total Nights:</span>
                        <span class="font-medium text-gray-800 dark:text-white">{{ $guest->total_nights ?? 0 }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600 dark:text-gray-400">Last Stay:</span>
                        <span class="text-gray-800 dark:text-white">
                            {{ $guest->last_stay ? $guest->last_stay->format('M d, Y') : 'Never' }}
                        </span>
                    </div>
                    <div class="pt-3 border-t border-gray-200 dark:border-gray-700">
                        <a href="{{ route('guests.show', $guest) }}#stays" class="text-primary hover:text-primary/80 text-sm font-medium">
                            View All Stays →
                        </a>
                    </div>
                </div>
            </div>
            @endif

            <!-- Update Notes -->
            <div class="bg-white rounded-lg border border-gray-200 dark:bg-gray-800 dark:border-gray-700 shadow-sm p-6">
                <h4 class="text-lg font-semibold text-gray-800 dark:text-white/90 mb-4">Update Notes</h4>
                <div class="space-y-3 text-sm text-gray-600 dark:text-gray-400">
                    <div class="flex items-start">
                        <i class="fas fa-history text-primary mt-1 mr-2"></i>
                        <span>All changes are logged in the system audit trail</span>
                    </div>
                    <div class="flex items-start">
                        <i class="fas fa-exclamation-circle text-warning mt-1 mr-2"></i>
                        <span>Changes to identification require verification</span>
                    </div>
                    <div class="flex items-start">
                        <i class="fas fa-user-check text-success mt-1 mr-2"></i>
                        <span>Your name will be recorded as the editor</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection