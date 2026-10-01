<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'Grand Hotel') }} - Login</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased text-stone-900 bg-stone-50">
    <div class="min-h-screen flex">
        <!-- Left Panel: Form -->
        <div class="w-full lg:w-1/2 flex items-center justify-center p-8 sm:p-12 lg:p-24 bg-white">
            <div class="w-full max-w-md">
                @yield('content')
            </div>
        </div>

        <!-- Right Panel: Visual -->
        <div class="hidden lg:flex w-1/2 bg-primary-700 relative overflow-hidden items-center justify-center">
            <!-- Decorative elements for elegant feel -->
            <div class="absolute inset-0 bg-primary-700 opacity-90"></div>
            <div class="relative z-10 text-center px-12 text-white">
                <h1 class="text-4xl font-semibold mb-4">Premium Hospitality</h1>
                <p class="text-primary-100/80 text-lg">Manage reservations, inventory, and operations with our integrated professional suite.</p>
            </div>
            
            <!-- Abstract pattern/shape for background -->
            <svg class="absolute right-0 bottom-0 text-primary-500 opacity-20 transform translate-x-1/3 translate-y-1/3" width="600" height="600" viewBox="0 0 200 200" xmlns="http://www.w3.org/2000/svg">
                <path fill="currentColor" d="M47.7,-57.2C59.9,-47.3,66.6,-29.4,69.5,-11.1C72.3,7.2,71.2,25.8,62.8,40.9C54.4,56,38.7,67.6,21.3,72.6C3.9,77.7,-15.1,76.2,-31.6,68.8C-48.1,61.4,-62,48,-70.6,31.7C-79.3,15.4,-82.7,-3.7,-77.8,-20.5C-72.9,-37.2,-59.8,-51.5,-44.7,-60.7C-29.7,-69.8,-14.8,-73.7,1.6,-75.6C18,-77.5,35.5,-67.1,47.7,-57.2Z" transform="translate(100 100)" />
            </svg>
        </div>
    </div>
</body>
</html>
