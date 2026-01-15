@extends('layouts.app')

@section('content')
<div class="p-4 mx-auto max-w-screen-2xl md:p-6">
    <!-- Header -->
    <div class="mb-6">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex items-center space-x-4">
                <div class="relative">
                    <div class="w-16 h-16 rounded-full bg-gray-200 dark:bg-gray-700 flex items-center justify-center text-2xl font-semibold text-gray-600 dark:text-gray-400">
                        {{ substr($staff->name, 0, 1) }}
                    </div>
                    <div class="absolute bottom-0 right-0 w-4 h-4 rounded-full {{ $staff->status === 'active' ? 'bg-success' : 'bg-danger' }} border-2 border-white dark:border-gray-800"></div>
                </div>
                <div>
                    <h2 class="text-2xl font-bold text-gray-800 dark:text-white/90">{{ $staff->name }}</h2>
                    <div class="flex items-center space-x-2 mt-1">
                        <span class="px-3 py-1 text-xs rounded-full bg-primary/10 text-primary">
                            {{ $staff->staffProfile->employment_status ?? 'N/A' }}
                        </span>
                        {{-- @if($staff->staffProfile->department) --}}
                        <span class="px-3 py-1 text-xs rounded-full bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                            {{ $staff->staffProfile->department->name ?? 'N/A' }}
                        </span>
                        {{-- @endif --}}
                        <span class="px-3 py-1 text-xs rounded-full bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300">
                            {{ $staff->roles->first()->name ?? 'Staff' }}
                        </span>
                    </div>
                </div>
            </div>
            <div class="flex items-center space-x-2">
                @can('update', $staff)
                <a href="{{ route('hr.edit', $staff) }}" class="px-4 py-2 bg-warning text-white rounded hover:bg-warning/90">
                    <i class="fas fa-edit mr-2"></i> Edit Profile
                </a>
                @endcan
                <a href="{{ route('hr.index') }}" class="px-4 py-2 border border-gray-300 dark:border-gray-700 rounded text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800">
                    <i class="fas fa-arrow-left mr-2"></i> Back
                </a>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Left Column - Personal Info -->
        <div class="lg:col-span-2">
            <!-- Personal Information -->
            <div class="bg-white rounded-lg border border-gray-200 dark:bg-gray-800 dark:border-gray-700 shadow-sm mb-6">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90 mb-4">Personal Information</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="space-y-4">
                            <div>
                                <p class="text-sm text-gray-500 dark:text-gray-400">Email Address</p>
                                <p class="text-gray-800 dark:text-white">{{ $staff->email }}</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500 dark:text-gray-400">Phone Number</p>
                                <p class="text-gray-800 dark:text-white">{{ $staff->phone }}</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500 dark:text-gray-400">Date of Birth</p>
                                <p class="text-gray-800 dark:text-white">{{ $staff->dob?->format('D, ' . SystemHelper::dateFormat()) ?? 'N/A' }}</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500 dark:text-gray-400">Gender</p>
                                <p class="text-gray-800 dark:text-white">{{ ucfirst($staff->gender ?? 'N/A') }}</p>
                            </div>
                        </div>
                        <div class="space-y-4">
                            <div>
                                <p class="text-sm text-gray-500 dark:text-gray-400">Hire Date</p>
                                <p class="text-gray-800 dark:text-white">
                                    {{ optional($staff->staffProfile)?->hire_date?->format('D, ' . \App\Helpers\SystemHelper::dateFormat()) ?? 'N/A' }}
                                </p>

                            </div>
                            <div>
                                <p class="text-sm text-gray-500 dark:text-gray-400">Service Duration</p>
                                <p class="text-gray-800 dark:text-white">{{ $staff->staffProfile->service_duration ?? 0 }} months</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500 dark:text-gray-400">Base Salary</p>
                                <p class="text-gray-800 dark:text-white">KSH {{ number_format($staff->staffProfile->salary ?? 0, 2) }}</p>
                            </div>
                           <p class="text-gray-800 dark:text-white">
                                {{ optional($staff->staffProfile)?->hire_date?->addMonths(optional($staff->staffProfile)?->contract_period ?? 0)->format(SystemHelper::dateFormat()) ?? 'N/A' }}
                            </p>


                        </div>
                    </div>
                    
                    <!-- Bank Details -->
                    @if(optional($staff->staffProfile)->bank_name)
                    <div class="mt-8 pt-6 border-t border-gray-200 dark:border-gray-700">
                        <h4 class="font-medium text-gray-800 dark:text-white mb-3">Bank Details</h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <p class="text-sm text-gray-500 dark:text-gray-400">Bank Name</p>
                                <p class="text-gray-800 dark:text-white">{{ optional($staff->staffProfile)->bank_name }}</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500 dark:text-gray-400">Account Number</p>
                                <p class="text-gray-800 dark:text-white">{{ optional($staff->staffProfile)->account_number }}</p>
                            </div>
                        </div>
                    </div>
                    @endif

                    <!-- Emergency Contact -->
                    @if(optional($staff->staffProfile)->emergency_contact)
                    <div class="mt-8 pt-6 border-t border-gray-200 dark:border-gray-700">
                        <h4 class="font-medium text-gray-800 dark:text-white mb-3">Emergency Contact</h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <p class="text-sm text-gray-500 dark:text-gray-400">Contact Person</p>
                                <p class="text-gray-800 dark:text-white">{{ optional($staff->staffProfile->emergency_contact)['name'] ?? 'N/A' }}</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500 dark:text-gray-400">Phone Number</p>
                                <p class="text-gray-800 dark:text-white">{{ optional($staff->staffProfile->emergency_contact)['phone'] ?? 'N/A' }}</p>
                            </div>
                        </div>
                    </div>
                    @endif

                </div>
            </div>

            <!-- Current Month Attendance -->
            <div class="bg-white rounded-lg border border-gray-200 dark:bg-gray-800 dark:border-gray-700 shadow-sm mb-6">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90 mb-4">Attendance This Month</h3>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
                        <div class="text-center p-4 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
                            <p class="text-2xl font-bold text-gray-800 dark:text-white">{{ $attendance['present'] ?? 0 }}</p>
                            <p class="text-sm text-gray-600 dark:text-gray-400">Days Present</p>
                        </div>
                        <div class="text-center p-4 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
                            <p class="text-2xl font-bold text-gray-800 dark:text-white">{{ $attendance['absent'] ?? 0 }}</p>
                            <p class="text-sm text-gray-600 dark:text-gray-400">Days Absent</p>
                        </div>
                        <div class="text-center p-4 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
                            <p class="text-2xl font-bold text-gray-800 dark:text-white">{{ $attendance['on_leave'] ?? 0 }}</p>
                            <p class="text-sm text-gray-600 dark:text-gray-400">Leave Days</p>
                        </div>
                        <div class="text-center p-4 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
                            <p class="text-2xl font-bold text-gray-800 dark:text-white">{{ $attendance['overtime_hours'] ?? 0 }}</p>
                            <p class="text-sm text-gray-600 dark:text-gray-400">Overtime Hours</p>
                        </div>
                    </div>
                    
                    <!-- Recent Shifts -->
                    @if($staff->shiftAssignments->count() > 0)
                    <div>
                        <h4 class="font-medium text-gray-800 dark:text-white mb-3">This Week's Schedule</h4>
                        <div class="space-y-2">
                            @foreach($staff->shiftAssignments as $assignment)
                            <div class="flex items-center justify-between p-3 border border-gray-200 dark:border-gray-700 rounded-lg">
                                <div>
                                    <p class="text-sm text-gray-800 dark:text-white">{{ $assignment->date->format('D, d M') }}</p>
                                    <p class="text-xs text-gray-600 dark:text-gray-400">
                                        {{ $assignment->shift->start_time }} - {{ $assignment->shift->end_time }}
                                    </p>
                                </div>
                                <span class="px-2 py-1 text-xs rounded-full 
                                    @if($assignment->status === 'completed') bg-success/10 text-success 
                                    @elseif($assignment->status === 'absent') bg-danger/10 text-danger 
                                    @elseif($assignment->status === 'on_leave') bg-info/10 text-info 
                                    @else bg-warning/10 text-warning @endif">
                                    {{ ucfirst($assignment->status) }}
                                </span>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Leave Balance & Requests -->
            <div class="bg-white rounded-lg border border-gray-200 dark:bg-gray-800 dark:border-gray-700 shadow-sm">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90 mb-4">Leave Information</h3>
                    
                    <!-- Leave Balance -->
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
                        <div class="text-center p-4 bg-blue-50 dark:bg-blue-900/20 rounded-lg">
                            <p class="text-2xl font-bold text-gray-800 dark:text-white">{{ $leaveBalance['entitlement'] ?? 0 }}</p>
                            <p class="text-sm text-gray-600 dark:text-gray-400">Annual Entitlement</p>
                        </div>
                        <div class="text-center p-4 bg-green-50 dark:bg-green-900/20 rounded-lg">
                            <p class="text-2xl font-bold text-gray-800 dark:text-white">{{ $leaveBalance['used'] ?? 0 }}</p>
                            <p class="text-sm text-gray-600 dark:text-gray-400">Days Used</p>
                        </div>
                        <div class="text-center p-4 bg-yellow-50 dark:bg-yellow-900/20 rounded-lg">
                            <p class="text-2xl font-bold text-gray-800 dark:text-white">{{ $leaveBalance['pending'] ?? 0 }}</p>
                            <p class="text-sm text-gray-600 dark:text-gray-400">Pending Days</p>
                        </div>
                        <div class="text-center p-4 bg-purple-50 dark:bg-purple-900/20 rounded-lg">
                            <p class="text-2xl font-bold text-gray-800 dark:text-white">{{ $leaveBalance['remaining'] ?? 0 }}</p>
                            <p class="text-sm text-gray-600 dark:text-gray-400">Remaining</p>
                        </div>
                    </div>
                    
                    <!-- Recent Leave Requests -->
                    @if($staff->leaveRequests->count() > 0)
                    <div>
                        <h4 class="font-medium text-gray-800 dark:text-white mb-3">Recent Leave Requests</h4>
                        <div class="space-y-2">
                            @foreach($staff->leaveRequests as $leave)
                            <div class="flex items-center justify-between p-3 border border-gray-200 dark:border-gray-700 rounded-lg">
                                <div>
                                    <p class="text-sm text-gray-800 dark:text-white">
                                        {{ ucfirst($leave->leave_type) }} Leave
                                    </p>
                                    <p class="text-xs text-gray-600 dark:text-gray-400">
                                        {{ $leave->start_date->format('d M') }} - {{ $leave->end_date->format('d M') }}
                                        ({{ $leave->duration_days }} days)
                                    </p>
                                </div>
                                <span class="px-2 py-1 text-xs rounded-full 
                                    @if($leave->status === 'approved') bg-success/10 text-success 
                                    @elseif($leave->status === 'rejected') bg-danger/10 text-danger 
                                    @else bg-warning/10 text-warning @endif">
                                    {{ ucfirst($leave->status) }}
                                </span>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Right Column - Payroll & Actions -->
        <div class="lg:col-span-1">
            <!-- Current Payroll -->
            @if($currentPayroll)
            <div class="bg-white rounded-lg border border-gray-200 dark:bg-gray-800 dark:border-gray-700 shadow-sm mb-6">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90 mb-4">Current Payroll</h3>
                    <div class="space-y-3">
                        <div class="flex justify-between">
                            <span class="text-gray-600 dark:text-gray-400">Basic Salary</span>
                            <span class="font-medium text-gray-800 dark:text-white">
                                KSH {{ number_format($currentPayroll->basic_salary, 2) }}
                            </span>
                        </div>
                        @if($currentPayroll->overtime_amount > 0)
                        <div class="flex justify-between">
                            <span class="text-gray-600 dark:text-gray-400">Overtime</span>
                            <span class="font-medium text-gray-800 dark:text-white">
                                KSH {{ number_format($currentPayroll->overtime_amount, 2) }}
                            </span>
                        </div>
                        @endif
                        @if($currentPayroll->allowances > 0)
                        <div class="flex justify-between">
                            <span class="text-gray-600 dark:text-gray-400">Allowances</span>
                            <span class="font-medium text-gray-800 dark:text-white">
                                KSH {{ number_format($currentPayroll->allowances, 2) }}
                            </span>
                        </div>
                        @endif
                        @if($currentPayroll->bonuses > 0)
                        <div class="flex justify-between">
                            <span class="text-gray-600 dark:text-gray-400">Bonuses</span>
                            <span class="font-medium text-gray-800 dark:text-white">
                                KSH {{ number_format($currentPayroll->bonuses, 2) }}
                            </span>
                        </div>
                        @endif
                        <div class="flex justify-between border-t border-gray-200 dark:border-gray-700 pt-3">
                            <span class="font-medium text-gray-800 dark:text-white">Gross Salary</span>
                            <span class="font-bold text-gray-800 dark:text-white">
                                KSH {{ number_format($currentPayroll->gross, 2) }}
                            </span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600 dark:text-gray-400">Tax Deductions</span>
                            <span class="font-medium text-gray-800 dark:text-white">
                                KSH {{ number_format($currentPayroll->tax_deductions, 2) }}
                            </span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600 dark:text-gray-400">Other Deductions</span>
                            <span class="font-medium text-gray-800 dark:text-white">
                                KSH {{ number_format($currentPayroll->other_deductions, 2) }}
                            </span>
                        </div>
                        <div class="flex justify-between border-t border-gray-200 dark:border-gray-700 pt-3">
                            <span class="font-bold text-gray-800 dark:text-white">Net Salary</span>
                            <span class="font-bold text-success dark:text-success">
                                KSH {{ number_format($currentPayroll->net, 2) }}
                            </span>
                        </div>
                        <div class="mt-4">
                            <span class="px-3 py-1 text-xs rounded-full 
                                @if($currentPayroll->status === 'paid') bg-success/10 text-success 
                                @elseif($currentPayroll->status === 'approved') bg-warning/10 text-warning 
                                @elseif($currentPayroll->status === 'cancelled') bg-danger/10 text-danger 
                                @else bg-gray-100 text-gray-800 @endif">
                                {{ ucfirst($currentPayroll->status) }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
            @endif

            <!-- Quick Actions -->
            <div class="bg-white rounded-lg border border-gray-200 dark:bg-gray-800 dark:border-gray-700 shadow-sm mb-6">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90 mb-4">Quick Actions</h3>
                    <div class="space-y-3">
                        @can('update', $staff)
                        <a href="{{ route('hr.edit', $staff) }}" class="w-full flex items-center justify-between p-3 border border-gray-200 dark:border-gray-700 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-900">
                            <div class="flex items-center">
                                <div class="w-8 h-8 rounded-full bg-warning/10 text-warning flex items-center justify-center mr-3">
                                    <i class="fas fa-edit"></i>
                                </div>
                                <span class="text-gray-800 dark:text-white">Edit Profile</span>
                            </div>
                            <i class="fas fa-chevron-right text-gray-400"></i>
                        </a>
                        @endcan
                        
                        @can('create', App\Models\LeaveRequest::class)
                        <a href="#" onclick="showLeaveRequestModal()" class="w-full flex items-center justify-between p-3 border border-gray-200 dark:border-gray-700 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-900">
                            <div class="flex items-center">
                                <div class="w-8 h-8 rounded-full bg-info/10 text-info flex items-center justify-center mr-3">
                                    <i class="fas fa-plane"></i>
                                </div>
                                <span class="text-gray-800 dark:text-white">Apply Leave</span>
                            </div>
                            <i class="fas fa-chevron-right text-gray-400"></i>
                        </a>
                        @endcan
                        
                        @can('viewPayroll', App\Models\Payroll::class)
                        <a href="#" class="w-full flex items-center justify-between p-3 border border-gray-200 dark:border-gray-700 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-900">
                            <div class="flex items-center">
                                <div class="w-8 h-8 rounded-full bg-success/10 text-success flex items-center justify-center mr-3">
                                    <i class="fas fa-money-check-alt"></i>
                                </div>
                                <span class="text-gray-800 dark:text-white">View Payslips</span>
                            </div>
                            <i class="fas fa-chevron-right text-gray-400"></i>
                        </a>
                        @endcan
                        
                        @can('terminate', $staff)
                        <a href="#" onclick="showTerminationModal()" class="w-full flex items-center justify-between p-3 border border-gray-200 dark:border-gray-700 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-900">
                            <div class="flex items-center">
                                <div class="w-8 h-8 rounded-full bg-danger/10 text-danger flex items-center justify-center mr-3">
                                    <i class="fas fa-user-times"></i>
                                </div>
                                <span class="text-gray-800 dark:text-white">Terminate Employment</span>
                            </div>
                            <i class="fas fa-chevron-right text-gray-400"></i>
                        </a>
                        @endcan
                    </div>
                </div>
            </div>

            <!-- Payroll History -->
            @if($staff->payrolls->count() > 0)
            <div class="bg-white rounded-lg border border-gray-200 dark:bg-gray-800 dark:border-gray-700 shadow-sm">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90 mb-4">Recent Payrolls</h3>
                    <div class="space-y-3">
                        @foreach($staff->payrolls as $payroll)
                        <div class="p-3 border border-gray-200 dark:border-gray-700 rounded-lg">
                            <div class="flex justify-between items-start mb-2">
                                <span class="font-medium text-gray-800 dark:text-white">
                                    {{ Carbon\Carbon::parse($payroll->period_month)->format('F Y') }}
                                </span>
                                <span class="px-2 py-1 text-xs rounded-full 
                                    @if($payroll->status === 'paid') bg-success/10 text-success 
                                    @elseif($payroll->status === 'approved') bg-warning/10 text-warning 
                                    @else bg-gray-100 text-gray-800 @endif">
                                    {{ ucfirst($payroll->status) }}
                                </span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-600 dark:text-gray-400">Net Salary</span>
                                <span class="font-medium text-gray-800 dark:text-white">
                                    KSH {{ number_format($payroll->net, 2) }}
                                </span>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>

<!-- Leave Request Modal -->
<div id="leaveRequestModal" class="fixed inset-0 bg-gray-900 bg-opacity-50 hidden z-50 flex items-center justify-center p-4">
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl max-w-md w-full">
        <div class="p-6">
            <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90 mb-4">Apply for Leave</h3>
            <form id="leaveRequestForm" action="{{ route('hr.index') }}" method="POST">
                @csrf
                <input type="hidden" name="user_id" value="{{ $staff->id }}">
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Leave Type</label>
                        <select name="leave_type" class="w-full rounded border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-white" required>
                            <option value="annual">Annual Leave</option>
                            <option value="sick">Sick Leave</option>
                            <option value="maternity">Maternity Leave</option>
                            <option value="paternity">Paternity Leave</option>
                            <option value="unpaid">Unpaid Leave</option>
                        </select>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Start Date</label>
                            <input type="date" name="start_date" class="w-full rounded border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-white" required>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">End Date</label>
                            <input type="date" name="end_date" class="w-full rounded border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-white" required>
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Reason</label>
                        <textarea name="reason" rows="3" class="w-full rounded border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-white" required></textarea>
                    </div>
                </div>
                <div class="mt-6 flex justify-end space-x-3">
                    <button type="button" onclick="hideLeaveRequestModal()" class="px-4 py-2 border border-gray-300 dark:border-gray-700 rounded text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-900">
                        Cancel
                    </button>
                    <button type="submit" class="px-4 py-2 bg-primary text-white rounded hover:bg-primary/90">
                        Apply for Leave
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Termination Modal -->
<div id="terminationModal" class="fixed inset-0 bg-gray-900 bg-opacity-50 hidden z-50 flex items-center justify-center p-4">
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl max-w-md w-full">
        <div class="p-6">
            <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90 mb-4">Terminate Employment</h3>
            <form id="terminationForm" action="{{ route('hr.create', $staff) }}" method="POST">
                @csrf
                @method('DELETE')
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Termination Date</label>
                        <input type="date" name="termination_date" class="w-full rounded border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-white" required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Reason for Termination</label>
                        <textarea name="reason" rows="3" class="w-full rounded border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-white" required></textarea>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Exit Interview Notes (Optional)</label>
                        <textarea name="exit_interview_notes" rows="2" class="w-full rounded border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-white"></textarea>
                    </div>
                </div>
                <div class="mt-6 flex justify-end space-x-3">
                    <button type="button" onclick="hideTerminationModal()" class="px-4 py-2 border border-gray-300 dark:border-gray-700 rounded text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-900">
                        Cancel
                    </button>
                    <button type="submit" class="px-4 py-2 bg-danger text-white rounded hover:bg-danger/90">
                        Terminate Employment
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function showLeaveRequestModal() {
    document.getElementById('leaveRequestModal').classList.remove('hidden');
}

function hideLeaveRequestModal() {
    document.getElementById('leaveRequestModal').classList.add('hidden');
}

function showTerminationModal() {
    if (confirm('Are you sure you want to terminate this employee?')) {
        document.getElementById('terminationModal').classList.remove('hidden');
    }
}

function hideTerminationModal() {
    document.getElementById('terminationModal').classList.add('hidden');
}
</script>
@endpush