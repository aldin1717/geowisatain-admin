<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\RoomType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoomTypeManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_room_types_only_require_a_name_and_description(): void
    {
        $role = Role::create(['name' => 'Admin', 'slug' => 'admin']);
        $user = User::factory()->create(['role_id' => $role->id]);
        $this->actingAs($user);

        $this->get(route('room-types.create'))
            ->assertOk()
            ->assertDontSee('base_price')
            ->assertDontSee('name="capacity"')
            ->assertDontSee('name="is_active"')
            ->assertDontSee('name="image"');

        $this->post(route('room-types.store'), [
            'name' => 'Family Suite',
            'description' => 'A large room for families.',
        ])->assertRedirect(route('room-types.index'));

        $roomType = RoomType::where('slug', 'family-suite')->firstOrFail();
        $this->get(route('room-types.show', $roomType))
            ->assertOk()
            ->assertSee('Family Suite')
            ->assertSee('A large room for families.')
            ->assertDontSee('Base Price')
            ->assertDontSee('Capacity');
    }
}
