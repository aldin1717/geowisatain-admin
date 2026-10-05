<?php

namespace Tests\Feature;

use App\Models\InventoryCategory;
use App\Models\InventoryItem;
use App\Models\InventoryTransaction;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InventoryManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_warehouse_staff_can_manage_items_and_record_stock_movements(): void
    {
        $role = Role::create(['name' => 'Warehouse', 'slug' => 'warehouse']);
        $user = User::factory()->create(['role_id' => $role->id]);
        $this->actingAs($user);

        $category = InventoryCategory::create(['name' => 'Housekeeping']);
        $this->get(route('inventory.categories.index'))->assertOk()->assertSee('Housekeeping');

        $this->post(route('inventory.items.store'), [
            'category_id' => $category->id,
            'name' => 'Bath towels',
            'sku' => 'TOWEL-01',
            'unit' => 'pcs',
            'minimum_stock' => 2,
        ])->assertRedirect(route('inventory.index'));

        $item = InventoryItem::where('sku', 'TOWEL-01')->firstOrFail();

        $this->get(route('inventory.transactions.create', ['type' => 'stock_in']))->assertOk();
        $this->post(route('inventory.transactions.store'), [
            'item_id' => $item->id,
            'type' => 'stock_in',
            'quantity' => 5,
            'notes' => 'Opening shipment',
        ])->assertRedirect(route('inventory.transactions.index'));

        $item->refresh();
        $this->assertSame('5.00', $item->current_stock);
        $this->assertDatabaseHas('inventory_transactions', [
            'item_id' => $item->id,
            'type' => 'stock_in',
            'stock_before' => '0.00',
            'stock_after' => '5.00',
            'created_by' => $user->id,
        ]);

        $this->post(route('inventory.transactions.store'), [
            'item_id' => $item->id,
            'type' => 'stock_out',
            'quantity' => 6,
        ])->assertSessionHasErrors('quantity');
        $this->assertSame('5.00', $item->fresh()->current_stock);

        $this->post(route('inventory.transactions.store'), [
            'item_id' => $item->id,
            'type' => 'adjustment',
            'quantity' => 0,
            'notes' => 'Physical count',
        ])->assertRedirect(route('inventory.transactions.index'));

        $this->assertSame('0.00', $item->fresh()->current_stock);
        $this->assertSame(2, InventoryTransaction::where('item_id', $item->id)->count());

        $this->get(route('inventory.index', ['low_stock' => 1]))
            ->assertOk()
            ->assertSee('Bath towels')
            ->assertSee('Low-stock alert');
    }

    public function test_receptionists_cannot_access_inventory(): void
    {
        $role = Role::create(['name' => 'Receptionist', 'slug' => 'receptionist']);
        $user = User::factory()->create(['role_id' => $role->id]);

        $this->actingAs($user)->get(route('inventory.index'))->assertForbidden();
    }
}
