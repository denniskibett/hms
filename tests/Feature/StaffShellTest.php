<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\Room;
use App\Models\RoomType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StaffShellTest extends TestCase
{
    use RefreshDatabase;

    private function userWithRole(string $role): User
    {
        $user = User::factory()->create();
        $user->roles()->attach(Role::firstOrCreate(['name' => $role])->id);

        return $user;
    }

    public function test_login_page_has_no_fake_social_sign_in(): void
    {
        $this->get(route('login'))->assertOk()
            ->assertSee('Sign in')
            ->assertDontSee('Sign in with Google')
            ->assertDontSee('formbold.com');
    }

    public function test_search_only_shows_what_the_role_can_open(): void
    {
        Room::factory()->create(['room_number' => '204', 'room_type_id' => RoomType::factory()->create()->id]);

        $this->actingAs($this->userWithRole('housekeeping'))
            ->get(route('search', ['q' => '204']))->assertOk()->assertSee('Room 204');

        // Kitchen staff can't open rooms, so rooms aren't listed for them
        $this->actingAs($this->userWithRole('kitchen'))
            ->get(route('search', ['q' => '204']))->assertOk()->assertDontSee('Room 204');

        // No term: just the search box
        $this->actingAs($this->userWithRole('manager'))->get(route('search'))->assertOk();
    }

    public function test_header_search_stays_inside_the_app(): void
    {
        $this->actingAs($this->userWithRole('manager'))
            ->get(route('dashboard'))->assertOk()
            ->assertDontSee('formbold.com')
            ->assertSee(route('search'), false);
    }
}
