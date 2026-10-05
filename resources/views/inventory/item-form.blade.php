@extends('layouts.app')

@section('title', $item->exists ? 'Edit Inventory Item' : 'Add Inventory Item')

@section('content')
    <div class="mb-6">
        <h1 class="text-2xl font-semibold text-stone-900">{{ $item->exists ? 'Edit Inventory Item' : 'Add Inventory Item' }}</h1>
        <p class="mt-1 text-sm text-stone-500">Stock changes must be recorded through Stock In, Stock Out, or Stock Adjustment.</p>
    </div>

    @if($categories->isEmpty())
        <div class="mb-5 rounded-lg border border-amber-300 bg-amber-50 p-4 text-sm text-amber-900">
            Create an inventory category before adding items. <a href="{{ route('inventory.categories.index') }}" class="font-semibold underline">Manage categories</a>.
        </div>
    @endif

    <div class="max-w-2xl bg-white rounded-xl shadow-sm border border-stone-200 p-6">
        <form method="POST" action="{{ $item->exists ? route('inventory.items.update', $item) : route('inventory.items.store') }}" class="space-y-5">
            @csrf
            @if($item->exists)
                @method('PUT')
            @endif

            <div>
                <label for="name" class="block text-sm font-medium text-stone-700 mb-1">Item Name <span class="text-red-500">*</span></label>
                <input id="name" name="name" value="{{ old('name', $item->name) }}" required class="w-full rounded-lg border border-stone-300 px-3 py-2.5 text-sm">
                @error('name')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="sku" class="block text-sm font-medium text-stone-700 mb-1">SKU <span class="text-red-500">*</span></label>
                    <input id="sku" name="sku" value="{{ old('sku', $item->sku) }}" required class="w-full rounded-lg border border-stone-300 px-3 py-2.5 text-sm">
                    @error('sku')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="category_id" class="block text-sm font-medium text-stone-700 mb-1">Category <span class="text-red-500">*</span></label>
                    <select id="category_id" name="category_id" required class="w-full rounded-lg border border-stone-300 px-3 py-2.5 text-sm">
                        <option value="">Select category</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" @selected(old('category_id', $item->category_id) == $category->id)>{{ $category->name }}</option>
                        @endforeach
                    </select>
                    @error('category_id')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="unit" class="block text-sm font-medium text-stone-700 mb-1">Unit <span class="text-red-500">*</span></label>
                    <input id="unit" name="unit" value="{{ old('unit', $item->unit ?: 'pcs') }}" required class="w-full rounded-lg border border-stone-300 px-3 py-2.5 text-sm">
                    @error('unit')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="minimum_stock" class="block text-sm font-medium text-stone-700 mb-1">Low-stock threshold <span class="text-red-500">*</span></label>
                    <input id="minimum_stock" type="number" name="minimum_stock" value="{{ old('minimum_stock', $item->minimum_stock ?? 0) }}" required min="0" step="0.01" class="w-full rounded-lg border border-stone-300 px-3 py-2.5 text-sm">
                    @error('minimum_stock')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
            </div>

            <div>
                <label for="description" class="block text-sm font-medium text-stone-700 mb-1">Description</label>
                <textarea id="description" name="description" rows="3" class="w-full rounded-lg border border-stone-300 px-3 py-2.5 text-sm">{{ old('description', $item->description) }}</textarea>
                @error('description')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>

            <div class="flex justify-end gap-3 border-t border-stone-200 pt-5">
                <a href="{{ route('inventory.index') }}" class="rounded-lg border border-stone-300 px-4 py-2 text-sm">Cancel</a>
                <button type="submit" @disabled($categories->isEmpty()) class="rounded-lg bg-primary-700 px-5 py-2 text-sm font-medium text-white disabled:opacity-50">Save Item</button>
            </div>
        </form>
    </div>
@endsection
