<div class="grid grid-cols-1 gap-6 md:grid-cols-2">
    <div>
        <label for="name" class="mb-1.5 block text-sm font-medium text-stone-700">Name <span class="text-red-500">*</span></label>
        <input type="text" id="name" name="name" value="{{ old('name', $user?->name) }}" required
            class="block w-full rounded-lg border border-stone-300 px-3 py-2.5 text-sm focus:border-primary-500 focus:ring-2 focus:ring-primary-500">
        @error('name')<p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>@enderror
    </div>

    <div>
        <label for="email" class="mb-1.5 block text-sm font-medium text-stone-700">Email <span class="text-red-500">*</span></label>
        <input type="email" id="email" name="email" value="{{ old('email', $user?->email) }}" required
            class="block w-full rounded-lg border border-stone-300 px-3 py-2.5 text-sm focus:border-primary-500 focus:ring-2 focus:ring-primary-500">
        @error('email')<p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>@enderror
    </div>

    <div>
        <label for="role_id" class="mb-1.5 block text-sm font-medium text-stone-700">Role <span class="text-red-500">*</span></label>
        <select id="role_id" name="role_id" required
            class="block w-full rounded-lg border border-stone-300 px-3 py-2.5 text-sm focus:border-primary-500 focus:ring-2 focus:ring-primary-500">
            <option value="">Select a role</option>
            @foreach($roles as $role)
                <option value="{{ $role->id }}" @selected((string) old('role_id', $user?->role_id) === (string) $role->id)>
                    {{ $role->name }}{{ $role->slug === 'admin' ? ' (Super Admin)' : '' }}
                </option>
            @endforeach
        </select>
        @error('role_id')<p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>@enderror
    </div>

    <div>
        <label for="password" class="mb-1.5 block text-sm font-medium text-stone-700">
            Password @if(!$user)<span class="text-red-500">*</span>@else<span class="text-stone-400">(leave blank to keep current password)</span>@endif
        </label>
        <input type="password" id="password" name="password" @required(!$user) autocomplete="new-password"
            class="block w-full rounded-lg border border-stone-300 px-3 py-2.5 text-sm focus:border-primary-500 focus:ring-2 focus:ring-primary-500">
        @error('password')<p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>@enderror
    </div>

    <div>
        <label for="password_confirmation" class="mb-1.5 block text-sm font-medium text-stone-700">Confirm Password @if(!$user)<span class="text-red-500">*</span>@endif</label>
        <input type="password" id="password_confirmation" name="password_confirmation" @required(!$user) autocomplete="new-password"
            class="block w-full rounded-lg border border-stone-300 px-3 py-2.5 text-sm focus:border-primary-500 focus:ring-2 focus:ring-primary-500">
    </div>

    @if($user)
        <div class="md:col-span-2">
            <label class="flex items-center gap-2 text-sm font-medium text-stone-700">
                <input type="hidden" name="is_active" value="0">
                <input type="checkbox" name="is_active" value="1" @checked((bool) old('is_active', $user->is_active))
                    class="h-4 w-4 rounded border-stone-300 text-primary-600 focus:ring-primary-500">
                Active account
            </label>
            <p class="mt-1 text-xs text-stone-500">Inactive users cannot sign in and will be signed out on their next request.</p>
            @error('is_active')<p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>@enderror
        </div>
    @endif
</div>
