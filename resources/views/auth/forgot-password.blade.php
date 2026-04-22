<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Partify - Forgot Password</title>
    <link rel="icon" type="image/png" href="{{ asset('images/partify.png') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-900 min-h-screen flex items-center justify-center p-4 antialiased">
    <div class="max-w-md w-full">
        <div class="text-center mb-8 flex flex-col items-center">
            <a href="/" class="flex flex-col items-center">
                <img src="{{ asset('images/partify.png') }}" alt="Partify Logo" class="h-20 w-auto mb-3 drop-shadow-lg transition-transform hover:scale-105">
                <span class="inline-block text-4xl font-extrabold text-blue-500 tracking-wider">
                    <span class="text-white">PART</span>IFY
                </span>
            </a>
        </div>

        <div class="bg-gray-800 rounded-xl shadow-2xl p-8 border border-gray-700">
            <div class="text-center mb-6">
                <h2 class="text-2xl font-bold text-white mb-2">Forgot your password?</h2>
                <p class="text-gray-400 text-sm">No problem. Just let us know your email address and we will email you a password reset link.</p>
            </div>

            <x-auth-session-status class="mb-4 text-green-400 text-center font-bold" :status="session('status')" />

            <form method="POST" action="{{ route('password.email') }}">
                @csrf
                
                @if ($errors->any())
                    <div class="mb-4 text-red-500 text-sm text-center bg-red-900 bg-opacity-20 p-2 rounded">
                        {{ $errors->first() }}
                    </div>
                @endif

                <div class="mb-6">
                    <label for="email" class="block text-sm font-medium text-gray-300 mb-1">Email Address</label>
                    <input id="email" class="w-full bg-gray-900 border border-gray-700 rounded-lg px-4 py-2.5 text-white focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition" type="email" name="email" value="{{ old('email') }}" required autofocus />
                </div>

                <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-4 rounded-lg transition duration-200 shadow-lg shadow-blue-500/30">
                    Send Password Reset Link
                </button>
            </form>
            
            <p class="mt-6 text-center text-sm text-gray-400">
                Back to <a href="{{ route('login') }}" class="text-blue-400 hover:text-blue-300 font-medium transition">Login</a>
            </p>
        </div>
    </div>
</body>
</html>