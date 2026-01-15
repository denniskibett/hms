@extends('layouts.app')

@section('content')
<div class="p-4 mx-auto max-w-screen-2xl md:p-6">
    <!-- Header -->
    <div class="mb-6">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-2xl font-bold text-gray-800 dark:text-white/90">Edit Employee Profile</h2>
                <p class="text-gray-600 dark:text-gray-400">Update employee information - {{ $employee->full_name }}</p>
            </div>
            <div class="flex items-center space-x-2">
                <a href="{{ route('hr.employees.show', $employee) }}" class="px-4 py-2 border border-gray-300 dark:border-gray-700 rounded text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800">
                    <i class="fas fa-eye mr-1"></i> View Profile
                </a>
                <a href="{{ route('hr.employees.index') }}" class="px-4 py-2 border border-gray-300 dark:border-gray-700 rounded text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800">
                    <i class="fas fa-arrow-left mr-1"></i> Back to List
                </a>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-12 gap-6">
        <!-- Form -->
        <div class="col-span-12 lg:col-span-8">
            <div class="bg-white rounded-lg border border-gray-200 dark:bg-gray-800 dark:border-gray-700 shadow-sm">
                <div class="p-6">
                    <form action="{{ route('hr.employees.update', $employee) }}" method="POST" enctype="multipart/form-data">
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
                                           value="{{ old('first_name', $employee->first_name) }}">
                                    @error('first_name')
                                    <p class="mt-1 text-sm text-danger">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Last Name *</label>
                                    <input type="text" name="last_name" required 
                                           class="w-full border border-gray-300 dark:border-gray-700 rounded px-3 py-2 dark:bg-gray-900" 
                                           value="{{ old('last_name', $employee->last_name) }}">
                                    @error('last_name')
                                    <p class="mt-1 text-sm text-danger">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Date of Birth</label>
                                    <input type="date" name="date_of_birth" 
                                           class="w-full border border-gray-300 dark:border-gray-700 rounded px-3 py-2 dark:bg-gray-900" 
                                           value="{{ old('date_of_birth', $employee->date_of_birth ? $employee->date_of_birth->format('Y-m-d') : '') }}">
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Gender</label>
                                    <select name="gender" class="w-full border border-gray-300 dark:border-gray-700 rounded px-3 py-2 dark:bg-gray-900">
                                        <option value="">Select Gender</option>
                                        <option value="male" {{ old('gender', $employee->gender) == 'male' ? 'selected' : '' }}>Male</option>
                                        <option value="female" {{ old('gender', $employee->gender) == 'female' ? 'selected' : '' }}>Female</option>
                                        <option value="other" {{ old('gender', $employee->gender) == 'other' ? 'selected' : '' }}>Other</option>
                                    </select>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Nationality</label>
                                    <input type="text" name="nationality" 
                                           class="w-full border border-gray-300 dark:border-gray-700 rounded px-3 py-2 dark:bg-gray-900" 
                                           value="{{ old('nationality', $employee->nationality) }}">
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Marital Status</label>
                                    <select name="marital_status" class="w-full border border-gray-300 dark:border-gray-700 rounded px-3 py-2 dark:bg-gray-900">
                                        <option value="">Select Status</option>
                                        <option value="single" {{ old('marital_status', $employee->marital_status) == 'single' ? 'selected' : '' }}>Single</option>
                                        <option value="married" {{ old('marital_status', $employee->marital_status) == 'married' ? 'selected' : '' }}>Married</option>
                                        <option value="divorced" {{ old('marital_status', $employee->marital_status) == 'divorced' ? 'selected' : '' }}>Divorced</option>
                                        <option value="widowed" {{ old('marital_status', $employee->marital_status) == 'widowed' ? 'selected' : '' }}>Widowed</option>
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
                                           value="{{ old('email', $employee->email) }}">
                                    @error('email')
                                    <p class="mt-1 text-sm text-danger">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Phone *</label>
                                    <input type="tel" name="phone" required 
                                           class="w-full border border-gray-300 dark:border-gray-700 rounded px-3 py-2 dark:bg-gray-900" 
                                           value="{{ old('phone', $employee->phone) }}">
                                    @error('phone')
                                    <p class="mt-1 text-sm text-danger">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div class="md:col-span-2">
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Address *</label>
                                    <textarea name="address" rows="2" required 
                                              class="w-full border border-gray-300 dark:border-gray-700 rounded px-3 py-2 dark:bg-gray-900">{{ old('address', $employee->address) }}</textarea>
                                    @error('address')
                                    <p class="mt-1 text-sm text-danger">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Emergency Contact Name *</label>
                                    <input type="text" name="emergency_contact_name" required 
                                           class="w-full border border-gray-300 dark:border-gray-700 rounded px-3 py-2 dark:bg-gray-900" 
                                           value="{{ old('emergency_contact_name', $employee->emergency_contact_name) }}">
                                    @error('emergency_contact_name')
                                    <p class="mt-1 text-sm text-danger">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Emergency Contact Phone *</label>
                                    <input type="tel" name="emergency_contact_phone" required 
                                           class="w-full border border-gray-300 dark:border-gray-700 rounded px-3 py-2 dark:bg-gray-900" 
                                           value="{{ old('emergency_contact_phone', $employee->emergency_contact_phone) }}">
                                    @error('emergency_contact_phone')
                                    <p class="mt-1 text-sm text-danger">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Employment Details -->
                        <div class="mb-8">
                            <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90 mb-4">Employment Details</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Employee ID</label>
                                    <input type="text" readonly 
                                           class="w-full border border-gray-300 dark:border-gray-700 rounded px-3 py-2 dark:bg-gray-900 bg-gray-50" 
                                           value="{{ $employee->employee_id }}">
                                    <input type="hidden" name="employee_id" value="{{ $employee->employee_id }}">
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Department *</label>
                                    <select name="department_id" required class="w-full border border-gray-300 dark:border-gray-700 rounded px-3 py-2 dark:bg-gray-900">
                                        <option value="">Select Department</option>
                                        @foreach($departments as $department)
                                        <option value="{{ $department->id }}" {{ old('department_id', $employee->department_id) == $department->id ? 'selected' : '' }}>
                                            {{ $department->name }}
                                        </option>
                                        @endforeach
                                    </select>
                                    @error('department_id')
                                    <p class="mt-1 text-sm text-danger">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Position *</label>
                                    <input type="text" name="position" required 
                                           class="w-full border border-gray-300 dark:border-gray-700 rounded px-3 py-2 dark:bg-gray-900" 
                                           value="{{ old('position', $employee->position) }}">
                                    @error('position')
                                    <p class="mt-1 text-sm text-danger">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Employment Type *</label>
                                    <select name="employment_type" required class="w-full border border-gray-300 dark:border-gray-700 rounded px-3 py-2 dark:bg-gray-900">
                                        <option value="">Select Type</option>
                                        <option value="full_time" {{ old('employment_type', $employee->employment_type) == 'full_time' ? 'selected' : '' }}>Full-time</option>
                                        <option value="part_time" {{ old('employment_type', $employee->employment_type) == 'part_time' ? 'selected' : '' }}>Part-time</option>
                                        <option value="contract" {{ old('employment_type', $employee->employment_type) == 'contract' ? 'selected' : '' }}>Contract</option>
                                        <option value="temporary" {{ old('employment_type', $employee->employment_type) == 'temporary' ? 'selected' : '' }}>Temporary</option>
                                        <option value="intern" {{ old('employment_type', $employee->employment_type) == 'intern' ? 'selected' : '' }}>Intern</option>
                                    </select>
                                    @error('employment_type')
                                    <p class="mt-1 text-sm text-danger">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Hire Date *</label>
                                    <input type="date" name="hire_date" required 
                                           class="w-full border border-gray-300 dark:border-gray-700 rounded px-3 py-2 dark:bg-gray-900" 
                                           value="{{ old('hire_date', $employee->hire_date->format('Y-m-d')) }}">
                                    @error('hire_date')
                                    <p class="mt-1 text-sm text-danger">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Work Schedule</label>
                                    <select name="work_schedule" class="w-full border border-gray-300 dark:border-gray-700 rounded px-3 py-2 dark:bg-gray-900">
                                        <option value="">Select Schedule</option>
                                        <option value="9-5" {{ old('work_schedule', $employee->work_schedule) == '9-5' ? 'selected' : '' }}>9 AM - 5 PM</option>
                                        <option value="shift_a" {{ old('work_schedule', $employee->work_schedule) == 'shift_a' ? 'selected' : '' }}>Shift A (Morning)</option>
                                        <option value="shift_b" {{ old('work_schedule', $employee->work_schedule) == 'shift_b' ? 'selected' : '' }}>Shift B (Evening)</option>
                                        <option value="shift_c" {{ old('work_schedule', $employee->work_schedule) == 'shift_c' ? 'selected' : '' }}>Shift C (Night)</option>
                                        <option value="flexible" {{ old('work_schedule', $employee->work_schedule) == 'flexible' ? 'selected' : '' }}>Flexible</option>
                                    </select>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Salary *</label>
                                    <div class="relative">
                                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                            <span class="text-gray-500">$</span>
                                        </div>
                                        <input type="number" name="salary" step="0.01" required 
                                               class="pl-8 w-full border border-gray-300 dark:border-gray-700 rounded px-3 py-2 dark:bg-gray-900" 
                                               value="{{ old('salary', $employee->salary) }}">
                                    </div>
                                    @error('salary')
                                    <p class="mt-1 text-sm text-danger">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Salary Type</label>
                                    <select name="salary_type" class="w-full border border-gray-300 dark:border-gray-700 rounded px-3 py-2 dark:bg-gray-900">
                                        <option value="monthly" {{ old('salary_type', $employee->salary_type) == 'monthly' ? 'selected' : '' }}>Monthly</option>
                                        <option value="hourly" {{ old('salary_type', $employee->salary_type) == 'hourly' ? 'selected' : '' }}>Hourly</option>
                                        <option value="weekly" {{ old('salary_type', $employee->salary_type) == 'weekly' ? 'selected' : '' }}>Weekly</option>
                                        <option value="bi_weekly" {{ old('salary_type', $employee->salary_type) == 'bi_weekly' ? 'selected' : '' }}>Bi-weekly</option>
                                    </select>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Status *</label>
                                    <select name="status" required class="w-full border border-gray-300 dark:border-gray-700 rounded px-3 py-2 dark:bg-gray-900">
                                        <option value="active" {{ old('status', $employee->status) == 'active' ? 'selected' : '' }}>Active</option>
                                        <option value="on_leave" {{ old('status', $employee->status) == 'on_leave' ? 'selected' : '' }}>On Leave</option>
                                        <option value="inactive" {{ old('status', $employee->status) == 'inactive' ? 'selected' : '' }}>Inactive</option>
                                        <option value="terminated" {{ old('status', $employee->status) == 'terminated' ? 'selected' : '' }}>Terminated</option>
                                    </select>
                                    @error('status')
                                    <p class="mt-1 text-sm text-danger">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Bank Information -->
                        <div class="mb-8">
                            <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90 mb-4">Bank Information</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Bank Name</label>
                                    <input type="text" name="bank_name" 
                                           class="w-full border border-gray-300 dark:border-gray-700 rounded px-3 py-2 dark:bg-gray-900" 
                                           value="{{ old('bank_name', $employee->bank_name) }}">
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Account Number</label>
                                    <input type="text" name="bank_account_number" 
                                           class="w-full border border-gray-300 dark:border-gray-700 rounded px-3 py-2 dark:bg-gray-900" 
                                           value="{{ old('bank_account_number', $employee->bank_account_number) }}">
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Account Holder Name</label>
                                    <input type="text" name="bank_account_holder" 
                                           class="w-full border border-gray-300 dark:border-gray-700 rounded px-3 py-2 dark:bg-gray-900" 
                                           value="{{ old('bank_account_holder', $employee->bank_account_holder) }}">
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Bank Branch</label>
                                    <input type="text" name="bank_branch" 
                                           class="w-full border border-gray-300 dark:border-gray-700 rounded px-3 py-2 dark:bg-gray-900" 
                                           value="{{ old('bank_branch', $employee->bank_branch) }}">
                                </div>
                            </div>
                        </div>

                        <!-- Account Information -->
                        <div class="mb-8">
                            <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90 mb-4">Account Information</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">System Access</label>
                                    <div class="flex items-center space-x-4">
                                        <label class="inline-flex items-center">
                                            <input type="radio" name="system_access" value="1" {{ old('system_access', $employee->system_access) == '1' ? 'checked' : '' }} class="text-primary">
                                            <span class="ml-2 text-gray-700 dark:text-gray-300">Enabled</span>
                                        </label>
                                        <label class="inline-flex items-center">
                                            <input type="radio" name="system_access" value="0" {{ old('system_access', $employee->system_access) == '0' ? 'checked' : '' }} class="text-primary">
                                            <span class="ml-2 text-gray-700 dark:text-gray-300">Disabled</span>
                                        </label>
                                    </div>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">User Role</label>
                                    <select name="user_role" class="w-full border border-gray-300 dark:border-gray-700 rounded px-3 py-2 dark:bg-gray-900">
                                        <option value="">No System Access</option>
                                        @foreach($roles as $role)
                                        <option value="{{ $role->name }}" {{ old('user_role', $employee->user_role) == $role->name ? 'selected' : '' }}>
                                            {{ ucfirst($role->name) }}
                                        </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- Profile Image -->
                        <div class="mb-8">
                            <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90 mb-4">Profile Image</h3>
                            <div class="flex items-center space-x-6">
                                @if($employee->profile_image)
                                <div class="flex-shrink-0">
                                    <img src="{{ asset('storage/' . $employee->profile_image) }}" alt="Current Profile" 
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

                        <!-- Additional Information -->
                        <div class="mb-8">
                            <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90 mb-4">Additional Information</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div class="md:col-span-2">
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Notes</label>
                                    <textarea name="notes" rows="3" 
                                              class="w-full border border-gray-300 dark:border-gray-700 rounded px-3 py-2 dark:bg-gray-900">{{ old('notes', $employee->notes) }}</textarea>
                                </div>
                            </div>
                        </div>

                        <!-- Submit Button -->
                        <div class="mt-8 flex justify-end space-x-3">
                            <a href="{{ route('hr.employees.show', $employee) }}" class="px-6 py-2 border border-gray-300 dark:border-gray-700 rounded text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800">
                                Cancel
                            </a>
                            <button type="submit" class="px-6 py-2 bg-primary text-white rounded hover:bg-primary/90">
                                Update Employee Profile
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="col-span-12 lg:col-span-4">
            <!-- Employee Summary -->
            <div class="bg-white rounded-lg border border-gray-200 dark:bg-gray-800 dark:border-gray-700 shadow-sm p-6 mb-6">
                <h4 class="text-lg font-semibold text-gray-800 dark:text-white/90 mb-4">Employee Summary</h4>
                <div class="space-y-3">
                    <div class="flex justify-between">
                        <span class="text-gray-600 dark:text-gray-400">Employee ID:</span>
                        <span class="font-medium text-gray-800 dark:text-white">
                            {{ $employee->employee_id }}
                        </span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600 dark:text-gray-400">Current Status:</span>
                        <span class="px-2 py-1 text-xs rounded-full 
                            @if($employee->status === 'active') bg-success/10 text-success 
                            @elseif($employee->status === 'on_leave') bg-warning/10 text-warning 
                            @elseif($employee->status === 'inactive') bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300
                            @else bg-danger/10 text-danger @endif">
                            {{ ucfirst(str_replace('_', ' ', $employee->status)) }}
                        </span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600 dark:text-gray-400">Department:</span>
                        <span class="font-medium text-gray-800 dark:text-white">
                            {{ $employee->department->name ?? 'N/A' }}
                        </span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600 dark:text-gray-400">Position:</span>
                        <span class="font-medium text-gray-800 dark:text-white">{{ $employee->position }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600 dark:text-gray-400">Hire Date:</span>
                        <span class="text-gray-800 dark:text-white">{{ $employee->hire_date->format('M d, Y') }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600 dark:text-gray-400">Tenure:</span>
                        <span class="text-gray-800 dark:text-white">{{ $employee->tenure }} months</span>
                    </div>
                </div>
            </div>

            <!-- Update Notes -->
            <div class="bg-white rounded-lg border border-gray-200 dark:bg-gray-800 dark:border-gray-700 shadow-sm p-6 mb-6">
                <h4 class="text-lg font-semibold text-gray-800 dark:text-white/90 mb-4">Update Notes</h4>
                <div class="space-y-3 text-sm text-gray-600 dark:text-gray-400">
                    <div class="flex items-start">
                        <i class="fas fa-history text-primary mt-1 mr-2"></i>
                        <span>All changes are logged in the system audit trail</span>
                    </div>
                    <div class="flex items-start">
                        <i class="fas fa-exclamation-circle text-warning mt-1 mr-2"></i>
                        <span>Changing salary or bank details requires approval</span>
                    </div>
                    <div class="flex items-start">
                        <i class="fas fa-user-check text-success mt-1 mr-2"></i>
                        <span>Your name will be recorded as the editor</span>
                    </div>
                    <div class="flex items-start">
                        <i class="fas fa-lock text-danger mt-1 mr-2"></i>
                        <span>Confidential information is protected</span>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="bg-white rounded-lg border border-gray-200 dark:bg-gray-800 dark:border-gray-700 shadow-sm p-6">
                <h4 class="text-lg font-semibold text-gray-800 dark:text-white/90 mb-4">Quick Actions</h4>
                <div class="space-y-3">
                    <a href="{{ route('hr.attendance.index', ['employee' => $employee->id]) }}" class="flex items-center p-3 bg-blue-50 dark:bg-blue-900/20 text-blue-600 dark:text-blue-400 rounded hover:bg-blue-100 dark:hover:bg-blue-900/30">
                        <i class="fas fa-clock mr-2"></i>
                        <span>View Attendance</span>
                    </a>
                    <a href="{{ route('hr.leave.index', ['employee' => $employee->id]) }}" class="flex items-center p-3 bg-green-50 dark:bg-green-900/20 text-green-600 dark:text-green-400 rounded hover:bg-green-100 dark:hover:bg-green-900/30">
                        <i class="fas fa-calendar-alt mr-2"></i>
                        <span>Manage Leave</span>
                    </a>
                    <a href="{{ route('hr.payroll.index', ['employee' => $employee->id]) }}" class="flex items-center p-3 bg-yellow-50 dark:bg-yellow-900/20 text-yellow-600 dark:text-yellow-400 rounded hover:bg-yellow-100 dark:hover:bg-yellow-900/30">
                        <i class="fas fa-money-check-alt mr-2"></i>
                        <span>View Payroll</span>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection