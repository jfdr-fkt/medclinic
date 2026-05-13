<!DOCTYPE html>
@php
    $appUser = Auth::user();
    $rootClass = '';
    if ($appUser) {
        if ($appUser->theme === 'dark') $rootClass .= 'dark ';
        $rootClass .= 'fs-' . ($appUser->font_size ?? 'md') . ' ';
        if ($appUser->colorblind_mode) $rootClass .= 'cb-mode';
    }
@endphp
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="{{ trim($rootClass) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>ClinicMS — @yield('title', 'Dashboard')</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <script>
        // Apply dark/light mode class BEFORE Tailwind loads to prevent flash
        (function() {
            try {
                const stored = localStorage.getItem('theme');
                if (stored === 'dark') document.documentElement.classList.add('dark');
                else if (stored === 'light') document.documentElement.classList.remove('dark');
            } catch (e) {}
        })();
    </script>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    fontFamily: { sans: ['Inter', 'sans-serif'] },
                    colors: {
                        brand: { 50:'#f0fdfa',100:'#ccfbf1',200:'#99f6e4',300:'#5eead4',400:'#2dd4bf',500:'#14b8a6',600:'#0d9488',700:'#0f766e',800:'#115e59',900:'#134e4a' },
                    }
                }
            }
        }
    </script>
    <style>
        body { font-family: 'Inter', sans-serif; }
        ::-webkit-scrollbar { width: 6px; height: 6px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 3px; }
        ::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
        .dark ::-webkit-scrollbar-thumb { background: #475569; }

        /* ── Font size scaling ── */
        html.fs-sm { font-size: 14px; }
        html.fs-md { font-size: 16px; } /* default */
        html.fs-lg { font-size: 18px; }
        html.fs-xl { font-size: 20px; }

        /* ── Color-blind safe palette overrides (red ↔ red-orange shifted, green ↔ blue-tinged) ── */
        html.cb-mode .bg-red-100 { background-color: #fed7aa !important; } /* warm orange */
        html.cb-mode .bg-red-500 { background-color: #f97316 !important; }
        html.cb-mode .text-red-700 { color: #c2410c !important; }
        html.cb-mode .text-red-600 { color: #ea580c !important; }
        html.cb-mode .bg-green-500 { background-color: #0ea5e9 !important; } /* sky blue replaces green */
        html.cb-mode .bg-emerald-500 { background-color: #0ea5e9 !important; }
        html.cb-mode .text-green-700 { color: #0369a1 !important; }
        html.cb-mode .text-emerald-700 { color: #0369a1 !important; }

        /* ──────────────── DARK MODE ──────────────── */
        :root.dark {
            --bg-canvas: #0d1424;          /* deep page background */
            --bg-card: #1a2438;             /* solid card surface */
            --bg-elevated: #243049;         /* hover/secondary surface */
            --bg-input: #0f1a2e;            /* input field bg */
            --border-subtle: #2d3a52;
            --border-strong: #3f4d6b;
            --text-primary: #f1f5f9;
            --text-secondary: #cbd5e1;
            --text-muted: #94a3b8;
        }
        .dark body { background-color: var(--bg-canvas); color: var(--text-primary); }

        /* Surfaces */
        .dark .card { background-color: var(--bg-card) !important; border-color: var(--border-subtle) !important; color: var(--text-primary); }
        .dark header { background-color: var(--bg-card) !important; border-color: var(--border-subtle) !important; }
        .dark .bg-white { background-color: var(--bg-card) !important; }
        .dark .bg-gray-50, .dark .bg-slate-50 { background-color: var(--bg-elevated) !important; }
        .dark .bg-gray-100 { background-color: var(--bg-elevated) !important; }

        /* Translucent variants of all light backgrounds become solid elevated surface */
        .dark [class*="bg-white/"], .dark [class*="bg-gray-50/"], .dark [class*="bg-slate-50/"],
        .dark [class*="bg-gray-100/"] { background-color: var(--bg-elevated) !important; }

        /* Translucent colored backgrounds (50, 50/N, 100/N) → solid elevated. NOTE: solid bg-{color}-100 is handled separately below to preserve colored button/badge identity. */
        .dark .bg-blue-50,    .dark [class*="bg-blue-50/"],    .dark [class*="bg-blue-100/"],
        .dark .bg-amber-50,   .dark [class*="bg-amber-50/"],   .dark [class*="bg-amber-100/"],
        .dark .bg-red-50,     .dark [class*="bg-red-50/"],     .dark [class*="bg-red-100/"],
        .dark .bg-green-50,   .dark [class*="bg-green-50/"],   .dark [class*="bg-green-100/"],
        .dark .bg-emerald-50, .dark [class*="bg-emerald-50/"], .dark [class*="bg-emerald-100/"],
        .dark .bg-orange-50,  .dark [class*="bg-orange-50/"],  .dark [class*="bg-orange-100/"],
        .dark .bg-indigo-50,  .dark [class*="bg-indigo-50/"],  .dark [class*="bg-indigo-100/"],
        .dark .bg-purple-50,  .dark [class*="bg-purple-50/"],  .dark [class*="bg-purple-100/"],
        .dark .bg-pink-50,    .dark [class*="bg-pink-50/"],    .dark [class*="bg-pink-100/"],
        .dark .bg-cyan-50,    .dark [class*="bg-cyan-50/"],    .dark [class*="bg-cyan-100/"],
        .dark .bg-teal-50,    .dark [class*="bg-teal-50/"],    .dark [class*="bg-teal-100/"],
        .dark .bg-slate-50,   .dark [class*="bg-slate-50/"],   .dark [class*="bg-slate-100/"],
        .dark .bg-brand-50,   .dark [class*="bg-brand-50/"],   .dark [class*="bg-brand-100/"] {
            background-color: var(--bg-elevated) !important;
        }

        /* Solid bg-{color}-100 → keep COLORED tint so buttons/badges stay visible in dark mode */
        .dark .bg-blue-100    { background-color: rgba(59,130,246,0.22) !important; }
        .dark .bg-amber-100   { background-color: rgba(245,158,11,0.22) !important; }
        .dark .bg-red-100     { background-color: rgba(239,68,68,0.22) !important; }
        .dark .bg-green-100   { background-color: rgba(34,197,94,0.22) !important; }
        .dark .bg-emerald-100 { background-color: rgba(16,185,129,0.22) !important; }
        .dark .bg-orange-100  { background-color: rgba(249,115,22,0.22) !important; }
        .dark .bg-indigo-100  { background-color: rgba(99,102,241,0.22) !important; }
        .dark .bg-purple-100  { background-color: rgba(168,85,247,0.22) !important; }
        .dark .bg-pink-100    { background-color: rgba(236,72,153,0.22) !important; }
        .dark .bg-cyan-100    { background-color: rgba(6,182,212,0.22) !important; }
        .dark .bg-teal-100    { background-color: rgba(20,184,166,0.22) !important; }
        .dark .bg-slate-100   { background-color: rgba(100,116,139,0.25) !important; }
        .dark .bg-brand-100   { background-color: rgba(20,184,166,0.22) !important; }

        /* Solid hover variants for colored buttons */
        .dark .hover\:bg-blue-200:hover    { background-color: rgba(59,130,246,0.35) !important; }
        .dark .hover\:bg-amber-200:hover   { background-color: rgba(245,158,11,0.35) !important; }
        .dark .hover\:bg-red-200:hover     { background-color: rgba(239,68,68,0.35) !important; }
        .dark .hover\:bg-green-200:hover   { background-color: rgba(34,197,94,0.35) !important; }
        .dark .hover\:bg-emerald-200:hover { background-color: rgba(16,185,129,0.35) !important; }
        .dark .hover\:bg-orange-200:hover  { background-color: rgba(249,115,22,0.35) !important; }
        .dark .hover\:bg-indigo-200:hover  { background-color: rgba(99,102,241,0.35) !important; }
        .dark .hover\:bg-purple-200:hover  { background-color: rgba(168,85,247,0.35) !important; }
        .dark .hover\:bg-pink-200:hover    { background-color: rgba(236,72,153,0.35) !important; }
        .dark .hover\:bg-cyan-200:hover    { background-color: rgba(6,182,212,0.35) !important; }
        .dark .hover\:bg-teal-200:hover    { background-color: rgba(20,184,166,0.35) !important; }

        /* Table header: high contrast text + solid darker surface */
        .dark thead tr {
            background: var(--bg-card) !important;
            background-image: none !important;
            border-color: var(--border-strong) !important;
        }
        .dark .th { color: #f1f5f9 !important; font-weight: 700 !important; }

        /* Gradient stat cards: keep tinted hue but darker so they don't glow */
        .dark [class*="from-blue-50"][class*="to-blue-100"] { background-image: linear-gradient(to bottom right, rgba(59,130,246,0.15), rgba(59,130,246,0.07)) !important; background-color: transparent !important; }
        .dark [class*="from-amber-50"][class*="to-amber-100"] { background-image: linear-gradient(to bottom right, rgba(245,158,11,0.15), rgba(245,158,11,0.07)) !important; background-color: transparent !important; }
        .dark [class*="from-red-50"][class*="to-red-100"] { background-image: linear-gradient(to bottom right, rgba(239,68,68,0.15), rgba(239,68,68,0.07)) !important; background-color: transparent !important; }
        .dark [class*="from-orange-50"][class*="to-orange-100"] { background-image: linear-gradient(to bottom right, rgba(249,115,22,0.15), rgba(249,115,22,0.07)) !important; background-color: transparent !important; }
        .dark [class*="from-emerald-50"][class*="to-emerald-100"] { background-image: linear-gradient(to bottom right, rgba(16,185,129,0.15), rgba(16,185,129,0.07)) !important; background-color: transparent !important; }
        .dark [class*="from-green-50"][class*="to-green-100"] { background-image: linear-gradient(to bottom right, rgba(34,197,94,0.15), rgba(34,197,94,0.07)) !important; background-color: transparent !important; }
        .dark [class*="from-indigo-50"][class*="to-indigo-100"] { background-image: linear-gradient(to bottom right, rgba(99,102,241,0.15), rgba(99,102,241,0.07)) !important; background-color: transparent !important; }
        .dark [class*="from-slate-50"][class*="to-slate-100"] { background-image: linear-gradient(to bottom right, rgba(100,116,139,0.2), rgba(100,116,139,0.1)) !important; background-color: transparent !important; }
        .dark [class*="from-brand-50"][class*="to-brand-100"] { background-image: linear-gradient(to bottom right, rgba(20,184,166,0.15), rgba(20,184,166,0.07)) !important; background-color: transparent !important; }
        .dark [class*="from-gray-50"][class*="to-gray-200"] { background-image: linear-gradient(to bottom right, rgba(148,163,184,0.15), rgba(148,163,184,0.07)) !important; background-color: transparent !important; }
        .dark [class*="from-cyan-50"][class*="to-cyan-100"], .dark [class*="from-teal-50"][class*="to-teal-100"] { background-image: linear-gradient(to bottom right, rgba(20,184,166,0.15), rgba(20,184,166,0.07)) !important; background-color: transparent !important; }

        /* Text */
        .dark .text-gray-900, .dark .text-slate-900 { color: var(--text-primary) !important; }
        .dark .text-gray-800, .dark .text-slate-800 { color: var(--text-primary) !important; }
        .dark .text-gray-700, .dark .text-slate-700 { color: var(--text-secondary) !important; }
        .dark .text-gray-600 { color: var(--text-secondary) !important; }
        .dark .text-gray-500 { color: var(--text-muted) !important; }
        .dark .text-gray-400 { color: var(--text-muted) !important; }
        .dark .text-gray-300 { color: #475569 !important; }

        /* Colored text — desaturate for dark bg readability */
        .dark .text-blue-700, .dark .text-blue-900 { color: #93c5fd !important; }
        .dark .text-green-700, .dark .text-green-900 { color: #86efac !important; }
        .dark .text-emerald-700, .dark .text-emerald-900 { color: #6ee7b7 !important; }
        .dark .text-amber-700, .dark .text-amber-900 { color: #fcd34d !important; }
        .dark .text-red-700, .dark .text-red-900 { color: #fca5a5 !important; }
        .dark .text-orange-700, .dark .text-orange-900 { color: #fdba74 !important; }
        .dark .text-indigo-700, .dark .text-indigo-900 { color: #a5b4fc !important; }
        .dark .text-purple-700 { color: #d8b4fe !important; }
        .dark .text-pink-700 { color: #fbcfe8 !important; }
        .dark .text-teal-700 { color: #5eead4 !important; }
        .dark .text-cyan-700 { color: #67e8f9 !important; }
        .dark .text-brand-600, .dark .text-brand-700, .dark .text-brand-900 { color: #5eead4 !important; }

        /* Borders */
        .dark .border-gray-50, .dark .border-gray-100, .dark .border-gray-200,
        .dark .border-slate-100, .dark .border-slate-200 { border-color: var(--border-subtle) !important; }
        .dark .border-blue-100, .dark .border-blue-200 { border-color: rgba(59,130,246,0.3) !important; }
        .dark .border-green-100, .dark .border-green-200 { border-color: rgba(34,197,94,0.3) !important; }
        .dark .border-emerald-100, .dark .border-emerald-200 { border-color: rgba(16,185,129,0.3) !important; }
        .dark .border-amber-100, .dark .border-amber-200 { border-color: rgba(245,158,11,0.3) !important; }
        .dark .border-red-100, .dark .border-red-200 { border-color: rgba(239,68,68,0.3) !important; }
        .dark .border-orange-100, .dark .border-orange-200 { border-color: rgba(249,115,22,0.3) !important; }
        .dark .border-indigo-100, .dark .border-indigo-200 { border-color: rgba(99,102,241,0.3) !important; }
        .dark .border-purple-100, .dark .border-purple-200 { border-color: rgba(168,85,247,0.3) !important; }
        .dark .border-cyan-100, .dark .border-cyan-200 { border-color: rgba(6,182,212,0.3) !important; }
        .dark .border-brand-100, .dark .border-brand-200 { border-color: rgba(20,184,166,0.3) !important; }
        .dark .divide-gray-50 > :not([hidden]) ~ :not([hidden]),
        .dark .divide-gray-100 > :not([hidden]) ~ :not([hidden]),
        .dark .divide-gray-200 > :not([hidden]) ~ :not([hidden]) { border-color: var(--border-subtle) !important; }

        /* Inputs (covers .input class + raw form controls) */
        .dark .input,
        .dark input[type="text"], .dark input[type="email"], .dark input[type="password"],
        .dark input[type="tel"], .dark input[type="number"], .dark input[type="date"],
        .dark input[type="time"], .dark textarea, .dark select {
            background-color: var(--bg-input) !important;
            border-color: var(--border-subtle) !important;
            color: var(--text-primary) !important;
        }
        .dark .input::placeholder, .dark input::placeholder, .dark textarea::placeholder { color: var(--text-muted) !important; }

        /* Buttons */
        .dark .btn-secondary { background-color: var(--bg-card) !important; border-color: var(--border-subtle) !important; color: var(--text-secondary) !important; }
        .dark .btn-secondary:hover { background-color: var(--bg-elevated) !important; }

        /* Hover states for interactive surfaces */
        .dark .hover\:bg-gray-50:hover, .dark .hover\:bg-gray-100:hover,
        .dark .hover\:bg-blue-50:hover, .dark [class*="hover:bg-blue-50/"]:hover,
        .dark .hover\:bg-green-50:hover, .dark [class*="hover:bg-green-50/"]:hover,
        .dark .hover\:bg-amber-50:hover, .dark [class*="hover:bg-amber-50/"]:hover,
        .dark .hover\:bg-red-50:hover, .dark .hover\:bg-emerald-50:hover,
        .dark .hover\:bg-indigo-50:hover, .dark .hover\:bg-purple-50:hover,
        .dark .hover\:bg-pink-50:hover, .dark [class*="hover:bg-brand-50/"]:hover {
            background-color: var(--bg-elevated) !important;
        }

        /* The shared utility classes — defined AFTER overrides so dark mode can still target them */
        .card { @apply bg-white rounded-2xl shadow-sm border border-gray-100; }
        .badge-rx     { @apply inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-semibold bg-red-100 text-red-700; }
        .badge-otc    { @apply inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-700; }
        .badge-ctrl   { @apply inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-semibold bg-purple-100 text-purple-700; }
        .badge-ok     { @apply inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-700; }
        .badge-low    { @apply inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-semibold bg-amber-100 text-amber-700; }
        .badge-crit   { @apply inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-semibold bg-red-100 text-red-700; }
        .badge-expired{ @apply inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-semibold bg-gray-200 text-gray-600; }

        .btn-primary   { @apply inline-flex items-center gap-2 px-4 py-2.5 bg-brand-600 text-white text-sm font-semibold rounded-xl hover:bg-brand-700 transition-colors shadow-sm; }
        .btn-secondary { @apply inline-flex items-center gap-2 px-4 py-2.5 bg-white border border-gray-200 text-gray-700 text-sm font-medium rounded-xl hover:bg-gray-50 transition-colors; }
        .btn-danger    { @apply inline-flex items-center gap-2 px-4 py-2.5 bg-red-600 text-white text-sm font-semibold rounded-xl hover:bg-red-700 transition-colors shadow-sm; }
        .input         { @apply block w-full px-3.5 py-2.5 border-2 border-gray-200 rounded-xl text-sm text-gray-800 placeholder-gray-400 focus:ring-2 focus:ring-brand-500 focus:border-brand-500 outline-none transition-all bg-white; }
        .label         { @apply block text-xs font-bold text-gray-700 mb-1.5 uppercase tracking-wide; }
        .th { @apply px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider; }
        .td { @apply px-5 py-4 text-sm text-gray-700; }

        .status-dot { @apply inline-block w-2 h-2 rounded-full; }
        .status-dot.available { @apply bg-emerald-400; }
        .status-dot.busy      { @apply bg-red-400; }
        .status-dot.away      { @apply bg-amber-400; }
        .status-dot.offline   { @apply bg-gray-400; }

        /* Sidebar collapse: only icons remain */
        @media (min-width: 768px) {
            .sidebar-collapsed { width: 4.5rem !important; }
            .sidebar-collapsed .nav-text,
            .sidebar-collapsed .sidebar-logo-group,
            .sidebar-collapsed .sidebar-section-title,
            .sidebar-collapsed .sidebar-user-info { display: none !important; }
            .sidebar-collapsed .sidebar-header { justify-content: center !important; padding-left: 0 !important; padding-right: 0 !important; }
            .sidebar-collapsed .sidebar-footer-link { justify-content: center !important; }
            .sidebar-collapsed nav a { justify-content: center !important; padding-left: 0.5rem !important; padding-right: 0.5rem !important; }
        }
    </style>
    @stack('head')
</head>
<body class="bg-slate-50 text-gray-800 antialiased">

<div class="flex h-screen overflow-hidden">

    <!-- ========== DESKTOP SIDEBAR ========== -->
    <aside id="sidebar" class="hidden md:flex w-64 flex-shrink-0 flex-col bg-slate-900 transition-all duration-200">
        <!-- Logo + collapse toggle -->
        <div class="sidebar-header h-16 flex items-center justify-between gap-3 px-4 border-b border-slate-800">
            <div class="sidebar-logo-group flex items-center gap-3 overflow-hidden">
                <div class="w-9 h-9 bg-gradient-to-br from-brand-400 to-brand-600 rounded-xl flex items-center justify-center flex-shrink-0 shadow-lg shadow-brand-500/30">
                    <i class="fa-solid fa-staff-snake text-white text-base"></i>
                </div>
                <div>
                    <p class="text-white font-bold text-base leading-none">ClinicMS</p>
                    <p class="text-slate-300 text-xs mt-0.5">Management System</p>
                </div>
            </div>
            <button onclick="toggleDesktopSidebar()" class="text-slate-200 hover:text-white p-2 rounded-lg hover:bg-slate-700 transition-colors flex-shrink-0" title="Toggle sidebar">
                <i class="fa-solid fa-bars text-sm"></i>
            </button>
        </div>

        <!-- Nav -->
        <nav class="flex-1 overflow-y-auto py-5 px-3 space-y-6">
            <!-- Overview -->
            <div>
                <p class="sidebar-section-title text-[11px] font-bold text-slate-400 uppercase tracking-widest px-3 mb-2">Overview</p>
                <ul class="space-y-1">
                    <li>
                        <a href="{{ route('dashboard') }}" title="Dashboard"
                           class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-semibold transition-all
                               {{ request()->routeIs('dashboard') ? 'bg-brand-500/20 text-white border border-brand-400/40 shadow-sm' : 'text-slate-100 hover:bg-slate-700 hover:text-white' }}">
                            <i class="fa-solid fa-gauge-high w-5 text-center {{ request()->routeIs('dashboard') ? 'text-brand-300' : 'text-slate-300' }}"></i>
                            <span class="nav-text">Dashboard</span>
                        </a>
                    </li>
                </ul>
            </div>
            <!-- Clinical -->
            <div>
                <p class="sidebar-section-title text-[11px] font-bold text-slate-400 uppercase tracking-widest px-3 mb-2">Clinical</p>
                <ul class="space-y-1">
                    <li>
                        <a href="{{ route('patients.index') }}" title="Patients"
                           class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-semibold transition-all
                               {{ request()->routeIs('patients.*') ? 'bg-brand-500/20 text-white border border-brand-400/40 shadow-sm' : 'text-slate-100 hover:bg-slate-700 hover:text-white' }}">
                            <i class="fa-solid fa-user-injured w-5 text-center {{ request()->routeIs('patients.*') ? 'text-brand-300' : 'text-slate-300' }}"></i>
                            <span class="nav-text">Patients</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('medicines.index') }}" title="Medicines"
                           class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-semibold transition-all
                               {{ request()->routeIs('medicines.*') ? 'bg-brand-500/20 text-white border border-brand-400/40 shadow-sm' : 'text-slate-100 hover:bg-slate-700 hover:text-white' }}">
                            <i class="fa-solid fa-pills w-5 text-center {{ request()->routeIs('medicines.*') ? 'text-brand-300' : 'text-slate-300' }}"></i>
                            <span class="nav-text">Medicines</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('scan.index') }}" title="Add Medicine"
                           class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-semibold transition-all
                               {{ request()->routeIs('scan.*') ? 'bg-brand-500/20 text-white border border-brand-400/40 shadow-sm' : 'text-slate-100 hover:bg-slate-700 hover:text-white' }}">
                            <i class="fa-solid fa-plus w-5 text-center {{ request()->routeIs('scan.*') ? 'text-brand-300' : 'text-slate-300' }}"></i>
                            <span class="nav-text">Add Medicine</span>
                        </a>
                    </li>
                </ul>
            </div>
            <!-- Team -->
            <div>
                <p class="sidebar-section-title text-[11px] font-bold text-slate-400 uppercase tracking-widest px-3 mb-2">Team</p>
                <ul class="space-y-1">
                    <li>
                        <a href="{{ route('staff.index') }}" title="Staff"
                           class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-semibold transition-all
                               {{ request()->routeIs('staff.*') ? 'bg-brand-500/20 text-white border border-brand-400/40 shadow-sm' : 'text-slate-100 hover:bg-slate-700 hover:text-white' }}">
                            <i class="fa-solid fa-user-doctor w-5 text-center {{ request()->routeIs('staff.*') ? 'text-brand-300' : 'text-slate-300' }}"></i>
                            <span class="nav-text">Staff</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('chat.index') }}" title="Staff Chat"
                           class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-semibold transition-all
                               {{ request()->routeIs('chat.*') ? 'bg-brand-500/20 text-white border border-brand-400/40 shadow-sm' : 'text-slate-100 hover:bg-slate-700 hover:text-white' }}">
                            <i class="fa-solid fa-comments w-5 text-center {{ request()->routeIs('chat.*') ? 'text-brand-300' : 'text-slate-300' }}"></i>
                            <span class="nav-text">Staff Chat</span>
                        </a>
                    </li>
                </ul>
            </div>
        </nav>

        <!-- User footer -->
        <div class="border-t border-slate-800 p-3">
            <a href="{{ route('profile.edit') }}" title="Profile & Settings"
               class="sidebar-footer-link flex items-center gap-3 p-2 rounded-xl hover:bg-slate-800 transition-colors">
                <div class="relative flex-shrink-0">
                    @if(Auth::user()->avatarUrl())
                    <img src="{{ Auth::user()->avatarUrl() }}" alt="{{ Auth::user()->name }}" class="h-9 w-9 rounded-full object-cover">
                    @else
                    <div class="h-9 w-9 rounded-full bg-gradient-to-br from-brand-400 to-brand-600 flex items-center justify-center text-white text-sm font-bold">
                        {{ strtoupper(substr(Auth::user()->name, 0, 2)) }}
                    </div>
                    @endif
                    <span class="status-dot {{ Auth::user()->statusColor() === 'emerald' ? 'available' : (Auth::user()->statusColor() === 'red' ? 'busy' : (Auth::user()->statusColor() === 'amber' ? 'away' : 'offline')) }} absolute -bottom-0.5 -right-0.5 ring-2 ring-slate-900"></span>
                </div>
                <div class="sidebar-user-info flex-1 min-w-0">
                    <p class="text-sm font-semibold text-white truncate">{{ Auth::user()->name }}</p>
                    <p class="text-xs text-slate-300 capitalize">{{ Auth::user()->role }}</p>
                </div>
            </a>
        </div>
    </aside>

    <!-- Mobile sidebar -->
    <div id="mobileOverlay" class="hidden fixed inset-0 bg-black/60 z-40 md:hidden" onclick="toggleMobileSidebar()"></div>
    <aside id="mobileSidebar" class="fixed inset-y-0 left-0 w-72 bg-slate-900 z-50 transform -translate-x-full transition-transform duration-200 md:hidden flex flex-col">
        <div class="h-16 flex items-center justify-between px-5 border-b border-slate-800">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 bg-gradient-to-br from-brand-400 to-brand-600 rounded-xl flex items-center justify-center shadow-lg shadow-brand-500/30">
                    <i class="fa-solid fa-staff-snake text-white text-base"></i>
                </div>
                <span class="text-white font-bold">ClinicMS</span>
            </div>
            <button onclick="toggleMobileSidebar()" class="text-slate-200 hover:text-white p-2 rounded-lg hover:bg-slate-700">
                <i class="fa-solid fa-xmark text-lg"></i>
            </button>
        </div>
        <nav class="flex-1 overflow-y-auto py-5 px-3 space-y-6">
            <div>
                <p class="text-[11px] font-bold text-slate-400 uppercase tracking-widest px-3 mb-2">Overview</p>
                <ul class="space-y-1">
                    <li><a href="{{ route('dashboard') }}" class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-semibold {{ request()->routeIs('dashboard') ? 'bg-brand-500/20 text-white border border-brand-400/40' : 'text-slate-100 hover:bg-slate-700 hover:text-white' }}"><i class="fa-solid fa-gauge-high w-5 text-center text-slate-300"></i> Dashboard</a></li>
                </ul>
            </div>
            <div>
                <p class="text-[11px] font-bold text-slate-400 uppercase tracking-widest px-3 mb-2">Clinical</p>
                <ul class="space-y-1">
                    <li><a href="{{ route('patients.index') }}"  class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-semibold {{ request()->routeIs('patients.*') ? 'bg-brand-500/20 text-white border border-brand-400/40' : 'text-slate-100 hover:bg-slate-700 hover:text-white' }}"><i class="fa-solid fa-user-injured w-5 text-center text-slate-300"></i> Patients</a></li>
                    <li><a href="{{ route('medicines.index') }}" class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-semibold {{ request()->routeIs('medicines.*') ? 'bg-brand-500/20 text-white border border-brand-400/40' : 'text-slate-100 hover:bg-slate-700 hover:text-white' }}"><i class="fa-solid fa-pills w-5 text-center text-slate-300"></i> Medicines</a></li>
                    <li><a href="{{ route('scan.index') }}"      class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-semibold {{ request()->routeIs('scan.*') ? 'bg-brand-500/20 text-white border border-brand-400/40' : 'text-slate-100 hover:bg-slate-700 hover:text-white' }}"><i class="fa-solid fa-plus w-5 text-center text-slate-300"></i> Add Medicine</a></li>
                </ul>
            </div>
            <div>
                <p class="text-[11px] font-bold text-slate-400 uppercase tracking-widest px-3 mb-2">Team</p>
                <ul class="space-y-1">
                    <li><a href="{{ route('staff.index') }}" class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-semibold {{ request()->routeIs('staff.*') ? 'bg-brand-500/20 text-white border border-brand-400/40' : 'text-slate-100 hover:bg-slate-700 hover:text-white' }}"><i class="fa-solid fa-user-doctor w-5 text-center text-slate-300"></i> Staff</a></li>
                    <li><a href="{{ route('chat.index') }}"  class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-semibold {{ request()->routeIs('chat.*') ? 'bg-brand-500/20 text-white border border-brand-400/40' : 'text-slate-100 hover:bg-slate-700 hover:text-white' }}"><i class="fa-solid fa-comments w-5 text-center text-slate-300"></i> Staff Chat</a></li>
                </ul>
            </div>
        </nav>
        <div class="border-t border-slate-800 p-4">
            <a href="{{ route('profile.edit') }}" class="flex items-center gap-3 p-2 rounded-xl hover:bg-slate-800">
                <div class="h-9 w-9 rounded-full bg-gradient-to-br from-brand-400 to-brand-600 flex items-center justify-center text-white text-sm font-bold">{{ strtoupper(substr(Auth::user()->name, 0, 2)) }}</div>
                <div class="flex-1 min-w-0"><p class="text-sm font-semibold text-white truncate">{{ Auth::user()->name }}</p><p class="text-xs text-slate-300 capitalize">{{ Auth::user()->role }}</p></div>
            </a>
        </div>
    </aside>

    <!-- ========== MAIN ========== -->
    <div class="flex flex-1 flex-col overflow-hidden">

        <!-- Top bar -->
        <header class="h-16 flex items-center justify-between border-b border-gray-200 bg-white px-4 md:px-6 flex-shrink-0 z-30">
            <div class="flex items-center gap-3">
                <button onclick="toggleMobileSidebar()" class="md:hidden p-2 text-gray-500 hover:text-gray-700 rounded-xl hover:bg-gray-100">
                    <i class="fa-solid fa-bars text-lg"></i>
                </button>
                <div class="hidden sm:flex items-center gap-2 text-sm">
                    <span class="text-gray-300">/</span>
                    <span class="font-semibold text-gray-800">@yield('page-title', 'Dashboard')</span>
                </div>
            </div>

            <div class="flex items-center gap-1 sm:gap-2">
                <!-- Status pill -->
                <div class="relative">
                    <button onclick="toggleDropdown('statusMenu')" id="statusBtn"
                            class="hidden sm:flex items-center gap-1.5 text-xs font-medium text-gray-600 bg-gray-50 hover:bg-gray-100 border border-gray-200 rounded-full px-3 py-1.5 transition-colors">
                        <span class="status-dot {{ Auth::user()->statusColor() === 'emerald' ? 'available' : (Auth::user()->statusColor() === 'red' ? 'busy' : (Auth::user()->statusColor() === 'amber' ? 'away' : 'offline')) }} {{ Auth::user()->isOnline() && Auth::user()->status === 'available' ? 'animate-pulse' : '' }}"></span>
                        <span id="statusLabel">{{ Auth::user()->statusLabel() }}</span>
                        <i class="fa-solid fa-chevron-down text-[10px] text-gray-400"></i>
                    </button>
                    <div id="statusMenu" class="hidden absolute right-0 mt-2 w-44 bg-white rounded-xl shadow-lg border border-gray-100 py-2 z-40">
                        @foreach([
                            ['available','Available','emerald'],
                            ['busy','Busy','red'],
                            ['away','Away','amber'],
                            ['offline','Appear Offline','gray'],
                        ] as [$key, $label, $color])
                        <button onclick="setStatus('{{ $key }}')" class="w-full text-left px-3 py-2 text-sm text-gray-700 hover:bg-gray-50 flex items-center gap-2.5">
                            <span class="status-dot {{ $key }}"></span>
                            <span>{{ $label }}</span>
                            @if(Auth::user()->status === $key)
                            <i class="fa-solid fa-check text-xs text-brand-600 ml-auto"></i>
                            @endif
                        </button>
                        @endforeach
                    </div>
                </div>

                <!-- Notifications -->
                <a href="{{ route('chat.index') }}" class="relative p-2 text-gray-500 hover:text-gray-700 hover:bg-gray-50 rounded-xl transition-colors">
                    <i class="fa-solid fa-bell text-base"></i>
                    @if(isset($lowStockMedicines) && $lowStockMedicines->count() > 0)
                    <span class="absolute top-1.5 right-1.5 h-2 w-2 rounded-full bg-red-500 ring-2 ring-white"></span>
                    @endif
                </a>

                <span class="hidden md:inline text-sm text-gray-400">{{ date('M j, Y') }}</span>

                <!-- Profile dropdown -->
                <div class="relative ml-1 pl-2 border-l border-gray-200">
                    <button onclick="toggleDropdown('profileMenu')" class="flex items-center gap-2 p-1 hover:bg-gray-50 rounded-xl transition-colors">
                        @if(Auth::user()->avatarUrl())
                        <img src="{{ Auth::user()->avatarUrl() }}" alt="{{ Auth::user()->name }}" class="h-8 w-8 rounded-full object-cover">
                        @else
                        <div class="h-8 w-8 rounded-full bg-gradient-to-br from-brand-400 to-brand-600 flex items-center justify-center text-white text-xs font-bold">
                            {{ strtoupper(substr(Auth::user()->name, 0, 2)) }}
                        </div>
                        @endif
                        <span class="hidden sm:block text-sm font-medium text-gray-700 max-w-[100px] truncate">{{ Str::before(Auth::user()->name, ' ') }}</span>
                        <i class="fa-solid fa-chevron-down text-[10px] text-gray-400 hidden sm:block"></i>
                    </button>
                    <div id="profileMenu" class="hidden absolute right-0 mt-2 w-56 bg-white rounded-xl shadow-lg border border-gray-100 py-2 z-40">
                        <div class="px-4 py-2 border-b border-gray-100 mb-1">
                            <p class="text-sm font-semibold text-gray-800 truncate">{{ Auth::user()->name }}</p>
                            <p class="text-xs text-gray-500 truncate">{{ Auth::user()->email }}</p>
                        </div>
                        <a href="{{ route('profile.edit') }}" class="flex items-center gap-3 px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">
                            <i class="fa-solid fa-user-pen w-4 text-gray-400"></i> My Profile
                        </a>
                        <a href="{{ route('staff.index') }}" class="flex items-center gap-3 px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">
                            <i class="fa-solid fa-users w-4 text-gray-400"></i> Staff Directory
                        </a>
                        <a href="{{ route('chat.index') }}" class="flex items-center gap-3 px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">
                            <i class="fa-solid fa-message w-4 text-gray-400"></i> Messages
                        </a>
                        <div class="border-t border-gray-100 my-1"></div>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="w-full text-left flex items-center gap-3 px-4 py-2 text-sm text-red-600 hover:bg-red-50">
                                <i class="fa-solid fa-arrow-right-from-bracket w-4"></i> Log Out
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </header>

        <!-- Flash messages -->
        @if(session('success'))
        <div class="mx-4 md:mx-6 mt-4 p-3.5 bg-emerald-50 border border-emerald-200 rounded-xl flex items-center gap-3 text-sm text-emerald-800">
            <i class="fa-solid fa-circle-check text-emerald-500 flex-shrink-0"></i> {{ session('success') }}
        </div>
        @endif
        @if(session('error'))
        <div class="mx-4 md:mx-6 mt-4 p-3.5 bg-red-50 border border-red-200 rounded-xl flex items-center gap-3 text-sm text-red-800">
            <i class="fa-solid fa-circle-exclamation text-red-500 flex-shrink-0"></i> {{ session('error') }}
        </div>
        @endif

        <main class="flex-1 overflow-y-auto p-4 md:p-6">
            @yield('content')
        </main>
    </div>
</div>

<script>
function toggleDesktopSidebar() {
    document.getElementById('sidebar').classList.toggle('sidebar-collapsed');
}
function toggleMobileSidebar() {
    const s = document.getElementById('mobileSidebar');
    const o = document.getElementById('mobileOverlay');
    const open = !s.classList.contains('-translate-x-full');
    s.classList.toggle('-translate-x-full', open);
    o.classList.toggle('hidden', open);
}
function toggleDropdown(id) {
    document.querySelectorAll('[id$="Menu"]').forEach(el => { if (el.id !== id) el.classList.add('hidden'); });
    document.getElementById(id).classList.toggle('hidden');
}
document.addEventListener('click', e => {
    if (!e.target.closest('[onclick*="toggleDropdown"]') && !e.target.closest('[id$="Menu"]')) {
        document.querySelectorAll('[id$="Menu"]').forEach(el => el.classList.add('hidden'));
    }
});
function setStatus(status) {
    fetch('{{ route("profile.status") }}', {
        method: 'POST',
        headers: { 'Content-Type':'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content, 'Accept':'application/json' },
        body: JSON.stringify({ status })
    }).then(r => r.json()).then(() => location.reload());
}
</script>
@stack('scripts')
</body>
</html>
