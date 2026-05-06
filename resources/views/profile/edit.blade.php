@extends('layouts.app')
@section('title', 'My Profile')
@section('page-title', 'My Profile')

@section('content')
<div class="space-y-5 max-w-3xl">

    <!-- Header card -->
    <div class="card overflow-hidden">
        <div class="h-24 bg-gradient-to-r from-brand-500 via-brand-600 to-brand-700"></div>
        <div class="px-6 pb-5 -mt-12">
            <div class="flex items-end gap-4">
                <div class="h-24 w-24 rounded-2xl bg-gradient-to-br from-brand-400 to-brand-700 flex items-center justify-center text-white text-3xl font-bold ring-4 ring-white shadow-lg">
                    {{ strtoupper(substr($user->name, 0, 2)) }}
                </div>
                <div class="pb-2">
                    <h2 class="text-xl font-bold text-gray-900">{{ $user->name }}</h2>
                    <p class="text-sm text-gray-500 capitalize flex items-center gap-1.5">
                        <span class="status-dot {{ $user->statusColor() === 'emerald' ? 'available' : ($user->statusColor() === 'red' ? 'busy' : ($user->statusColor() === 'amber' ? 'away' : 'offline')) }}"></span>
                        {{ $user->role }} &bull; {{ $user->statusLabel() }}
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Profile info -->
    <div class="card p-6">
        <h3 class="font-bold text-gray-900 text-base mb-1">Personal Information</h3>
        <p class="text-sm text-gray-500 mb-5">Update your account details</p>

        <form method="POST" action="{{ route('profile.update') }}" class="space-y-4">
            @csrf @method('PUT')
            <div class="grid grid-cols-2 gap-4">
                <div class="col-span-2 sm:col-span-1">
                    <label class="label">Full Name</label>
                    <input type="text" name="name" required value="{{ old('name', $user->name) }}" class="input">
                </div>
                <div class="col-span-2 sm:col-span-1">
                    <label class="label">Email Address</label>
                    <input type="email" name="email" required value="{{ old('email', $user->email) }}" class="input">
                </div>
                <div class="col-span-2 sm:col-span-1">
                    <label class="label">Phone</label>
                    <input type="tel" name="phone" value="{{ old('phone', $user->phone) }}" class="input" placeholder="09XX-XXX-XXXX">
                </div>
                <div class="col-span-2 sm:col-span-1">
                    <label class="label">Specialization</label>
                    <input type="text" name="specialization" value="{{ old('specialization', $user->specialization) }}" class="input" placeholder="e.g. Cardiology">
                </div>
                <div class="col-span-2">
                    <label class="label">Bio</label>
                    <textarea name="bio" rows="3" class="input resize-none" placeholder="A short bio about yourself…">{{ old('bio', $user->bio) }}</textarea>
                </div>
            </div>
            <div class="flex justify-end pt-2 border-t border-gray-100">
                <button type="submit" class="btn-primary">
                    <i class="fa-solid fa-floppy-disk"></i> Save Changes
                </button>
            </div>
        </form>
    </div>

    <!-- Password -->
    <div class="card p-6">
        <h3 class="font-bold text-gray-900 text-base mb-1">Change Password</h3>
        <p class="text-sm text-gray-500 mb-5">Update your account password</p>

        <form method="POST" action="{{ route('profile.password') }}" class="space-y-4">
            @csrf @method('PUT')
            <div>
                <label class="label">Current Password</label>
                <input type="password" name="current_password" required class="input">
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="label">New Password</label>
                    <input type="password" name="password" required minlength="6" class="input">
                </div>
                <div>
                    <label class="label">Confirm Password</label>
                    <input type="password" name="password_confirmation" required minlength="6" class="input">
                </div>
            </div>
            <div class="flex justify-end pt-2 border-t border-gray-100">
                <button type="submit" class="btn-primary">
                    <i class="fa-solid fa-key"></i> Update Password
                </button>
            </div>
        </form>
    </div>

    <!-- Account info -->
    <div class="card p-6 bg-gray-50/50">
        <div class="grid grid-cols-2 gap-4 text-sm">
            <div>
                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1">Role</p>
                <p class="font-medium text-gray-900 capitalize">{{ $user->role }}</p>
            </div>
            <div>
                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1">Joined</p>
                <p class="font-medium text-gray-900">{{ $user->created_at->format('F j, Y') }}</p>
            </div>
        </div>
    </div>
</div>
@endsection
