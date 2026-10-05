<?php

namespace App\Http\Controllers;

use App\Enums\InventoryTransactionType;
use App\Models\InventoryCategory;
use App\Models\InventoryItem;
use App\Models\InventoryTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class InventoryController extends Controller
{
    public function index(Request $request)
    {
        $query = InventoryItem::with('category')->withCount('transactions');
        $lowStockCount = InventoryItem::whereColumn('current_stock', '<=', 'minimum_stock')->count();

        if ($search = $request->input('search')) {
            $query->where(function ($items) use ($search) {
                $items->where('name', 'like', "%{$search}%")
                    ->orWhere('sku', 'like', "%{$search}%")
                    ->orWhereHas('category', fn ($categories) => $categories->where('name', 'like', "%{$search}%"));
            });
        }

        if ($request->boolean('low_stock')) {
            $query->whereColumn('current_stock', '<=', 'minimum_stock');
        }

        $items = $query->orderBy('name')->paginate(15)->withQueryString();

        return view('inventory.index', compact('items', 'lowStockCount'));
    }

    public function createItem()
    {
        return view('inventory.item-form', [
            'item' => new InventoryItem,
            'categories' => InventoryCategory::orderBy('name')->get(),
        ]);
    }

    public function storeItem(Request $request)
    {
        InventoryItem::create($this->validateItem($request));

        return redirect()->route('inventory.index')->with('success', 'Inventory item created successfully.');
    }

    public function editItem(InventoryItem $item)
    {
        return view('inventory.item-form', [
            'item' => $item,
            'categories' => InventoryCategory::orderBy('name')->get(),
        ]);
    }

    public function updateItem(Request $request, InventoryItem $item)
    {
        $item->update($this->validateItem($request, $item));

        return redirect()->route('inventory.index')->with('success', 'Inventory item updated successfully.');
    }

    public function destroyItem(InventoryItem $item)
    {
        if ($item->transactions()->exists()) {
            return back()->with('error', 'Items with stock transaction history cannot be deleted.');
        }

        $item->delete();

        return redirect()->route('inventory.index')->with('success', 'Inventory item deleted successfully.');
    }

    public function categories()
    {
        $categories = InventoryCategory::withCount('items')->orderBy('name')->get();

        return view('inventory.categories', compact('categories'));
    }

    public function storeCategory(Request $request)
    {
        InventoryCategory::create($request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:inventory_categories,name'],
            'description' => ['nullable', 'string', 'max:2000'],
        ]));

        return redirect()->route('inventory.categories.index')->with('success', 'Category created successfully.');
    }

    public function updateCategory(Request $request, InventoryCategory $category)
    {
        $category->update($request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('inventory_categories', 'name')->ignore($category)],
            'description' => ['nullable', 'string', 'max:2000'],
        ]));

        return redirect()->route('inventory.categories.index')->with('success', 'Category updated successfully.');
    }

    public function destroyCategory(InventoryCategory $category)
    {
        if ($category->items()->exists()) {
            return back()->with('error', 'Categories with inventory items cannot be deleted.');
        }

        $category->delete();

        return redirect()->route('inventory.categories.index')->with('success', 'Category deleted successfully.');
    }

    public function transactions(Request $request)
    {
        $query = InventoryTransaction::with(['item', 'creator']);

        if ($request->filled('type')) {
            $query->where('type', $request->input('type'));
        }

        $transactions = $query->latest()->paginate(20)->withQueryString();

        return view('inventory.transactions', compact('transactions'));
    }

    public function createTransaction(Request $request)
    {
        $type = $request->query('type', InventoryTransactionType::StockIn->value);
        $validTypes = array_map(fn (InventoryTransactionType $case) => $case->value, InventoryTransactionType::cases());
        abort_unless(in_array($type, $validTypes, true), 404);

        return view('inventory.transaction-form', [
            'items' => InventoryItem::orderBy('name')->get(),
            'type' => $type,
        ]);
    }

    public function storeTransaction(Request $request)
    {
        $minimumQuantity = $request->input('type') === InventoryTransactionType::Adjustment->value ? 0 : 0.01;
        $data = $request->validate([
            'item_id' => ['required', 'exists:inventory_items,id'],
            'type' => ['required', Rule::enum(InventoryTransactionType::class)],
            'quantity' => ['required', 'numeric', 'decimal:0,2', 'min:'.$minimumQuantity],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);

        DB::transaction(function () use ($data) {
            $item = InventoryItem::whereKey($data['item_id'])->lockForUpdate()->firstOrFail();
            $before = (float) $item->current_stock;
            $quantity = (float) $data['quantity'];

            $after = match ($data['type']) {
                InventoryTransactionType::StockIn->value => $before + $quantity,
                InventoryTransactionType::StockOut->value => $before - $quantity,
                InventoryTransactionType::Adjustment->value => $quantity,
            };

            if ($after < 0) {
                throw ValidationException::withMessages([
                    'quantity' => 'Stock out quantity cannot exceed the current stock.',
                ]);
            }

            $recordedQuantity = $data['type'] === InventoryTransactionType::Adjustment->value
                ? $after - $before
                : $quantity;

            $item->update(['current_stock' => $after]);
            InventoryTransaction::create([
                'item_id' => $item->id,
                'type' => $data['type'],
                'quantity' => $recordedQuantity,
                'stock_before' => $before,
                'stock_after' => $after,
                'notes' => $data['notes'] ?? null,
                'created_by' => auth()->id(),
            ]);
        });

        return redirect()->route('inventory.transactions.index')->with('success', 'Stock transaction recorded successfully.');
    }

    private function validateItem(Request $request, ?InventoryItem $item = null): array
    {
        return $request->validate([
            'category_id' => ['required', 'exists:inventory_categories,id'],
            'name' => ['required', 'string', 'max:255'],
            'sku' => ['required', 'string', 'max:100', Rule::unique('inventory_items', 'sku')->ignore($item)],
            'unit' => ['required', 'string', 'max:50'],
            'minimum_stock' => ['required', 'numeric', 'decimal:0,2', 'min:0'],
            'description' => ['nullable', 'string', 'max:2000'],
        ]);
    }
}
