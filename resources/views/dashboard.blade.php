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

@push('head')
<style>
/* Welcome strip — use inline rgba on decorative circles so the layout's
   .dark [class*="bg-white/..."] catch-all (which converts those to solid
   elevated bg) can't punch visible "holes" into the gradient. */
.welcome-strip {
    background: linear-gradient(to right, #0d9488, #0f766e, #115e59);
    color: #fff;
    border-radius: 1rem;
    padding: 1.75rem 1.5rem;
    box-shadow: 0 1px 3px rgba(0,0,0,.06);
    overflow: hidden;
    position: relative;
}
.dark .welcome-strip {
    background: linear-gradient(to right, #0f766e, #115e59, #042f2e) !important;
    box-shadow: 0 4px 16px rgba(0,0,0,.35);
}
.welcome-strip::before, .welcome-strip::after {
    content: "";
    position: absolute;
    border-radius: 9999px;
    background: rgba(255,255,255,0.06);
    pointer-events: none;
}
.welcome-strip::before { top: -2.5rem; right: -2.5rem; width: 10rem; height: 10rem; }
.welcome-strip::after  { top: 50%; right: 8rem; width: 6rem; height: 6rem; }

/* Dashboard stat cards — bigger numbers, dark-mode-safe colored tints */
.dash-stat {
    display: flex; flex-direction: column; gap: .5rem;
    padding: 1.25rem 1.1rem;
    border-radius: 1rem;
    border: 2px solid transparent;
    transition: transform .12s, box-shadow .12s, border-color .12s;
}
.dash-stat:hover { transform: translateY(-1px); box-shadow: 0 6px 16px rgba(0,0,0,.08); }
.dash-stat .stat-icon {
    width: 2.75rem; height: 2.75rem; border-radius: .9rem;
    display: inline-flex; align-items: center; justify-content: center;
    color: #fff; font-size: 1rem;
    flex-shrink: 0;
}
.dash-stat .stat-number {
    font-size: 2.5rem;
    line-height: 1.05;
    font-weight: 800;
    letter-spacing: -.02em;
}
.dash-stat .stat-label {
    font-size: 0.82rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .04em;
}
.dash-blue   { background:#eff6ff; border-color:#bfdbfe; color:#1e3a8a; }
.dash-blue   .stat-icon { background:#3b82f6; }
.dash-amber  { background:#fffbeb; border-color:#fde68a; color:#78350f; }
.dash-amber  .stat-icon { background:#f59e0b; }
.dash-orange { background:#fff7ed; border-color:#fed7aa; color:#7c2d12; }
.dash-orange .stat-icon { background:#f97316; }
.dash-indigo { background:#eef2ff; border-color:#c7d2fe; color:#312e81; }
.dash-indigo .stat-icon { background:#6366f1; }
.dark .dash-blue   { background: rgba(59,130,246,.14)  !important; border-color: rgba(59,130,246,.4)  !important; color:#93c5fd; }
.dark .dash-amber  { background: rgba(245,158,11,.14)  !important; border-color: rgba(245,158,11,.4)  !important; color:#fcd34d; }
.dark .dash-orange { background: rgba(249,115,22,.14)  !important; border-color: rgba(249,115,22,.4)  !important; color:#fdba74; }
.dark .dash-indigo { background: rgba(99,102,241,.14)  !important; border-color: rgba(99,102,241,.4)  !important; color:#a5b4fc; }

/* Section panels with breathing room */
.dash-panel {
    background:#fff;
    border: 2px solid #e5e7eb;
    border-radius: 1rem;
    box-shadow: 0 1px 3px rgba(0,0,0,.04);
}
.dark .dash-panel { background:#1a2438 !important; border-color:#2d3a52 !important; }

/* Status dot variants for the online-staff list */
.online-dot { width:.625rem; height:.625rem; border-radius:9999px; border:2px solid #fff; }
.dark .online-dot { border-color:#1a2438; }
.online-dot.available { background:#10b981; }
.online-dot.busy      { background:#ef4444; }
.online-dot.away      { background:#f59e0b; }
</style>
@endpush

@section('content')
<div class="space-y-6">

    <!-- ── Welcome strip ── -->
    <div class="welcome-strip">
        <div class="relative flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
                <h1 class="text-2xl font-bold">
                    Good {{ now()->hour < 12 ? 'morning' : (now()->hour < 18 ? 'afternoon' : 'evening') }},
                    {{ Str::before(Auth::user()->name, ' ') }}
                </h1>
                <p class="text-white/80 text-sm mt-1">{{ date('l, F j, Y') }} &bull; Here's what's happening at the clinic today</p>
            </div>
            @if(Auth::user()->can_('patients.view'))
            <a href="{{ route('patients.index') }}" class="inline-flex items-center gap-2 bg-white/15 hover:bg-white/25 border border-white/20 text-white px-4 py-2.5 rounded-xl text-sm font-semibold transition-colors w-fit"
               style="backdrop-filter: blur(4px);">
                <i class="fa-solid fa-user-plus"></i> Open Patient List
            </a>
            @endif
        </div>
    </div>

    <!-- ── Stat cards ── -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <a href="{{ Auth::user()->can_('patients.view') ? route('patients.index') : '#' }}" class="dash-stat dash-blue group">
            <div class="flex items-center justify-between">
                <span class="stat-icon"><i class="fa-solid fa-user-group"></i></span>
                <i class="fa-solid fa-arrow-right opacity-50 group-hover:translate-x-1 group-hover:opacity-100 transition-all"></i>
            </div>
            <p class="stat-number">{{ $todayPatients }}</p>
            <p class="stat-label">Patients today</p>
        </a>

        <a href="{{ route('medicines.index', ['view' => 'low']) }}" class="dash-stat dash-amber group">
            <div class="flex items-center justify-between">
                <span class="stat-icon"><i class="fa-solid fa-triangle-exclamation"></i></span>
                <i class="fa-solid fa-arrow-right opacity-50 group-hover:translate-x-1 group-hover:opacity-100 transition-all"></i>
            </div>
            <p class="stat-number">{{ $lowStockMedicines->count() }}</p>
            <p class="stat-label">Low stock items</p>
        </a>

        <a href="{{ route('medicines.index', ['view' => 'expiring']) }}" class="dash-stat dash-orange group">
            <div class="flex items-center justify-between">
                <span class="stat-icon"><i class="fa-solid fa-calendar-xmark"></i></span>
                <i class="fa-solid fa-arrow-right opacity-50 group-hover:translate-x-1 group-hover:opacity-100 transition-all"></i>
            </div>
            <p class="stat-number">{{ $expiringSoon->count() }}</p>
            <p class="stat-label">Expiring ≤30 days</p>
        </a>

        <a href="{{ route('staff.index') }}" class="dash-stat dash-indigo group">
            <div class="flex items-center justify-between">
                <span class="stat-icon"><i class="fa-solid fa-user-doctor"></i></span>
                <i class="fa-solid fa-arrow-right opacity-50 group-hover:translate-x-1 group-hover:opacity-100 transition-all"></i>
            </div>
            <p class="stat-number">{{ $onlineStaff->count() }}</p>
            <p class="stat-label">Staff online</p>
        </a>
    </div>

    <!-- ── Quick Actions ── -->
    <div class="dash-panel p-5">
        <h3 class="font-bold text-gray-900 dark:text-white text-sm mb-4 flex items-center gap-2">
            <i class="fa-solid fa-bolt text-brand-500"></i> Quick Actions
        </h3>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
            @if(Auth::user()->can_('patients.view'))
            <a href="{{ route('patients.index') }}" class="flex items-center gap-3 p-3 rounded-xl border-2 border-gray-100 dark:border-slate-700 hover:border-blue-300 dark:hover:border-blue-700 hover:bg-blue-50/50 dark:hover:bg-blue-900/15 transition-all">
                <div class="w-10 h-10 rounded-lg bg-blue-100 dark:bg-blue-900/35 text-blue-600 dark:text-blue-300 flex items-center justify-center flex-shrink-0">
                    <i class="fa-solid fa-user-injured"></i>
                </div>
                <div class="min-w-0">
                    <p class="text-sm font-semibold text-gray-800 dark:text-gray-100 truncate">Patients</p>
                    <p class="text-xs text-gray-400 dark:text-gray-500">Manage records</p>
                </div>
            </a>
            @endif
            @if(Auth::user()->can_('medicines.create'))
            <a href="{{ route('scan.index') }}" class="flex items-center gap-3 p-3 rounded-xl border-2 border-gray-100 dark:border-slate-700 hover:border-purple-300 dark:hover:border-purple-700 hover:bg-purple-50/50 dark:hover:bg-purple-900/15 transition-all">
                <div class="w-10 h-10 rounded-lg bg-purple-100 dark:bg-purple-900/35 text-purple-600 dark:text-purple-300 flex items-center justify-center flex-shrink-0">
                    <i class="fa-solid fa-plus"></i>
                </div>
                <div class="min-w-0">
                    <p class="text-sm font-semibold text-gray-800 dark:text-gray-100 truncate">Add Medicine</p>
                    <p class="text-xs text-gray-400 dark:text-gray-500">Add to inventory</p>
                </div>
            </a>
            @endif
            @if(Auth::user()->can_('medicines.dispense'))
            <a href="{{ route('medicines.index') }}" class="flex items-center gap-3 p-3 rounded-xl border-2 border-gray-100 dark:border-slate-700 hover:border-emerald-300 dark:hover:border-emerald-700 hover:bg-emerald-50/50 dark:hover:bg-emerald-900/15 transition-all">
                <div class="w-10 h-10 rounded-lg bg-emerald-100 dark:bg-emerald-900/35 text-emerald-600 dark:text-emerald-300 flex items-center justify-center flex-shrink-0">
                    <i class="fa-solid fa-pills"></i>
                </div>
                <div class="min-w-0">
                    <p class="text-sm font-semibold text-gray-800 dark:text-gray-100 truncate">Inventory</p>
                    <p class="text-xs text-gray-400 dark:text-gray-500">View stock</p>
                </div>
            </a>
            @endif
            <a href="{{ route('chat.index') }}" class="flex items-center gap-3 p-3 rounded-xl border-2 border-gray-100 dark:border-slate-700 hover:border-amber-300 dark:hover:border-amber-700 hover:bg-amber-50/50 dark:hover:bg-amber-900/15 transition-all">
                <div class="w-10 h-10 rounded-lg bg-amber-100 dark:bg-amber-900/35 text-amber-600 dark:text-amber-300 flex items-center justify-center flex-shrink-0">
                    <i class="fa-solid fa-comments"></i>
                </div>
                <div class="min-w-0">
                    <p class="text-sm font-semibold text-gray-800 dark:text-gray-100 truncate">Messages</p>
                    <p class="text-xs text-gray-400 dark:text-gray-500">Staff chat</p>
                </div>
            </a>
        </div>
    </div>

    <!-- ── Two-column section ── -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">

        <!-- Pinned Patients -->
        <div class="dash-panel p-5 lg:col-span-2">
            <div class="flex items-center justify-between mb-4">
                <h3 class="font-bold text-gray-900 dark:text-white text-sm flex items-center gap-2">
                    <i class="fa-solid fa-thumbtack text-amber-500"></i> Pinned Patients
                </h3>
                @if(Auth::user()->can_('patients.view'))
                <a href="{{ route('patients.index') }}" class="text-xs text-blue-600 dark:text-blue-300 hover:underline font-medium">View all →</a>
                @endif
            </div>
            @if($myPinnedPatients->count() > 0)
            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                @foreach($myPinnedPatients as $p)
                <a href="{{ route('patients.show', $p) }}" class="flex items-center gap-3 p-3 rounded-xl border-2 border-gray-100 dark:border-slate-700 hover:border-blue-300 dark:hover:border-blue-700 hover:bg-blue-50/40 dark:hover:bg-blue-900/15 transition-all">
                    <div class="w-10 h-10 rounded-full bg-gradient-to-br from-blue-400 to-blue-700 flex items-center justify-center text-white text-sm font-bold flex-shrink-0">
                        {{ strtoupper(substr($p->name, 0, 2)) }}
                    </div>
                    <div class="min-w-0 flex-1">
                        <p class="text-sm font-semibold text-gray-800 dark:text-gray-100 truncate">{{ $p->name }}</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400 truncate">{{ $p->doctor?->name ?? 'No doctor' }}</p>
                    </div>
                </a>
                @endforeach
            </div>
            @else
            <div class="text-center py-10">
                <div class="w-14 h-14 bg-amber-50 dark:bg-slate-800 rounded-2xl flex items-center justify-center mx-auto mb-2">
                    <i class="fa-solid fa-thumbtack text-amber-300 dark:text-gray-500"></i>
                </div>
                <p class="text-sm text-gray-500 dark:text-gray-400">No pinned patients yet</p>
                <p class="text-xs text-gray-400 dark:text-gray-500 mt-0.5">Pin patients from the Patients page for quick access</p>
            </div>
            @endif
        </div>

        <!-- Online Staff -->
        <div class="dash-panel p-5">
            <div class="flex items-center justify-between mb-4">
                <h3 class="font-bold text-gray-900 dark:text-white text-sm flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span> Active Staff
                </h3>
                <a href="{{ route('staff.index') }}" class="text-xs text-indigo-600 dark:text-indigo-300 hover:underline font-medium">All →</a>
            </div>
            @if($onlineStaff->count() > 0)
            <div class="space-y-2.5">
                @foreach($onlineStaff->take(5) as $s)
                @php
                    $roleGrad = match($s->role) {
                        'admin'       => 'from-slate-500 to-slate-700',
                        'clinic_head' => 'from-purple-500 to-purple-700',
                        'doctor'      => 'from-blue-500 to-blue-700',
                        'pharmacist'  => 'from-green-500 to-emerald-700',
                        'nurse'       => 'from-cyan-500 to-teal-600',
                        'secretary'   => 'from-amber-400 to-rose-500',
                        'assistant'   => 'from-emerald-400 to-emerald-600',
                        default       => 'from-brand-400 to-brand-700',
                    };
                    // Available / Busy / Away dot — already filtered to "not offline"
                    // in DashboardController so all three are guaranteed present here.
                    $dotClass = match($s->statusColor()) {
                        'red'   => 'busy',
                        'amber' => 'away',
                        default => 'available',
                    };
                    $statusLabel = $s->statusLabel();
                @endphp
                <a href="{{ route('chat.index', ['with' => $s->id]) }}" class="flex items-center gap-3 p-2 rounded-xl hover:bg-gray-50 dark:hover:bg-slate-800 transition-colors">
                    <div class="relative flex-shrink-0">
                        @if($s->avatarUrl())
                        <img src="{{ $s->avatarUrl() }}" alt="{{ $s->name }}" class="w-9 h-9 rounded-full object-cover">
                        @else
                        <div class="w-9 h-9 rounded-full bg-gradient-to-br {{ $roleGrad }} flex items-center justify-center text-white text-xs font-bold">
                            {{ strtoupper(substr($s->name, 0, 2)) }}
                        </div>
                        @endif
                        <span class="absolute -bottom-0.5 -right-0.5 online-dot {{ $dotClass }}" title="{{ $statusLabel }}"></span>
                    </div>
                    <div class="min-w-0 flex-1">
                        <p class="text-sm font-semibold text-gray-800 dark:text-gray-100 truncate">{{ $s->name }}</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">{{ $s->roleLabel() }} · {{ $statusLabel }}</p>
                    </div>
                    <i class="fa-solid fa-message text-gray-300 dark:text-gray-500 text-xs"></i>
                </a>
                @endforeach
            </div>
            @else
            <div class="text-center py-8">
                <p class="text-sm text-gray-400 dark:text-gray-500">No staff active right now</p>
            </div>
            @endif
        </div>
    </div>

    <!-- ── Low Stock alerts ── -->
    @if($lowStockMedicines->count() > 0)
    <div class="dash-panel p-5">
        <div class="flex items-center justify-between mb-4">
            <h3 class="font-bold text-gray-900 dark:text-white text-sm flex items-center gap-2">
                <i class="fa-solid fa-triangle-exclamation text-amber-500"></i> Low Stock Alerts
            </h3>
            <a href="{{ route('medicines.index', ['view'=>'low']) }}" class="text-xs text-brand-600 dark:text-brand-300 hover:underline font-medium">View all →</a>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
            @foreach($lowStockMedicines as $m)
            @php $qty = $m->latestInventory?->quantity ?? 0; @endphp
            <a href="{{ route('medicines.show', $m) }}" class="flex items-center gap-3 p-3 rounded-xl border-2 border-amber-100 dark:border-amber-800/40 bg-amber-50/40 dark:bg-amber-900/15 hover:bg-amber-50 dark:hover:bg-amber-900/25 hover:border-amber-300 dark:hover:border-amber-700 transition-colors">
                <div class="w-10 h-10 rounded-lg bg-amber-100 dark:bg-amber-900/40 text-amber-700 dark:text-amber-300 flex items-center justify-center font-bold text-xs flex-shrink-0">
                    {{ strtoupper(substr($m->name, 0, 1)) }}
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-semibold text-gray-800 dark:text-gray-100 truncate">{{ $m->name }}</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400 truncate">{{ $m->location?->full_location ?? 'No location' }}</p>
                </div>
                <span class="text-sm font-bold {{ $qty <= 5 ? 'text-red-600 dark:text-red-300' : 'text-amber-700 dark:text-amber-300' }} flex-shrink-0">{{ $qty }}u</span>
            </a>
            @endforeach
        </div>
    </div>
    @endif

</div>
@endsection
