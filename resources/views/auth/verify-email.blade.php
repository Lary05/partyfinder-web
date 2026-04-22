<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Partify - Verify Email</title>
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
                <h2 class="text-2xl font-bold text-white mb-2">Verify your email!</h2>
                <p class="text-gray-400 text-sm leading-relaxed">
                    Thanks for signing up! Before you dive into the party, please verify your email address by clicking on the link we just emailed to you. 
                    If you can't find it, don't forget to check your Spam folder!
                </p>
            </div>

            @if (session('status') == 'verification-link-sent')
                <div class="mb-6 bg-green-900/30 border border-green-500 text-green-400 px-4 py-3 rounded-lg text-sm text-center font-medium">
                    A new verification link has been sent to your email address!
                </div>
            @endif

            <div class="flex flex-col gap-4 mt-6">
                <form method="POST" action="{{ route('verification.send') }}">
                    @csrf
                    <button type="submit" class="w-full bg-blue-600 hover:bg-blue-500 text-white font-bold py-3 px-4 rounded-lg transition duration-200 shadow-lg">
                        Resend Verification Email
                    </button>
                </form>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="w-full bg-transparent hover:bg-gray-700 text-gray-400 hover:text-white border border-gray-600 font-bold py-3 px-4 rounded-lg transition duration-200">
                        Log Out
                    </button>
                </form>
            </div>
        </div>
    </div>
</body>
</html>