@extends('layouts.app')
@section('title', 'Dashboard')
@section('page-title', 'Dashboard')

@if(session('just_logged_in'))
@push('scripts')
<script>
    // Back-button trap: keep the user on the dashboard right after login.
    // We push a duplicate history entry and re-push on every popstate so
    // pressing back stays on /dashboard. Subsequent dashboard visits do
    // not install the trap (flash is one-shot), so footsteps work normally.
    (function () {
        history.pushState({ locked: true }, '', location.href);
        window.addEventListener('popstate', function () {
            history.pushState({ locked: true }, '', location.href);
        });
    })();
</script>
@endpush
@endif

@section('content')
<div class="space-y-6">

    <!-- ── Welcome strip (rounded) ── -->
    <div class="relative rounded-2xl overflow-hidden bg-gradient-to-r from-brand-600 via-brand-700 to-brand-800 px-6 py-7 text-white shadow-sm">
        <div class="absolute -top-10 -right-10 w-40 h-40 bg-white/5 rounded-full"></div>
        <div class="absolute top-1/2 right-32 w-24 h-24 bg-white/5 rounded-full"></div>
        <div class="relative flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
                <h1 class="text-2xl font-bold">
                    Good {{ now()->hour < 12 ? 'morning' : (now()->hour < 18 ? 'afternoon' : 'evening') }},
                    {{ Str::before(Auth::user()->name, ' ') }}
                </h1>
                <p class="text-white/80 text-sm mt-1">{{ date('l, F j, Y') }} &bull; Here's what's happening at the clinic today</p>
            </div>
            <a href="{{ route('patients.index') }}" class="inline-flex items-center gap-2 bg-white/15 hover:bg-white/25 backdrop-blur-sm border border-white/20 text-white px-4 py-2.5 rounded-xl text-sm font-semibold transition-colors w-fit">
                <i class="fa-solid fa-user-plus"></i> Add New Patient
            </a>
        </div>
    </div>

    <!-- ── Colorful Stat cards ── -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <a href="{{ route('patients.index') }}"
           class="rounded-2xl p-5 bg-gradient-to-br from-blue-50 to-blue-100/50 border-2 border-blue-200 hover:border-blue-400 hover:shadow-lg hover:shadow-blue-100 transition-all group">
            <div class="flex items-center justify-between mb-3">
                <div class="w-11 h-11 rounded-xl bg-blue-500 flex items-center justify-center shadow-md shadow-blue-200">
                    <i class="fa-solid fa-user-group text-white"></i>
                </div>
                <i class="fa-solid fa-arrow-right text-blue-300 group-hover:translate-x-1 transition-transform"></i>
            </div>
            <p class="text-3xl font-extrabold text-blue-900">{{ $todayPatients }}</p>
            <p class="text-sm font-semibold text-blue-700/70 mt-1">Patients today</p>
        </a>

        <a href="{{ route('medicines.index', ['low_stock'=>1]) }}"
           class="rounded-2xl p-5 bg-gradient-to-br from-amber-50 to-amber-100/50 border-2 border-amber-200 hover:border-amber-400 hover:shadow-lg hover:shadow-amber-100 transition-all group">
            <div class="flex items-center justify-between mb-3">
                <div class="w-11 h-11 rounded-xl bg-amber-500 flex items-center justify-center shadow-md shadow-amber-200">
                    <i class="fa-solid fa-triangle-exclamation text-white"></i>
                </div>
                <i class="fa-solid fa-arrow-right text-amber-300 group-hover:translate-x-1 transition-transform"></i>
            </div>
            <p class="text-3xl font-extrabold text-amber-900">{{ $lowStockMedicines->count() }}</p>
            <p class="text-sm font-semibold text-amber-700/70 mt-1">Low stock items</p>
        </a>

        <a href="{{ route('medicines.index', ['expiring'=>1]) }}"
           class="rounded-2xl p-5 bg-gradient-to-br from-orange-50 to-orange-100/50 border-2 border-orange-200 hover:border-orange-400 hover:shadow-lg hover:shadow-orange-100 transition-all group">
            <div class="flex items-center justify-between mb-3">
                <div class="w-11 h-11 rounded-xl bg-orange-500 flex items-center justify-center shadow-md shadow-orange-200">
                    <i class="fa-solid fa-calendar-xmark text-white"></i>
                </div>
                <i class="fa-solid fa-arrow-right text-orange-300 group-hover:translate-x-1 transition-transform"></i>
            </div>
            <p class="text-3xl font-extrabold text-orange-900">{{ $expiringSoon->count() }}</p>
            <p class="text-sm font-semibold text-orange-700/70 mt-1">Expiring ≤30 days</p>
        </a>

        <a href="{{ route('staff.index') }}"
           class="rounded-2xl p-5 bg-gradient-to-br from-indigo-50 to-indigo-100/50 border-2 border-indigo-200 hover:border-indigo-400 hover:shadow-lg hover:shadow-indigo-100 transition-all group">
            <div class="flex items-center justify-between mb-3">
                <div class="w-11 h-11 rounded-xl bg-indigo-500 flex items-center justify-center shadow-md shadow-indigo-200">
                    <i class="fa-solid fa-user-doctor text-white"></i>
                </div>
                <i class="fa-solid fa-arrow-right text-indigo-300 group-hover:translate-x-1 transition-transform"></i>
            </div>
            <p class="text-3xl font-extrabold text-indigo-900">{{ $onlineStaff->count() }}</p>
            <p class="text-sm font-semibold text-indigo-700/70 mt-1">Staff online</p>
        </a>
    </div>

    <!-- ── Quick Actions ── -->
    <div class="card p-5">
        <h3 class="font-bold text-gray-900 text-sm mb-4 flex items-center gap-2">
            <i class="fa-solid fa-bolt text-brand-500"></i> Quick Actions
        </h3>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
            <a href="{{ route('patients.index') }}" class="flex items-center gap-3 p-3 rounded-xl border-2 border-gray-100 hover:border-blue-300 hover:bg-blue-50/50 transition-all">
                <div class="w-10 h-10 rounded-lg bg-blue-100 text-blue-600 flex items-center justify-center flex-shrink-0">
                    <i class="fa-solid fa-user-injured"></i>
                </div>
                <div class="min-w-0">
                    <p class="text-sm font-semibold text-gray-800 truncate">Patients</p>
                    <p class="text-xs text-gray-400">Manage records</p>
                </div>
            </a>
            <a href="{{ route('scan.index') }}" class="flex items-center gap-3 p-3 rounded-xl border-2 border-gray-100 hover:border-purple-300 hover:bg-purple-50/50 transition-all">
                <div class="w-10 h-10 rounded-lg bg-purple-100 text-purple-600 flex items-center justify-center flex-shrink-0">
                    <i class="fa-solid fa-plus"></i>
                </div>
                <div class="min-w-0">
                    <p class="text-sm font-semibold text-gray-800 truncate">Add Medicine</p>
                    <p class="text-xs text-gray-400">Add to inventory</p>
                </div>
            </a>
            <a href="{{ route('medicines.index') }}" class="flex items-center gap-3 p-3 rounded-xl border-2 border-gray-100 hover:border-emerald-300 hover:bg-emerald-50/50 transition-all">
                <div class="w-10 h-10 rounded-lg bg-emerald-100 text-emerald-600 flex items-center justify-center flex-shrink-0">
                    <i class="fa-solid fa-pills"></i>
                </div>
                <div class="min-w-0">
                    <p class="text-sm font-semibold text-gray-800 truncate">Inventory</p>
                    <p class="text-xs text-gray-400">View stock</p>
                </div>
            </a>
            <a href="{{ route('chat.index') }}" class="flex items-center gap-3 p-3 rounded-xl border-2 border-gray-100 hover:border-amber-300 hover:bg-amber-50/50 transition-all">
                <div class="w-10 h-10 rounded-lg bg-amber-100 text-amber-600 flex items-center justify-center flex-shrink-0">
                    <i class="fa-solid fa-comments"></i>
                </div>
                <div class="min-w-0">
                    <p class="text-sm font-semibold text-gray-800 truncate">Messages</p>
                    <p class="text-xs text-gray-400">Staff chat</p>
                </div>
            </a>
        </div>
    </div>

    <!-- ── Two-column section ── -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">

        <!-- Pinned Patients (blue, since patients = blue domain) -->
        <div class="rounded-2xl p-5 lg:col-span-2 border-2 border-blue-200 bg-gradient-to-br from-blue-50/40 to-blue-100/20">
            <div class="flex items-center justify-between mb-4">
                <h3 class="font-bold text-gray-900 text-sm flex items-center gap-2">
                    <i class="fa-solid fa-thumbtack text-amber-500"></i> Pinned Patients
                </h3>
                <a href="{{ route('patients.index') }}" class="text-xs text-blue-600 hover:underline font-medium">View all →</a>
            </div>
            @if($myPinnedPatients->count() > 0)
            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                @foreach($myPinnedPatients as $p)
                <a href="{{ route('patients.show', $p) }}" class="flex items-center gap-3 p-3 rounded-xl border-2 border-blue-100 bg-white/60 hover:border-blue-400 hover:bg-blue-50 transition-all">
                    <div class="w-10 h-10 rounded-full bg-gradient-to-br from-blue-400 to-blue-700 flex items-center justify-center text-white text-sm font-bold flex-shrink-0">
                        {{ strtoupper(substr($p->name, 0, 2)) }}
                    </div>
                    <div class="min-w-0 flex-1">
                        <p class="text-sm font-semibold text-gray-800 truncate">{{ $p->name }}</p>
                        <p class="text-xs text-gray-400 truncate">{{ $p->doctor?->name ?? 'No doctor' }}</p>
                    </div>
                </a>
                @endforeach
            </div>
            @else
            <div class="text-center py-8">
                <div class="w-12 h-12 bg-amber-50 rounded-2xl flex items-center justify-center mx-auto mb-2">
                    <i class="fa-solid fa-thumbtack text-amber-300"></i>
                </div>
                <p class="text-sm text-gray-500">No pinned patients yet</p>
                <p class="text-xs text-gray-400 mt-0.5">Pin patients from the Patients page for quick access</p>
            </div>
            @endif
        </div>

        <!-- Online Staff (indigo, staff domain) -->
        <div class="rounded-2xl p-5 border-2 border-indigo-200 bg-gradient-to-br from-indigo-50/40 to-indigo-100/20">
            <div class="flex items-center justify-between mb-4">
                <h3 class="font-bold text-gray-900 text-sm flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-indigo-400 animate-pulse"></span> Online Staff
                </h3>
                <a href="{{ route('staff.index') }}" class="text-xs text-indigo-600 hover:underline font-medium">All →</a>
            </div>
            @if($onlineStaff->count() > 0)
            <div class="space-y-2.5">
                @foreach($onlineStaff->take(5) as $s)
                @php
                    $roleGrad = match($s->role) {
                        'admin'     => 'from-slate-500 to-slate-700',
                        'doctor'    => 'from-blue-500 to-blue-700',
                        'nurse'     => 'from-cyan-500 to-teal-600',
                        'assistant' => 'from-emerald-400 to-emerald-600',
                        default     => 'from-amber-400 to-amber-600',
                    };
                @endphp
                <a href="{{ route('chat.index', ['with' => $s->id]) }}" class="flex items-center gap-3 p-2 rounded-xl hover:bg-white/60 transition-colors">
                    <div class="relative">
                        @if($s->avatarUrl())
                        <img src="{{ $s->avatarUrl() }}" alt="{{ $s->name }}" class="w-9 h-9 rounded-full object-cover">
                        @else
                        <div class="w-9 h-9 rounded-full bg-gradient-to-br {{ $roleGrad }} flex items-center justify-center text-white text-xs font-bold">
                            {{ strtoupper(substr($s->name, 0, 2)) }}
                        </div>
                        @endif
                        <span class="absolute -bottom-0.5 -right-0.5 w-2.5 h-2.5 bg-emerald-400 rounded-full ring-2 ring-white"></span>
                    </div>
                    <div class="min-w-0 flex-1">
                        <p class="text-sm font-semibold text-gray-800 truncate">{{ $s->name }}</p>
                        <p class="text-xs text-gray-400 capitalize">{{ $s->role }}</p>
                    </div>
                    <i class="fa-solid fa-message text-gray-300 text-xs"></i>
                </a>
                @endforeach
            </div>
            @else
            <div class="text-center py-6">
                <p class="text-sm text-gray-400">No staff online right now</p>
            </div>
            @endif
        </div>
    </div>

    <!-- ── Low Stock alerts ── -->
    @if($lowStockMedicines->count() > 0)
    <div class="card p-5 border-2 border-amber-100">
        <div class="flex items-center justify-between mb-4">
            <h3 class="font-bold text-gray-900 text-sm flex items-center gap-2">
                <i class="fa-solid fa-triangle-exclamation text-amber-500"></i> Low Stock Alerts
            </h3>
            <a href="{{ route('medicines.index', ['low_stock'=>1]) }}" class="text-xs text-brand-600 hover:underline font-medium">View all →</a>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
            @foreach($lowStockMedicines as $m)
            @php $qty = $m->latestInventory?->quantity ?? 0; @endphp
            <a href="{{ route('medicines.show', $m) }}" class="flex items-center gap-3 p-3 rounded-xl border-2 border-amber-100 bg-amber-50/40 hover:bg-amber-50 hover:border-amber-300 transition-colors">
                <div class="w-9 h-9 rounded-lg bg-amber-100 text-amber-700 flex items-center justify-center font-bold text-xs flex-shrink-0">
                    {{ strtoupper(substr($m->name, 0, 1)) }}
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-semibold text-gray-800 truncate">{{ $m->name }}</p>
                    <p class="text-xs text-gray-500 truncate">{{ $m->location?->full_location ?? 'No location' }}</p>
                </div>
                <span class="text-sm font-bold {{ $qty <= 5 ? 'text-red-600' : 'text-amber-700' }} flex-shrink-0">{{ $qty }}u</span>
            </a>
            @endforeach
        </div>
    </div>
    @endif

</div>
@endsection
