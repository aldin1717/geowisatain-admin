@php
    $guest = $guest ?? null;
@endphp

<div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-6">
    {{-- Full Name --}}
    <div>
        <label for="full_name" class="block text-sm font-medium text-stone-700 mb-1.5">Full Name <span class="text-red-500">*</span></label>
        <input type="text" name="full_name" id="full_name" value="{{ old('full_name', $guest?->full_name) }}" required
            class="block w-full rounded-lg border border-stone-300 px-3 py-2.5 text-sm text-stone-900 placeholder:text-stone-400 focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
            placeholder="Enter full name">
        @error('full_name')
            <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    {{-- Gender --}}
    <div>
        <label for="gender" class="block text-sm font-medium text-stone-700 mb-1.5">Gender</label>
        <select name="gender" id="gender"
            class="block w-full rounded-lg border border-stone-300 px-3 py-2.5 text-sm text-stone-900 focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
            <option value="">— Select —</option>
            <option value="male" @selected(old('gender', $guest?->gender) === 'male')>Male</option>
            <option value="female" @selected(old('gender', $guest?->gender) === 'female')>Female</option>
        </select>
        @error('gender')
            <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    {{-- Identity Type --}}
    <div>
        <label for="identity_type" class="block text-sm font-medium text-stone-700 mb-1.5">Identity Type <span class="text-red-500">*</span></label>
        <select name="identity_type" id="identity_type" required
            class="block w-full rounded-lg border border-stone-300 px-3 py-2.5 text-sm text-stone-900 focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
            <option value="">— Select —</option>
            @foreach(\App\Enums\IdentityType::cases() as $type)
                <option value="{{ $type->value }}" @selected(old('identity_type', $guest?->identity_type?->value) === $type->value)>{{ $type->label() }}</option>
            @endforeach
        </select>
        @error('identity_type')
            <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    {{-- Identity Number --}}
    <div>
        <label for="identity_number" class="block text-sm font-medium text-stone-700 mb-1.5">Identity Number <span class="text-red-500">*</span></label>
        <input type="text" name="identity_number" id="identity_number" value="{{ old('identity_number', $guest?->identity_number) }}" required
            class="block w-full rounded-lg border border-stone-300 px-3 py-2.5 text-sm text-stone-900 placeholder:text-stone-400 focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
            placeholder="Enter identity number">
        @error('identity_number')
            <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    {{-- Phone --}}
    <div>
        <label for="phone" class="block text-sm font-medium text-stone-700 mb-1.5">Phone</label>
        <input type="text" name="phone" id="phone" value="{{ old('phone', $guest?->phone) }}"
            class="block w-full rounded-lg border border-stone-300 px-3 py-2.5 text-sm text-stone-900 placeholder:text-stone-400 focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
            placeholder="e.g. +62 812-3456-7890">
        @error('phone')
            <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    {{-- Email --}}
    <div>
        <label for="email" class="block text-sm font-medium text-stone-700 mb-1.5">Email</label>
        <input type="email" name="email" id="email" value="{{ old('email', $guest?->email) }}"
            class="block w-full rounded-lg border border-stone-300 px-3 py-2.5 text-sm text-stone-900 placeholder:text-stone-400 focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
            placeholder="guest@example.com">
        @error('email')
            <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    {{-- City --}}
    <div>
        <label for="city" class="block text-sm font-medium text-stone-700 mb-1.5">City</label>
        <input type="text" name="city" id="city" value="{{ old('city', $guest?->city) }}"
            class="block w-full rounded-lg border border-stone-300 px-3 py-2.5 text-sm text-stone-900 placeholder:text-stone-400 focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
            placeholder="Enter city">
        @error('city')
            <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    {{-- Country --}}
    <div>
        <label for="country" class="block text-sm font-medium text-stone-700 mb-1.5">Country</label>
        <input type="text" name="country" id="country" value="{{ old('country', $guest?->country) }}"
            class="block w-full rounded-lg border border-stone-300 px-3 py-2.5 text-sm text-stone-900 placeholder:text-stone-400 focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
            placeholder="Enter country">
        @error('country')
            <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    {{-- Address (full width) --}}
    <div class="md:col-span-2">
        <label for="address" class="block text-sm font-medium text-stone-700 mb-1.5">Address</label>
        <textarea name="address" id="address" rows="2"
            class="block w-full rounded-lg border border-stone-300 px-3 py-2.5 text-sm text-stone-900 placeholder:text-stone-400 focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
            placeholder="Enter full address">{{ old('address', $guest?->address) }}</textarea>
        @error('address')
            <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    {{-- Notes (full width) --}}
    <div class="md:col-span-2">
        <label for="notes" class="block text-sm font-medium text-stone-700 mb-1.5">Notes</label>
        <textarea name="notes" id="notes" rows="3"
            class="block w-full rounded-lg border border-stone-300 px-3 py-2.5 text-sm text-stone-900 placeholder:text-stone-400 focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
            placeholder="Any additional notes about the guest...">{{ old('notes', $guest?->notes) }}</textarea>
        @error('notes')
            <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>
</div>
