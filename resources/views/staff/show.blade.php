@extends('layouts.app')
@section('title', $user->name)
@section('page-title', 'Staff Profile')

@section('content')
@php
    $isAdmin = Auth::user()->role === 'admin';
    $roleColors = [
        'admin'       => ['bg'=>'bg-slate-100',  'text'=>'text-slate-700',  'grad'=>'from-slate-500 to-slate-700',  'icon'=>'fa-user-shield'],
        'clinic_head' => ['bg'=>'bg-purple-100', 'text'=>'text-purple-700', 'grad'=>'from-purple-500 to-purple-700','icon'=>'fa-user-tie'],
        'doctor'      => ['bg'=>'bg-blue-100',   'text'=>'text-blue-700',   'grad'=>'from-blue-500 to-blue-700',    'icon'=>'fa-user-doctor'],
        'pharmacist'  => ['bg'=>'bg-green-100',  'text'=>'text-green-700',  'grad'=>'from-green-500 to-green-700',  'icon'=>'fa-prescription-bottle-medical'],
        'nurse'       => ['bg'=>'bg-cyan-100',   'text'=>'text-teal-700',   'grad'=>'from-cyan-500 to-teal-600',    'icon'=>'fa-user-nurse'],
        'secretary'   => ['bg'=>'bg-amber-100',  'text'=>'text-amber-700',  'grad'=>'from-amber-400 to-amber-600',  'icon'=>'fa-id-badge'],
        'assistant'   => ['bg'=>'bg-emerald-100','text'=>'text-emerald-700','grad'=>'from-emerald-400 to-emerald-600','icon'=>'fa-user'],
    ];
    $cfg = $roleColors[$user->role] ?? $roleColors['assistant'];

    $shiftsByDate = $shifts->keyBy(fn($s) => \Carbon\Carbon::parse($s->shift_date)->toDateString());
    $shiftTypeColors = [
        'morning'   => 'bg-amber-100 text-amber-700 border-amber-300 dark:bg-amber-900/30 dark:text-amber-200 dark:border-amber-800/60',
        'afternoon' => 'bg-orange-100 text-orange-700 border-orange-300 dark:bg-orange-900/30 dark:text-orange-200 dark:border-orange-800/60',
        'night'     => 'bg-indigo-100 text-indigo-700 border-indigo-300 dark:bg-indigo-900/30 dark:text-indigo-200 dark:border-indigo-800/60',
        'on_call'   => 'bg-purple-100 text-purple-700 border-purple-300 dark:bg-purple-900/30 dark:text-purple-200 dark:border-purple-800/60',
    ];

    $statusHue = $user->statusColor();
@endphp

<div class="space-y-5 max-w-5xl mx-auto">

    {{-- Back link --}}
    <div class="flex items-center gap-3">
        <a href="{{ route('staff.index') }}"
           class="w-9 h-9 flex items-center justify-center rounded-xl text-gray-400 dark:text-gray-500 hover:text-gray-700 dark:hover:text-gray-200 hover:bg-gray-100 dark:hover:bg-slate-800 transition-colors">
            <i class="fa-solid fa-arrow-left"></i>
        </a>
        <span class="text-sm text-gray-400 dark:text-gray-500">Back to Staff Directory</span>
    </div>

    {{-- Profile header — flat in-gradient layout (no overlap bug) --}}
    <div class="rounded-2xl overflow-hidden bg-gradient-to-r {{ $cfg['grad'] }} text-white shadow-md relative">
        <div class="absolute inset-0 opacity-20 pointer-events-none" style="background-image: radial-gradient(circle at 18% 30%, rgba(255,255,255,.55) 0, transparent 35%), radial-gradient(circle at 80% 75%, rgba(255,255,255,.35) 0, transparent 32%);"></div>
        <div class="relative px-5 sm:px-6 py-5 flex items-center gap-4 flex-wrap">
            @if($user->avatarUrl())
                <img src="{{ $user->avatarUrl() }}" alt="{{ $user->name }}"
                     class="w-16 h-16 sm:w-20 sm:h-20 rounded-2xl object-cover ring-2 ring-white/40 shadow-md flex-shrink-0">
            @else
                <div class="w-16 h-16 sm:w-20 sm:h-20 rounded-2xl bg-white/15 backdrop-blur-sm ring-1 ring-white/25 flex items-center justify-center text-white text-xl sm:text-2xl font-extrabold flex-shrink-0">
                    {{ strtoupper(substr($user->name, 0, 2)) }}
                </div>
            @endif
            <div class="flex-1 min-w-0">
                <h2 class="text-xl sm:text-2xl font-extrabold leading-tight truncate">{{ $user->name }}</h2>
                <div class="flex items-center gap-2 mt-1.5 flex-wrap">
                    <span class="inline-flex items-center gap-1.5 bg-white/20 ring-1 ring-white/25 text-white px-2.5 py-1 rounded-full text-xs font-bold">
                        <i class="fa-solid {{ $cfg['icon'] }} text-[10px]"></i> {{ $user->roleLabel() }}
                    </span>
                    <span class="inline-flex items-center gap-1.5 bg-white text-{{ $statusHue }}-700 px-2.5 py-1 rounded-full text-xs font-bold shadow-sm">
                        <span class="w-1.5 h-1.5 rounded-full bg-{{ $statusHue }}-500 {{ $user->isOnline() ? 'animate-pulse' : '' }}"></span>
                        {{ $user->statusLabel() }}
                    </span>
                    @if($user->specialization)
                    <span class="text-sm text-white/80">&bull; {{ $user->specialization }}</span>
                    @endif
                </div>
            </div>
            <a href="{{ route('chat.index', ['with' => $user->id]) }}"
               class="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-white text-purple-700 hover:bg-purple-50 text-sm font-bold transition-colors shadow-sm w-full sm:w-auto flex-shrink-0">
                <i class="fa-solid fa-comment"></i> Message
            </a>
        </div>
    </div>

    {{-- Contact + About --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
        <div class="card p-5">
            <h3 class="font-bold text-gray-900 dark:text-white text-base mb-4 flex items-center gap-2">
                <span class="w-7 h-7 rounded-lg bg-brand-100 dark:bg-brand-900/40 text-brand-600 dark:text-brand-300 inline-flex items-center justify-center">
                    <i class="fa-solid fa-address-card text-xs"></i>
                </span>
                Contact
            </h3>
            <div class="space-y-3 text-base">
                <div class="flex items-start gap-3 min-w-0">
                    <i class="fa-solid fa-envelope w-5 text-gray-400 dark:text-gray-500 mt-1 flex-shrink-0"></i>
                    <span class="text-gray-800 dark:text-gray-100 font-medium break-all">{{ $user->email }}</span>
                </div>
                <div class="flex items-center gap-3">
                    <i class="fa-solid fa-phone w-5 text-gray-400 dark:text-gray-500"></i>
                    <span class="text-gray-800 dark:text-gray-100 font-medium">{{ $user->phone ?? 'No phone' }}</span>
                </div>
                @if($user->last_seen_at)
                <div class="flex items-center gap-3">
                    <i class="fa-solid fa-clock w-5 text-gray-400 dark:text-gray-500"></i>
                    <span class="text-sm text-gray-600 dark:text-gray-300">Last seen {{ $user->last_seen_at->diffForHumans() }}</span>
                </div>
                @endif
            </div>
        </div>

        <div class="card p-5 md:col-span-2">
            <h3 class="font-bold text-gray-900 dark:text-white text-base mb-4 flex items-center gap-2">
                <span class="w-7 h-7 rounded-lg bg-brand-100 dark:bg-brand-900/40 text-brand-600 dark:text-brand-300 inline-flex items-center justify-center">
                    <i class="fa-solid fa-circle-info text-xs"></i>
                </span>
                About
            </h3>
            <p class="text-base text-gray-800 dark:text-gray-100 leading-relaxed">{{ $user->bio ?? 'No bio provided.' }}</p>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-3">Joined {{ $user->created_at?->format('F j, Y') }}</p>
        </div>
    </div>

    {{-- Monthly shift schedule --}}
    <div class="card p-5">
        <div class="flex items-center justify-between mb-4 flex-wrap gap-2">
            <h3 class="font-bold text-gray-900 dark:text-white text-base flex items-center gap-2">
                <span class="w-7 h-7 rounded-lg bg-brand-100 dark:bg-brand-900/40 text-brand-600 dark:text-brand-300 inline-flex items-center justify-center">
                    <i class="fa-solid fa-calendar-days text-xs"></i>
                </span>
                {{ $monthStart->format('F Y') }} Schedule
                <span class="text-sm text-gray-500 dark:text-gray-400 font-medium">({{ $shifts->count() }} shifts)</span>
            </h3>
            <div class="flex flex-wrap gap-1.5 text-xs">
                <span class="inline-flex items-center gap-1 bg-amber-100  text-amber-700  dark:bg-amber-900/30  dark:text-amber-200  px-2 py-0.5 rounded-full">Day</span>
                <span class="inline-flex items-center gap-1 bg-orange-100 text-orange-700 dark:bg-orange-900/30 dark:text-orange-200 px-2 py-0.5 rounded-full">Evening</span>
                <span class="inline-flex items-center gap-1 bg-indigo-100 text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-200 px-2 py-0.5 rounded-full">Night</span>
                <span class="inline-flex items-center gap-1 bg-purple-100 text-purple-700 dark:bg-purple-900/30 dark:text-purple-200 px-2 py-0.5 rounded-full">On Call</span>
            </div>
        </div>

        {{-- Day-of-week header --}}
        <div class="grid grid-cols-7 gap-1.5 mb-1">
            @foreach(['Sun','Mon','Tue','Wed','Thu','Fri','Sat'] as $dow)
            <div class="text-[10px] font-bold text-gray-400 dark:text-gray-500 uppercase tracking-wider text-center py-1">{{ $dow }}</div>
            @endforeach
        </div>

        {{-- Calendar grid --}}
        <div class="grid grid-cols-7 gap-1.5">
            @php
                $cursor = $monthStart->copy()->startOfWeek(\Carbon\Carbon::SUNDAY);
                $end    = $monthEnd->copy()->endOfWeek(\Carbon\Carbon::SATURDAY);
            @endphp
            @while($cursor <= $end)
                @php
                    $isCurrentMonth = $cursor->month === $monthStart->month;
                    $isToday        = $cursor->isToday();
                    $shift          = $shiftsByDate->get($cursor->toDateString());
                    $shiftClass     = $shift ? ($shiftTypeColors[$shift->shift_type] ?? '') : '';

                    if (!$isCurrentMonth) {
                        $cellBg = 'bg-gray-50/50 dark:bg-slate-900/40 border-gray-100 dark:border-slate-800';
                        $numText = 'text-gray-300 dark:text-gray-600';
                    } elseif ($isToday) {
                        $cellBg = 'border-brand-500 ring-2 ring-brand-200 dark:ring-brand-700/40 bg-brand-50/40 dark:bg-brand-900/20';
                        $numText = 'text-brand-700 dark:text-brand-300';
                    } else {
                        $cellBg = 'border-gray-200 dark:border-slate-700 bg-white dark:bg-slate-800/60';
                        $numText = 'text-gray-700 dark:text-gray-200';
                    }
                @endphp
                <div class="aspect-square min-h-[72px] rounded-lg border p-1.5 text-xs {{ $cellBg }} flex flex-col">
                    <div class="text-sm font-bold {{ $numText }} text-center">{{ $cursor->day }}</div>
                    @if($shift && $isCurrentMonth)
                    <div class="mt-1 px-1 py-0.5 rounded border text-[10px] font-bold capitalize text-center {{ $shiftClass }}">
                        {{ $shift->shift_type === 'on_call' ? 'On Call' : $shift->shift_type }}
                    </div>
                    <div class="text-[10px] font-semibold text-gray-600 dark:text-gray-300 mt-auto text-center">{{ \Carbon\Carbon::parse($shift->start_time)->format('ga') }}–{{ \Carbon\Carbon::parse($shift->end_time)->format('ga') }}</div>
                    @endif
                </div>
                @php $cursor->addDay(); @endphp
            @endwhile
        </div>
    </div>

    {{-- Upcoming shifts beyond this month --}}
    @if($upcoming->count() > 0)
    <div class="card p-5">
        <h3 class="font-bold text-gray-900 dark:text-white text-base mb-4 flex items-center gap-2">
            <span class="w-7 h-7 rounded-lg bg-brand-100 dark:bg-brand-900/40 text-brand-600 dark:text-brand-300 inline-flex items-center justify-center">
                <i class="fa-solid fa-forward text-xs"></i>
            </span>
            Upcoming (Next Month)
        </h3>
        <div class="space-y-2">
            @foreach($upcoming as $s)
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-1 p-3 bg-gray-50 dark:bg-slate-800/60 rounded-xl">
                <p class="font-semibold text-gray-900 dark:text-gray-100 text-sm">{{ \Carbon\Carbon::parse($s->shift_date)->format('l, F j') }}</p>
                <p class="text-sm text-gray-600 dark:text-gray-300 font-medium capitalize sm:text-right">
                    {{ $s->shift_type === 'on_call' ? 'On Call' : $s->shift_type }}
                    &bull;
                    {{ \Carbon\Carbon::parse($s->start_time)->format('g:i A') }}–{{ \Carbon\Carbon::parse($s->end_time)->format('g:i A') }}
                </p>
            </div>
            @endforeach
        </div>
    </div>
    @endif
</div>
@endsection
