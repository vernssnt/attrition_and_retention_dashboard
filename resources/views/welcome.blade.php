<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />

        <!-- Styles / Scripts -->
        @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
            @vite(['resources/css/app.css', 'resources/js/app.js'])
        @endif
    </head>
    <body class="bg-[#FDFDFC] dark:bg-[#0a0a0a] text-[#1b1b18] flex items-center justify-center min-h-screen">
        <div class="max-w-xl text-center">
            <h1 class="text-4xl font-bold mb-4">
                Welcome to Laravel
            </h1>

            <p class="text-gray-600 mb-6">
                Laravel is a web application framework with expressive, elegant syntax.
            </p>

            <div class="flex justify-center gap-4">
                <a
                    href="https://laravel.com/docs"
                    target="_blank"
                    class="px-4 py-2 bg-black text-white rounded"
                >
                    Documentation
                </a>

                <a
                    href="https://laracasts.com"
                    target="_blank"
                    class="px-4 py-2 border border-black rounded"
                >
                    Laracasts
                </a>
            </div>

            @if (Route::has('login'))
                <div class="mt-8">
                    @auth
                        <a href="{{ url('/dashboard') }}" class="underline">
                            Dashboard
                        </a>
                    @else
                        <a href="{{ route('login') }}" class="underline mr-4">
                            Log in
                        </a>

                        @if (Route::has('register'))
                            <a href="{{ route('register') }}" class="underline">
                                Register
                            </a>
                        @endif
                    @endauth
                </div>
            @endif
        </div>
    </body>
</html>
