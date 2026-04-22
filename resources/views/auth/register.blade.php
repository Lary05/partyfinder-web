<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Partify - Create an Account</title>
    <link rel="icon" type="image/png" href="{{ asset('images/partify.png') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-900 min-h-screen flex items-center justify-center p-4 antialiased" x-data="registerForm()">
    <div class="max-w-md w-full">
        <div class="text-center mb-8 flex flex-col items-center">
            <a href="/" class="flex flex-col items-center">
                <img src="{{ asset('images/partify.png') }}" alt="Partify Logo" class="h-20 w-auto mb-3 drop-shadow-lg transition-transform hover:scale-105">
                <span class="inline-block text-4xl font-extrabold text-blue-500 tracking-wider">
                    <span class="text-white">PART</span>IFY
                </span>
            </a>
            <p class="text-gray-400 mt-2">Join the ultimate party network.</p>
        </div>

        <div class="bg-gray-800 rounded-xl shadow-2xl p-8 border border-gray-700">
            <form method="POST" action="{{ route('register') }}">
                @csrf

                @if ($errors->any())
                    <div class="mb-4 text-red-500 text-sm bg-red-900 bg-opacity-20 p-3 rounded-lg border border-red-800">
                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>• {{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div class="mb-4">
                    <label for="name" class="block text-sm font-medium text-gray-300 mb-1">Full Name</label>
                    <input id="name" class="w-full bg-gray-900 border border-gray-700 rounded-lg px-4 py-2.5 text-white focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition" type="text" name="name" value="{{ old('name') }}" required autofocus autocomplete="name" />
                </div>

                <div class="mb-4">
                    <label for="email" class="block text-sm font-medium text-gray-300 mb-1">Email Address</label>
                    <input id="email" class="w-full bg-gray-900 border border-gray-700 rounded-lg px-4 py-2.5 text-white focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition" type="email" name="email" value="{{ old('email') }}" required autocomplete="username" />
                </div>

                <div class="mb-4 relative">
                    <label for="password" class="block text-sm font-medium text-gray-300 mb-1">Password</label>
                    <div class="relative">
                        <input id="password" x-model="password" @input="checkStrength" :type="showPassword ? 'text' : 'password'" class="w-full bg-gray-900 border border-gray-700 rounded-lg px-4 py-2.5 pr-12 text-white focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition" name="password" required autocomplete="new-password" />
                        <button type="button" @click="showPassword = !showPassword" class="absolute inset-y-0 right-0 px-4 flex items-center text-gray-400 hover:text-white focus:outline-none">
                            <i class="fas" :class="showPassword ? 'fa-eye-slash' : 'fa-eye'"></i>
                        </button>
                    </div>

                    <div class="mt-2" x-show="password.length > 0" x-transition>
                        <div class="flex justify-between items-center mb-1">
                            <span class="text-xs font-bold" :class="strengthColorText" x-text="strengthText"></span>
                        </div>
                        <div class="flex gap-1 h-1.5 w-full bg-gray-900 rounded-full overflow-hidden">
                            <div class="h-full transition-all duration-300" :class="score >= 1 ? strengthColorBg : 'bg-transparent'" style="width: 25%"></div>
                            <div class="h-full transition-all duration-300" :class="score >= 2 ? strengthColorBg : 'bg-transparent'" style="width: 25%"></div>
                            <div class="h-full transition-all duration-300" :class="score >= 3 ? strengthColorBg : 'bg-transparent'" style="width: 25%"></div>
                            <div class="h-full transition-all duration-300" :class="score >= 4 ? strengthColorBg : 'bg-transparent'" style="width: 25%"></div>
                        </div>
                        <p class="text-[10px] text-gray-500 mt-1">Requires 8+ chars, upper & lowercase, a number, and a symbol.</p>
                    </div>
                </div>

                <div class="mb-6 relative">
                    <label for="password_confirmation" class="block text-sm font-medium text-gray-300 mb-1">Confirm Password</label>
                    <div class="relative">
                        <input id="password_confirmation" :type="showConfirm ? 'text' : 'password'" class="w-full bg-gray-900 border border-gray-700 rounded-lg px-4 py-2.5 pr-12 text-white focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition" name="password_confirmation" required autocomplete="new-password" />
                        <button type="button" @click="showConfirm = !showConfirm" class="absolute inset-y-0 right-0 px-4 flex items-center text-gray-400 hover:text-white focus:outline-none">
                            <i class="fas" :class="showConfirm ? 'fa-eye-slash' : 'fa-eye'"></i>
                        </button>
                    </div>
                </div>

                <button type="submit" 
                        :disabled="score < 3"
                        :class="score < 3 ? 'bg-gray-600 cursor-not-allowed opacity-50' : 'bg-blue-600 hover:bg-blue-500 shadow-lg shadow-blue-500/30'"
                        class="w-full text-white font-bold py-3 px-4 rounded-lg transition duration-200">
                    Create Account
                </button>
            </form>
            
            <p class="mt-6 text-center text-sm text-gray-400">
                Already have an account? <a href="{{ route('login') }}" class="text-blue-400 hover:text-blue-300 font-medium transition">Log in</a>
            </p>
        </div>
    </div>

    <script>
        function registerForm() {
            return {
                password: '',
                showPassword: false,
                showConfirm: false,
                score: 0,
                strengthText: '',
                strengthColorText: '',
                strengthColorBg: '',

                checkStrength() {
                    let pwd = this.password;
                    let s = 0;
                    
                    if (pwd.length === 0) {
                        this.score = 0; return;
                    }

                    if (pwd.length >= 8) s++; // Hossz
                    if (/[A-Z]/.test(pwd) && /[a-z]/.test(pwd)) s++; // Kis- és nagybetű
                    if (/\d/.test(pwd)) s++; // Szám
                    if (/[^A-Za-z0-9]/.test(pwd)) s++; // Szimbólum

                    this.score = s;

                    switch(s) {
                        case 0:
                        case 1:
                            this.strengthText = 'Weak';
                            this.strengthColorText = 'text-red-500';
                            this.strengthColorBg = 'bg-red-500';
                            break;
                        case 2:
                            this.strengthText = 'Medium';
                            this.strengthColorText = 'text-yellow-500';
                            this.strengthColorBg = 'bg-yellow-500';
                            break;
                        case 3:
                            this.strengthText = 'Strong';
                            this.strengthColorText = 'text-green-500';
                            this.strengthColorBg = 'bg-green-500';
                            break;
                        case 4:
                            this.strengthText = 'Very Strong';
                            this.strengthColorText = 'text-blue-400';
                            this.strengthColorBg = 'bg-blue-400';
                            break;
                    }
                }
            }
        }
    </script>
</body>
</html>