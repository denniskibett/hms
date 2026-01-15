@extends('layouts.app')

@section('content')
<div class="p-4 mx-auto max-w-screen-2xl md:p-6">
    <!-- Header -->
    <div class="mb-6">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-2xl font-bold text-gray-800 dark:text-white/90">Add New Guest</h2>
                <p class="text-gray-600 dark:text-gray-400">Register a new guest in the system</p>
            </div>
            <div>
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
                    <form action="{{ route('guests.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        
                        <!-- Personal Information -->
                        <div class="mb-8">
                            <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90 mb-4">Personal Information</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">First Name *</label>
                                    <input type="text" name="first_name" required 
                                           class="w-full border border-gray-300 dark:border-gray-700 rounded px-3 py-2 dark:bg-gray-900" 
                                           value="{{ old('first_name') }}">
                                    @error('first_name')
                                    <p class="mt-1 text-sm text-danger">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Last Name *</label>
                                    <input type="text" name="last_name" required 
                                           class="w-full border border-gray-300 dark:border-gray-700 rounded px-3 py-2 dark:bg-gray-900" 
                                           value="{{ old('last_name') }}">
                                    @error('last_name')
                                    <p class="mt-1 text-sm text-danger">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Date of Birth</label>
                                    <input type="date" name="date_of_birth" 
                                           class="w-full border border-gray-300 dark:border-gray-700 rounded px-3 py-2 dark:bg-gray-900" 
                                           value="{{ old('date_of_birth') }}">
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Gender</label>
                                    <select name="gender" class="w-full border border-gray-300 dark:border-gray-700 rounded px-3 py-2 dark:bg-gray-900">
                                        <option value="">Select Gender</option>
                                        <option value="male" {{ old('gender') == 'male' ? 'selected' : '' }}>Male</option>
                                        <option value="female" {{ old('gender') == 'female' ? 'selected' : '' }}>Female</option>
                                        <option value="other" {{ old('gender') == 'other' ? 'selected' : '' }}>Other</option>
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
                                           value="{{ old('email') }}">
                                    @error('email')
                                    <p class="mt-1 text-sm text-danger">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Phone *</label>
                                    <input type="tel" name="phone" required 
                                           class="w-full border border-gray-300 dark:border-gray-700 rounded px-3 py-2 dark:bg-gray-900" 
                                           value="{{ old('phone') }}">
                                    @error('phone')
                                    <p class="mt-1 text-sm text-danger">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div class="md:col-span-2">
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Address</label>
                                    <textarea name="address" rows="2" 
                                              class="w-full border border-gray-300 dark:border-gray-700 rounded px-3 py-2 dark:bg-gray-900">{{ old('address') }}</textarea>
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
                                        <option value="passport" {{ old('identification_type') == 'passport' ? 'selected' : '' }}>Passport</option>
                                        <option value="national_id" {{ old('identification_type') == 'national_id' ? 'selected' : '' }}>National ID</option>
                                        <option value="drivers_license" {{ old('identification_type') == 'drivers_license' ? 'selected' : '' }}>Driver's License</option>
                                        <option value="other" {{ old('identification_type') == 'other' ? 'selected' : '' }}>Other</option>
                                    </select>
                                    @error('identification_type')
                                    <p class="mt-1 text-sm text-danger">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Identification Number *</label>
                                    <input type="text" name="identification_number" required 
                                           class="w-full border border-gray-300 dark:border-gray-700 rounded px-3 py-2 dark:bg-gray-900" 
                                           value="{{ old('identification_number') }}">
                                    @error('identification_number')
                                    <p class="mt-1 text-sm text-danger">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Nationality *</label>
                                    <input type="text" name="nationality" required 
                                           class="w-full border border-gray-300 dark:border-gray-700 rounded px-3 py-2 dark:bg-gray-900" 
                                           value="{{ old('nationality') }}" placeholder="e.g., USA, UK, India">
                                    @error('nationality')
                                    <p class="mt-1 text-sm text-danger">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Issuing Country</label>
                                    <input type="text" name="issuing_country" 
                                           class="w-full border border-gray-300 dark:border-gray-700 rounded px-3 py-2 dark:bg-gray-900" 
                                           value="{{ old('issuing_country') }}">
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
                                           value="{{ old('company') }}">
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Job Title</label>
                                    <input type="text" name="job_title" 
                                           class="w-full border border-gray-300 dark:border-gray-700 rounded px-3 py-2 dark:bg-gray-900" 
                                           value="{{ old('job_title') }}">
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">VIP Status</label>
                                    <div class="flex items-center space-x-4">
                                        <label class="inline-flex items-center">
                                            <input type="radio" name="vip_status" value="1" {{ old('vip_status') == '1' ? 'checked' : '' }} class="text-primary">
                                            <span class="ml-2 text-gray-700 dark:text-gray-300">Yes</span>
                                        </label>
                                        <label class="inline-flex items-center">
                                            <input type="radio" name="vip_status" value="0" {{ old('vip_status') == '0' ? 'checked' : '' }} class="text-primary">
                                            <span class="ml-2 text-gray-700 dark:text-gray-300">No</span>
                                        </label>
                                    </div>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Special Requirements</label>
                                    <textarea name="special_requirements" rows="2" 
                                              class="w-full border border-gray-300 dark:border-gray-700 rounded px-3 py-2 dark:bg-gray-900">{{ old('special_requirements') }}</textarea>
                                </div>
                            </div>
                        </div>

                        <!-- Profile Image -->
                        <div class="mb-8">
                            <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90 mb-4">Profile Image</h3>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Upload Photo</label>
                                <input type="file" name="profile_image" accept="image/*" 
                                       class="w-full border border-gray-300 dark:border-gray-700 rounded px-3 py-2 dark:bg-gray-900">
                                <p class="mt-1 text-xs text-gray-500">Max file size: 2MB. Allowed: jpg, jpeg, png</p>
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
                                           value="{{ old('emergency_contact_name') }}">
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Emergency Contact Phone</label>
                                    <input type="tel" name="emergency_contact_phone" 
                                           class="w-full border border-gray-300 dark:border-gray-700 rounded px-3 py-2 dark:bg-gray-900" 
                                           value="{{ old('emergency_contact_phone') }}">
                                </div>

                                <div class="md:col-span-2">
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Emergency Contact Relationship</label>
                                    <input type="text" name="emergency_contact_relationship" 
                                           class="w-full border border-gray-300 dark:border-gray-700 rounded px-3 py-2 dark:bg-gray-900" 
                                           value="{{ old('emergency_contact_relationship') }}" placeholder="e.g., Spouse, Parent, Sibling">
                                </div>
                            </div>
                        </div>

                        <!-- Submit Button -->
                        <div class="mt-8 flex justify-end">
                            <button type="submit" class="px-6 py-2 bg-primary text-white rounded hover:bg-primary/90">
                                Create Guest Profile
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="col-span-12 lg:col-span-4">
            <!-- Guidelines -->
            <div class="bg-white rounded-lg border border-gray-200 dark:bg-gray-800 dark:border-gray-700 shadow-sm p-6 mb-6">
                <h4 class="text-lg font-semibold text-gray-800 dark:text-white/90 mb-4">Registration Guidelines</h4>
                <ul class="space-y-3 text-sm text-gray-600 dark:text-gray-400">
                    <li class="flex items-start">
                        <i class="fas fa-asterisk text-primary mt-1 mr-2 text-xs"></i>
                        <span>Fields marked with * are mandatory</span>
                    </li>
                    <li class="flex items-start">
                        <i class="fas fa-passport text-warning mt-1 mr-2"></i>
                        <span>Valid identification is required for all guests</span>
                    </li>
                    <li class="flex items-start">
                        <i class="fas fa-phone-alt text-success mt-1 mr-2"></i>
                        <span>Ensure contact information is accurate and current</span>
                    </li>
                    <li class="flex items-start">
                        <i class="fas fa-user-shield text-danger mt-1 mr-2"></i>
                        <span>Guest information is confidential and protected</span>
                    </li>
                    <li class="flex items-start">
                        <i class="fas fa-vip text-yellow-500 mt-1 mr-2"></i>
                        <span>Mark VIP guests for special attention and services</span>
                    </li>
                </ul>
            </div>

            <!-- Quick Tips -->
            <div class="bg-white rounded-lg border border-gray-200 dark:bg-gray-800 dark:border-gray-700 shadow-sm p-6">
                <h4 class="text-lg font-semibold text-gray-800 dark:text-white/90 mb-4">Quick Tips</h4>
                <div class="space-y-3 text-sm text-gray-600 dark:text-gray-400">
                    <div class="flex items-start">
                        <i class="fas fa-lightbulb text-primary mt-1 mr-2"></i>
                        <span>Capture clear identification documents for verification</span>
                    </div>
                    <div class="flex items-start">
                        <i class="fas fa-lightbulb text-primary mt-1 mr-2"></i>
                        <span>Note any special requirements for better service</span>
                    </div>
                    <div class="flex items-start">
                        <i class="fas fa-lightbulb text-primary mt-1 mr-2"></i>
                        <span>Emergency contact is crucial for guest safety</span>
                    </div>
                    <div class="flex items-start">
                        <i class="fas fa-lightbulb text-primary mt-1 mr-2"></i>
                        <span>VIP guests may require additional amenities</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection