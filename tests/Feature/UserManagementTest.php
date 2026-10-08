<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_super_admin_can_create_update_and_deactivate_users(): void
    {
        $adminRole = Role::create(['name' => 'Admin', 'slug' => 'admin']);
        $staffRole = Role::create(['name' => 'Receptionist', 'slug' => 'receptionist']);
        $admin = User::factory()->create(['role_id' => $adminRole->id]);
        $this->actingAs($admin);

        $this->get(route('users.index'))->assertOk()->assertSee('Users');
        $this->get(route('users.create'))->assertOk()->assertSee('Add User');
        $this->post(route('users.store'), [
            'name' => 'Front Desk',
            'email' => 'frontdesk@example.test',
            'role_id' => $staffRole->id,
            'password' => 'secure-password',
            'password_confirmation' => 'secure-password',
        ])->assertRedirect(route('users.index'));

        $user = User::where('email', 'frontdesk@example.test')->firstOrFail();
        $this->assertTrue($user->is_active);
        $this->assertNotSame('secure-password', $user->password);

        $this->put(route('users.update', $user), [
            'name' => 'Reception Team',
            'email' => 'frontdesk@example.test',
            'role_id' => $staffRole->id,
            'password' => '',
            'password_confirmation' => '',
            'is_active' => '1',
        ])->assertRedirect(route('users.index'));
        $this->assertSame('Reception Team', $user->fresh()->name);

        $this->post(route('users.deactivate', $user))->assertRedirect(route('users.index'));
        $this->assertFalse($user->fresh()->is_active);
    }

    public function test_only_super_admin_can_access_user_management(): void
    {
        $staffRole = Role::create(['name' => 'Receptionist', 'slug' => 'receptionist']);
        $staff = User::factory()->create(['role_id' => $staffRole->id]);

        $this->actingAs($staff)->get(route('users.index'))->assertForbidden();
    }

    public function test_super_admin_cannot_deactivate_themselves_or_the_last_active_super_admin(): void
    {
        $adminRole = Role::create(['name' => 'Admin', 'slug' => 'admin']);
        $admin = User::factory()->create(['role_id' => $adminRole->id]);

        $this->actingAs($admin)
            ->post(route('users.deactivate', $admin))
            ->assertRedirect()
            ->assertSessionHas('error');

        $this->assertTrue($admin->fresh()->is_active);

        $secondAdmin = User::factory()->create(['role_id' => $adminRole->id]);
        $this->post(route('users.deactivate', $admin))
            ->assertRedirect(route('users.index'));
        $this->assertFalse($admin->fresh()->is_active);
        $this->assertTrue($secondAdmin->fresh()->is_active);
    }

    public function test_disabled_users_are_logged_out_on_their_next_request(): void
    {
        $adminRole = Role::create(['name' => 'Admin', 'slug' => 'admin']);
        $user = User::factory()->create(['role_id' => $adminRole->id, 'is_active' => false]);

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertRedirect(route('login'))
            ->assertSessionHasErrors('email');
    }
}
