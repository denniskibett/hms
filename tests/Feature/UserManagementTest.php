<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class UserManagementTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = $this->userWithRole('admin');
    }

    private function userWithRole(string ...$roles): User
    {
        $user = User::factory()->create();
        foreach ($roles as $role) {
            $user->roles()->attach(Role::firstOrCreate(['name' => $role])->id);
        }

        return $user;
    }

    private function payload(User $user, array $overrides = []): array
    {
        return array_merge([
            'name' => $user->name, 'email' => $user->email, 'phone' => $user->phone,
            'status' => $user->fresh()->status, 'roles' => $user->roles()->pluck('name')->all(),
        ], $overrides);
    }

    public function test_admin_creates_an_account_that_can_sign_in(): void
    {
        Role::firstOrCreate(['name' => 'finance']);

        $this->actingAs($this->admin)->postJson(route('users.store'), [
            'name' => 'Mary Accounts', 'email' => 'mary@example.com', 'roles' => ['finance'], 'status' => 'active', 'password' => 'long-password-1',
        ])->assertCreated();

        $mary = User::where('email', 'mary@example.com')->first();
        $this->assertTrue($mary->hasRole('finance'));

        $this->post('/logout');
        $this->post(route('login'), ['email' => 'mary@example.com', 'password' => 'long-password-1']);
        $this->assertAuthenticatedAs($mary);
    }

    public function test_staff_accounts_need_an_email_and_a_role(): void
    {
        $this->actingAs($this->admin)->postJson(route('users.store'), [
            'name' => 'X', 'roles' => ['kitchen'], 'status' => 'active', 'password' => 'long-password-1',
        ])->assertJsonValidationErrors('email');

        $this->actingAs($this->admin)->postJson(route('users.store'), [
            'name' => 'X', 'email' => 'x@example.com', 'roles' => [], 'status' => 'active', 'password' => 'long-password-1',
        ])->assertJsonValidationErrors('roles');
    }

    public function test_edit_roles_status_and_password(): void
    {
        $cook = $this->userWithRole('kitchen');
        Role::firstOrCreate(['name' => 'procurement']);

        $this->actingAs($this->admin)->putJson(route('users.update', $cook), $this->payload($cook, [
            'roles' => ['kitchen', 'procurement'], 'password' => 'brand-new-pass',
        ]))->assertOk();

        $cook->refresh();
        $this->assertEqualsCanonicalizing(['kitchen', 'procurement'], $cook->roles->pluck('name')->all());
        $this->assertTrue(Hash::check('brand-new-pass', $cook->password));

        // Suspending blocks sign-in
        $this->actingAs($this->admin)->putJson(route('users.update', $cook), $this->payload($cook, ['status' => 'suspended']))->assertOk();
        $this->post('/logout');
        $this->post(route('login'), ['email' => $cook->email, 'password' => 'brand-new-pass']);
        $this->assertGuest();
    }

    public function test_admins_cannot_lock_themselves_or_the_hotel_out(): void
    {
        Role::firstOrCreate(['name' => 'manager']);

        // Own admin role
        $this->actingAs($this->admin)->putJson(route('users.update', $this->admin), $this->payload($this->admin, ['roles' => ['manager']]))
            ->assertJsonValidationErrors('roles');

        // The only other admin can be demoted while this one remains
        $other = $this->userWithRole('admin');
        $this->actingAs($this->admin)->putJson(route('users.update', $other), $this->payload($other, ['roles' => ['manager']]))->assertOk();

        // But not the last active admin, by someone else with the role (e.g. a second admin later deactivated)
        $this->admin->forceFill(['status' => 'inactive'])->save();
        $third = $this->userWithRole('admin');
        $this->actingAs($third)->putJson(route('users.update', $third), $this->payload($third, ['status' => 'inactive']))->assertJsonValidationErrors('roles');
    }

    public function test_delete_only_unused_accounts(): void
    {
        $unused = $this->userWithRole('kitchen');
        $this->actingAs($this->admin)->deleteJson(route('users.destroy', $unused))->assertOk();
        $this->assertModelMissing($unused);

        $this->actingAs($this->admin)->deleteJson(route('users.destroy', $this->admin))->assertUnprocessable();

        $hired = $this->userWithRole('kitchen');
        $hired->staffProfile()->create(['salary' => 1, 'hire_date' => today(), 'employment_status' => 'permanent']);
        $this->actingAs($this->admin)->deleteJson(route('users.destroy', $hired))->assertUnprocessable();
    }

    public function test_pages_and_access(): void
    {
        $guest = $this->userWithRole('guest');

        $this->actingAs($this->admin)->get(route('users.index'))->assertOk()->assertDontSee($guest->email);
        $this->actingAs($this->admin)->get(route('users.index', ['role' => 'guest']))->assertOk()->assertSee($guest->email);
        $this->actingAs($this->admin)->get(route('users.show', $guest))->assertOk();
        $this->actingAs($this->userWithRole('manager'))->get(route('users.index'))->assertForbidden();
    }
}
