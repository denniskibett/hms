@extends('layouts.app')

@section('content')
<div class="p-4 mx-auto max-w-screen-2xl md:p-6">
    <!-- Header -->
    <div class="mb-6">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-2xl font-bold text-gray-800 dark:text-white/90">Employee Profile</h2>
                <p class="text-gray-600 dark:text-gray-400">View employee information and records</p>
            </div>
            <div class="flex items-center space-x-2">
                @can('update', $employee)
                <a href="{{ route('hr.employees.edit', $employee) }}" class="px-4 py-2 bg-warning text-white rounded hover:bg-warning/90">
                    <i class="fas fa-edit mr-1"></i> Edit Profile
                </a>
                @endcan
                <a href="{{ route('hr.employees.index') }}" class="px-4 py-2 border border-gray-300 dark:border-gray-700 rounded text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800">
                    <i class="fas fa-arrow-left mr-1"></i> Back to List
                </a>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-12 gap-6">
        <!-- Left Column - Profile -->
        <div class="col-span-12 lg:col-span-4">
            <!-- Profile Card -->
            <div class="bg-white rounded-lg border border-gray-200 dark:bg-gray-800 dark:border-gray-700 shadow-sm mb-6">
                <div class="p-6">
                    <!-- Profile Header -->
                    <div class="flex flex-col items-center mb-6">
                        <div class="relative mb-4">
                            @if($employee->profile_image)
                                <img src="{{ asset('storage/' . $employee->profile_image) }}" alt="{{ $employee->full_name }}" 
                                     class="w-32 h-32 rounded-full border-4 border-white dark:border-gray-800 shadow">
                            @else
                                <div class="w-32 h-32 rounded-full border-4 border-white dark:border-gray-800 shadow bg-primary/10 flex items-center justify-center">
                                    <span class="text-4xl font-bold text-primary">{{ substr($employee->first_name, 0, 1) }}</span>
                                </div>
                            @endif
                            
                            <!-- Status Badge -->
                            <div class="absolute -top-2 -right-2">
                                <span class="px-3 py-1 text-xs font-bold rounded-full 
                                    @if($employee->status === 'active') bg-success/20 text-success 
                                    @elseif($employee->status === 'on_leave') bg-warning/20 text-warning 
                                    @elseif($employee->status === 'inactive') bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300
                                    @else bg-danger/20 text-danger @endif">
                                    {{ ucfirst(str_replace('_', ' ', $employee->status)) }}
                                </span>
                            </div>
                        </div>
                        
                        <h3 class="text-xl font-bold text-gray-800 dark:text-white/90">{{ $employee->full_name }}</h3>
                        <p class="text-gray-600 dark:text-gray-400">{{ $employee->employee_id }}</p>
                        <p class="text-gray-600 dark:text-gray-400">{{ $employee->position }}</p>
                        
                        <!-- Department Badge -->
                        <div class="mt-2">
                            <span class="px-3 py-1 text-xs font-medium rounded-full bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                                {{ $employee->department->name ?? 'N/A' }}
                            </span>
                        </div>
                    </div>

                    <!-- Quick Stats -->
                    <div class="grid grid-cols-3 gap-4 mb-6">
                        <div class="text-center p-3 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
                            <p class="text-2xl font-bold text-gray-800 dark:text-white">{{ $employee->tenure ?? 0 }}</p>
                            <p class="text-sm text-gray-600 dark:text-gray-400">Months</p>
                        </div>
                        <div class="text-center p-3 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
                            <p class="text-2xl font-bold text-gray-800 dark:text-white">{{ $employee->leave_balance ?? 0 }}</p>
                            <p class="text-sm text-gray-600 dark:text-gray-400">Leave Days</p>
                        </div>
                        <div class="text-center p-3 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
                            <p class="text-2xl font-bold text-gray-800 dark:text-white">
                                ${{ number_format($employee->salary, 2) }}
                            </p>
                            <p class="text-sm text-gray-600 dark:text-gray-400">Salary</p>
                        </div>
                    </div>

                    <!-- Quick Info -->
                    <div class="space-y-4">
                        <div class="flex items-center justify-between">
                            <span class="text-gray-600 dark:text-gray-400">Employee ID</span>
                            <span class="font-medium text-gray-800 dark:text-white">
                                {{ $employee->employee_id }}
                            </span>
                        </div>
                        
                        <div class="flex items-center justify-between">
                            <span class="text-gray-600 dark:text-gray-400">Hire Date</span>
                            <span class="font-medium text-gray-800 dark:text-white">
                                {{ $employee->hire_date->format('M d, Y') }}
                            </span>
                        </div>
                        
                        <div class="flex items-center justify-between">
                            <span class="text-gray-600 dark:text-gray-400">Employment Type</span>
                            <span class="font-medium text-gray-800 dark:text-white">
                                {{ ucfirst(str_replace('_', ' ', $employee->employment_type)) }}
                            </span>
                        </div>
                        
                        <div class="flex items-center justify-between">
                            <span class="text-gray-600 dark:text-gray-400">Work Schedule</span>
                            <span class="font-medium text-gray-800 dark:text-white">
                                {{ $employee->work_schedule ?? 'Standard' }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Contact Information -->
            <div class="bg-white rounded-lg border border-gray-200 dark:bg-gray-800 dark:border-gray-700 shadow-sm">
                <div class="p-6">
                    <h4 class="text-lg font-semibold text-gray-800 dark:text-white/90 mb-4">Contact Information</h4>
                    <div class="space-y-3">
                        <div class="flex items-center">
                            <i class="fas fa-envelope text-gray-400 mr-3 w-5"></i>
                            <a href="mailto:{{ $employee->email }}" class="text-gray-800 dark:text-white hover:text-primary dark:hover:text-primary">
                                {{ $employee->email }}
                            </a>
                        </div>
                        <div class="flex items-center">
                            <i class="fas fa-phone text-gray-400 mr-3 w-5"></i>
                            <a href="tel:{{ $employee->phone }}" class="text-gray-800 dark:text-white hover:text-primary dark:hover:text-primary">
                                {{ $employee->phone }}
                            </a>
                        </div>
                        <div class="flex items-start">
                            <i class="fas fa-map-marker-alt text-gray-400 mr-3 mt-1 w-5"></i>
                            <span class="text-gray-800 dark:text-white">{{ $employee->address }}</span>
                        </div>
                        
                        <div class="pt-4 border-t border-gray-200 dark:border-gray-700">
                            <h5 class="font-medium text-gray-800 dark:text-white mb-2">Emergency Contact</h5>
                            <div class="space-y-2">
                                <div class="flex items-center">
                                    <i class="fas fa-user text-gray-400 mr-3 w-5"></i>
                                    <span class="text-gray-800 dark:text-white">{{ $employee->emergency_contact_name }}</span>
                                </div>
                                <div class="flex items-center">
                                    <i class="fas fa-phone-alt text-gray-400 mr-3 w-5"></i>
                                    <span class="text-gray-800 dark:text-white">{{ $employee->emergency_contact_phone }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Column - Details -->
        <div class="col-span-12 lg:col-span-8">
            <!-- Tabs -->
            <div class="bg-white rounded-lg border border-gray-200 dark:bg-gray-800 dark:border-gray-700 shadow-sm mb-6">
                <div class="border-b border-gray-200 dark:border-gray-700">
                    <nav class="flex space-x-1 px-6 overflow-x-auto">
                        <button onclick="showTab('personal')" id="personal-tab" class="px-4 py-3 text-sm font-medium border-b-2 border-primary text-primary whitespace-nowrap">Personal Info</button>
                        <button onclick="showTab('employment')" id="employment-tab" class="px-4 py-3 text-sm font-medium border-b-2 border-transparent text-gray-500 hover:text-gray-700 dark:hover:text-gray-300 whitespace-nowrap">Employment</button>
                        <button onclick="showTab('documents')" id="documents-tab" class="px-4 py-3 text-sm font-medium border-b-2 border-transparent text-gray-500 hover:text-gray-700 dark:hover:text-gray-300 whitespace-nowrap">Documents</button>
                        <button onclick="showTab('attendance')" id="attendance-tab" class="px-4 py-3 text-sm font-medium border-b-2 border-transparent text-gray-500 hover:text-gray-700 dark:hover:text-gray-300 whitespace-nowrap">Attendance</button>
                        <button onclick="showTab('leave')" id="leave-tab" class="px-4 py-3 text-sm font-medium border-b-2 border-transparent text-gray-500 hover:text-gray-700 dark:hover:text-gray-300 whitespace-nowrap">Leave</button>
                    </nav>
                </div>

                <!-- Personal Info Tab -->
                <div id="personal-tab-content" class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-600 dark:text-gray-400 mb-1">Full Name</label>
                            <p class="text-gray-800 dark:text-white font-medium">{{ $employee->full_name }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-600 dark:text-gray-400 mb-1">Date of Birth</label>
                            <p class="text-gray-800 dark:text-white font-medium">
                                {{ $employee->date_of_birth ? $employee->date_of_birth->format('M d, Y') : 'Not provided' }}
                            </p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-600 dark:text-gray-400 mb-1">Gender</label>
                            <p class="text-gray-800 dark:text-white font-medium">{{ ucfirst($employee->gender) }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-600 dark:text-gray-400 mb-1">Nationality</label>
                            <p class="text-gray-800 dark:text-white font-medium">{{ $employee->nationality ?? 'Not specified' }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-600 dark:text-gray-400 mb-1">Marital Status</label>
                            <p class="text-gray-800 dark:text-white font-medium">{{ ucfirst($employee->marital_status) ?? 'Not specified' }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-600 dark:text-gray-400 mb-1">Email</label>
                            <p class="text-gray-800 dark:text-white font-medium">{{ $employee->email }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-600 dark:text-gray-400 mb-1">Phone</label>
                            <p class="text-gray-800 dark:text-white font-medium">{{ $employee->phone }}</p>
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-600 dark:text-gray-400 mb-1">Address</label>
                            <p class="text-gray-800 dark:text-white">{{ $employee->address }}</p>
                        </div>
                    </div>
                </div>

                <!-- Employment Tab -->
                <div id="employment-tab-content" class="p-6 hidden">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-600 dark:text-gray-400 mb-1">Department</label>
                            <p class="text-gray-800 dark:text-white font-medium">{{ $employee->department->name ?? 'N/A' }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-600 dark:text-gray-400 mb-1">Position</label>
                            <p class="text-gray-800 dark:text-white font-medium">{{ $employee->position }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-600 dark:text-gray-400 mb-1">Employment Type</label>
                            <p class="text-gray-800 dark:text-white font-medium">
                                {{ ucfirst(str_replace('_', ' ', $employee->employment_type)) }}
                            </p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-600 dark:text-gray-400 mb-1">Work Schedule</label>
                            <p class="text-gray-800 dark:text-white font-medium">{{ $employee->work_schedule ?? 'Standard' }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-600 dark:text-gray-400 mb-1">Hire Date</label>
                            <p class="text-gray-800 dark:text-white font-medium">{{ $employee->hire_date->format('M d, Y') }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-600 dark:text-gray-400 mb-1">Tenure</label>
                            <p class="text-gray-800 dark:text-white font-medium">{{ $employee->tenure }} months</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-600 dark:text-gray-400 mb-1">Salary</label>
                            <p class="text-gray-800 dark:text-white font-medium">${{ number_format($employee->salary, 2) }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-600 dark:text-gray-400 mb-1">Salary Type</label>
                            <p class="text-gray-800 dark:text-white font-medium">{{ ucfirst(str_replace('_', ' ', $employee->salary_type)) }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-600 dark:text-gray-400 mb-1">Bank Name</label>
                            <p class="text-gray-800 dark:text-white font-medium">{{ $employee->bank_name ?? 'Not specified' }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-600 dark:text-gray-400 mb-1">Account Number</label>
                            <p class="text-gray-800 dark:text-white font-medium">{{ $employee->bank_account_number ?? 'Not specified' }}</p>
                        </div>
                    </div>

                    <!-- Supervisor Information -->
                    @if($employee->supervisor)
                    <div class="mt-8 pt-6 border-t border-gray-200 dark:border-gray-700">
                        <h4 class="text-lg font-semibold text-gray-800 dark:text-white/90 mb-4">Supervisor Information</h4>
                        <div class="flex items-center p-4 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
                            <div class="flex-shrink-0">
                                @if($employee->supervisor->profile_image)
                                <img src="{{ asset('storage/' . $employee->supervisor->profile_image) }}" alt="{{ $employee->supervisor->full_name }}" 
                                     class="w-12 h-12 rounded-full">
                                @else
                                <div class="w-12 h-12 rounded-full bg-primary/10 flex items-center justify-center">
                                    <span class="text-primary font-bold">{{ substr($employee->supervisor->first_name, 0, 1) }}</span>
                                </div>
                                @endif
                            </div>
                            <div class="ml-4">
                                <h5 class="font-medium text-gray-800 dark:text-white">{{ $employee->supervisor->full_name }}</h5>
                                <p class="text-sm text-gray-600 dark:text-gray-400">{{ $employee->supervisor->position }}</p>
                                <p class="text-sm text-gray-500 dark:text-gray-400">{{ $employee->supervisor->email }}</p>
                            </div>
                        </div>
                    </div>
                    @endif
                </div>

                <!-- Documents Tab -->
                <div id="documents-tab-content" class="p-6 hidden">
                    <h4 class="text-lg font-semibold text-gray-800 dark:text-white/90 mb-4">Employee Documents</h4>
                    
                    <div class="space-y-4">
                        <!-- Resume/CV -->
                        @if($employee->resume)
                        <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-4">
                            <div class="flex items-start">
                                <div class="p-2 bg-gray-100 dark:bg-gray-700 rounded mr-3">
                                    <i class="fas fa-file-pdf text-danger"></i>
                                </div>
                                <div class="flex-1">
                                    <h5 class="font-medium text-gray-800 dark:text-white mb-1">Resume/CV</h5>
                                    <p class="text-sm text-gray-600 dark:text-gray-400 mb-2">
                                        Uploaded on {{ $employee->created_at->format('M d, Y') }}
                                    </p>
                                </div>
                                <a href="{{ asset('storage/' . $employee->resume) }}" target="_blank" class="text-primary hover:text-primary/80 ml-4">
                                    <i class="fas fa-download mr-1"></i> Download
                                </a>
                            </div>
                        </div>
                        @endif

                        <!-- ID Copy -->
                        @if($employee->id_copy)
                        <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-4">
                            <div class="flex items-start">
                                <div class="p-2 bg-gray-100 dark:bg-gray-700 rounded mr-3">
                                    <i class="fas fa-id-card text-primary"></i>
                                </div>
                                <div class="flex-1">
                                    <h5 class="font-medium text-gray-800 dark:text-white mb-1">ID/Passport Copy</h5>
                                    <p class="text-sm text-gray-600 dark:text-gray-400 mb-2">
                                        Identification document
                                    </p>
                                </div>
                                <a href="{{ asset('storage/' . $employee->id_copy) }}" target="_blank" class="text-primary hover:text-primary/80 ml-4">
                                    <i class="fas fa-eye mr-1"></i> View
                                </a>
                            </div>
                        </div>
                        @endif

                        <!-- Additional Documents -->
                        @if($employee->documents && $employee->documents->count() > 0)
                            @foreach($employee->documents as $document)
                            <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-4">
                                <div class="flex items-start">
                                    <div class="p-2 bg-gray-100 dark:bg-gray-700 rounded mr-3">
                                        <i class="fas fa-file text-primary"></i>
                                    </div>
                                    <div class="flex-1">
                                        <h5 class="font-medium text-gray-800 dark:text-white mb-1">{{ $document->document_type }}</h5>
                                        <p class="text-sm text-gray-600 dark:text-gray-400 mb-2">
                                            {{ $document->description }}
                                        </p>
                                        <p class="text-xs text-gray-500">
                                            Uploaded: {{ $document->created_at->format('M d, Y') }}
                                        </p>
                                    </div>
                                    <a href="{{ asset('storage/' . $document->file_path) }}" target="_blank" class="text-primary hover:text-primary/80 ml-4">
                                        <i class="fas fa-download mr-1"></i> Download
                                    </a>
                                </div>
                            </div>
                            @endforeach
                        @endif

                        @if(!$employee->resume && !$employee->id_copy && (!$employee->documents || $employee->documents->count() == 0))
                        <div class="text-center py-8 text-gray-500 dark:text-gray-400">
                            <i class="fas fa-file-alt text-4xl mb-3"></i>
                            <p>No documents uploaded</p>
                        </div>
                        @endif
                    </div>
                </div>

                <!-- Attendance Tab -->
                <div id="attendance-tab-content" class="p-6 hidden">
                    <h4 class="text-lg font-semibold text-gray-800 dark:text-white/90 mb-4">Attendance Records</h4>
                    
                    <!-- Attendance Stats -->
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                        <div class="bg-gray-50 dark:bg-gray-700/50 rounded-lg p-4 text-center">
                            <p class="text-2xl font-bold text-gray-800 dark:text-white">{{ $attendanceStats['present'] ?? 0 }}</p>
                            <p class="text-sm text-gray-600 dark:text-gray-400">Present</p>
                        </div>
                        <div class="bg-gray-50 dark:bg-gray-700/50 rounded-lg p-4 text-center">
                            <p class="text-2xl font-bold text-gray-800 dark:text-white">{{ $attendanceStats['absent'] ?? 0 }}</p>
                            <p class="text-sm text-gray-600 dark:text-gray-400">Absent</p>
                        </div>
                        <div class="bg-gray-50 dark:bg-gray-700/50 rounded-lg p-4 text-center">
                            <p class="text-2xl font-bold text-gray-800 dark:text-white">{{ $attendanceStats['late'] ?? 0 }}</p>
                            <p class="text-sm text-gray-600 dark:text-gray-400">Late</p>
                        </div>
                        <div class="bg-gray-50 dark:bg-gray-700/50 rounded-lg p-4 text-center">
                            <p class="text-2xl font-bold text-gray-800 dark:text-white">{{ $attendanceStats['leave'] ?? 0 }}</p>
                            <p class="text-sm text-gray-600 dark:text-gray-400">On Leave</p>
                        </div>
                    </div>

                    <!-- Recent Attendance -->
                    @if($recentAttendance && $recentAttendance->count() > 0)
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-900">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Date</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Check-in</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Check-out</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Status</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Hours</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                @foreach($recentAttendance as $attendance)
                                <tr>
                                    <td class="px-4 py-3 text-sm text-gray-900 dark:text-white">
                                        {{ $attendance->date->format('M d, Y') }}
                                    </td>
                                    <td class="px-4 py-3 text-sm text-gray-900 dark:text-white">
                                        {{ $attendance->check_in ? $attendance->check_in->format('h:i A') : '--:--' }}
                                    </td>
                                    <td class="px-4 py-3 text-sm text-gray-900 dark:text-white">
                                        {{ $attendance->check_out ? $attendance->check_out->format('h:i A') : '--:--' }}
                                    </td>
                                    <td class="px-4 py-3 text-sm">
                                        <span class="px-2 py-1 text-xs rounded-full 
                                            @if($attendance->status === 'present') bg-success/10 text-success 
                                            @elseif($attendance->status === 'absent') bg-danger/10 text-danger 
                                            @elseif($attendance->status === 'late') bg-warning/10 text-warning 
                                            @else bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200 @endif">
                                            {{ ucfirst($attendance->status) }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-sm text-gray-900 dark:text-white">
                                        {{ $attendance->hours ?? '--' }}
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @else
                    <p class="text-gray-500 dark:text-gray-400">No attendance records found.</p>
                    @endif
                </div>

                <!-- Leave Tab -->
                <div id="leave-tab-content" class="p-6 hidden">
                    <h4 class="text-lg font-semibold text-gray-800 dark:text-white/90 mb-4">Leave Information</h4>
                    
                    <!-- Leave Balance -->
                    <div class="bg-gray-50 dark:bg-gray-700/50 rounded-lg p-6 mb-6">
                        <h5 class="font-medium text-gray-800 dark:text-white mb-4">Leave Balance</h5>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div class="text-center">
                                <p class="text-2xl font-bold text-gray-800 dark:text-white">{{ $leaveBalance['annual'] ?? 0 }}</p>
                                <p class="text-sm text-gray-600 dark:text-gray-400">Annual Leave</p>
                            </div>
                            <div class="text-center">
                                <p class="text-2xl font-bold text-gray-800 dark:text-white">{{ $leaveBalance['sick'] ?? 0 }}</p>
                                <p class="text-sm text-gray-600 dark:text-gray-400">Sick Leave</p>
                            </div>
                            <div class="text-center">
                                <p class="text-2xl font-bold text-gray-800 dark:text-white">{{ $leaveBalance['unpaid'] ?? 0 }}</p>
                                <p class="text-sm text-gray-600 dark:text-gray-400">Unpaid Leave</p>
                            </div>
                        </div>
                    </div>

                    <!-- Leave History -->
                    <h5 class="font-medium text-gray-800 dark:text-white mb-4">Leave History</h5>
                    @if($leaveHistory && $leaveHistory->count() > 0)
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-900">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Type</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Start Date</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">End Date</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Days</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                @foreach($leaveHistory as $leave)
                                <tr>
                                    <td class="px-4 py-3 text-sm text-gray-900 dark:text-white">
                                        {{ ucfirst(str_replace('_', ' ', $leave->type)) }}
                                    </td>
                                    <td class="px-4 py-3 text-sm text-gray-900 dark:text-white">
                                        {{ $leave->start_date->format('M d, Y') }}
                                    </td>
                                    <td class="px-4 py-3 text-sm text-gray-900 dark:text-white">
                                        {{ $leave->end_date->format('M d, Y') }}
                                    </td>
                                    <td class="px-4 py-3 text-sm text-gray-900 dark:text-white">
                                        {{ $leave->days }}
                                    </td>
                                    <td class="px-4 py-3 text-sm">
                                        <span class="px-2 py-1 text-xs rounded-full 
                                            @if($leave->status === 'approved') bg-success/10 text-success 
                                            @elseif($leave->status === 'pending') bg-warning/10 text-warning 
                                            @else bg-danger/10 text-danger @endif">
                                            {{ ucfirst($leave->status) }}
                                        </span>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @else
                    <p class="text-gray-500 dark:text-gray-400">No leave history found.</p>
                    @endif
                </div>
            </div>

            <!-- Activity Timeline -->
            <div class="bg-white rounded-lg border border-gray-200 dark:bg-gray-800 dark:border-gray-700 shadow-sm">
                <div class="p-6">
                    <h4 class="text-lg font-semibold text-gray-800 dark:text-white/90 mb-4">Recent Activity</h4>
                    
                    <div class="space-y-4">
                        <div class="flex items-start">
                            <div class="flex-shrink-0 mt-1">
                                <div class="w-8 h-8 rounded-full bg-primary/10 flex items-center justify-center">
                                    <i class="fas fa-user-plus text-primary"></i>
                                </div>
                            </div>
                            <div class="ml-3 flex-1">
                                <p class="text-sm text-gray-800 dark:text-white">Employee profile created</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{ $employee->created_at->diffForHumans() }}</p>
                            </div>
                        </div>
                        
                        @if($employee->updated_at != $employee->created_at)
                        <div class="flex items-start">
                            <div class="flex-shrink-0 mt-1">
                                <div class="w-8 h-8 rounded-full bg-warning/10 flex items-center justify-center">
                                    <i class="fas fa-edit text-warning"></i>
                                </div>
                            </div>
                            <div class="ml-3 flex-1">
                                <p class="text-sm text-gray-800 dark:text-white">Profile updated</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{ $employee->updated_at->diffForHumans() }}</p>
                            </div>
                        </div>
                        @endif
                        
                        @if($recentAttendance && $recentAttendance->count() > 0)
                        <div class="flex items-start">
                            <div class="flex-shrink-0 mt-1">
                                <div class="w-8 h-8 rounded-full bg-success/10 flex items-center justify-center">
                                    <i class="fas fa-clock text-success"></i>
                                </div>
                            </div>
                            <div class="ml-3 flex-1">
                                <p class="text-sm text-gray-800 dark:text-white">Last attendance recorded</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{ $recentAttendance->first()->created_at->diffForHumans() }}</p>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function showTab(tabName) {
    // Hide all tab contents
    document.querySelectorAll('[id$="-tab-content"]').forEach(tab => {
        tab.classList.add('hidden');
    });
    
    // Remove active state from all tabs
    document.querySelectorAll('[id$="-tab"]').forEach(tabBtn => {
        tabBtn.classList.remove('border-primary', 'text-primary');
        tabBtn.classList.add('border-transparent', 'text-gray-500');
    });
    
    // Show selected tab content
    document.getElementById(tabName + '-tab-content').classList.remove('hidden');
    
    // Activate selected tab button
    document.getElementById(tabName + '-tab').classList.add('border-primary', 'text-primary');
    document.getElementById(tabName + '-tab').classList.remove('border-transparent', 'text-gray-500');
}
</script>
@endpush