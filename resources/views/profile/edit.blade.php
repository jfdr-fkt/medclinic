@extends('layouts.app')
@section('title', 'My Profile & Settings')
@section('page-title', 'My Profile')

@section('content')
@php
    $bannerColor = match($user->role) {
        'admin'     => 'bg-slate-700',
        'doctor'    => 'bg-blue-600',
        'nurse'     => 'bg-teal-600',
        'assistant' => 'bg-emerald-600',
        default     => 'bg-brand-600',
    };
    $avatarGrad = match($user->role) {
        'admin'     => 'from-slate-500 to-slate-700',
        'doctor'    => 'from-blue-500 to-blue-700',
        'nurse'     => 'from-cyan-500 to-teal-600',
        'assistant' => 'from-emerald-400 to-emerald-600',
        default     => 'from-brand-400 to-brand-700',
    };
    $statusKey = $user->statusColor() === 'emerald' ? 'available'
              : ($user->statusColor() === 'red' ? 'busy'
              : ($user->statusColor() === 'amber' ? 'away' : 'offline'));
@endphp

<div class="space-y-6 max-w-3xl">

    <!-- ── Profile header ── -->
    <div class="card overflow-hidden">
        <div class="h-32 {{ $bannerColor }} relative">
            <div class="absolute -top-10 -right-10 w-48 h-48 bg-white/5 rounded-full"></div>
            <div class="absolute bottom-4 right-6 text-white/30 text-xs font-medium uppercase tracking-widest">{{ $user->role }} profile</div>
        </div>

        <div class="px-6 pb-6 -mt-14">
            <div class="flex items-end gap-4 flex-wrap">
                <div class="relative flex-shrink-0">
                    @if($user->avatarUrl())
                    <img src="{{ $user->avatarUrl() }}" alt="{{ $user->name }}"
                         class="h-28 w-28 rounded-2xl object-cover ring-4 ring-white shadow-lg">
                    @else
                    <div class="h-28 w-28 rounded-2xl bg-gradient-to-br {{ $avatarGrad }} flex items-center justify-center text-white text-4xl font-bold ring-4 ring-white shadow-lg">
                        {{ strtoupper(substr($user->name, 0, 2)) }}
                    </div>
                    @endif
                    <button type="button" onclick="document.getElementById('avatarInput').click()"
                            class="absolute -bottom-1 -right-1 w-9 h-9 bg-brand-600 hover:bg-brand-700 text-white rounded-full flex items-center justify-center shadow-md ring-2 ring-white transition-colors"
                            title="Change avatar">
                        <i class="fa-solid fa-camera text-sm"></i>
                    </button>
                </div>
                <div class="pb-2 flex-1 min-w-0">
                    <h2 class="text-2xl font-extrabold text-gray-900 dark:text-white truncate">{{ $user->name }}</h2>
                    <div class="flex items-center gap-2 mt-1 flex-wrap">
                        <span class="inline-flex items-center gap-1.5 text-xs font-bold uppercase tracking-wider {{ $bannerColor }} text-white px-2 py-1 rounded-md">
                            {{ $user->role }}
                        </span>
                        @if($user->specialization)
                        <span class="text-sm text-gray-500">{{ $user->specialization }}</span>
                        @endif
                    </div>
                    <p class="text-xs text-gray-500 mt-1.5 flex items-center gap-1.5">
                        <span class="status-dot {{ $statusKey }}"></span>
                        Status: <span class="font-medium text-gray-700 dark:text-gray-300">{{ $user->statusLabel() }}</span>
                    </p>
                </div>
                @if($user->avatarUrl())
                <form method="POST" action="{{ route('profile.avatar.remove') }}" class="pb-2"
                      onsubmit="return confirm('Remove your avatar?')">
                    @csrf @method('DELETE')
                    <button type="submit" class="inline-flex items-center gap-2 text-sm text-red-600 hover:text-red-700 font-medium px-3 py-1.5 rounded-lg hover:bg-red-50">
                        <i class="fa-solid fa-trash"></i> Remove avatar
                    </button>
                </form>
                @endif
            </div>
        </div>
    </div>

    <!-- Hidden avatar upload (triggered by camera button) -->
    <form id="avatarForm" method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data" class="hidden">
        @csrf @method('PUT')
        <input type="hidden" name="name" value="{{ $user->name }}">
        <input type="hidden" name="email" value="{{ $user->email }}">
        <input type="hidden" name="phone" value="{{ $user->phone }}">
        <input type="hidden" name="specialization" value="{{ $user->specialization }}">
        <input type="hidden" name="bio" value="{{ $user->bio }}">
        <input type="file" id="avatarInput" name="avatar" accept="image/jpeg,image/png,image/webp" onchange="document.getElementById('avatarForm').submit()" form="avatarForm">
    </form>

    <!-- ── Personal Information ── -->
    <div class="card overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 dark:border-slate-700 flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-blue-100 text-blue-600 flex items-center justify-center">
                <i class="fa-solid fa-user-pen"></i>
            </div>
            <div>
                <h3 class="font-bold text-gray-900 dark:text-white text-base">Personal Information</h3>
                <p class="text-xs text-gray-500">Your basic contact details</p>
            </div>
        </div>

        <form method="POST" action="{{ route('profile.update') }}" class="p-6 space-y-5">
            @csrf @method('PUT')
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <div>
                    <label for="pf_name" class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-2 uppercase tracking-wider">Full Name</label>
                    <input id="pf_name" type="text" name="name" required value="{{ old('name', $user->name) }}" class="input" placeholder="Your full name">
                </div>
                <div>
                    <label for="pf_email" class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-2 uppercase tracking-wider">Email Address</label>
                    <input id="pf_email" type="email" name="email" required value="{{ old('email', $user->email) }}" class="input" placeholder="you@clinic.com">
                </div>
                <div>
                    <label for="pf_phone" class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-2 uppercase tracking-wider">Phone</label>
                    <input id="pf_phone" type="tel" name="phone" value="{{ old('phone', $user->phone) }}" class="input" placeholder="09XX-XXX-XXXX">
                </div>
                <div>
                    <label for="pf_spec" class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-2 uppercase tracking-wider">Specialization / Job Title</label>
                    <input id="pf_spec" type="text" name="specialization" value="{{ old('specialization', $user->specialization) }}" class="input" placeholder="e.g. Cardiology, IT Admin">
                </div>
                <div class="sm:col-span-2">
                    <label for="pf_bio" class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-2 uppercase tracking-wider">Bio</label>
                    <textarea id="pf_bio" name="bio" rows="3" class="input resize-y" placeholder="A short bio about yourself…">{{ old('bio', $user->bio) }}</textarea>
                </div>
            </div>
            <div class="flex justify-end pt-4 border-t border-gray-100 dark:border-slate-700">
                <button type="submit" class="px-6 py-2.5 bg-brand-600 hover:bg-brand-700 text-white text-sm font-semibold rounded-xl transition-colors shadow-sm inline-flex items-center gap-2">
                    <i class="fa-solid fa-floppy-disk"></i> Save Changes
                </button>
            </div>
        </form>
    </div>

    <!-- ── Display & Accessibility ── -->
    <div class="card overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 dark:border-slate-700 flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-amber-100 text-amber-600 flex items-center justify-center">
                <i class="fa-solid fa-gear"></i>
            </div>
            <div>
                <h3 class="font-bold text-gray-900 dark:text-white text-base">Display & Accessibility</h3>
                <p class="text-xs text-gray-500">Customize how the interface looks</p>
            </div>
        </div>

        <div class="p-6 space-y-3">
            <!-- Theme -->
            <div class="flex items-center justify-between gap-3 p-4 bg-gray-50 dark:bg-slate-800 rounded-xl border border-gray-100 dark:border-slate-700 flex-wrap">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-lg bg-amber-100 text-amber-600 flex items-center justify-center">
                        <i class="fa-solid fa-palette text-sm"></i>
                    </div>
                    <div>
                        <p class="font-semibold text-gray-800 dark:text-white text-sm">Theme</p>
                        <p class="text-xs text-gray-500">Light or dark mode</p>
                    </div>
                </div>
                <div class="flex items-center bg-white dark:bg-slate-900 border border-gray-200 dark:border-slate-700 rounded-xl p-1 gap-1">
                    <button type="button" onclick="setTheme('light')"
                            class="px-3 py-1.5 rounded-lg text-xs font-semibold flex items-center gap-1.5 transition-colors {{ $user->theme === 'light' ? 'bg-amber-100 text-amber-700' : 'text-gray-500 hover:text-gray-700' }}">
                        <i class="fa-solid fa-sun"></i> Light
                    </button>
                    <button type="button" onclick="setTheme('dark')"
                            class="px-3 py-1.5 rounded-lg text-xs font-semibold flex items-center gap-1.5 transition-colors {{ $user->theme === 'dark' ? 'bg-slate-700 text-white' : 'text-gray-500 hover:text-gray-700' }}">
                        <i class="fa-solid fa-moon"></i> Dark
                    </button>
                </div>
            </div>

            <form method="POST" action="{{ route('profile.appearance') }}" class="space-y-3">
                @csrf @method('PUT')

                <!-- Font size -->
                <div class="flex items-center justify-between gap-3 p-4 bg-gray-50 dark:bg-slate-800 rounded-xl border border-gray-100 dark:border-slate-700 flex-wrap">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-lg bg-blue-100 text-blue-600 flex items-center justify-center">
                            <i class="fa-solid fa-text-height text-sm"></i>
                        </div>
                        <div>
                            <p class="font-semibold text-gray-800 dark:text-white text-sm">Font size</p>
                            <p class="text-xs text-gray-500">Larger text helps with readability</p>
                        </div>
                    </div>
                    <div class="flex items-center bg-white dark:bg-slate-900 border border-gray-200 dark:border-slate-700 rounded-xl p-1 gap-1">
                        @php $sizes = ['sm'=>'text-xs','md'=>'text-sm','lg'=>'text-base','xl'=>'text-lg']; @endphp
                        @foreach(['sm','md','lg','xl'] as $key)
                        <label class="cursor-pointer px-3 py-1.5 rounded-lg font-bold flex items-center transition-colors {{ ($user->font_size ?? 'md') === $key ? 'bg-blue-100 text-blue-700' : 'text-gray-500 hover:text-gray-700' }} {{ $sizes[$key] }}">
                            <input type="radio" name="font_size" value="{{ $key }}" {{ ($user->font_size ?? 'md') === $key ? 'checked' : '' }} class="sr-only" onchange="this.form.submit()">
                            A
                        </label>
                        @endforeach
                    </div>
                </div>

                <!-- Color-blind mode -->
                <div class="flex items-center justify-between gap-3 p-4 bg-gray-50 dark:bg-slate-800 rounded-xl border border-gray-100 dark:border-slate-700 flex-wrap">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-lg bg-purple-100 text-purple-600 flex items-center justify-center">
                            <i class="fa-solid fa-eye text-sm"></i>
                        </div>
                        <div>
                            <p class="font-semibold text-gray-800 dark:text-white text-sm">Colorblind-friendly palette</p>
                            <p class="text-xs text-gray-500">Shifts red/green to orange/blue for status indicators</p>
                        </div>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="hidden" name="colorblind_mode" value="0">
                        <input type="checkbox" name="colorblind_mode" value="1" {{ $user->colorblind_mode ? 'checked' : '' }} class="sr-only peer" onchange="this.form.submit()">
                        <div class="w-11 h-6 bg-gray-300 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-purple-600"></div>
                    </label>
                </div>
            </form>
        </div>
    </div>

    <!-- ── Change Password ── -->
    <div class="card overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 dark:border-slate-700 flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-red-100 text-red-600 flex items-center justify-center">
                <i class="fa-solid fa-lock"></i>
            </div>
            <div>
                <h3 class="font-bold text-gray-900 dark:text-white text-base">Change Password</h3>
                <p class="text-xs text-gray-500">Pick something new and strong</p>
            </div>
        </div>

        <form method="POST" action="{{ route('profile.password') }}" class="p-6 space-y-5">
            @csrf @method('PUT')
            <div>
                <label for="pf_curpw" class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-2 uppercase tracking-wider">Current Password</label>
                <input id="pf_curpw" type="password" name="current_password" required class="input">
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <div>
                    <label for="pf_newpw" class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-2 uppercase tracking-wider">New Password</label>
                    <input id="pf_newpw" type="password" name="password" required minlength="6" class="input">
                </div>
                <div>
                    <label for="pf_confpw" class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-2 uppercase tracking-wider">Confirm Password</label>
                    <input id="pf_confpw" type="password" name="password_confirmation" required minlength="6" class="input">
                </div>
            </div>
            <div class="flex justify-end pt-4 border-t border-gray-100 dark:border-slate-700">
                <button type="submit" class="px-6 py-2.5 bg-brand-600 hover:bg-brand-700 text-white text-sm font-semibold rounded-xl transition-colors shadow-sm inline-flex items-center gap-2">
                    <i class="fa-solid fa-key"></i> Update Password
                </button>
            </div>
        </form>
    </div>

    <!-- ── Account info ── -->
    <div class="card p-6">
        <div class="grid grid-cols-2 gap-4 text-sm">
            <div>
                <p class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-1">Role</p>
                <p class="font-semibold text-gray-900 dark:text-white capitalize">{{ $user->role }}</p>
            </div>
            <div>
                <p class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-1">Joined</p>
                <p class="font-semibold text-gray-900 dark:text-white">{{ $user->created_at->format('F j, Y') }}</p>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function setTheme(theme) {
    document.documentElement.classList.toggle('dark', theme === 'dark');
    localStorage.setItem('theme', theme);
    fetch('{{ route("profile.theme") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
            'Accept': 'application/json',
        },
        body: JSON.stringify({ theme })
    }).then(() => location.reload());
}
</script>
@endpush
@endsection
