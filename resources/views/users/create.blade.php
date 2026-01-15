@extends('layouts.app')

@section('content')
    <div class="p-4 mx-auto max-w-screen-2xl md:p-6">
        <!-- Header -->
        <div class="mb-6">
            <h2 class="text-2xl font-bold text-gray-800 dark:text-white/90">Create New User</h2>
            <p class="text-gray-600 dark:text-gray-400">Add a new user to the system</p>
        </div>

        <!-- Form -->
        <div class="bg-white rounded-lg border border-gray-200 dark:border-gray-800 dark:bg-white/[0.03] p-6">
            <form action="{{ route('users.store') }}" method="POST">
                @csrf

                <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                    <!-- Basic Information -->
                    <div class="space-y-4">
                        <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90 mb-4">Basic Information</h3>
                        
                        <div>
                            <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Full Name *</label>
                            <input type="text" id="name" name="name" value="{{ old('name') }}" required
                                   class="w-full px-3 py-2 border border-gray-300 dark:border-gray-700 rounded bg-white dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary focus:border-primary">
                            @error('name')
                                <p class="mt-1 text-sm text-danger">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Email Address *</label>
                            <input type="email" id="email" name="email" value="{{ old('email') }}" required
                                   class="w-full px-3 py-2 border border-gray-300 dark:border-gray-700 rounded bg-white dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary focus:border-primary">
                            @error('email')
                                <p class="mt-1 text-sm text-danger">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="phone" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Phone Number</label>
                            <input type="tel" id="phone" name="phone" value="{{ old('phone') }}"
                                   class="w-full px-3 py-2 border border-gray-300 dark:border-gray-700 rounded bg-white dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary focus:border-primary">
                            @error('phone')
                                <p class="mt-1 text-sm text-danger">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="password" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Password *</label>
                            <input type="password" id="password" name="password" required
                                   class="w-full px-3 py-2 border border-gray-300 dark:border-gray-700 rounded bg-white dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary focus:border-primary">
                            @error('password')
                                <p class="mt-1 text-sm text-danger">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="password_confirmation" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Confirm Password *</label>
                            <input type="password" id="password_confirmation" name="password_confirmation" required
                                   class="w-full px-3 py-2 border border-gray-300 dark:border-gray-700 rounded bg-white dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary focus:border-primary">
                        </div>
                    </div>

                    <!-- Role & Status -->
                    <div class="space-y-4">
                        <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90 mb-4">Role & Status</h3>

                        <div>
                            <label for="role" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Role *</label>
                            <select id="role" name="role" required
                                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-700 rounded bg-white dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary focus:border-primary">
                                <option value="">Select a role</option>
                                @foreach($roles as $role)
                                    <option value="{{ $role->name }}" {{ old('role') == $role->name ? 'selected' : '' }}>
                                        {{ ucfirst($role->name) }}
                                    </option>
                                @endforeach
                            </select>
                            @error('role')
                                <p class="mt-1 text-sm text-danger">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Department (for staff) -->
                        <div id="department-field" class="hidden">
                            <label for="department_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Department</label>
                            <select id="department_id" name="department_id"
                                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-700 rounded bg-white dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary focus:border-primary">
                                <option value="">Select department</option>
                                @foreach($departments as $department)
                                    <option value="{{ $department->id }}" {{ old('department_id') == $department->id ? 'selected' : '' }}>
                                        {{ $department->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Staff-specific fields -->
                        <div id="staff-fields" class="space-y-4 hidden">
                            <div>
                                <label for="salary" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Salary (KSH)</label>
                                <input type="number" id="salary" name="salary" value="{{ old('salary', 0) }}" min="0" step="0.01"
                                       class="w-full px-3 py-2 border border-gray-300 dark:border-gray-700 rounded bg-white dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary focus:border-primary">
                            </div>

                            <div>
                                <label for="hire_date" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Hire Date</label>
                                <input type="date" id="hire_date" name="hire_date" value="{{ old('hire_date', date('Y-m-d')) }}"
                                       class="w-full px-3 py-2 border border-gray-300 dark:border-gray-700 rounded bg-white dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary focus:border-primary">
                            </div>

                            <div>
                                <label for="employment_status" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Employment Status</label>
                                <select id="employment_status" name="employment_status"
                                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-700 rounded bg-white dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary focus:border-primary">
                                    <option value="probation" {{ old('employment_status') == 'probation' ? 'selected' : '' }}>Probation</option>
                                    <option value="permanent" {{ old('employment_status') == 'permanent' ? 'selected' : '' }}>Permanent</option>
                                    <option value="contract" {{ old('employment_status') == 'contract' ? 'selected' : '' }}>Contract</option>
                                    <option value="internship" {{ old('employment_status') == 'internship' ? 'selected' : '' }}>Internship</option>
                                </select>
                            </div>
                        </div>

                        <!-- Guest-specific fields -->
                        <div id="guest-fields" class="space-y-4 hidden">
                            <div>
                                <label for="id_type" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">ID Type</label>
                                <select id="id_type" name="id_type"
                                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-700 rounded bg-white dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary focus:border-primary">
                                    <option value="passport" {{ old('id_type') == 'passport' ? 'selected' : '' }}>Passport</option>
                                    <option value="national_id" {{ old('id_type') == 'national_id' ? 'selected' : '' }}>National ID</option>
                                    <option value="driving_license" {{ old('id_type') == 'driving_license' ? 'selected' : '' }}>Driving License</option>
                                    <option value="other" {{ old('id_type') == 'other' ? 'selected' : '' }}>Other</option>
                                </select>
                            </div>

                            <div>
                                <label for="id_number" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">ID Number</label>
                                <input type="text" id="id_number" name="id_number" value="{{ old('id_number') }}"
                                       class="w-full px-3 py-2 border border-gray-300 dark:border-gray-700 rounded bg-white dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary focus:border-primary">
                            </div>

                            <div>
                                <label for="nationality" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Nationality</label>
                                <input type="text" id="nationality" name="nationality" value="{{ old('nationality') }}"
                                       class="w-full px-3 py-2 border border-gray-300 dark:border-gray-700 rounded bg-white dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary focus:border-primary">
                            </div>
                        </div>

                        <div>
                            <label for="status" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Status *</label>
                            <select id="status" name="status" required
                                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-700 rounded bg-white dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary focus:border-primary">
                                <option value="active" {{ old('status') == 'active' ? 'selected' : '' }}>Active</option>
                                <option value="inactive" {{ old('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                            </select>
                            @error('status')
                                <p class="mt-1 text-sm text-danger">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Form Actions -->
                <div class="flex items-center justify-end space-x-3 pt-6 mt-6 border-t border-gray-200 dark:border-gray-800">
                    <a href="{{ route('users.index') }}" class="px-4 py-2 border border-gray-300 dark:border-gray-700 rounded text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800">
                        Cancel
                    </a>
                    <button type="submit" class="px-4 py-2 bg-primary text-white rounded hover:bg-primary/90">
                        Create User
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const roleSelect = document.getElementById('role');
            const departmentField = document.getElementById('department-field');
            const staffFields = document.getElementById('staff-fields');
            const guestFields = document.getElementById('guest-fields');

            function toggleFields() {
                const role = roleSelect.value;
                
                // Hide all fields first
                departmentField.classList.add('hidden');
                staffFields.classList.add('hidden');
                guestFields.classList.add('hidden');

                // Show fields based on role
                if (role === 'guest') {
                    guestFields.classList.remove('hidden');
                } else if (['staff', 'receptionist', 'housekeeping', 'kitchen', 'procurement', 'hr', 'admin', 'manager'].includes(role)) {
                    departmentField.classList.remove('hidden');
                    staffFields.classList.remove('hidden');
                }
            }

            roleSelect.addEventListener('change', toggleFields);
            
            // Initial toggle
            toggleFields();
        });
    </script>
@endsection