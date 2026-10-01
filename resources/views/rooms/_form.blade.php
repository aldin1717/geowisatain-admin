@php
    $room = $room ?? null;
@endphp

<div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-6" x-data="roomForm()">
    {{-- Room Number --}}
    <div>
        <label for="room_number" class="block text-sm font-medium text-stone-700 mb-1.5">Room Number <span class="text-red-500">*</span></label>
        <input type="text" name="room_number" id="room_number" value="{{ old('room_number', $room?->room_number) }}" required
            class="block w-full rounded-lg border border-stone-300 px-3 py-2.5 text-sm text-stone-900 placeholder:text-stone-400 focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
            placeholder="e.g. 101">
        @error('room_number')
            <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    {{-- Room Type --}}
    <div>
        <label for="room_type_id" class="block text-sm font-medium text-stone-700 mb-1.5">Room Type <span class="text-red-500">*</span></label>
        <select name="room_type_id" id="room_type_id" required x-on:change="updateDefaults" x-ref="typeSelect"
            class="block w-full rounded-lg border border-stone-300 px-3 py-2.5 text-sm text-stone-900 focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
            <option value="">— Select Type —</option>
            @foreach($roomTypes as $type)
                <option value="{{ $type->id }}" 
                    data-capacity="{{ $type->capacity }}"
                    data-price="{{ (int)$type->base_price }}"
                    @selected(old('room_type_id', $room?->room_type_id) == $type->id)>
                    {{ $type->name }}
                </option>
            @endforeach
        </select>
        @error('room_type_id')
            <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    {{-- Floor --}}
    <div>
        <label for="floor" class="block text-sm font-medium text-stone-700 mb-1.5">Floor</label>
        <input type="text" name="floor" id="floor" value="{{ old('floor', $room?->floor) }}"
            class="block w-full rounded-lg border border-stone-300 px-3 py-2.5 text-sm text-stone-900 placeholder:text-stone-400 focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
            placeholder="e.g. 1st Floor">
        @error('floor')
            <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    {{-- Status --}}
    <div>
        <label for="status" class="block text-sm font-medium text-stone-700 mb-1.5">Status <span class="text-red-500">*</span></label>
        <select name="status" id="status" required
            class="block w-full rounded-lg border border-stone-300 px-3 py-2.5 text-sm text-stone-900 focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
            @foreach(\App\Enums\RoomStatus::cases() as $status)
                <option value="{{ $status->value }}" @selected(old('status', $room?->status?->value ?? 'available') === $status->value)>
                    {{ $status->label() }}
                </option>
            @endforeach
        </select>
        @error('status')
            <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    {{-- Capacity --}}
    <div>
        <label for="capacity" class="block text-sm font-medium text-stone-700 mb-1.5">Capacity (Persons) <span class="text-red-500">*</span></label>
        <input type="number" name="capacity" id="capacity" x-ref="capacityInput"
            value="{{ old('capacity', $room?->capacity) }}" required min="1"
            class="block w-full rounded-lg border border-stone-300 px-3 py-2.5 text-sm text-stone-900 placeholder:text-stone-400 focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
        <p class="mt-1 text-xs text-stone-500">Leave unchanged to use Room Type's default capacity.</p>
        @error('capacity')
            <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    {{-- Price Per Night --}}
    <div>
        <label for="price_per_night" class="block text-sm font-medium text-stone-700 mb-1.5">Price / Night (Rp) <span class="text-red-500">*</span></label>
        <input type="number" name="price_per_night" id="price_per_night" x-ref="priceInput"
            value="{{ old('price_per_night', $room ? (int)$room->price_per_night : '') }}" required min="0" step="1"
            class="block w-full rounded-lg border border-stone-300 px-3 py-2.5 text-sm text-stone-900 placeholder:text-stone-400 focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
        <p class="mt-1 text-xs text-stone-500">Leave unchanged to use Room Type's base price.</p>
        @error('price_per_night')
            <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    {{-- Description --}}
    <div class="md:col-span-2">
        <label for="description" class="block text-sm font-medium text-stone-700 mb-1.5">Specific Notes/Description</label>
        <textarea name="description" id="description" rows="2"
            class="block w-full rounded-lg border border-stone-300 px-3 py-2.5 text-sm text-stone-900 placeholder:text-stone-400 focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
            placeholder="E.g. near elevator, special view...">{{ old('description', $room?->description) }}</textarea>
        @error('description')
            <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    {{-- Image --}}
    <div class="md:col-span-2">
        <label class="block text-sm font-medium text-stone-700 mb-1.5">Specific Image</label>
        @if($room?->image)
            <div class="mb-3">
                <img src="{{ Storage::url($room->image) }}" alt="Current Image" class="w-32 h-32 object-cover rounded-lg border border-stone-200">
            </div>
        @endif
        <input type="file" name="image" id="image" accept="image/*"
            class="block w-full text-sm text-stone-500 file:mr-4 file:py-2.5 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-stone-50 file:text-stone-700 hover:file:bg-stone-100 border border-stone-300 rounded-lg">
        <p class="mt-1 text-xs text-stone-500">Optional. If not provided, it will fallback to the Room Type's image.</p>
        @error('image')
            <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    {{-- Is Active --}}
    <div class="md:col-span-2">
        <div class="flex items-center">
            <input type="checkbox" name="is_active" id="is_active" value="1" @checked(old('is_active', $room?->is_active ?? true))
                class="w-4 h-4 rounded border-stone-300 text-primary-600 focus:ring-primary-500">
            <label for="is_active" class="ml-2 text-sm font-medium text-stone-700">Active</label>
        </div>
        <p class="mt-1 text-xs text-stone-500">Inactive rooms cannot be booked.</p>
        @error('is_active')
            <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>
</div>

<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('roomForm', () => ({
        isEditMode: {{ $room ? 'true' : 'false' }},
        updateDefaults() {
            if (this.isEditMode) return; // don't auto-update if editing existing
            
            const select = this.$refs.typeSelect;
            const option = select.options[select.selectedIndex];
            
            if (option.value) {
                const capacity = option.getAttribute('data-capacity');
                const price = option.getAttribute('data-price');
                
                if (this.$refs.capacityInput.value === '') {
                    this.$refs.capacityInput.value = capacity;
                }
                if (this.$refs.priceInput.value === '') {
                    this.$refs.priceInput.value = price;
                }
            }
        }
    }))
})
</script>
