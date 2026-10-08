@extends('layouts.app')

@section('title', 'Add User')

@section('content')
    <div class="mb-6">
        <a href="{{ route('users.index') }}" class="text-sm text-stone-500 hover:text-primary-700">Users</a>
        <h1 class="mt-2 text-2xl font-semibold text-stone-900">Add User</h1>
    </div>
    <div class="rounded-xl border border-stone-200 bg-white p-6 shadow-sm sm:p-8">
        <form method="POST" action="{{ route('users.store') }}">
            @csrf
            @include('users._form', ['user' => null])
            <div class="mt-8 flex justify-end gap-3 border-t border-stone-100 pt-6">
                <a href="{{ route('users.index') }}" class="rounded-lg border border-stone-300 px-4 py-2.5 text-sm font-medium text-stone-700 hover:bg-stone-50">Cancel</a>
                <button type="submit" class="rounded-lg bg-primary-700 px-4 py-2.5 text-sm font-medium text-white hover:bg-primary-600">Create User</button>
            </div>
        </form>
    </div>
@endsection
