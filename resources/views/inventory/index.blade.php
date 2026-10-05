@extends('layouts.app')

@section('title', 'Inventory Items')

@section('content')
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-semibold text-stone-900">Inventory Items</h1>
            <p class="mt-1 text-sm text-stone-500">Manage warehouse items and current stock.</p>
        </div>
        <a href="{{ route('inventory.items.create') }}" class="inline-flex items-center gap-2 px-4 py-2.5 bg-primary-700 text-white text-sm font-medium rounded-lg hover:bg-primary-600 transition-colors">Add Item</a>
    </div>

    @if($lowStockCount > 0)
        <a href="{{ route('inventory.index', ['low_stock' => 1]) }}" class="block mb-5 rounded-lg border border-amber-300 bg-amber-50 px-4 py-3 text-sm text-amber-900">
            Low-stock alert: {{ $lowStockCount }} item(s) are at or below their minimum stock level.
        </a>
    @endif

    <form method="GET" action="{{ route('inventory.index') }}" class="mb-5 flex flex-wrap gap-3">
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Search item, SKU, or category"
            class="block w-full max-w-md rounded-lg border border-stone-300 px-3 py-2 text-sm text-stone-900">
        @if(request('low_stock'))
            <input type="hidden" name="low_stock" value="1">
        @endif
        <button class="px-4 py-2 bg-stone-100 text-stone-700 text-sm font-medium rounded-lg border border-stone-300">Search</button>
        @if(request('search') || request('low_stock'))
            <a href="{{ route('inventory.index') }}" class="px-4 py-2 text-sm text-stone-500">Clear</a>
        @endif
    </form>

    <div class="bg-white rounded-xl shadow-sm border border-stone-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead class="bg-stone-50 border-b border-stone-200">
                    <tr>
                        <th class="px-6 py-3 text-xs font-semibold text-stone-500 uppercase">Item / SKU</th>
                        <th class="px-6 py-3 text-xs font-semibold text-stone-500 uppercase">Category</th>
                        <th class="px-6 py-3 text-xs font-semibold text-stone-500 uppercase">Current Stock</th>
                        <th class="px-6 py-3 text-xs font-semibold text-stone-500 uppercase">Minimum</th>
                        <th class="px-6 py-3 text-xs font-semibold text-stone-500 uppercase text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-stone-100">
                    @forelse($items as $item)
                        <tr>
                            <td class="px-6 py-4">
                                <div class="font-medium text-stone-900">{{ $item->name }}</div>
                                <div class="text-xs text-stone-500">{{ $item->sku }}</div>
                            </td>
                            <td class="px-6 py-4 text-stone-600">{{ $item->category->name }}</td>
                            <td class="px-6 py-4">
                                <span class="{{ $item->isLowStock() ? 'text-amber-700 font-semibold' : 'text-stone-900' }}">{{ number_format((float) $item->current_stock, 2) }} {{ $item->unit }}</span>
                                @if($item->isLowStock())
                                    <span class="ml-2 rounded bg-amber-100 px-2 py-0.5 text-xs text-amber-800">Low</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-stone-600">{{ number_format((float) $item->minimum_stock, 2) }} {{ $item->unit }}</td>
                            <td class="px-6 py-4 text-right whitespace-nowrap">
                                <a href="{{ route('inventory.items.edit', $item) }}" class="text-primary-700 hover:underline">Edit</a>
                                @if($item->transactions_count === 0)
                                    <form method="POST" action="{{ route('inventory.items.destroy', $item) }}" class="inline ml-3" onsubmit="return confirm('Delete this item?')">
                                        @csrf
                                        @method('DELETE')
                                        <button class="text-red-600 hover:underline">Delete</button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-6 py-12 text-center text-stone-500">No inventory items found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($items->hasPages())
            <div class="px-6 py-4 border-t border-stone-200">{{ $items->links() }}</div>
        @endif
    </div>
@endsection
