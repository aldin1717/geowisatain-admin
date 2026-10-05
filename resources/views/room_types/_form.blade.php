<div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-6">
    <div class="md:col-span-2">
        <label for="name" class="block text-sm font-medium text-stone-700 mb-1.5">Name <span class="text-red-500">*</span></label>
        <input type="text" name="name" id="name" value="{{ old('name', $roomType->name ?? '') }}" required
            class="block w-full rounded-lg border border-stone-300 px-3 py-2.5 text-sm text-stone-900 placeholder:text-stone-400 focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
            placeholder="e.g. Deluxe Double">
        @error('name')
            <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="category" class="block text-sm font-medium text-stone-700 mb-1.5">Category <span class="text-red-500">*</span></label>
        <select name="category" id="category" required
            class="block w-full rounded-lg border border-stone-300 px-3 py-2.5 text-sm text-stone-900 focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
            <option value="room" @selected(old('category', $roomType->category ?? 'room') === 'room')>Room</option>
            <option value="ballroom" @selected(old('category', $roomType->category ?? 'room') === 'ballroom')>Ballroom</option>
        </select>
        @error('category')
            <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div class="md:col-span-2">
        <label for="description" class="block text-sm font-medium text-stone-700 mb-1.5">Description</label>
        <textarea name="description" id="description" rows="3"
            class="block w-full rounded-lg border border-stone-300 px-3 py-2.5 text-sm text-stone-900 placeholder:text-stone-400 focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
            placeholder="Describe the room type and its features...">{{ old('description', $roomType->description ?? '') }}</textarea>
        @error('description')
            <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

</div>
