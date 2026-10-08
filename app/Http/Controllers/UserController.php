<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::with('role');

        if ($search = $request->input('search')) {
            $query->where(function ($users) use ($search) {
                $users->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($request->input('status') === 'active') {
            $query->where('is_active', true);
        } elseif ($request->input('status') === 'inactive') {
            $query->where('is_active', false);
        }

        $users = $query->orderBy('name')->paginate(15)->withQueryString();

        return view('users.index', compact('users'));
    }

    public function create()
    {
        $roles = Role::orderBy('name')->get();

        return view('users.create', compact('roles'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'role_id' => ['required', 'integer', 'exists:roles,id'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);
        $data['is_active'] = true;

        User::create($data);

        return redirect()->route('users.index')->with('success', 'User created successfully.');
    }

    public function edit(User $user)
    {
        $roles = Role::orderBy('name')->get();

        return view('users.edit', compact('user', 'roles'));
    }

    public function update(Request $request, User $user)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'role_id' => ['required', 'integer', 'exists:roles,id'],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
            'is_active' => ['required', 'boolean'],
        ]);

        if (Auth::id() === $user->id && (! $data['is_active'] || (int) $data['role_id'] !== (int) $user->role_id)) {
            return back()->withInput()->with('error', 'You cannot deactivate or change the role of your own account.');
        }

        $wouldRemoveLastAdmin = DB::transaction(function () use ($user, &$data): bool {
            $lockedUser = User::query()->lockForUpdate()->findOrFail($user->id);
            $targetRole = Role::findOrFail($data['role_id']);
            $wasActiveAdmin = $lockedUser->is_active && $lockedUser->role()->where('slug', 'admin')->exists();
            $willRemainActiveAdmin = (bool) $data['is_active'] && $targetRole->slug === 'admin';

            if ($wasActiveAdmin && ! $willRemainActiveAdmin) {
                $activeAdminCount = User::query()
                    ->where('is_active', true)
                    ->whereHas('role', fn ($query) => $query->where('slug', 'admin'))
                    ->lockForUpdate()
                    ->get()
                    ->count();

                if ($activeAdminCount <= 1) {
                    return true;
                }
            }

            if (blank($data['password'])) {
                unset($data['password']);
            }

            $lockedUser->update($data);

            return false;
        });

        if ($wouldRemoveLastAdmin) {
            return back()->withInput()->with('error', 'The last active Super Admin cannot be deactivated or demoted.');
        }

        return redirect()->route('users.index')->with('success', 'User updated successfully.');
    }

    public function deactivate(User $user)
    {
        if (Auth::id() === $user->id) {
            return back()->with('error', 'You cannot deactivate your own account.');
        }

        $wouldDeactivateLastAdmin = DB::transaction(function () use ($user): bool {
            $lockedUser = User::query()->lockForUpdate()->findOrFail($user->id);

            if ($lockedUser->is_active && $lockedUser->role()->where('slug', 'admin')->exists()) {
                $activeAdminCount = User::query()
                    ->where('is_active', true)
                    ->whereHas('role', fn ($query) => $query->where('slug', 'admin'))
                    ->lockForUpdate()
                    ->get()
                    ->count();

                if ($activeAdminCount <= 1) {
                    return true;
                }
            }

            $lockedUser->update(['is_active' => false]);

            return false;
        });

        if ($wouldDeactivateLastAdmin) {
            return back()->with('error', 'The last active Super Admin cannot be deactivated.');
        }

        return redirect()->route('users.index')->with('success', 'User deactivated successfully.');
    }
}
