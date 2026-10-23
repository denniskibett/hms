<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\LeaveRequest;
use App\Models\Payroll;
use App\Models\Role;
use App\Models\Shift;
use App\Models\ShiftAssignment;
use App\Models\StaffProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HrTest extends TestCase
{
    use RefreshDatabase;

    private User $hr;
    private User $manager;
    private Department $kitchen;

    protected function setUp(): void
    {
        parent::setUp();

        $this->hr = $this->userWithRole('hr');
        $this->manager = $this->userWithRole('manager');
        $this->kitchen = Department::create(['name' => 'Kitchen', 'code' => 'KIT']);
    }

    private function userWithRole(string $role): User
    {
        $user = User::factory()->create();
        $user->roles()->attach(Role::firstOrCreate(['name' => $role])->id);

        return $user;
    }

    private function hire(array $overrides = []): StaffProfile
    {
        $this->actingAs($this->hr)->postJson(route('hr.staff.store'), array_merge([
            'name' => 'Jane Wanjiku',
            'phone' => '0711000111',
            'email' => 'jane@example.com',
            'role' => 'kitchen',
            'password' => 'secret-pass-1',
            'department_id' => $this->kitchen->id,
            'job_title' => 'Cook',
            'kra_pin' => 'a123456789b',
            'salary' => 50000,
            'hire_date' => today()->subYear()->toDateString(),
            'employment_status' => 'permanent',
        ], $overrides))->assertCreated();

        return StaffProfile::latest('id')->first();
    }

    public function test_hiring_creates_a_user_with_a_login_and_role(): void
    {
        $staff = $this->hire();

        $this->assertSame('Jane Wanjiku', $staff->user->name);
        $this->assertTrue($staff->user->hasRole('kitchen'));
        $this->assertSame('A123456789B', $staff->kra_pin);

        $this->post('/logout');
        $this->post(route('login'), ['email' => 'jane@example.com', 'password' => 'secret-pass-1']);
        $this->assertAuthenticatedAs($staff->user);
    }

    public function test_staff_without_access_need_no_email_and_bad_kra_pins_are_refused(): void
    {
        $this->hire(['name' => 'Peter Cleaner', 'email' => null, 'role' => null, 'password' => null]);
        $this->assertNull(StaffProfile::latest('id')->first()->user->email);

        $this->actingAs($this->hr)->postJson(route('hr.staff.store'), [
            'name' => 'X', 'phone' => '1', 'salary' => 1, 'hire_date' => today()->toDateString(), 'employment_status' => 'permanent', 'kra_pin' => '12345',
            'password' => 'secret-pass-1',
        ])->assertJsonValidationErrors(['kra_pin', 'email']);
    }

    public function test_changing_role_swaps_only_the_staff_role(): void
    {
        $staff = $this->hire();
        $data = $staff->formData();
        $data['role'] = 'receptionist';

        $this->actingAs($this->hr)->putJson(route('hr.staff.update', $staff), $data)->assertOk();

        $roles = $staff->user->fresh()->roles->pluck('name')->all();
        $this->assertSame(['receptionist'], $roles);
    }

    public function test_leaving_disables_login_and_clears_future_shifts(): void
    {
        $staff = $this->hire();
        $shift = Shift::create(['name' => 'Morning', 'start_time' => '06:00', 'end_time' => '14:00', 'type' => 'morning']);
        ShiftAssignment::create(['user_id' => $staff->user_id, 'shift_id' => $shift->id, 'date' => today()->addDays(3)->toDateString()]);

        $this->actingAs($this->hr)
            ->post(route('hr.staff.terminate', $staff), ['end_date' => today()->toDateString(), 'reason' => 'Resigned'])
            ->assertSessionHas('success');

        $this->assertFalse($staff->fresh()->is_active);
        $this->assertSame('inactive', $staff->user->fresh()->status);
        $this->assertSame(0, ShiftAssignment::count());

        $this->post('/logout');
        $this->post(route('login'), ['email' => 'jane@example.com', 'password' => 'secret-pass-1']);
        $this->assertGuest();

        // Their record is frozen
        $this->actingAs($this->hr)->putJson(route('hr.staff.update', $staff), $staff->formData())->assertUnprocessable();
    }

    public function test_leave_approval_balance_and_rota(): void
    {
        $staff = $this->hire();
        $shift = Shift::create(['name' => 'Morning', 'start_time' => '06:00', 'end_time' => '14:00', 'type' => 'morning']);
        $from = today()->startOfYear()->addMonths(6);
        ShiftAssignment::create(['user_id' => $staff->user_id, 'shift_id' => $shift->id, 'date' => $from->toDateString()]);

        $this->actingAs($this->hr)->post(route('hr.leave.store'), [
            'user_id' => $staff->user_id, 'leave_type' => 'annual', 'start_date' => $from->toDateString(), 'end_date' => $from->copy()->addDays(4)->toDateString(), 'reason' => 'Holiday',
        ])->assertSessionHas('success');
        $leave = LeaveRequest::sole();

        // Overlapping leave and more than the balance are refused
        $this->actingAs($this->hr)->post(route('hr.leave.store'), [
            'user_id' => $staff->user_id, 'leave_type' => 'sick', 'start_date' => $from->toDateString(), 'end_date' => $from->toDateString(), 'reason' => 'Flu',
        ])->assertSessionHasErrors('start_date');
        $this->actingAs($this->hr)->post(route('hr.leave.store'), [
            'user_id' => $staff->user_id, 'leave_type' => 'annual', 'start_date' => $from->copy()->addMonth()->toDateString(), 'end_date' => $from->copy()->addMonth()->addDays(29)->toDateString(), 'reason' => 'Long trip',
        ])->assertSessionHasErrors('end_date');

        // People can't approve their own leave
        $this->actingAs($staff->user)->post(route('hr.leave.decide', $leave), ['status' => 'approved'])->assertForbidden();

        $this->actingAs($this->manager)->post(route('hr.leave.decide', $leave), ['status' => 'approved'])->assertSessionHas('success');
        $this->assertSame('approved', $leave->fresh()->status);
        $this->assertSame(16, $staff->annualLeaveBalance($from->year));
        $this->assertSame('on_leave', ShiftAssignment::sole()->status);

        $this->actingAs($this->manager)->post(route('hr.leave.decide', $leave), ['status' => 'cancelled']);
        $this->assertSame('scheduled', ShiftAssignment::sole()->status);
    }

    public function test_statutory_deductions(): void
    {
        // 50,000 gross: NSSF 3,000, SHA 1,375, levy 750, taxable 44,875
        // PAYE: 2,400 + 2,083.25 + 3,762.60 - 2,400 relief = 5,845.85
        $d = Payroll::statutoryDeductions(50000);
        $this->assertSame(['paye' => 5845.85, 'nssf' => 3000.0, 'shif' => 1375.0, 'housing_levy' => 750.0], $d);

        // Low pay: SHA minimum, no PAYE
        $low = Payroll::statutoryDeductions(10000);
        $this->assertSame(300.0, $low['shif']);
        $this->assertSame(0.0, $low['paye']);

        // NSSF stops at the upper limit
        $this->assertSame(4320.0, Payroll::statutoryDeductions(200000)['nssf']);
    }

    public function test_payroll_month(): void
    {
        $staff = $this->hire();
        $month = today()->format('Y-m');
        $shift = Shift::create(['name' => 'Morning', 'start_time' => '06:00', 'end_time' => '14:00', 'type' => 'morning']);
        ShiftAssignment::create(['user_id' => $staff->user_id, 'shift_id' => $shift->id, 'date' => today()->startOfMonth()->toDateString(), 'status' => 'completed', 'overtime_hours' => 4]);

        $this->actingAs($this->hr)->post(route('hr.payroll.generate'), ['month' => $month])->assertSessionHas('success');
        $payroll = Payroll::sole();

        // 4 h × 50,000 / 195 × 1.5
        $this->assertSame('1538.46', $payroll->overtime_amount);
        $this->assertEquals(51538.46, (float) $payroll->fresh()->gross);
        $this->assertSame('draft', $payroll->status);

        // Running it again adds no duplicates
        $this->actingAs($this->hr)->post(route('hr.payroll.generate'), ['month' => $month]);
        $this->assertSame(1, Payroll::count());

        $this->actingAs($this->hr)->putJson(route('hr.payroll.update', $payroll), [
            'basic_salary' => 50000, 'overtime_hours' => 0, 'allowances' => 0, 'bonuses' => 0, 'other_deductions' => 1000,
        ])->assertOk();
        $this->assertEquals(50000 - 5845.85 - 3000 - 1375 - 750 - 1000, (float) $payroll->fresh()->net);

        // HR can't approve; the manager does; finance pays
        $finance = $this->userWithRole('finance');
        $this->actingAs($this->hr)->post(route('hr.payroll.status'), ['status' => 'approved', 'month' => $month])->assertForbidden();
        $this->actingAs($finance)->post(route('hr.payroll.status'), ['status' => 'paid', 'month' => $month])->assertSessionHas('error');
        $this->actingAs($this->manager)->post(route('hr.payroll.status'), ['status' => 'approved', 'month' => $month])->assertSessionHas('success');
        $this->actingAs($finance)->post(route('hr.payroll.status'), ['status' => 'paid', 'month' => $month])->assertSessionHas('success');
        $this->assertSame('paid', $payroll->fresh()->status);

        // Paid payroll is locked
        $this->actingAs($this->hr)->putJson(route('hr.payroll.update', $payroll), [
            'basic_salary' => 1, 'overtime_hours' => 0, 'allowances' => 0, 'bonuses' => 0, 'other_deductions' => 0,
        ])->assertUnprocessable();

        $this->actingAs($finance)->get(route('hr.payroll.payslip', $payroll))->assertOk()->assertSee('Jane Wanjiku')->assertSee('5,845.85');
    }

    public function test_rota_assignments(): void
    {
        $staff = $this->hire();
        $morning = Shift::create(['name' => 'Morning', 'start_time' => '06:00', 'end_time' => '14:00', 'type' => 'morning']);
        $night = Shift::create(['name' => 'Night', 'start_time' => '22:00', 'end_time' => '06:00', 'type' => 'night']);
        $this->assertSame(8.0, $night->duration_hours);
        $day = today()->toDateString();

        $this->actingAs($this->hr)->postJson(route('hr.rota.assign'), ['user_id' => $staff->user_id, 'date' => $day, 'shift_id' => $morning->id])->assertOk();
        $this->actingAs($this->hr)->postJson(route('hr.rota.assign'), ['user_id' => $staff->user_id, 'date' => $day, 'shift_id' => $night->id, 'status' => 'completed', 'overtime_hours' => 2])->assertOk();
        $this->assertSame($night->id, ShiftAssignment::sole()->shift_id);

        $this->actingAs($this->hr)->postJson(route('hr.rota.assign'), ['user_id' => $staff->user_id, 'date' => $day, 'shift_id' => null])->assertOk();
        $this->assertSame(0, ShiftAssignment::count());

        // Copy last week forward
        ShiftAssignment::create(['user_id' => $staff->user_id, 'shift_id' => $morning->id, 'date' => today()->startOfWeek()->subWeek()->toDateString()]);
        $this->actingAs($this->hr)->postJson(route('hr.rota.copy-week'), ['week' => today()->toDateString()])->assertOk();
        $this->assertSame(2, ShiftAssignment::count());
    }

    public function test_pages_and_access(): void
    {
        $staff = $this->hire();
        Shift::create(['name' => 'Morning', 'start_time' => '06:00', 'end_time' => '14:00', 'type' => 'morning']);

        foreach (['hr.staff.index', 'hr.rota.index', 'hr.leave.index', 'hr.payroll.index'] as $route) {
            $this->actingAs($this->hr)->get(route($route))->assertOk();
        }
        $this->actingAs($this->hr)->get(route('hr.staff.show', $staff))->assertOk()->assertSee('Jane Wanjiku');
        $this->actingAs($this->hr)->get(route('hr.index'))->assertRedirect('/hr/staff');
        $this->actingAs($this->hr)->get(route('dashboard'))->assertOk();

        $finance = $this->userWithRole('finance');
        $this->actingAs($finance)->get(route('hr.payroll.index'))->assertOk();
        $this->actingAs($finance)->get(route('hr.staff.index'))->assertForbidden();
        $this->actingAs($this->userWithRole('receptionist'))->get(route('hr.payroll.index'))->assertForbidden();
    }
}
