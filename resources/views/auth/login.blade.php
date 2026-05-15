<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>ClinicMS — Log In</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <script>
        tailwind.config = {
            theme: { extend: { fontFamily: { sans: ['Inter', 'sans-serif'] }, colors: { brand: { 400:'#2dd4bf',500:'#14b8a6',600:'#0d9488',700:'#0f766e',800:'#115e59' } } } }
        }
    </script>
    <style>
        body { font-family: 'Inter', sans-serif; }
        .input-field { display:block; width:100%; padding:.75rem 1rem .75rem 2.75rem; border:1.5px solid #e5e7eb; border-radius:.75rem; font-size:.875rem; outline:none; transition:.15s; background:white; }
        .input-field:focus { border-color:#0d9488; box-shadow:0 0 0 3px rgba(13,148,136,.12); }
        .hero-bg { background-image:url('https://images.unsplash.com/photo-1538108149393-fbbd81895907?auto=format&fit=crop&w=1200&q=80'); background-size:cover; background-position:center; }
    </style>
</head>
<body class="min-h-screen flex bg-slate-50">

    <!-- Left: hero image (desktop only) -->
    <div class="hidden lg:flex lg:w-1/2 hero-bg relative">
        <div class="absolute inset-0 bg-gradient-to-br from-slate-900/80 via-brand-800/70 to-brand-700/80"></div>
        <div class="relative z-10 flex flex-col justify-end p-12 text-white">
            <div class="w-12 h-12 bg-white/15 border border-white/20 rounded-2xl flex items-center justify-center mb-6 backdrop-blur-sm">
                <i class="fa-solid fa-staff-snake text-white text-xl"></i>
            </div>
            <h2 class="text-4xl font-extrabold mb-3 leading-tight">Caring for those<br>who care for others.</h2>
            <p class="text-white/80 text-base max-w-md">A modern clinic management system designed for healthcare professionals.</p>
            <p class="absolute bottom-6 left-12 text-white/40 text-xs">IT9AL Final Project &bull; {{ date('Y') }}</p>
        </div>
    </div>

    <!-- Right: log in form -->
    <div class="flex-1 flex items-center justify-center p-6">
        <div class="w-full max-w-md">

            <!-- Mobile logo -->
            <div class="lg:hidden text-center mb-8">
                <div class="w-14 h-14 bg-brand-600 rounded-2xl flex items-center justify-center mx-auto mb-3 shadow-lg shadow-brand-500/30">
                    <i class="fa-solid fa-staff-snake text-white text-xl"></i>
                </div>
                <h1 class="text-2xl font-bold text-gray-900">ClinicMS</h1>
            </div>

            <div class="bg-white rounded-2xl shadow-xl border border-gray-100 overflow-hidden">
                <div class="px-8 pt-8 pb-5">
                    <h2 class="text-2xl font-bold text-gray-900">Welcome back</h2>
                    <p class="text-gray-500 text-sm mt-1">Log in to access your clinic dashboard</p>
                </div>

                @if($errors->any())
                <div class="mx-8 mb-4 p-3.5 bg-red-50 border border-red-200 rounded-xl flex items-center gap-3">
                    <i class="fa-solid fa-circle-exclamation text-red-500 flex-shrink-0"></i>
                    <p class="text-sm text-red-700">{{ $errors->first() }}</p>
                </div>
                @endif

                <form method="POST" action="{{ route('login.post') }}" class="px-8 pb-6 space-y-4">
                    @csrf

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1.5">Email Address</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                                <i class="fa-solid fa-envelope text-gray-400 text-sm"></i>
                            </div>
                            <input type="email" name="email" value="{{ old('email') }}" required autocomplete="email" class="input-field" placeholder="you@clinic.com">
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1.5">Password</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                                <i class="fa-solid fa-lock text-gray-400 text-sm"></i>
                            </div>
                            <input type="password" name="password" id="pwInput" required autocomplete="current-password" class="input-field" placeholder="••••••••" style="padding-right:2.75rem">
                            <button type="button" onclick="togglePw()" class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-gray-400 hover:text-gray-600">
                                <i class="fa-solid fa-eye text-sm" id="eyeIcon"></i>
                            </button>
                        </div>
                    </div>

                    <div class="flex items-center justify-between">
                        <label class="flex items-center gap-2 text-sm text-gray-600 cursor-pointer select-none">
                            <input type="checkbox" name="remember" class="w-4 h-4 rounded border-gray-300 text-brand-600 focus:ring-brand-500">
                            Remember me
                        </label>
                    </div>

                    <button type="submit" class="w-full bg-brand-600 hover:bg-brand-700 text-white font-semibold py-3 rounded-xl transition-colors flex items-center justify-center gap-2 shadow-sm">
                        <i class="fa-solid fa-right-to-bracket"></i> Log In
                    </button>

                    <p class="text-sm text-center text-gray-500 pt-1">
                        Don't have an account?
                        <a href="{{ route('register') }}" class="text-brand-600 hover:text-brand-700 font-semibold">Create one</a>
                    </p>
                </form>

                <!-- Demo accounts — 2 per role for testing role-vs-role flows -->
                <div class="border-t border-gray-100 px-8 py-5 bg-gray-50/50 max-h-[26rem] overflow-y-auto">
                    <p class="text-xs text-gray-400 font-medium uppercase tracking-wider mb-3 text-center">Demo Accounts — password: <span class="font-bold text-gray-600">password</span></p>
                    @php
                        // Each row in the table renders as one role with both demo users side-by-side
                        // so you can quickly switch between "doctor1 view" vs "doctor2 view".
                        $demoRoles = [
                            ['Admin',       'fa-user-shield',                 'text-slate-600',  ['admin@clinic.com',       'admin2@clinic.com']],
                            ['Clinic Head', 'fa-user-tie',                    'text-purple-600', ['clinichead@clinic.com',  'clinichead2@clinic.com']],
                            ['Doctor',      'fa-user-doctor',                 'text-blue-600',   ['doctor@clinic.com',      'doctor2@clinic.com']],
                            ['Pharmacist',  'fa-prescription-bottle-medical', 'text-green-600',  ['pharmacist@clinic.com',  'pharmacist2@clinic.com']],
                            ['Nurse',       'fa-user-nurse',                  'text-pink-600',   ['nurse@clinic.com',       'nurse2@clinic.com']],
                            ['Secretary',   'fa-id-badge',                    'text-rose-600',   ['secretary@clinic.com',   'secretary2@clinic.com']],
                            ['Assistant',   'fa-user',                        'text-amber-600',  ['assistant@clinic.com',   'assistant2@clinic.com']],
                        ];
                    @endphp
                    <div class="space-y-2">
                        @foreach($demoRoles as [$label, $icon, $iconColor, $emails])
                        <div class="flex items-center gap-2">
                            <div class="flex items-center gap-1.5 w-28 flex-shrink-0">
                                <i class="fa-solid {{ $icon }} {{ $iconColor }} text-xs"></i>
                                <span class="text-xs font-semibold text-gray-700">{{ $label }}</span>
                            </div>
                            <div class="grid grid-cols-2 gap-2 flex-1 min-w-0">
                                @foreach($emails as $i => $email)
                                <button type="button" onclick="fillLogin('{{ $email }}')"
                                        class="px-2.5 py-1.5 bg-white border border-gray-200 rounded-lg hover:border-brand-400 hover:shadow-sm transition-all text-left min-w-0">
                                    <span class="text-[11px] font-mono text-gray-500 truncate block">{{ $email }}</span>
                                </button>
                                @endforeach
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function fillLogin(email) {
            document.querySelector('input[name=email]').value = email;
            document.getElementById('pwInput').value = 'password';
        }
        function togglePw() {
            const pw = document.getElementById('pwInput'), icon = document.getElementById('eyeIcon');
            if (pw.type === 'password') { pw.type = 'text'; icon.classList.replace('fa-eye','fa-eye-slash'); }
            else { pw.type = 'password'; icon.classList.replace('fa-eye-slash','fa-eye'); }
        }
    </script>
</body>
</html>
