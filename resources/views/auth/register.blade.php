<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>ClinicMS — Create Account</title>
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
        .input-plain { display:block; width:100%; padding:.75rem 1rem; border:1.5px solid #e5e7eb; border-radius:.75rem; font-size:.875rem; outline:none; transition:.15s; background:white; }
        .input-plain:focus { border-color:#0d9488; box-shadow:0 0 0 3px rgba(13,148,136,.12); }
        .hero-bg { background-image:url('https://images.unsplash.com/photo-1538108149393-fbbd81895907?auto=format&fit=crop&w=1200&q=80'); background-size:cover; background-position:center; }

        /* ── Custom select (same look as the app's cs-select) ── */
        .cs-wrapper { position: relative; }
        .cs-trigger {
            display: flex !important;
            align-items: center;
            justify-content: space-between;
            gap: .5rem;
            cursor: pointer;
            text-align: left;
            user-select: none;
            padding-right: .875rem !important;
        }
        .cs-trigger .cs-value { flex: 1; min-width: 0; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
        .cs-trigger .cs-chevron {
            flex-shrink: 0; width: 1.125rem; height: 1.125rem;
            color: #6b7280; transition: transform .2s, color .2s;
        }
        .cs-wrapper.open .cs-chevron { transform: rotate(180deg); color: #0d9488; }
        .cs-wrapper.open .cs-trigger { border-color: #0d9488; box-shadow: 0 0 0 3px rgba(13,148,136,.18); }
        .cs-panel {
            position: absolute; top: calc(100% + 6px); left: 0; right: 0; z-index: 50;
            background: #fff; border: 1.5px solid #e5e7eb; border-radius: .9rem;
            box-shadow: 0 10px 30px rgba(0,0,0,.14);
            overflow: hidden; max-height: 16rem; overflow-y: auto;
            animation: csDropIn .15s ease;
        }
        @keyframes csDropIn { from { opacity: 0; transform: translateY(-6px); } to { opacity: 1; transform: translateY(0); } }
        .cs-option { padding: .55rem .9rem; cursor: pointer; font-size: .875rem; color: #111827; transition: background .1s; }
        .cs-option:hover { background: #f3f4f6; }
        .cs-option.selected { background: #ecfdf5; color: #065f46; font-weight: 700; }
    </style>
</head>
<body class="min-h-screen flex bg-slate-50">

    <div class="hidden lg:flex lg:w-1/2 hero-bg relative">
        <div class="absolute inset-0 bg-gradient-to-br from-slate-900/80 via-brand-800/70 to-brand-700/80"></div>
        <div class="relative z-10 flex flex-col justify-end p-12 text-white">
            <div class="w-12 h-12 bg-white/15 border border-white/20 rounded-2xl flex items-center justify-center mb-6 backdrop-blur-sm">
                <i class="fa-solid fa-staff-snake text-white text-xl"></i>
            </div>
            <h2 class="text-4xl font-extrabold mb-3 leading-tight">Join the team.</h2>
            <p class="text-white/80 text-base max-w-md">Create your ClinicMS account to start managing patients, inventory, and your clinic team.</p>
        </div>
    </div>

    <div class="flex-1 flex items-center justify-center p-6">
        <div class="w-full max-w-md">
            <div class="lg:hidden text-center mb-6">
                <div class="w-14 h-14 bg-brand-600 rounded-2xl flex items-center justify-center mx-auto mb-3 shadow-lg shadow-brand-500/30">
                    <i class="fa-solid fa-staff-snake text-white text-xl"></i>
                </div>
                <h1 class="text-2xl font-bold text-gray-900">ClinicMS</h1>
            </div>

            <div class="bg-white rounded-2xl shadow-xl border border-gray-100 overflow-hidden">
                <div class="px-8 pt-8 pb-5">
                    <h2 class="text-2xl font-bold text-gray-900">Create your account</h2>
                    <p class="text-gray-500 text-sm mt-1">Sign up as a clinic staff member</p>
                </div>

                @if($errors->any())
                <div class="mx-8 mb-4 p-3.5 bg-red-50 border border-red-200 rounded-xl text-sm text-red-700">
                    <i class="fa-solid fa-circle-exclamation text-red-500 mr-2"></i>{{ $errors->first() }}
                </div>
                @endif

                <form method="POST" action="{{ route('register.post') }}" class="px-8 pb-8 space-y-4">
                    @csrf

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1.5">Full Name</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none"><i class="fa-solid fa-user text-gray-400 text-sm"></i></div>
                            <input type="text" name="name" value="{{ old('name') }}" required class="input-field" placeholder="Dr. Maria Santos">
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1.5">Email Address</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none"><i class="fa-solid fa-envelope text-gray-400 text-sm"></i></div>
                            <input type="email" name="email" value="{{ old('email') }}" required class="input-field" placeholder="you@clinic.com">
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1.5">Role</label>
                            <select name="role" required class="input-plain cs-select">
                                <option value="nurse"     {{ old('role')==='nurse'?'selected':'' }}>Nurse</option>
                                <option value="doctor"    {{ old('role')==='doctor'?'selected':'' }}>Doctor</option>
                                <option value="assistant" {{ old('role')==='assistant'?'selected':'' }}>Assistant</option>
                                <option value="admin"     {{ old('role')==='admin'?'selected':'' }}>Admin</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1.5">Phone</label>
                            <input type="tel" name="phone" value="{{ old('phone') }}" class="input-plain" placeholder="09XX-XXX-XXXX">
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1.5">Password</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none"><i class="fa-solid fa-lock text-gray-400 text-sm"></i></div>
                            <input type="password" name="password" required minlength="6" class="input-field" placeholder="Min. 6 characters">
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1.5">Confirm Password</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none"><i class="fa-solid fa-lock text-gray-400 text-sm"></i></div>
                            <input type="password" name="password_confirmation" required minlength="6" class="input-field" placeholder="Re-enter password">
                        </div>
                    </div>

                    <button type="submit" class="w-full bg-brand-600 hover:bg-brand-700 text-white font-semibold py-3 rounded-xl transition-colors flex items-center justify-center gap-2 shadow-sm mt-2">
                        <i class="fa-solid fa-user-plus"></i> Create Account
                    </button>

                    <p class="text-sm text-center text-gray-500 pt-1">
                        Already have an account?
                        <a href="{{ route('login') }}" class="text-brand-600 hover:text-brand-700 font-semibold">Log in</a>
                    </p>
                </form>
            </div>
        </div>
    </div>

    <script>
    // Custom select — wraps any <select.cs-select> with a styled trigger + rounded panel.
    (function () {
        const CHEVRON = '<svg class="cs-chevron" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="none"><path d="M5 7.5L10 12.5L15 7.5" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"/></svg>';

        function init(native) {
            if (native.dataset.csInited) return;
            native.dataset.csInited = '1';

            const wrapper = document.createElement('div');
            wrapper.className = 'cs-wrapper';
            native.parentNode.insertBefore(wrapper, native);
            wrapper.appendChild(native);
            native.style.cssText = 'position:absolute;opacity:0;width:1px;height:1px;pointer-events:none;';

            const trigger = document.createElement('button');
            trigger.type = 'button';
            trigger.className = (native.className.replace('cs-select', '').trim() + ' cs-trigger').replace(/\s+/g, ' ');
            trigger.innerHTML = '<span class="cs-value"></span>' + CHEVRON;
            wrapper.appendChild(trigger);

            const panel = document.createElement('div');
            panel.className = 'cs-panel hidden';
            Array.from(native.options).forEach(opt => {
                const item = document.createElement('div');
                item.className = 'cs-option';
                item.dataset.value = opt.value;
                item.textContent = opt.textContent;
                item.addEventListener('click', e => {
                    e.stopPropagation();
                    native.value = opt.value;
                    native.dispatchEvent(new Event('change', { bubbles: true }));
                    close(wrapper);
                });
                panel.appendChild(item);
            });
            wrapper.appendChild(panel);

            update(wrapper);
            native.addEventListener('change', () => update(wrapper));
            trigger.addEventListener('click', e => {
                e.stopPropagation();
                wrapper.classList.contains('open') ? close(wrapper) : open(wrapper);
            });
        }

        function update(wrapper) {
            const sel = wrapper.querySelector('select');
            const span = wrapper.querySelector('.cs-value');
            const opt = sel.options[sel.selectedIndex];
            span.textContent = opt ? opt.textContent : '';
            wrapper.querySelectorAll('.cs-option').forEach(item => {
                item.classList.toggle('selected', opt && item.dataset.value === sel.value);
            });
        }
        function open(w) {
            document.querySelectorAll('.cs-wrapper.open').forEach(o => { if (o !== w) close(o); });
            w.classList.add('open');
            w.querySelector('.cs-panel').classList.remove('hidden');
        }
        function close(w) {
            w.classList.remove('open');
            w.querySelector('.cs-panel')?.classList.add('hidden');
        }
        document.addEventListener('click', () => document.querySelectorAll('.cs-wrapper.open').forEach(close));
        document.addEventListener('keydown', e => { if (e.key === 'Escape') document.querySelectorAll('.cs-wrapper.open').forEach(close); });

        document.querySelectorAll('select.cs-select').forEach(init);
    })();
    </script>
</body>
</html>
