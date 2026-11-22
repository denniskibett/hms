<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\Expense;
use App\Models\Payroll;
use App\Models\Role;
use App\Models\StaffProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Payroll, expenses and staff records follow Breve's journeys. The payslip figures here were worked out by
 * Breve's own engine (App\Services\Payroll\KenyaPayroll in brevemaster, 2026 Kenyan rates), so the hotel and
 * Breve give the same answer for the same person.
 */
class BreveJourneysTest extends TestCase
{
    use RefreshDatabase;

    private User $hr;
    private User $manager;
    private User $finance;
    private Department $dept;

    protected function setUp(): void
    {
        parent::setUp();
        $this->hr = $this->userWithRole('hr');
        $this->manager = $this->userWithRole('manager');
        $this->finance = $this->userWithRole('finance');
        $this->dept = Department::create(['name' => 'Kitchen', 'code' => 'KIT']);
    }

    private function userWithRole(string $role): User
    {
        $user = User::factory()->create();
        $user->roles()->attach(Role::firstOrCreate(['name' => $role])->id);

        return $user;
    }

    private function hire(string $name, array $pay): StaffProfile
    {
        $this->actingAs($this->hr)->postJson(route('hr.staff.store'), array_merge([
            'name' => $name,
            'phone' => '07' . random_int(10000000, 99999999),
            'department_id' => $this->dept->id,
            'job_title' => 'Cook',
            'kra_pin' => 'A123456789B',
            'hire_date' => today()->subYear()->toDateString(),
            'employment_status' => 'permanent',
        ], $pay))->assertSuccessful();

        return StaffProfile::whereHas('user', fn ($q) => $q->where('name', $name))->sole();
    }

    private function prepare(): string
    {
        $month = now()->format('Y-m');
        $this->actingAs($this->hr)->post(route('hr.payroll.generate'), ['month' => $month])->assertSessionHas('success');

        return $month;
    }

    private function payslipFor(StaffProfile $staff): Payroll
    {
        return Payroll::where('user_id', $staff->user_id)->sole();
    }

    // ---- Staff records ------------------------------------------------------

    public function test_staff_record_keeps_named_allowances_deductions_and_pay_method(): void
    {
        $staff = $this->hire('Achieng Otieno', [
            'salary' => 40000, 'house_allowance' => 6000, 'transport_allowance' => 3500, 'sacco_deduction' => 2000,
            'insurance_premium' => 1500, 'pay_method' => 'mpesa', 'mpesa_phone' => '0712345678',
        ]);

        $this->assertSame([['name' => 'House allowance', 'amount' => 6000], ['name' => 'Transport', 'amount' => 3500]], $staff->allowances);
        $this->assertSame([['name' => 'SACCO', 'amount' => 2000]], $staff->deductions);
        $this->assertSame('mpesa', $staff->pay_method);
        $this->assertEquals(3500, $staff->formData()['transport_allowance']);   // the edit form gets them back

        // M-Pesa needs a number; a KRA PIN has to look like one
        $this->actingAs($this->hr)->postJson(route('hr.staff.store'), ['name' => 'X', 'phone' => '0700000000', 'salary' => 1, 'hire_date' => today()->toDateString(), 'employment_status' => 'probation', 'pay_method' => 'mpesa', 'kra_pin' => '12345'])
            ->assertUnprocessable()->assertJsonValidationErrors(['mpesa_phone', 'kra_pin']);
    }

    // ---- Payroll --------------------------------------------------------------

    public function test_payslips_match_breve_for_the_same_people(): void
    {
        $achieng = $this->hire('Achieng Otieno', ['salary' => 40000, 'house_allowance' => 6000, 'transport_allowance' => 3500, 'sacco_deduction' => 2000]);
        $boss = $this->hire('Kamau Njoroge', ['salary' => 150000, 'insurance_premium' => 4000]);
        $junior = $this->hire('Wafula Simiyu', ['salary' => 12000]);
        $this->prepare();

        // Breve KenyaPayroll::compute(40000, [6000, 3500], [2000]) and the others, run in brevemaster
        $a = $this->payslipFor($achieng);
        $this->assertEquals(49500, $a->gross);
        $this->assertEquals(2970, $a->nssf);
        $this->assertEquals(1361.25, $a->shif);
        $this->assertEquals(742.5, $a->housing_levy);
        $this->assertEquals(5711.23, $a->paye);
        $this->assertEquals(36715.02, $a->net);
        $this->assertEquals(44426.25, $a->breakdown['taxable_pay']);
        $this->assertSame(['House allowance', 'Transport'], array_column($a->allowanceLines(), 'name'));
        $this->assertSame(['SACCO'], array_column($a->deductionLines(), 'name'));

        $b = $this->payslipFor($boss);
        $this->assertEquals(6480, $b->nssf);            // NSSF Year 4 cap: 6% of 108,000
        $this->assertEquals(32926.85, $b->paye);        // includes 600 insurance relief (15% of 4,000)
        $this->assertEquals(600, $b->insurance_relief);
        $this->assertEquals(104218.15, $b->net);
        $this->assertEquals(150000 + 6480 + 2250, $b->employer_cost);

        $c = $this->payslipFor($junior);
        $this->assertEquals(0, $c->paye);
        $this->assertEquals(10770, $c->net);

        $this->actingAs($this->hr)->get(route('hr.payroll.payslip', $a))->assertOk()->assertSee('House allowance')->assertSee('SACCO')->assertSee('Taxable pay');
    }

    public function test_stipend_has_no_statutory_lines_and_stays_off_the_returns(): void
    {
        $intern = $this->hire('Intern Njeri', ['salary' => 15000, 'pay_basis' => 'stipend', 'loan_deduction' => 1000, 'pay_method' => 'mpesa', 'mpesa_phone' => '0722000000']);
        $cook = $this->hire('Cook Otieno', ['salary' => 30000, 'bank_name' => 'KCB', 'account_number' => '1100223344']);
        $month = $this->prepare();

        $slip = $this->payslipFor($intern);
        $this->assertEquals(0, $slip->tax_deductions);
        $this->assertEquals(14000, $slip->net);
        $this->assertTrue($slip->breakdown['stipend']);

        $statutory = $this->actingAs($this->finance)->get(route('hr.payroll.export', ['month' => $month, 'type' => 'statutory']))->assertOk()->streamedContent();
        $this->assertStringContainsString('Cook Otieno', $statutory);
        $this->assertStringNotContainsString('Intern Njeri', $statutory);

        $bulk = $this->actingAs($this->finance)->get(route('hr.payroll.export', ['month' => $month, 'type' => 'bulk']))->assertOk()->streamedContent();
        $this->assertStringContainsString('"Intern Njeri",M-Pesa,0722000000,,14000.00', $bulk);
        $this->assertStringContainsString('"Cook Otieno",Bank,1100223344,KCB', $bulk);
    }

    public function test_payroll_run_approve_pay_and_finance_sees_the_cost(): void
    {
        $this->hire('Achieng Otieno', ['salary' => 40000, 'house_allowance' => 6000, 'transport_allowance' => 3500]);
        $month = $this->prepare();

        $this->actingAs($this->finance)->post(route('hr.payroll.status'), ['status' => 'approved', 'month' => $month])->assertForbidden();
        $this->actingAs($this->manager)->post(route('hr.payroll.status'), ['status' => 'approved', 'month' => $month])->assertSessionHas('success');
        $this->actingAs($this->finance)->post(route('hr.payroll.status'), ['status' => 'paid', 'month' => $month])->assertSessionHas('success');

        $page = $this->actingAs($this->finance)->get(route('hr.payroll.index', ['month' => $month]))->assertOk()->assertSee('Cost to the hotel')->assertSee('NSSF Year 4');
        $this->assertEquals(49500 + 2970 + 742.5, $page->viewData('totals')['employer_cost']);

        $finance = $this->actingAs($this->finance)->get(route('finance.index'))->assertOk()->viewData('out');
        $this->assertEquals(53212.5, $finance['payroll']);
    }

    // ---- Expenses ---------------------------------------------------------------

    public function test_expense_request_approve_pay_with_receipt(): void
    {
        Storage::fake('local');
        $cook = $this->userWithRole('kitchen');

        // A cook asks; ticking "already paid" does nothing for staff outside finance
        $this->actingAs($cook)->post(route('expenses.store'), [
            'spent_on' => today()->toDateString(), 'category' => 'repairs', 'description' => 'Gas cylinder refill',
            'amount' => 3400, 'paid_now' => '1', 'receipt' => UploadedFile::fake()->image('receipt.jpg'),
        ], ['Accept' => 'application/json'])->assertCreated()->assertJsonPath('message', fn ($m) => str_contains($m, 'sent for approval'));

        $expense = Expense::sole();
        $this->assertSame('pending', $expense->status);
        Storage::disk('local')->assertExists($expense->receipt_path);
        $this->actingAs($cook)->get(route('expenses.receipt', $expense))->assertOk();
        $this->actingAs($this->userWithRole('kitchen'))->get(route('expenses.receipt', $expense))->assertForbidden();

        // Only a manager approves; finance can't pay it before that
        $this->actingAs($cook)->post(route('expenses.approve', $expense), ['decision' => 'approve'])->assertForbidden();
        $this->actingAs($this->finance)->post(route('expenses.pay', $expense), ['method' => 'cash'])->assertSessionHas('error');
        $this->actingAs($this->manager)->post(route('expenses.approve', $expense), ['decision' => 'approve'])->assertSessionHas('success');
        $this->assertSame('approved', $expense->fresh()->status);

        $this->actingAs($this->finance)->post(route('expenses.pay', $expense), ['method' => 'mobile_money'])->assertSessionHasErrors('reference');
        $this->actingAs($this->finance)->post(route('expenses.pay', $expense), ['method' => 'mobile_money', 'reference' => 'TKL9XY2'])->assertSessionHas('success');
        $paid = $expense->fresh();
        $this->assertSame('paid', $paid->status);
        $this->assertNotNull($paid->paid_at);
        $this->assertSame($this->manager->id, $paid->approved_by);

        $this->actingAs($this->finance)->get(route('expenses.show', $paid))->assertOk()->assertSee('Approved by')->assertSee('TKL9XY2');
        $this->assertEquals(3400, $this->actingAs($this->finance)->get(route('finance.index'))->viewData('out')['expenses']);

        // The cook sees their own expense only
        $this->actingAs($cook)->get(route('expenses.show', $paid))->assertOk();
        $this->actingAs($this->userWithRole('housekeeping'))->get(route('expenses.show', $paid))->assertForbidden();
    }

    public function test_rejected_expense_needs_a_reason_and_cannot_be_paid(): void
    {
        $expense = Expense::create(['expense_number' => 'EXP-T-1', 'spent_on' => today(), 'category' => 'other', 'description' => 'Team lunch', 'amount' => 9000, 'status' => 'pending', 'created_by' => $this->hr->id]);

        $this->actingAs($this->manager)->post(route('expenses.approve', $expense), ['decision' => 'reject'])->assertSessionHasErrors('reason');
        $this->actingAs($this->manager)->post(route('expenses.approve', $expense), ['decision' => 'reject', 'reason' => 'Not budgeted'])->assertSessionHas('success');
        $this->assertSame('rejected', $expense->fresh()->status);
        $this->actingAs($this->manager)->post(route('expenses.pay', $expense), ['method' => 'cash'])->assertSessionHas('error');
    }

    public function test_finance_records_an_expense_already_paid_in_one_step(): void
    {
        $this->actingAs($this->finance)->postJson(route('expenses.store'), [
            'spent_on' => today()->toDateString(), 'category' => 'utilities', 'description' => 'Water bill',
            'amount' => 7800, 'paid_now' => true, 'method' => 'bank_transfer',
        ])->assertUnprocessable()->assertJsonValidationErrors('reference');

        $this->actingAs($this->finance)->postJson(route('expenses.store'), [
            'spent_on' => today()->toDateString(), 'category' => 'utilities', 'description' => 'Water bill',
            'amount' => 7800, 'paid_now' => true, 'method' => 'bank_transfer', 'reference' => 'FT2611',
        ])->assertCreated();

        $this->assertSame('paid', Expense::sole()->status);
        $this->actingAs($this->finance)->get(route('expenses.index'))->assertOk()->assertSee('Water bill')->assertSee('Paid this month');
    }
}
