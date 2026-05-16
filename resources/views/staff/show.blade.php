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

    {{-- Profile header --}}
    <div class="card overflow-hidden">
        <div class="h-28 bg-gradient-to-r {{ $cfg['grad'] }}"></div>
        <div class="px-6 pb-6 -mt-14">
            <div class="flex items-end gap-4 flex-wrap">
                @if($user->avatarUrl())
                    <img src="{{ $user->avatarUrl() }}" alt="{{ $user->name }}"
                         class="h-24 w-24 rounded-2xl object-cover ring-4 ring-white dark:ring-slate-900 shadow-lg flex-shrink-0">
                @else
                    <div class="h-24 w-24 rounded-2xl bg-gradient-to-br {{ $cfg['grad'] }} flex items-center justify-center text-white text-3xl font-bold ring-4 ring-white dark:ring-slate-900 shadow-lg flex-shrink-0">
                        {{ strtoupper(substr($user->name, 0, 2)) }}
                    </div>
                @endif
                <div class="flex-1 min-w-0 pb-1">
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-white truncate">{{ $user->name }}</h2>
                    <div class="flex items-center gap-2 mt-1.5 flex-wrap">
                        <span class="inline-flex items-center gap-1.5 {{ $cfg['bg'] }} {{ $cfg['text'] }} dark:bg-slate-800 dark:text-gray-200 px-2.5 py-1 rounded-full text-xs font-semibold">
                            <i class="fa-solid {{ $cfg['icon'] }} text-[10px]"></i> {{ $user->roleLabel() }}
                        </span>
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium
                                     bg-{{ $statusHue }}-100 text-{{ $statusHue }}-700
                                     dark:bg-{{ $statusHue }}-900/30 dark:text-{{ $statusHue }}-300">
                            <span class="w-1.5 h-1.5 rounded-full bg-{{ $statusHue }}-400 {{ $user->isOnline() ? 'animate-pulse' : '' }}"></span>
                            {{ $user->statusLabel() }}
                        </span>
                        @if($user->specialization)
                        <span class="text-sm text-gray-500 dark:text-gray-400">&bull; {{ $user->specialization }}</span>
                        @endif
                    </div>
                </div>
                <div class="flex gap-2 pb-1 flex-shrink-0">
                    <a href="{{ route('chat.index', ['with' => $user->id]) }}"
                       class="inline-flex items-center gap-2 px-4 py-2 bg-purple-100 dark:bg-purple-900/30 text-purple-700 dark:text-purple-300 hover:bg-purple-200 dark:hover:bg-purple-900/50 rounded-xl text-sm font-semibold transition-colors">
                        <i class="fa-solid fa-comment"></i> Message
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- Contact + About --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
        <div class="card p-5">
            <h3 class="font-bold text-gray-800 dark:text-white text-sm mb-3 flex items-center gap-2">
                <i class="fa-solid fa-address-card text-brand-500"></i> Contact
            </h3>
            <div class="space-y-2.5 text-sm">
                <div class="flex items-start gap-2 min-w-0">
                    <i class="fa-solid fa-envelope w-4 text-gray-400 dark:text-gray-500 mt-0.5 flex-shrink-0"></i>
                    <span class="text-gray-700 dark:text-gray-200 break-all">{{ $user->email }}</span>
                </div>
                <div class="flex items-center gap-2">
                    <i class="fa-solid fa-phone w-4 text-gray-400 dark:text-gray-500"></i>
                    <span class="text-gray-700 dark:text-gray-200">{{ $user->phone ?? 'No phone' }}</span>
                </div>
                @if($user->last_seen_at)
                <div class="flex items-center gap-2">
                    <i class="fa-solid fa-clock w-4 text-gray-400 dark:text-gray-500"></i>
                    <span class="text-xs text-gray-500 dark:text-gray-400">Last seen {{ $user->last_seen_at->diffForHumans() }}</span>
                </div>
                @endif
            </div>
        </div>

        <div class="card p-5 md:col-span-2">
            <h3 class="font-bold text-gray-800 dark:text-white text-sm mb-3 flex items-center gap-2">
                <i class="fa-solid fa-circle-info text-brand-500"></i> About
            </h3>
            <p class="text-sm text-gray-700 dark:text-gray-200 leading-relaxed">{{ $user->bio ?? 'No bio provided.' }}</p>
            <p class="text-xs text-gray-400 dark:text-gray-500 mt-3">Joined {{ $user->created_at?->format('F j, Y') }}</p>
        </div>
    </div>

    {{-- Monthly shift schedule --}}
    <div class="card p-5">
        <div class="flex items-center justify-between mb-4 flex-wrap gap-2">
            <h3 class="font-bold text-gray-800 dark:text-white text-sm flex items-center gap-2">
                <i class="fa-solid fa-calendar-days text-brand-500"></i> {{ $monthStart->format('F Y') }} Schedule
                <span class="text-xs text-gray-500 dark:text-gray-400 font-normal">({{ $shifts->count() }} shifts)</span>
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
                <div class="aspect-square min-h-[60px] rounded-lg border p-1.5 text-xs {{ $cellBg }}">
                    <div class="font-bold {{ $numText }}">{{ $cursor->day }}</div>
                    @if($shift && $isCurrentMonth)
                    <div class="mt-0.5 px-1 py-0.5 rounded border text-[9px] font-bold capitalize text-center {{ $shiftClass }}">
                        {{ $shift->shift_type === 'on_call' ? 'On Call' : $shift->shift_type }}
                    </div>
                    <div class="text-[9px] text-gray-500 dark:text-gray-400 mt-0.5">{{ \Carbon\Carbon::parse($shift->start_time)->format('ga') }}–{{ \Carbon\Carbon::parse($shift->end_time)->format('ga') }}</div>
                    @endif
                </div>
                @php $cursor->addDay(); @endphp
            @endwhile
        </div>
    </div>

    {{-- Upcoming shifts beyond this month --}}
    @if($upcoming->count() > 0)
    <div class="card p-5">
        <h3 class="font-bold text-gray-800 dark:text-white text-sm mb-3 flex items-center gap-2">
            <i class="fa-solid fa-forward text-brand-500"></i> Upcoming (Next Month)
        </h3>
        <div class="space-y-2">
            @foreach($upcoming as $s)
            <div class="flex items-center justify-between p-3 bg-gray-50 dark:bg-slate-800/60 rounded-xl text-sm">
                <div>
                    <p class="font-medium text-gray-800 dark:text-gray-100">{{ \Carbon\Carbon::parse($s->shift_date)->format('l, F j') }}</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400 capitalize">
                        {{ $s->shift_type === 'on_call' ? 'On Call' : $s->shift_type }} &bull;
                        {{ \Carbon\Carbon::parse($s->start_time)->format('g:i A') }}–{{ \Carbon\Carbon::parse($s->end_time)->format('g:i A') }}
                    </p>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif
</div>
@endsection
