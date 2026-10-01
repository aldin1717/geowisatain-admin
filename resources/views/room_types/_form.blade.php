@php
    $roomType = $roomType ?? null;
@endphp

<div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-6">
    {{-- Name --}}
    <div class="md:col-span-2">
        <label for="name" class="block text-sm font-medium text-stone-700 mb-1.5">Name <span class="text-red-500">*</span></label>
        <input type="text" name="name" id="name" value="{{ old('name', $roomType?->name) }}" required
            class="block w-full rounded-lg border border-stone-300 px-3 py-2.5 text-sm text-stone-900 placeholder:text-stone-400 focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
            placeholder="e.g. Deluxe Double">
        @error('name')
            <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    {{-- Base Price --}}
    <div>
        <label for="base_price" class="block text-sm font-medium text-stone-700 mb-1.5">Base Price (Rp) <span class="text-red-500">*</span></label>
        <input type="number" name="base_price" id="base_price" value="{{ old('base_price', $roomType ? (int)$roomType->base_price : '') }}" required min="0" step="1"
            class="block w-full rounded-lg border border-stone-300 px-3 py-2.5 text-sm text-stone-900 placeholder:text-stone-400 focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
            placeholder="e.g. 500000">
        @error('base_price')
            <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    {{-- Capacity --}}
    <div>
        <label for="capacity" class="block text-sm font-medium text-stone-700 mb-1.5">Capacity (Persons) <span class="text-red-500">*</span></label>
        <input type="number" name="capacity" id="capacity" value="{{ old('capacity', $roomType?->capacity) }}" required min="1"
            class="block w-full rounded-lg border border-stone-300 px-3 py-2.5 text-sm text-stone-900 placeholder:text-stone-400 focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
            placeholder="e.g. 2">
        @error('capacity')
            <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    {{-- Description --}}
    <div class="md:col-span-2">
        <label for="description" class="block text-sm font-medium text-stone-700 mb-1.5">Description</label>
        <textarea name="description" id="description" rows="3"
            class="block w-full rounded-lg border border-stone-300 px-3 py-2.5 text-sm text-stone-900 placeholder:text-stone-400 focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
            placeholder="Describe the room type and its features...">{{ old('description', $roomType?->description) }}</textarea>
        @error('description')
            <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    {{-- Image --}}
    <div class="md:col-span-2">
        <label class="block text-sm font-medium text-stone-700 mb-1.5">Image</label>
        @if($roomType?->image)
            <div class="mb-3">
                <img src="{{ Storage::url($roomType->image) }}" alt="Current Image" class="w-32 h-32 object-cover rounded-lg border border-stone-200">
            </div>
        @endif
        <input type="file" name="image" id="image" accept="image/*"
            class="block w-full text-sm text-stone-500 file:mr-4 file:py-2.5 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-stone-50 file:text-stone-700 hover:file:bg-stone-100 border border-stone-300 rounded-lg">
        @error('image')
            <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    {{-- Is Active --}}
    <div class="md:col-span-2">
        <div class="flex items-center">
            <input type="checkbox" name="is_active" id="is_active" value="1" @checked(old('is_active', $roomType?->is_active ?? true))
                class="w-4 h-4 rounded border-stone-300 text-primary-600 focus:ring-primary-500">
            <label for="is_active" class="ml-2 text-sm font-medium text-stone-700">Active</label>
        </div>
        <p class="mt-1 text-xs text-stone-500">Inactive room types won't be available for new bookings.</p>
        @error('is_active')
            <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>
</div>
