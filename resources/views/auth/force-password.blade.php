<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>ClinicMS — Set Your Password</title>
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
    </style>
</head>
<body class="min-h-screen flex items-center justify-center bg-slate-50 p-6">

    <div class="w-full max-w-md">
        <div class="text-center mb-6">
            <div class="w-14 h-14 bg-brand-600 rounded-2xl flex items-center justify-center mx-auto mb-3 shadow-lg shadow-brand-500/30">
                <i class="fa-solid fa-key text-white text-xl"></i>
            </div>
            <h1 class="text-2xl font-bold text-gray-900">Set Your Password</h1>
            <p class="text-sm text-gray-500 mt-1">Welcome, {{ Auth::user()->name }}. Pick a password before continuing.</p>
        </div>

        <div class="bg-white rounded-2xl shadow-xl border border-gray-100 overflow-hidden">
            {{-- Required-action banner so the user knows this isn't a regular "change password" flow --}}
            <div class="bg-amber-50 border-b border-amber-200 px-6 py-4 flex items-start gap-3">
                <i class="fa-solid fa-shield-halved text-amber-600 mt-0.5"></i>
                <div class="text-sm">
                    <p class="font-semibold text-amber-800">Required by your administrator</p>
                    <p class="text-amber-700 mt-0.5">Your account was created with a temporary password. You can't access ClinicMS until you replace it.</p>
                </div>
            </div>

            @if($errors->any())
            <div class="mx-6 mt-4 p-3.5 bg-red-50 border border-red-200 rounded-xl text-sm text-red-700">
                <i class="fa-solid fa-circle-exclamation text-red-500 mr-2"></i>{{ $errors->first() }}
            </div>
            @endif

            <form method="POST" action="{{ route('password.force.update') }}" class="px-6 py-6 space-y-4">
                @csrf

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1.5">New Password</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none"><i class="fa-solid fa-lock text-gray-400 text-sm"></i></div>
                        <input type="password" name="password" required minlength="6" class="input-field" placeholder="min. 6 characters" autofocus>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1.5">Confirm New Password</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none"><i class="fa-solid fa-lock text-gray-400 text-sm"></i></div>
                        <input type="password" name="password_confirmation" required minlength="6" class="input-field" placeholder="Re-enter password">
                    </div>
                </div>

                <button type="submit" class="w-full bg-brand-600 hover:bg-brand-700 text-white font-semibold py-3 rounded-xl transition-colors flex items-center justify-center gap-2 shadow-sm mt-2">
                    <i class="fa-solid fa-check"></i> Set Password & Continue
                </button>
            </form>

            <form method="POST" action="{{ route('logout') }}" class="px-6 pb-6 -mt-2">
                @csrf
                <button type="submit" class="w-full text-center text-sm text-gray-500 hover:text-gray-700 py-2">
                    Not you? <span class="text-brand-600 font-semibold">Log out</span>
                </button>
            </form>
        </div>
    </div>

</body>
</html>
