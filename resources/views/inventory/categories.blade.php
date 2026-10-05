@extends('layouts.app')

@section('title', 'Inventory Categories')

@section('content')
    <div class="mb-6">
        <h1 class="text-2xl font-semibold text-stone-900">Inventory Categories</h1>
        <p class="mt-1 text-sm text-stone-500">Organize stock items into categories.</p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="bg-white rounded-xl shadow-sm border border-stone-200 p-6 h-fit">
            <h2 class="font-semibold text-stone-900 mb-4">Add Category</h2>
            <form method="POST" action="{{ route('inventory.categories.store') }}" class="space-y-4">
                @csrf
                <div>
                    <label for="name" class="block text-sm font-medium text-stone-700 mb-1">Name <span class="text-red-500">*</span></label>
                    <input id="name" name="name" value="{{ old('name') }}" required class="w-full rounded-lg border border-stone-300 px-3 py-2.5 text-sm">
                    @error('name')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="description" class="block text-sm font-medium text-stone-700 mb-1">Description</label>
                    <textarea id="description" name="description" rows="3" class="w-full rounded-lg border border-stone-300 px-3 py-2.5 text-sm">{{ old('description') }}</textarea>
                    @error('description')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
                <button class="rounded-lg bg-primary-700 px-4 py-2 text-sm font-medium text-white">Add Category</button>
            </form>
        </div>

        <div class="lg:col-span-2 bg-white rounded-xl shadow-sm border border-stone-200 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left">
                    <thead class="bg-stone-50 border-b border-stone-200">
                        <tr>
                            <th class="px-5 py-3 text-xs font-semibold uppercase text-stone-500">Category</th>
                            <th class="px-5 py-3 text-xs font-semibold uppercase text-stone-500">Items</th>
                            <th class="px-5 py-3 text-xs font-semibold uppercase text-stone-500 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-stone-100">
                        @forelse($categories as $category)
                            <tr>
                                <td class="px-5 py-4">
                                    <form id="category-{{ $category->id }}" method="POST" action="{{ route('inventory.categories.update', $category) }}" class="space-y-2">
                                        @csrf
                                        @method('PUT')
                                        <input name="name" value="{{ $category->name }}" required class="w-full rounded border border-stone-300 px-2 py-1 font-medium">
                                        <textarea name="description" rows="1" class="w-full rounded border border-stone-300 px-2 py-1 text-xs">{{ $category->description }}</textarea>
                                    </form>
                                </td>
                                <td class="px-5 py-4 text-stone-600">{{ $category->items_count }}</td>
                                <td class="px-5 py-4 text-right whitespace-nowrap">
                                    <button form="category-{{ $category->id }}" class="text-primary-700 hover:underline">Save</button>
                                    @if($category->items_count === 0)
                                        <form method="POST" action="{{ route('inventory.categories.destroy', $category) }}" class="inline ml-3" onsubmit="return confirm('Delete this category?')">
                                            @csrf
                                            @method('DELETE')
                                            <button class="text-red-600 hover:underline">Delete</button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="3" class="px-5 py-10 text-center text-stone-500">No categories yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
