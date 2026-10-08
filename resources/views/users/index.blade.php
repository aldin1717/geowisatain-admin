@extends('layouts.app')

@section('title', 'Users')

@section('content')
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-stone-900">Users</h1>
            <p class="mt-1 text-sm text-stone-500">Manage staff accounts and access roles.</p>
        </div>
        <a href="{{ route('users.create') }}" class="inline-flex items-center justify-center gap-2 rounded-lg bg-primary-700 px-4 py-2.5 text-sm font-medium text-white transition-colors hover:bg-primary-600">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
            Add User
        </a>
    </div>

    <form method="GET" action="{{ route('users.index') }}" class="mb-6 flex flex-wrap gap-3">
        <input type="search" name="search" value="{{ request('search') }}" placeholder="Search name or email..."
            class="block w-full max-w-sm rounded-lg border border-stone-300 px-3 py-2 text-sm text-stone-900 focus:border-primary-500 focus:ring-2 focus:ring-primary-500">
        <select name="status" class="rounded-lg border border-stone-300 px-3 py-2 text-sm text-stone-900 focus:border-primary-500 focus:ring-2 focus:ring-primary-500">
            <option value="">All statuses</option>
            <option value="active" @selected(request('status') === 'active')>Active</option>
            <option value="inactive" @selected(request('status') === 'inactive')>Inactive</option>
        </select>
        <button type="submit" class="rounded-lg border border-stone-300 bg-stone-100 px-4 py-2 text-sm font-medium text-stone-700 hover:bg-stone-200">Filter</button>
        @if(request()->hasAny(['search', 'status']))
            <a href="{{ route('users.index') }}" class="flex items-center px-2 text-sm text-stone-500 hover:text-stone-700">Clear</a>
        @endif
    </form>

    <div class="overflow-hidden rounded-xl border border-stone-200 bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="border-b border-stone-200 bg-stone-50">
                    <tr>
                        <th class="px-6 py-3 text-xs font-semibold uppercase tracking-wider text-stone-500">Name</th>
                        <th class="px-6 py-3 text-xs font-semibold uppercase tracking-wider text-stone-500">Email</th>
                        <th class="px-6 py-3 text-xs font-semibold uppercase tracking-wider text-stone-500">Role</th>
                        <th class="px-6 py-3 text-xs font-semibold uppercase tracking-wider text-stone-500">Status</th>
                        <th class="px-6 py-3 text-right text-xs font-semibold uppercase tracking-wider text-stone-500">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-stone-100">
                    @forelse($users as $user)
                        <tr class="hover:bg-stone-50">
                            <td class="px-6 py-4 font-medium text-stone-900">
                                {{ $user->name }}
                                @if($user->id === Auth::id())
                                    <span class="ml-1 text-xs font-normal text-stone-400">(You)</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-stone-600">{{ $user->email }}</td>
                            <td class="px-6 py-4 text-stone-600">{{ $user->role?->name ?? 'No role' }}</td>
                            <td class="px-6 py-4">
                                <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-medium {{ $user->is_active ? 'bg-emerald-100 text-emerald-800' : 'bg-stone-100 text-stone-600' }}">
                                    {{ $user->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center justify-end gap-3">
                                    <a href="{{ route('users.edit', $user) }}" class="font-medium text-primary-700 hover:text-primary-900">Edit</a>
                                    @if($user->is_active)
                                        <form method="POST" action="{{ route('users.deactivate', $user) }}" onsubmit="return confirm('Deactivate this user account?')">
                                            @csrf
                                            <button type="submit" class="font-medium text-red-600 hover:text-red-800">Deactivate</button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-6 py-12 text-center text-stone-500">No users found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($users->hasPages())
            <div class="border-t border-stone-200 px-6 py-4">{{ $users->links() }}</div>
        @endif
    </div>
@endsection
