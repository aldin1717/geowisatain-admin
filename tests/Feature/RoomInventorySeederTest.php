<?php

namespace Tests\Feature;

use App\Models\Room;
use App\Models\RoomType;
use Database\Seeders\RoomInventorySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoomInventorySeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_floor_plan_replaces_room_inventory_with_categories_and_temporary_rates(): void
    {
        $oldType = RoomType::create([
            'name' => 'Old Type',
            'category' => 'room',
            'slug' => 'old-type',
        ]);
        Room::create([
            'room_number' => 'OLD-101',
            'room_type_id' => $oldType->id,
            'floor' => '1',
            'capacity' => 2,
            'price_per_night' => 100000,
            'status' => 'available',
            'is_active' => true,
        ]);

        $this->seed(RoomInventorySeeder::class);

        $this->assertDatabaseCount('rooms', 127);
        $this->assertDatabaseCount('room_types', 8);
        $this->assertDatabaseMissing('rooms', ['room_number' => 'OLD-101']);
        $this->assertDatabaseMissing('room_types', ['slug' => 'old-type']);
        $this->assertDatabaseMissing('rooms', ['room_number' => '208']);
        $this->assertDatabaseMissing('rooms', ['room_number' => '309']);
        $this->assertDatabaseMissing('rooms', ['room_number' => '323']);

        $this->assertDatabaseHas('rooms', [
            'room_number' => '108',
            'price_per_night' => 300000,
            'status' => 'available',
        ]);
        $this->assertDatabaseHas('rooms', [
            'room_number' => '101',
            'price_per_night' => 400000,
            'status' => 'available',
        ]);
        $this->assertDatabaseHas('rooms', [
            'room_number' => '318',
            'price_per_night' => 0,
            'status' => 'maintenance',
        ]);
    }
}
