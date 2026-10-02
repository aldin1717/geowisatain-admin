@extends('layouts.guest')

@section('content')
<div class="mb-10 text-center lg:text-left">
    <div class="inline-flex items-center justify-center w-12 h-12 rounded-xl bg-primary-100 text-primary-700 mb-6 shadow-sm ring-1 ring-primary-200/80">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
    </div>
    <h2 class="text-3xl font-semibold text-stone-900 tracking-tight">{{ config('app.name', 'Geowisata Inn') }}</h2>
    <p class="mt-2 text-stone-500">Sign in to access the management system</p>
</div>

<form method="POST" action="{{ route('login') }}" class="space-y-6">
    @csrf

    <div>
        <label for="email" class="block text-sm font-medium text-stone-700">Email Address</label>
        <div class="mt-2">
            <input id="email" name="email" type="email" autocomplete="email" required value="{{ old('email') }}"
                class="block w-full rounded-md border-0 py-2.5 text-stone-900 shadow-sm ring-1 ring-inset ring-stone-300 placeholder:text-stone-400 focus:ring-2 focus:ring-inset focus:ring-primary-500 sm:text-sm sm:leading-6 px-3 transition-colors">
        </div>
        @error('email')
            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <div class="flex items-center justify-between">
            <label for="password" class="block text-sm font-medium text-stone-700">Password</label>
        </div>
        <div class="mt-2">
            <input id="password" name="password" type="password" autocomplete="current-password" required
                class="block w-full rounded-md border-0 py-2.5 text-stone-900 shadow-sm ring-1 ring-inset ring-stone-300 placeholder:text-stone-400 focus:ring-2 focus:ring-inset focus:ring-primary-500 sm:text-sm sm:leading-6 px-3 transition-colors">
        </div>
        @error('password')
            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div class="flex items-center justify-between">
        <div class="flex items-center">
            <input id="remember" name="remember" type="checkbox" class="h-4 w-4 rounded border-stone-300 text-primary-600 focus:ring-primary-500">
            <label for="remember" class="ml-2 block text-sm text-stone-700">Remember me</label>
        </div>
    </div>

    <div>
        <button type="submit" class="flex w-full justify-center rounded-md bg-primary-600 px-3 py-2.5 text-sm font-semibold leading-6 text-white shadow-sm hover:bg-primary-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary-600 transition-colors">
            Sign in
        </button>
    </div>
</form>

<div class="mt-10 text-center text-sm text-stone-500">
    <p>Demo accounts:</p>
    <div class="mt-2 space-y-1">
        <p>Admin: admin@hotel.test / password</p>
        <p>Receptionist: receptionist@hotel.test / password</p>
        <p>Warehouse: warehouse@hotel.test / password</p>
    </div>
</div>
@endsection
