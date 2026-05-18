@extends('layouts.app')
@section('title', $user->name)
@section('page-title', 'Staff Profile')

@section('content')
@php
    $me = Auth::user();
    $canManage = $me->can_('staff.shifts.manage');
    $isSelf = $me->id === $user->id;
    $canEditCalendar = $canManage || $isSelf;

    $roleColors = [
        'admin' => ['grad'=>'from-slate-500 to-slate-700','icon'=>'fa-user-shield'],
        'clinic_head' => ['grad'=>'from-purple-500 to-purple-700','icon'=>'fa-user-tie'],
        'doctor' => ['grad'=>'from-blue-500 to-blue-700','icon'=>'fa-user-doctor'],
        'pharmacist' => ['grad'=>'from-green-500 to-green-700','icon'=>'fa-prescription-bottle-medical'],
        'nurse' => ['grad'=>'from-cyan-500 to-teal-600','icon'=>'fa-user-nurse'],
        'secretary' => ['grad'=>'from-amber-400 to-amber-600','icon'=>'fa-id-badge'],
        'assistant' => ['grad'=>'from-emerald-400 to-emerald-600','icon'=>'fa-user'],
    ];
    $cfg = $roleColors[$user->role] ?? $roleColors['assistant'];

    $shiftsByDate = $shifts->keyBy(fn($s) => \Carbon\Carbon::parse($s->shift_date)->toDateString());
    $eventsByDate = $events->groupBy(fn($e) => $e->event_date->toDateString());

    $shiftTypeMeta = [
        'morning' => ['Morning', 'amber', 'bg-amber-100 text-amber-700 border-amber-300 dark:bg-amber-900/30 dark:text-amber-200 dark:border-amber-800/60', 'bg-amber-500'],
        'afternoon' => ['Afternoon', 'orange', 'bg-orange-100 text-orange-700 border-orange-300 dark:bg-orange-900/30 dark:text-orange-200 dark:border-orange-800/60', 'bg-orange-500'],
        'night' => ['Night', 'indigo', 'bg-indigo-100 text-indigo-700 border-indigo-300 dark:bg-indigo-900/30 dark:text-indigo-200 dark:border-indigo-800/60', 'bg-indigo-500'],
        'on_call' => ['On Call', 'purple', 'bg-purple-100 text-purple-700 border-purple-300 dark:bg-purple-900/30 dark:text-purple-200 dark:border-purple-800/60', 'bg-purple-500'],
    ];

    $statusHue = $user->statusColor();
    $today = today();

    $combined = collect();
    foreach ($shifts as $s) {
        $combined->push(['kind' => 'shift', 'date' => \Carbon\Carbon::parse($s->shift_date), 'data' => $s]);
    }
    foreach ($events as $e) {
        $combined->push(['kind' => 'event', 'date' => $e->event_date, 'data' => $e]);
    }
    $combined = $combined->sortBy(fn($i) => $i['date']->timestamp);
@endphp

<style>
.cal-cell { min-height: 130px; border: 1.5px solid #e2e8f0; border-radius: .75rem; padding: .55rem .55rem .5rem; background: #fff; display: flex; flex-direction: column; gap: .3rem; overflow: hidden; transition: all .12s; cursor: default; position: relative; }
.cal-cell.clickable { cursor: pointer; }
.cal-cell.clickable:hover { border-color: #0d9488; box-shadow: 0 4px 10px rgba(13,148,136,.18); }
.dark .cal-cell { background: rgba(26,36,56,.65); border-color: #2d3a52; }
.dark .cal-cell.clickable:hover { border-color: #14b8a6; }
.cal-cell.is-today { border-color: #0d9488; background: rgba(20,184,166,.08); }
.dark .cal-cell.is-today { border-color: #14b8a6; background: rgba(20,184,166,.14); }
.cal-cell.is-other-month { background: #f8fafc; border-color: #e2e8f0; }
.dark .cal-cell.is-other-month { background: rgba(15,26,46,.4); border-color: rgba(45,58,82,.5); }
.cal-cell.is-past { background: #f1f5f9; }
.cal-cell.is-past .cd-num { color: #94a3b8 !important; }
.cal-cell.is-past .cal-chip { opacity: .85; }
.dark .cal-cell.is-past { background: rgba(15,26,46,.55); }
.dark .cal-cell.is-past .cd-num { color: #64748b !important; }
.cal-cell .cd-num { font-size: 1rem; font-weight: 700; color: #1e293b; line-height: 1; }
.dark .cal-cell .cd-num { color: #e2e8f0; }
.cal-cell.is-other-month .cd-num { color: #cbd5e1; }
.dark .cal-cell.is-other-month .cd-num { color: #475569; }
.cal-cell.is-today .cd-num { color: #0d9488; font-weight: 800; font-size: 1.05rem; }
.dark .cal-cell.is-today .cd-num { color: #5eead4; }

.cal-chip { display: flex; align-items: center; gap: .25rem; padding: .25rem .45rem; border-radius: .4rem; font-size: .78rem; font-weight: 700; line-height: 1.25; border: 1px solid; overflow: hidden; white-space: nowrap; min-height: 1.55rem; position: relative; }
.cal-chip > span.chip-text { flex: 1; min-width: 0; overflow: hidden; text-overflow: ellipsis; }
.cal-chip .chip-del { display: none; width: 1.1rem; height: 1.1rem; align-items: center; justify-content: center; border-radius: 999px; background: rgba(0,0,0,.18); color: #fff; flex-shrink: 0; font-size: .55rem; cursor: pointer; transition: background .12s; }
.cal-chip .chip-del:hover { background: rgba(239,68,68,.85); }
.cal-cell:hover .cal-chip .chip-del { display: inline-flex; }
.cal-chip-time { font-size: .78rem; font-weight: 700; color: #334155; padding: 0 .15rem; letter-spacing: -.01em; }
.dark .cal-chip-time { color: #cbd5e1; }

.cal-dot-row { display: none; gap: 3px; margin-top: 4px; }
.cal-dot { width: 6px; height: 6px; border-radius: 999px; flex-shrink: 0; }

.month-picker-trigger { display: inline-flex; align-items: center; gap: .4rem; padding: .25rem .5rem; border-radius: .5rem; cursor: pointer; transition: background .12s; }
.month-picker-trigger:hover { background: rgba(13,148,136,.08); }
.dark .month-picker-trigger:hover { background: rgba(20,184,166,.12); }

@media (max-width: 767px) {
    .cal-cell { min-height: 56px; padding: .35rem .3rem; gap: 0; cursor: pointer; }
    .cal-cell .cd-num { font-size: .95rem; text-align: center; flex: 1; display: flex; align-items: center; justify-content: center; }
    .cal-cell .cal-chip, .cal-cell .cal-chip-time, .cal-cell .more-pill { display: none !important; }
    .cal-cell .cal-dot-row { display: flex; justify-content: center; }
    .cal-cell.is-selected-day { background: linear-gradient(135deg,#0d9488,#0f766e) !important; border-color: #0d9488 !important; }
    .cal-cell.is-selected-day .cd-num { color: #fff !important; }
    .cal-cell.is-selected-day .cal-dot { background: #fff !important; opacity: .9; }
}

/* Bulk-assign mini calendar cells */
.bulk-cell { aspect-ratio: 1; border: 1.5px solid #e2e8f0; border-radius: .5rem; display: flex; align-items: center; justify-content: center; font-size: .85rem; font-weight: 600; cursor: pointer; background: #fff; transition: all .1s; user-select: none; position: relative; }
.bulk-cell:hover { border-color: #0d9488; }
.dark .bulk-cell { background: rgba(26,36,56,.65); border-color: #2d3a52; color: #cbd5e1; }
.bulk-cell.is-other-month { color: #cbd5e1; background: #f8fafc; }
.dark .bulk-cell.is-other-month { color: #475569; background: rgba(15,26,46,.4); }
.bulk-cell.is-today { border-color: #0d9488; color: #0d9488; }
.bulk-cell.is-past { color: #cbd5e1 !important; background: #f8fafc !important; cursor: not-allowed; pointer-events: none; }
.dark .bulk-cell.is-past { color: #475569 !important; background: rgba(15,26,46,.5) !important; }
.bulk-cell.is-selected { background: #0d9488 !important; color: #fff !important; border-color: #0d9488 !important; }
.bulk-cell.is-selected::after { content: attr(data-shift-letter); position: absolute; top: 1px; right: 3px; font-size: .55rem; font-weight: 700; opacity: .85; }

.staff-pill { display: inline-flex; align-items: center; gap: .45rem; padding: .4rem .8rem; border-radius: 999px; font-size: .82rem; font-weight: 600; border: 1.5px solid transparent; cursor: pointer; transition: all .12s; }
.staff-pill[data-role="admin"] { background: #f1f5f9; color: #334155; border-color: #94a3b8; }
.dark .staff-pill[data-role="admin"] { background: rgba(71,85,105,.22); color: #cbd5e1; border-color: rgba(148,163,184,.4); }
.staff-pill[data-role="clinic_head"] { background: #faf5ff; color: #7e22ce; border-color: #c084fc; }
.dark .staff-pill[data-role="clinic_head"] { background: rgba(147,51,234,.18); color: #e9d5ff; border-color: rgba(192,132,252,.45); }
.staff-pill[data-role="doctor"] { background: #eff6ff; color: #1d4ed8; border-color: #60a5fa; }
.dark .staff-pill[data-role="doctor"] { background: rgba(59,130,246,.18); color: #bfdbfe; border-color: rgba(96,165,250,.45); }
.staff-pill[data-role="pharmacist"] { background: #ecfdf5; color: #047857; border-color: #34d399; }
.dark .staff-pill[data-role="pharmacist"] { background: rgba(16,185,129,.18); color: #a7f3d0; border-color: rgba(52,211,153,.45); }
.staff-pill[data-role="nurse"] { background: #f0fdfa; color: #0f766e; border-color: #2dd4bf; }
.dark .staff-pill[data-role="nurse"] { background: rgba(20,184,166,.22); color: #99f6e4; border-color: rgba(45,212,191,.5); }
.staff-pill[data-role="secretary"] { background: #fffbeb; color: #b45309; border-color: #fbbf24; }
.dark .staff-pill[data-role="secretary"] { background: rgba(245,158,11,.2); color: #fde68a; border-color: rgba(251,191,36,.5); }
.staff-pill[data-role="assistant"] { background: #ecfdf5; color: #047857; border-color: #6ee7b7; }
.dark .staff-pill[data-role="assistant"] { background: rgba(52,211,153,.2); color: #d1fae5; border-color: rgba(110,231,183,.45); }
.staff-pill:hover { filter: brightness(.96); transform: translateY(-1px); }
.dark .staff-pill:hover { filter: brightness(1.1); }
.staff-pill .pill-x { display: none; width: 1.1rem; height: 1.1rem; align-items: center; justify-content: center; border-radius: 999px; background: rgba(255,255,255,.4); font-size: .65rem; }
.staff-pill.is-selected { background: #0d9488 !important; color: #fff !important; border-color: #0d9488 !important; box-shadow: 0 2px 8px rgba(13,148,136,.35); }
.staff-pill.is-selected .pill-x { display: inline-flex; }

.icon-btn-sm { width: 2rem; height: 2rem; border-radius: .55rem; display: inline-flex; align-items: center; justify-content: center; font-size: 1rem; transition: background .12s; }
.icon-btn-rose { color: #f43f5e; }
.icon-btn-rose:hover { background: rgba(244,63,94,.12); }
.btn-clear-all { display: inline-flex; align-items: center; gap: .35rem; padding: .35rem .75rem; border-radius: .55rem; font-size: .85rem; font-weight: 700; color: #f43f5e; background: rgba(244,63,94,.08); transition: background .12s; }
.btn-clear-all:hover { background: rgba(244,63,94,.18); }
</style>

<div class="space-y-5 max-w-6xl mx-auto">

    <div class="flex items-center gap-3">
        <a href="{{ route('staff.index') }}"
           class="w-9 h-9 flex items-center justify-center rounded-xl text-gray-400 dark:text-gray-500 hover:text-gray-700 dark:hover:text-gray-200 hover:bg-gray-100 dark:hover:bg-slate-800">
            <i class="fa-solid fa-arrow-left"></i>
        </a>
        <span class="text-sm text-gray-400 dark:text-gray-500">Back to Staff Directory</span>
    </div>

    <div class="rounded-2xl overflow-hidden bg-gradient-to-r {{ $cfg['grad'] }} text-white shadow-md relative">
        <div class="absolute inset-0 opacity-20 pointer-events-none" style="background-image: radial-gradient(circle at 18% 30%, rgba(255,255,255,.55) 0, transparent 35%), radial-gradient(circle at 80% 75%, rgba(255,255,255,.35) 0, transparent 32%);"></div>
        <div class="relative px-5 sm:px-7 py-6 flex items-center gap-5 flex-wrap">
            @if($user->avatarUrl())
                <img src="{{ $user->avatarUrl() }}" alt="{{ $user->name }}" class="w-20 h-20 sm:w-24 sm:h-24 rounded-2xl object-cover ring-2 ring-white/40 shadow-md flex-shrink-0">
            @else
                <div class="w-20 h-20 sm:w-24 sm:h-24 rounded-2xl bg-white/15 backdrop-blur-sm ring-1 ring-white/25 flex items-center justify-center text-white text-2xl sm:text-3xl font-extrabold flex-shrink-0">
                    {{ strtoupper(substr($user->name, 0, 2)) }}
                </div>
            @endif
            <div class="flex-1 min-w-0">
                <h2 class="text-2xl sm:text-3xl font-extrabold leading-tight truncate">{{ $user->name }}</h2>
                <div class="flex items-center gap-2 mt-2 flex-wrap">
                    <span class="inline-flex items-center gap-1.5 bg-white/20 ring-1 ring-white/25 text-white px-3 py-1.5 rounded-full text-sm font-bold">
                        <i class="fa-solid {{ $cfg['icon'] }} text-xs"></i> {{ $user->roleLabel() }}
                    </span>
                    <span class="inline-flex items-center gap-1.5 bg-white text-{{ $statusHue }}-700 px-3 py-1.5 rounded-full text-sm font-bold shadow-sm">
                        <span class="w-2 h-2 rounded-full bg-{{ $statusHue }}-500 {{ $user->isOnline() ? 'animate-pulse' : '' }}"></span>
                        {{ $user->statusLabel() }}
                    </span>
                    @if($user->specialization)
                    <span class="text-base text-white/85">&bull; {{ $user->specialization }}</span>
                    @endif
                </div>
            </div>
            <a href="{{ route('chat.index', ['with' => $user->id]) }}"
               class="inline-flex items-center justify-center gap-2 px-5 py-3 rounded-xl bg-white text-purple-700 hover:bg-purple-50 text-base font-bold transition-colors shadow-sm w-full sm:w-auto flex-shrink-0">
                <i class="fa-solid fa-comment"></i> Message
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
        <div class="card p-6">
            <h3 class="font-bold text-gray-900 dark:text-white text-lg mb-5 flex items-center gap-2.5">
                <span class="w-8 h-8 rounded-lg bg-brand-100 dark:bg-brand-900/40 text-brand-600 dark:text-brand-300 inline-flex items-center justify-center">
                    <i class="fa-solid fa-address-card text-sm"></i>
                </span>
                Contact
            </h3>
            <div class="space-y-4 text-base">
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
                    <span class="text-base text-gray-600 dark:text-gray-300">Last seen {{ $user->last_seen_at->diffForHumans() }}</span>
                </div>
                @endif
            </div>
        </div>
        <div class="card p-6 md:col-span-2">
            <h3 class="font-bold text-gray-900 dark:text-white text-lg mb-5 flex items-center gap-2.5">
                <span class="w-8 h-8 rounded-lg bg-brand-100 dark:bg-brand-900/40 text-brand-600 dark:text-brand-300 inline-flex items-center justify-center">
                    <i class="fa-solid fa-circle-info text-sm"></i>
                </span>
                About
            </h3>
            <p class="text-base text-gray-800 dark:text-gray-100 leading-relaxed">{{ $user->bio ?? 'No bio provided.' }}</p>
            <p class="text-base text-gray-500 dark:text-gray-400 mt-3">Joined {{ $user->created_at?->format('F j, Y') }}</p>
        </div>
    </div>

    <div class="card p-5" id="calendarCard">
        <div class="flex items-center justify-between mb-5 flex-wrap gap-3">
            <div class="flex items-center gap-3 flex-wrap">
                <h3 class="font-bold text-gray-900 dark:text-white text-xl flex items-center gap-2.5">
                    <i class="fa-solid fa-calendar text-brand-500"></i>
                    <span class="month-picker-trigger" onclick="event.stopPropagation(); toggleMonthPicker()" id="monthPickerTrigger">
                        {{ $monthStart->format('F Y') }}
                        <i class="fa-solid fa-chevron-down text-xs text-gray-400"></i>
                    </span>
                </h3>
                <span class="text-sm text-gray-500 dark:text-gray-400 font-medium">{{ $shifts->count() }} shift{{ $shifts->count()===1?'':'s' }} · {{ $events->count() }} event{{ $events->count()===1?'':'s' }}</span>
            </div>
            <div class="flex items-center gap-2 flex-wrap">
                @if($canManage)
                <button type="button" onclick="openBulkAssign()"
                        class="inline-flex items-center gap-2 px-4 h-9 rounded-lg bg-brand-600 hover:bg-brand-700 text-white text-sm font-bold shadow-sm">
                    <i class="fa-solid fa-calendar-plus"></i> Add Shift
                </button>
                @endif
                @if($canEditCalendar)
                <button type="button" onclick="openEventBulk()"
                        class="inline-flex items-center gap-2 px-4 h-9 rounded-lg border-2 border-pink-500 text-pink-700 dark:text-pink-300 hover:bg-pink-50 dark:hover:bg-pink-900/20 text-sm font-bold">
                    <i class="fa-solid fa-calendar-plus"></i> Events
                </button>
                @endif
            </div>
        </div>

        {{-- Month-picker dropdown --}}
        <div id="monthPicker" class="hidden mb-4 p-4 rounded-xl bg-gray-50 dark:bg-slate-800/60 border border-gray-200 dark:border-slate-700">
            <div class="flex items-center justify-between mb-3">
                <a href="{{ route('staff.show', ['user' => $user->id, 'year' => $year - 1, 'month' => $month]) }}"
                   class="w-8 h-8 inline-flex items-center justify-center rounded-lg hover:bg-gray-200 dark:hover:bg-slate-700 text-gray-600 dark:text-gray-300">
                    <i class="fa-solid fa-chevron-left text-xs"></i>
                </a>
                <span class="font-bold text-lg text-gray-900 dark:text-white">{{ $year }}</span>
                <a href="{{ route('staff.show', ['user' => $user->id, 'year' => $year + 1, 'month' => $month]) }}"
                   class="w-8 h-8 inline-flex items-center justify-center rounded-lg hover:bg-gray-200 dark:hover:bg-slate-700 text-gray-600 dark:text-gray-300">
                    <i class="fa-solid fa-chevron-right text-xs"></i>
                </a>
            </div>
            <div class="grid grid-cols-3 sm:grid-cols-4 md:grid-cols-6 gap-2">
                @foreach(range(1,12) as $m)
                    @php $mLabel = \Carbon\Carbon::create($year, $m, 1)->format('M'); @endphp
                    <a href="{{ route('staff.show', ['user' => $user->id, 'year' => $year, 'month' => $m]) }}"
                       class="px-3 py-2.5 rounded-lg text-sm font-bold text-center transition-colors {{ $m === $month ? 'bg-brand-600 text-white' : 'bg-white dark:bg-slate-900 text-gray-700 dark:text-gray-200 hover:bg-brand-50 dark:hover:bg-slate-700 border border-gray-200 dark:border-slate-700' }}">
                        {{ $mLabel }}
                    </a>
                @endforeach
            </div>
        </div>

        {{-- Day-of-week header --}}
        <div class="grid grid-cols-7 gap-1.5 mb-2">
            @foreach(['Sun','Mon','Tue','Wed','Thu','Fri','Sat'] as $dow)
            <div class="text-xs font-bold text-gray-400 dark:text-gray-500 uppercase tracking-wider text-center py-1">{{ $dow }}</div>
            @endforeach
        </div>

        {{-- Calendar grid --}}
        <div class="grid grid-cols-7 gap-1.5" id="monthGrid">
            @php
                $cursor = $monthStart->copy()->startOfWeek(\Carbon\Carbon::SUNDAY);
                $end = $monthEnd->copy()->endOfWeek(\Carbon\Carbon::SATURDAY);
            @endphp
            @while($cursor <= $end)
                @php
                    $isCurrentMonth = $cursor->month === $monthStart->month;
                    $isToday = $cursor->isSameDay($today);
                    $isPast = $cursor->lt($today) && !$isToday;
                    $dStr = $cursor->toDateString();
                    $cellShift = $shiftsByDate->get($dStr);
                    $cellEvents = $eventsByDate->get($dStr, collect());
                    $stMeta = $cellShift ? ($shiftTypeMeta[$cellShift->shift_type] ?? null) : null;
                    $cellClasses = 'cal-cell';
                    if (!$isCurrentMonth) $cellClasses .= ' is-other-month';
                    if ($isToday) $cellClasses .= ' is-today';
                    if ($isPast && $isCurrentMonth) $cellClasses .= ' is-past';
                    if ($isCurrentMonth) $cellClasses .= ' clickable';
                @endphp
                <div class="{{ $cellClasses }}" data-date="{{ $dStr }}" data-day-cell data-past="{{ $isPast ? '1' : '' }}"
                     @if($isCurrentMonth) onclick="onDayClick('{{ $dStr }}', this, {{ $isPast ? 'true' : 'false' }})" @endif>
                    <div class="flex items-center justify-between">
                        <span class="cd-num">{{ $cursor->day }}</span>
                    </div>
                    @if($cellShift && $isCurrentMonth)
                    <span class="cal-chip {{ $stMeta[2] }}" title="{{ $stMeta[0] }} · {{ \Carbon\Carbon::parse($cellShift->start_time)->format('ga') }}–{{ \Carbon\Carbon::parse($cellShift->end_time)->format('ga') }}">
                        <span class="chip-text">{{ $stMeta[0] }}</span>
                        @if($canManage)
                        <button type="button" class="chip-del" onclick="event.stopPropagation(); deleteShift({{ $cellShift->id }}, '{{ addslashes($stMeta[0]) }}')" title="Remove shift"><i class="fa-solid fa-xmark"></i></button>
                        @endif
                    </span>
                    <span class="cal-chip-time">{{ \Carbon\Carbon::parse($cellShift->start_time)->format('g:iA') }}–{{ \Carbon\Carbon::parse($cellShift->end_time)->format('g:iA') }}</span>
                    @endif
                    @foreach($cellEvents->take(2) as $ev)
                    @php
                        $canDelEv = $canManage || $ev->created_by === $me->id || ($ev->user_id && $ev->user_id === $me->id);
                    @endphp
                    <span class="cal-chip {{ $ev->colorClasses() }}" title="{{ $ev->title }}{{ $ev->isGlobal() ? ' (clinic-wide)' : '' }}">
                        <span class="chip-text">@if($ev->isGlobal())<i class="fa-solid fa-globe text-[.65rem] mr-0.5"></i>@endif{{ $ev->title }}</span>
                        @if($canDelEv)
                        <button type="button" class="chip-del" onclick="event.stopPropagation(); deleteEvent({{ $ev->id }}, '{{ addslashes($ev->title) }}')" title="Remove event"><i class="fa-solid fa-xmark"></i></button>
                        @endif
                    </span>
                    @endforeach
                    @if($cellEvents->count() > 2)
                    <span class="more-pill text-xs font-bold text-gray-400 dark:text-gray-500">+{{ $cellEvents->count() - 2 }} more</span>
                    @endif
                    {{-- Mobile dots --}}
                    @if($isCurrentMonth && ($cellShift || $cellEvents->count() > 0))
                    <div class="cal-dot-row">
                        @if($cellShift)<span class="cal-dot {{ $stMeta[3] }}" title="{{ $stMeta[0] }} shift"></span>@endif
                        @foreach($cellEvents->take(3) as $ev)
                        <span class="cal-dot {{ $ev->dotColor() }}" title="{{ $ev->title }}"></span>
                        @endforeach
                    </div>
                    @endif
                </div>
                @php $cursor->addDay(); @endphp
            @endwhile
        </div>

        {{-- Mobile selected-day detail (Google-Calendar style bottom panel) --}}
        <div id="dayDetail" class="md:hidden mt-4 p-4 rounded-xl bg-gray-50 dark:bg-slate-800/60 border-2 border-gray-200 dark:border-slate-700 hidden">
            <div class="flex items-center justify-between mb-3">
                <p class="font-bold text-gray-900 dark:text-white" id="dayDetailLabel">—</p>
                @if($canEditCalendar)
                <button type="button" id="dayDetailAdd" onclick="if(lastDetailDate) openEventModalForDate(lastDetailDate)" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-pink-600 text-white text-xs font-bold">
                    <i class="fa-solid fa-calendar-plus"></i> Add Event
                </button>
                @endif
            </div>
            <div id="dayDetailContent" class="space-y-2"></div>
        </div>

        {{-- This-month list (desktop + as fallback on mobile) --}}
        @if($combined->count() > 0)
        <div class="mt-6 pt-5 border-t border-gray-100 dark:border-slate-700 hidden md:block">
            <h4 class="text-sm font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-3">This Month</h4>
            <div class="space-y-2">
                @foreach($combined as $item)
                @php $itemMeta = $item['kind']==='shift' ? ($shiftTypeMeta[$item['data']->shift_type] ?? null) : null; @endphp
                <div class="flex items-center gap-3 p-3 bg-gray-50 dark:bg-slate-800/60 rounded-xl">
                    <div class="text-center min-w-[3.5rem]">
                        <div class="text-xs font-bold text-gray-400 dark:text-gray-500 uppercase">{{ $item['date']->format('D') }}</div>
                        <div class="text-lg font-extrabold text-gray-800 dark:text-gray-100 leading-tight">{{ $item['date']->day }}</div>
                    </div>
                    <div class="flex-1 min-w-0">
                        @if($item['kind'] === 'shift')
                        <p class="font-semibold text-gray-900 dark:text-gray-100 text-base flex items-center gap-2">
                            <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-md text-xs font-bold {{ $itemMeta[2] }}">{{ $itemMeta[0] }}</span>
                            shift
                        </p>
                        <p class="text-sm text-gray-500 dark:text-gray-400">{{ \Carbon\Carbon::parse($item['data']->start_time)->format('g:i A') }} – {{ \Carbon\Carbon::parse($item['data']->end_time)->format('g:i A') }}</p>
                        @else
                        <p class="font-semibold text-gray-900 dark:text-gray-100 text-base flex items-center gap-1.5">
                            <span class="w-2 h-2 rounded-full {{ $item['data']->dotColor() }}"></span>
                            {{ $item['data']->title }}
                            @if($item['data']->isGlobal())
                            <span class="text-[10px] font-bold uppercase tracking-wider text-gray-400 ml-1">Clinic-wide</span>
                            @endif
                        </p>
                        @if($item['data']->description)
                        <p class="text-sm text-gray-500 dark:text-gray-400 truncate">{{ $item['data']->description }}</p>
                        @endif
                        @endif
                    </div>
                    @if($canManage && $item['kind'] === 'shift')
                    <form method="POST" action="{{ route('staff.shifts.destroy', $item['data']) }}" onsubmit="return confirm('Remove this shift?')" class="inline">
                        @csrf @method('DELETE')
                        <button type="submit" class="w-8 h-8 inline-flex items-center justify-center rounded-lg text-rose-500 hover:bg-rose-50 dark:hover:bg-rose-900/20" title="Remove shift">
                            <i class="fa-solid fa-trash text-sm"></i>
                        </button>
                    </form>
                    @endif
                    @if(($canManage || $item['data']->created_by === $me->id || ($item['data']->user_id && $item['data']->user_id === $me->id)) && $item['kind'] === 'event')
                    <form method="POST" action="{{ route('staff.events.destroy', $item['data']) }}" onsubmit="return confirm('Remove this event?')" class="inline">
                        @csrf @method('DELETE')
                        <button type="submit" class="w-8 h-8 inline-flex items-center justify-center rounded-lg text-rose-500 hover:bg-rose-50 dark:hover:bg-rose-900/20" title="Remove event">
                            <i class="fa-solid fa-trash text-sm"></i>
                        </button>
                    </form>
                    @endif
                </div>
                @endforeach
            </div>
        </div>
        @endif
    </div>

    @if($upcoming->count() > 0)
    <div class="card p-6">
        <h3 class="font-bold text-gray-900 dark:text-white text-lg mb-5 flex items-center gap-2.5">
            <span class="w-8 h-8 rounded-lg bg-brand-100 dark:bg-brand-900/40 text-brand-600 dark:text-brand-300 inline-flex items-center justify-center">
                <i class="fa-solid fa-forward text-sm"></i>
            </span>
            Upcoming Shifts (Beyond {{ $monthStart->format('F') }})
        </h3>
        <div class="space-y-2">
            @foreach($upcoming as $s)
            @php $upMeta = $shiftTypeMeta[$s->shift_type] ?? null; @endphp
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-1 p-4 bg-gray-50 dark:bg-slate-800/60 rounded-xl">
                <p class="font-semibold text-gray-900 dark:text-gray-100 text-base">{{ \Carbon\Carbon::parse($s->shift_date)->format('l, F j') }}</p>
                <p class="text-base text-gray-600 dark:text-gray-300 font-medium sm:text-right flex items-center gap-2 sm:justify-end">
                    <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-md text-xs font-bold {{ $upMeta[2] }}">{{ $upMeta[0] }}</span>
                    {{ \Carbon\Carbon::parse($s->start_time)->format('g:i A') }}–{{ \Carbon\Carbon::parse($s->end_time)->format('g:i A') }}
                </p>
            </div>
            @endforeach
        </div>
    </div>
    @endif
</div>

{{-- Bottom data snapshots for JS --}}
@php
    $shiftsJson = [];
    foreach ($shifts as $s) {
        $key = \Carbon\Carbon::parse($s->shift_date)->toDateString();
        $stm = $shiftTypeMeta[$s->shift_type] ?? null;
        $shiftsJson[$key] = [
            'type' => $s->shift_type,
            'label' => $stm[0] ?? $s->shift_type,
            'color' => $stm[1] ?? 'gray',
            'start' => \Carbon\Carbon::parse($s->start_time)->format('g:i A'),
            'end' => \Carbon\Carbon::parse($s->end_time)->format('g:i A'),
            'id' => $s->id,
        ];
    }
    $eventsJson = [];
    foreach ($events as $e) {
        $key = $e->event_date->toDateString();
        $canDel = $canManage || $e->created_by === $me->id || ($e->user_id && $e->user_id === $me->id);
        $eventsJson[$key][] = [
            'id' => $e->id,
            'title' => $e->title,
            'description' => $e->description,
            'color' => $e->color,
            'dot' => $e->dotColor(),
            'global' => $e->isGlobal(),
            'canDelete' => $canDel,
        ];
    }
@endphp

@if($canEditCalendar)
<div id="eventModal" class="hidden fixed inset-0 bg-black/60 z-50 flex items-center justify-center p-4 backdrop-blur-sm" onclick="if(event.target===this) closeEventModal()">
    <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-2xl w-full max-w-md border-2 border-gray-100 dark:border-slate-700 overflow-hidden">
        <div class="bg-gradient-to-r from-pink-500 to-rose-600 px-6 py-5 text-white flex items-center justify-between">
            <h3 class="text-lg font-bold">Add Event</h3>
            <button type="button" onclick="closeEventModal()" class="w-8 h-8 rounded-xl flex items-center justify-center hover:bg-white/20"><i class="fa-solid fa-xmark"></i></button>
        </div>
        <form method="POST" action="{{ route('staff.events.store', $user) }}" class="p-5 space-y-4">
            @csrf
            <input type="hidden" name="event_date" id="eventFormDate">
            <p class="text-sm text-gray-500 dark:text-gray-400">On <span id="eventFormDateLabel" class="font-bold text-gray-900 dark:text-white"></span></p>
            <div>
                <label class="label">Title <span class="text-red-500">*</span></label>
                <input type="text" name="title" maxlength="120" required class="input" placeholder="e.g. Annual leave, Eid holiday, Team meeting">
            </div>
            <div>
                <label class="label">Description <span class="text-gray-400 text-xs font-normal">(optional)</span></label>
                <textarea name="description" rows="2" maxlength="500" class="input" placeholder="Notes about this event"></textarea>
            </div>
            <div>
                <label class="label">Color</label>
                <select name="color" class="input cs-select">
                    <option value="info">Info (Green)</option>
                    <option value="note">Note (Blue)</option>
                    <option value="festive">Festive (Magenta)</option>
                    <option value="important">Important (Red)</option>
                    <option value="holiday">Holiday (Rose)</option>
                </select>
            </div>
            @if($canManage)
            <div>
                <label class="label">Scope</label>
                <div class="grid grid-cols-3 gap-2">
                    <label class="flex flex-col items-center gap-1.5 p-3 rounded-xl border-2 border-gray-200 dark:border-slate-700 cursor-pointer has-[:checked]:border-brand-500 has-[:checked]:bg-brand-50 dark:has-[:checked]:bg-brand-900/20" onclick="setEventScope('personal')">
                        <input type="radio" name="scope" value="personal" checked class="sr-only">
                        <i class="fa-solid fa-user text-brand-500 text-lg"></i>
                        <span class="text-xs font-bold">Personal</span>
                    </label>
                    <label class="flex flex-col items-center gap-1.5 p-3 rounded-xl border-2 border-gray-200 dark:border-slate-700 cursor-pointer has-[:checked]:border-brand-500 has-[:checked]:bg-brand-50 dark:has-[:checked]:bg-brand-900/20" onclick="setEventScope('global')">
                        <input type="radio" name="scope" value="global" class="sr-only">
                        <i class="fa-solid fa-globe text-brand-500 text-lg"></i>
                        <span class="text-xs font-bold">Clinic-wide</span>
                    </label>
                    <label class="flex flex-col items-center gap-1.5 p-3 rounded-xl border-2 border-gray-200 dark:border-slate-700 cursor-pointer has-[:checked]:border-brand-500 has-[:checked]:bg-brand-50 dark:has-[:checked]:bg-brand-900/20" onclick="setEventScope('custom')">
                        <input type="radio" name="scope" value="custom" class="sr-only">
                        <i class="fa-solid fa-users text-brand-500 text-lg"></i>
                        <span class="text-xs font-bold">Custom</span>
                    </label>
                </div>
            </div>
            <div id="eventCustomPicker" class="hidden p-3 rounded-xl bg-gray-50 dark:bg-slate-800/60 border border-gray-200 dark:border-slate-700">
                <p class="text-xs text-gray-500 dark:text-gray-400 mb-2">{{ $user->name }} is always included. Tap any pill to add them too.</p>
                <div class="flex flex-wrap gap-1.5">
                    @foreach($allStaff as $st)
                    @if($st->id !== $user->id)
                    <label class="staff-pill" data-role="{{ $st->role }}">
                        <input type="checkbox" name="custom_user_ids[]" value="{{ $st->id }}" class="sr-only event-custom-cb">
                        <span>{{ $st->name }}</span>
                        <span class="pill-x"><i class="fa-solid fa-xmark"></i></span>
                    </label>
                    @endif
                    @endforeach
                </div>
            </div>
            @else
            <input type="hidden" name="scope" value="personal">
            @endif
            <div class="flex justify-end gap-3 pt-3 border-t border-gray-100 dark:border-slate-700">
                <button type="button" onclick="closeEventModal()" class="btn-secondary">Cancel</button>
                <button type="submit" class="btn-primary"><i class="fa-solid fa-check"></i> Save Event</button>
            </div>
        </form>
    </div>
</div>
@endif

{{-- Bulk-Assign Shift modal (admin/clinic_head) --}}
@if($canManage)
<div id="bulkModal" class="hidden fixed inset-0 bg-black/60 z-50 flex items-center justify-center p-3 backdrop-blur-sm" onclick="if(event.target===this) closeBulkAssign()">
    <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-2xl w-full max-w-3xl border-2 border-gray-100 dark:border-slate-700 overflow-hidden max-h-[95vh] flex flex-col">
        <div class="bg-gradient-to-r from-brand-600 to-teal-700 px-6 py-5 text-white flex items-center justify-between flex-shrink-0">
            <h3 class="text-lg font-bold flex items-center gap-2"><i class="fa-solid fa-calendar-plus"></i> Bulk Assign Shifts</h3>
            <button type="button" onclick="closeBulkAssign()" class="w-8 h-8 rounded-xl flex items-center justify-center hover:bg-white/20"><i class="fa-solid fa-xmark"></i></button>
        </div>
        <div class="overflow-y-auto flex-1 p-5 space-y-5">
            <p class="text-sm text-gray-500 dark:text-gray-400">Pick a shift type, then click days on the calendar to apply it. Switch types and keep clicking to mix shifts across the month.</p>

            <div>
                <label class="label">Shift Type</label>
                <div class="grid grid-cols-2 md:grid-cols-5 gap-2" id="bulkShiftPicker">
                    <button type="button" data-st="morning" data-letter="M" data-start="07:00" data-end="15:00" class="bulk-st-btn px-3 py-3 rounded-xl border-2 border-gray-200 dark:border-slate-700 hover:border-amber-400 text-base font-bold text-left">
                        <span class="block">Morning</span>
                        <span class="block text-sm text-gray-500 dark:text-gray-400 font-semibold">7am–3pm</span>
                    </button>
                    <button type="button" data-st="afternoon" data-letter="A" data-start="15:00" data-end="23:00" class="bulk-st-btn px-3 py-3 rounded-xl border-2 border-gray-200 dark:border-slate-700 hover:border-orange-400 text-base font-bold text-left">
                        <span class="block">Afternoon</span>
                        <span class="block text-sm text-gray-500 dark:text-gray-400 font-semibold">3pm–11pm</span>
                    </button>
                    <button type="button" data-st="night" data-letter="N" data-start="23:00" data-end="07:00" class="bulk-st-btn px-3 py-3 rounded-xl border-2 border-gray-200 dark:border-slate-700 hover:border-indigo-400 text-base font-bold text-left">
                        <span class="block">Night</span>
                        <span class="block text-sm text-gray-500 dark:text-gray-400 font-semibold">11pm–7am</span>
                    </button>
                    <button type="button" data-st="on_call" data-letter="C" data-start="09:00" data-end="17:00" class="bulk-st-btn px-3 py-3 rounded-xl border-2 border-gray-200 dark:border-slate-700 hover:border-purple-400 text-base font-bold text-left">
                        <span class="block">On Call</span>
                        <span class="block text-sm text-gray-500 dark:text-gray-400 font-semibold">9am–5pm</span>
                    </button>
                    <button type="button" data-st="custom" data-letter="?" data-start="" data-end="" class="bulk-st-btn px-3 py-3 rounded-xl border-2 border-gray-200 dark:border-slate-700 hover:border-rose-400 text-base font-bold text-left">
                        <span class="block">Custom</span>
                        <span class="block text-sm text-gray-500 dark:text-gray-400 font-semibold">Pick times</span>
                    </button>
                </div>
                <div id="bulkCustomTimes" class="hidden mt-3 p-3 rounded-xl border-2 border-rose-200 dark:border-rose-800/40 bg-rose-50/40 dark:bg-rose-900/15">
                    <p class="text-xs font-bold text-rose-700 dark:text-rose-300 mb-2 uppercase tracking-wider">Custom times — applied to every day you click next</p>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="label">Start</label>
                            <input type="time" id="bulkCustomStart" class="input" value="08:00" onchange="bulkUpdateCustomTimes()">
                        </div>
                        <div>
                            <label class="label">End</label>
                            <input type="time" id="bulkCustomEnd" class="input" value="17:00" onchange="bulkUpdateCustomTimes()">
                        </div>
                    </div>
                </div>
            </div>

            {{-- Interactive bulk month grid --}}
            <div>
                <div class="flex items-center justify-between mb-3">
                    <button type="button" onclick="bulkPrevMonth()" class="w-8 h-8 rounded-lg border border-gray-200 dark:border-slate-700 hover:bg-gray-100 dark:hover:bg-slate-800 inline-flex items-center justify-center text-gray-600 dark:text-gray-300">
                        <i class="fa-solid fa-chevron-left text-xs"></i>
                    </button>
                    <span id="bulkMonthLabel" class="font-bold text-lg text-gray-900 dark:text-white"></span>
                    <button type="button" onclick="bulkNextMonth()" class="w-8 h-8 rounded-lg border border-gray-200 dark:border-slate-700 hover:bg-gray-100 dark:hover:bg-slate-800 inline-flex items-center justify-center text-gray-600 dark:text-gray-300">
                        <i class="fa-solid fa-chevron-right text-xs"></i>
                    </button>
                </div>
                <div class="grid grid-cols-7 gap-1 mb-2">
                    @foreach(['S','M','T','W','T','F','S'] as $dow)
                    <div class="text-[10px] font-bold text-gray-400 dark:text-gray-500 uppercase tracking-wider text-center">{{ $dow }}</div>
                    @endforeach
                </div>
                <div id="bulkGrid" class="grid grid-cols-7 gap-1"></div>
            </div>

            <div id="bulkStagedWrap" class="hidden">
                <label class="label flex items-center justify-between">
                    <span>Pending Shifts <span id="bulkStagedCount" class="text-brand-600 font-bold"></span></span>
                    <button type="button" onclick="bulkClearStaged()" class="btn-clear-all">
                        <i class="fa-solid fa-trash text-sm"></i> Clear all
                    </button>
                </label>
                <div id="bulkStagedList" class="space-y-1.5 max-h-48 overflow-y-auto p-2 rounded-lg bg-gray-50 dark:bg-slate-800/60 border border-gray-200 dark:border-slate-700"></div>
            </div>

            <div>
                <button type="button" onclick="bulkToggleStaffPicker()" class="flex items-center gap-2 text-sm font-bold text-brand-700 dark:text-brand-300 hover:underline">
                    <i class="fa-solid fa-chevron-right text-xs" id="bulkStaffChevron"></i>
                    Apply to additional staff
                </button>
                <div id="bulkStaffPicker" class="hidden mt-3 p-3 rounded-xl bg-gray-50 dark:bg-slate-800/60 border border-gray-200 dark:border-slate-700">
                    <p class="text-sm text-gray-500 dark:text-gray-400 mb-3"><span id="bulkBaseStaffName">{{ $user->name }}</span> is included by default. Tap any pill to copy the same shifts to them too.</p>
                    <div class="relative mb-3">
                        <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-400 text-sm pointer-events-none"></i>
                        <input type="text" id="bulkStaffSearch" oninput="filterBulkStaff()" placeholder="Search staff…"
                               class="input pl-10 text-sm py-2">
                    </div>
                    @php
                        $groupedStaff = $allStaff->groupBy('role');
                        $roleOrder = ['clinic_head','doctor','nurse','pharmacist','secretary','assistant','admin'];
                        $roleLabels = ['clinic_head'=>'Clinic Head','doctor'=>'Doctor','nurse'=>'Nurse','pharmacist'=>'Pharmacist','secretary'=>'Secretary','assistant'=>'Assistant','admin'=>'Admin'];
                    @endphp
                    <div id="bulkStaffPills" class="space-y-3">
                        @foreach($roleOrder as $roleKey)
                            @if(isset($groupedStaff[$roleKey]) && $groupedStaff[$roleKey]->count() > 0)
                            <div class="bulk-role-group" data-role-group="{{ $roleKey }}">
                                <p class="text-[10px] font-bold uppercase tracking-wider text-gray-400 dark:text-gray-500 mb-1.5 flex items-center gap-1.5">
                                    <span class="w-1.5 h-1.5 rounded-full bg-gray-300 dark:bg-slate-600"></span>
                                    {{ $roleLabels[$roleKey] }}
                                    <span class="text-gray-400 dark:text-gray-500 font-normal normal-case">({{ $groupedStaff[$roleKey]->count() }})</span>
                                </p>
                                <div class="flex flex-wrap gap-1.5">
                                    @foreach($groupedStaff[$roleKey] as $st)
                                    <button type="button" data-staff-id="{{ $st->id }}" data-staff-name="{{ $st->name }}" data-role="{{ $st->role }}" class="staff-pill bulk-staff-pill {{ $st->id === $user->id ? 'is-base' : '' }}" onclick="onBulkStaffPillClick(this)">
                                        <span>{{ $st->name }}</span>
                                        <span class="pill-x"><i class="fa-solid fa-xmark"></i></span>
                                    </button>
                                    @endforeach
                                </div>
                            </div>
                            @endif
                        @endforeach
                    </div>
                    <p id="bulkStaffEmpty" class="hidden text-xs text-gray-400 dark:text-gray-500 italic py-2 text-center">No matches.</p>
                </div>
            </div>
        </div>
        <div class="border-t border-gray-100 dark:border-slate-700 px-5 py-4 flex items-center justify-between gap-3 flex-shrink-0">
            <span id="bulkSummary" class="text-sm text-gray-500 dark:text-gray-400">No shifts staged.</span>
            <div class="flex gap-2">
                <button type="button" onclick="closeBulkAssign()" class="btn-secondary">Cancel</button>
                <button type="button" id="bulkSubmitBtn" onclick="bulkSubmit()" disabled class="btn-primary disabled:opacity-50 disabled:cursor-not-allowed"><i class="fa-solid fa-check"></i> Save</button>
            </div>
        </div>
    </div>
</div>
@endif

{{-- Bulk Event modal — same vibe as bulk-shift --}}
@if($canEditCalendar)
<div id="eventBulkModal" class="hidden fixed inset-0 bg-black/60 z-50 flex items-center justify-center p-3 backdrop-blur-sm" onclick="if(event.target===this) closeEventBulk()">
    <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-2xl w-full max-w-3xl border-2 border-gray-100 dark:border-slate-700 overflow-hidden max-h-[95vh] flex flex-col">
        <div class="bg-gradient-to-r from-pink-500 to-rose-600 px-6 py-5 text-white flex items-center justify-between flex-shrink-0">
            <h3 class="text-lg font-bold flex items-center gap-2"><i class="fa-solid fa-calendar-plus"></i> Add Events</h3>
            <button type="button" onclick="closeEventBulk()" class="w-8 h-8 rounded-xl flex items-center justify-center hover:bg-white/20"><i class="fa-solid fa-xmark"></i></button>
        </div>
        <div class="overflow-y-auto flex-1 p-5 space-y-5">
            <p class="text-sm text-gray-500 dark:text-gray-400">Set the event details, then click days on the calendar to stage them. One event per selected day will be created.</p>

            <div>
                <label class="label">Title <span class="text-red-500">*</span></label>
                <input type="text" id="evbTitle" maxlength="120" class="input" placeholder="e.g. Annual leave, Eid holiday, Team meeting">
            </div>
            <div>
                <label class="label">Description <span class="text-gray-400 text-xs font-normal">(optional)</span></label>
                <textarea id="evbDescription" rows="2" maxlength="500" class="input" placeholder="Notes for everyone affected"></textarea>
            </div>
            <div>
                <label class="label">Color</label>
                <select id="evbColor" class="input cs-select">
                    <option value="info">Info (Green)</option>
                    <option value="note">Note (Blue)</option>
                    <option value="festive">Festive (Magenta)</option>
                    <option value="important">Important (Red)</option>
                    <option value="holiday">Holiday (Rose)</option>
                </select>
            </div>

            <div>
                <div class="flex items-center justify-between mb-3">
                    <button type="button" onclick="evbPrevMonth()" class="w-8 h-8 rounded-lg border border-gray-200 dark:border-slate-700 hover:bg-gray-100 dark:hover:bg-slate-800 inline-flex items-center justify-center text-gray-600 dark:text-gray-300">
                        <i class="fa-solid fa-chevron-left text-xs"></i>
                    </button>
                    <span id="evbMonthLabel" class="font-bold text-lg text-gray-900 dark:text-white"></span>
                    <button type="button" onclick="evbNextMonth()" class="w-8 h-8 rounded-lg border border-gray-200 dark:border-slate-700 hover:bg-gray-100 dark:hover:bg-slate-800 inline-flex items-center justify-center text-gray-600 dark:text-gray-300">
                        <i class="fa-solid fa-chevron-right text-xs"></i>
                    </button>
                </div>
                <div class="grid grid-cols-7 gap-1 mb-2">
                    @foreach(['S','M','T','W','T','F','S'] as $dow)
                    <div class="text-[10px] font-bold text-gray-400 dark:text-gray-500 uppercase tracking-wider text-center">{{ $dow }}</div>
                    @endforeach
                </div>
                <div id="evbGrid" class="grid grid-cols-7 gap-1"></div>
            </div>

            <div id="evbStagedWrap" class="hidden">
                <label class="label flex items-center justify-between">
                    <span>Selected Dates <span id="evbStagedCount" class="text-pink-600 font-bold"></span></span>
                    <button type="button" onclick="evbClearStaged()" class="btn-clear-all">
                        <i class="fa-solid fa-trash text-sm"></i> Clear all
                    </button>
                </label>
                <div id="evbStagedList" class="flex flex-wrap gap-1.5 max-h-32 overflow-y-auto p-2 rounded-lg bg-gray-50 dark:bg-slate-800/60 border border-gray-200 dark:border-slate-700"></div>
            </div>

            @if($canManage)
            <div>
                <label class="label">Scope</label>
                <div class="grid grid-cols-3 gap-2">
                    <button type="button" data-scope="personal" class="evb-scope-btn flex flex-col items-center gap-1.5 p-3 rounded-xl border-2 border-gray-200 dark:border-slate-700 hover:border-brand-500 is-selected">
                        <i class="fa-solid fa-user text-brand-500 text-lg"></i>
                        <span class="text-xs font-bold">Personal</span>
                    </button>
                    <button type="button" data-scope="global" class="evb-scope-btn flex flex-col items-center gap-1.5 p-3 rounded-xl border-2 border-gray-200 dark:border-slate-700 hover:border-brand-500">
                        <i class="fa-solid fa-globe text-brand-500 text-lg"></i>
                        <span class="text-xs font-bold">Clinic-wide</span>
                    </button>
                    <button type="button" data-scope="custom" class="evb-scope-btn flex flex-col items-center gap-1.5 p-3 rounded-xl border-2 border-gray-200 dark:border-slate-700 hover:border-brand-500">
                        <i class="fa-solid fa-users text-brand-500 text-lg"></i>
                        <span class="text-xs font-bold">Custom</span>
                    </button>
                </div>
            </div>
            <div id="evbStaffPicker" class="hidden p-3 rounded-xl bg-gray-50 dark:bg-slate-800/60 border border-gray-200 dark:border-slate-700">
                <p class="text-sm text-gray-500 dark:text-gray-400 mb-3"><span class="font-bold text-gray-900 dark:text-white">{{ $user->name }}</span> is always included.</p>
                <div class="relative mb-3">
                    <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-400 text-sm pointer-events-none"></i>
                    <input type="text" id="evbStaffSearch" oninput="filterEvbStaff()" placeholder="Search staff…" class="input pl-10 text-sm py-2">
                </div>
                <div id="evbStaffPills" class="space-y-3">
                    @foreach($roleOrder as $roleKey)
                        @if(isset($groupedStaff[$roleKey]) && $groupedStaff[$roleKey]->count() > 0)
                        <div class="evb-role-group" data-role-group="{{ $roleKey }}">
                            <p class="text-[10px] font-bold uppercase tracking-wider text-gray-400 dark:text-gray-500 mb-1.5">
                                {{ $roleLabels[$roleKey] }}
                                <span class="text-gray-400 dark:text-gray-500 font-normal normal-case">({{ $groupedStaff[$roleKey]->count() }})</span>
                            </p>
                            <div class="flex flex-wrap gap-1.5">
                                @foreach($groupedStaff[$roleKey] as $st)
                                <button type="button" data-staff-id="{{ $st->id }}" data-staff-name="{{ $st->name }}" data-role="{{ $st->role }}" class="staff-pill evb-staff-pill {{ $st->id === $user->id ? 'is-base' : '' }}" onclick="onEvbStaffClick(this)">
                                    <span>{{ $st->name }}</span>
                                    <span class="pill-x"><i class="fa-solid fa-xmark"></i></span>
                                </button>
                                @endforeach
                            </div>
                        </div>
                        @endif
                    @endforeach
                </div>
                <p id="evbStaffEmpty" class="hidden text-xs text-gray-400 dark:text-gray-500 italic py-2 text-center">No matches.</p>
            </div>
            @endif
        </div>
        <div class="border-t border-gray-100 dark:border-slate-700 px-5 py-4 flex items-center justify-between gap-3 flex-shrink-0">
            <span id="evbSummary" class="text-sm text-gray-500 dark:text-gray-400">No dates staged.</span>
            <div class="flex gap-2">
                <button type="button" onclick="closeEventBulk()" class="btn-secondary">Cancel</button>
                <button type="button" id="evbSubmitBtn" onclick="evbSubmit()" disabled class="btn-primary disabled:opacity-50 disabled:cursor-not-allowed"><i class="fa-solid fa-check"></i> Save</button>
            </div>
        </div>
    </div>
</div>
@endif

{{-- Hidden delete forms (per-shift + per-event) — JS submits the matching one --}}
<div class="hidden">
    @foreach($shifts as $s)
    <form id="delShift{{ $s->id }}" method="POST" action="{{ route('staff.shifts.destroy', $s) }}">@csrf @method('DELETE')</form>
    @endforeach
    @foreach($events as $e)
    @php $canDelE = $canManage || $e->created_by === $me->id || ($e->user_id && $e->user_id === $me->id); @endphp
    @if($canDelE)
    <form id="delEvent{{ $e->id }}" method="POST" action="{{ route('staff.events.destroy', $e) }}">@csrf @method('DELETE')</form>
    @endif
    @endforeach
</div>

@push('scripts')
<script>
const shiftMap = {!! json_encode($shiftsJson) !!};
const eventMap = {!! json_encode($eventsJson) !!};
const baseUserId = {{ $user->id }};
const baseUserName = {!! json_encode($user->name) !!};

function deleteShift(id, label) {
    if (!confirm('Remove ' + label + ' shift?')) return;
    const f = document.getElementById('delShift' + id);
    if (f) f.submit();
}
function deleteEvent(id, title) {
    if (!confirm('Remove event "' + title + '"?')) return;
    const f = document.getElementById('delEvent' + id);
    if (f) f.submit();
}

let pickedDate = null;
let lastDetailDate = null;

function fmtDate(dStr) {
    return new Date(dStr + 'T00:00:00').toLocaleDateString('en-US', { weekday: 'long', month: 'short', day: 'numeric' });
}

function onDayClick(date, cell, isPast) {
    if (window.matchMedia('(max-width: 767px)').matches) {
        document.querySelectorAll('[data-day-cell].is-selected-day').forEach(el => el.classList.remove('is-selected-day'));
        cell.classList.add('is-selected-day');
        showDayDetail(date);
    } else {
        @if($canEditCalendar)
        if (isPast) return;
        openEventModalForDate(date);
        @endif
    }
}
function openEventModalForDate(date) {
    @if($canEditCalendar)
    document.getElementById('eventFormDate').value = date;
    document.getElementById('eventFormDateLabel').textContent = fmtDate(date);
    document.getElementById('eventModal').classList.remove('hidden');
    document.body.style.overflow = 'hidden';
    @endif
}

function showDayDetail(date) {
    lastDetailDate = date;
    const panel = document.getElementById('dayDetail');
    const label = document.getElementById('dayDetailLabel');
    const content = document.getElementById('dayDetailContent');
    label.textContent = fmtDate(date);
    content.innerHTML = '';

    const shift = shiftMap[date];
    const dayEvents = eventMap[date] || [];

    if (!shift && dayEvents.length === 0) {
        content.innerHTML = '<p class="text-sm text-gray-400 dark:text-gray-500 italic py-2">Nothing scheduled.</p>';
    } else {
        if (shift) {
            const canDel = {{ $canManage ? 'true' : 'false' }};
            const delBtn = canDel ? `<button type="button" onclick="deleteShift(${shift.id}, '${shift.label}')" class="icon-btn-sm icon-btn-rose flex-shrink-0" title="Remove"><i class="fa-solid fa-trash"></i></button>` : '';
            content.insertAdjacentHTML('beforeend',
                '<div class="flex items-center gap-3 p-3 rounded-xl bg-white dark:bg-slate-900 border border-gray-200 dark:border-slate-700">' +
                '<span class="w-2.5 h-2.5 rounded-full bg-' + shift.color + '-500 flex-shrink-0"></span>' +
                '<div class="flex-1 min-w-0">' +
                '<p class="font-bold text-sm text-gray-900 dark:text-white">' + shift.label + ' shift</p>' +
                '<p class="text-xs text-gray-500 dark:text-gray-400">' + shift.start + ' – ' + shift.end + '</p>' +
                '</div>' +
                delBtn +
                '</div>'
            );
        }
        dayEvents.forEach(ev => {
            const globalBadge = ev.global ? '<span class="text-[10px] font-bold uppercase tracking-wider text-gray-400 ml-1">Clinic-wide</span>' : '';
            const desc = ev.description ? '<p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">' + ev.description + '</p>' : '';
            const delBtn = ev.canDelete ? `<button type="button" onclick="deleteEvent(${ev.id}, ${JSON.stringify(ev.title)})" class="icon-btn-sm icon-btn-rose flex-shrink-0" title="Remove"><i class="fa-solid fa-trash"></i></button>` : '';
            content.insertAdjacentHTML('beforeend',
                '<div class="flex items-start gap-3 p-3 rounded-xl bg-white dark:bg-slate-900 border border-gray-200 dark:border-slate-700">' +
                '<span class="w-2.5 h-2.5 rounded-full ' + ev.dot + ' flex-shrink-0 mt-1.5"></span>' +
                '<div class="flex-1 min-w-0">' +
                '<p class="font-bold text-sm text-gray-900 dark:text-white">' + ev.title + globalBadge + '</p>' +
                desc +
                '</div>' +
                delBtn +
                '</div>'
            );
        });
    }
    panel.classList.remove('hidden');
    panel.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
}

function openAddPickerFromDetail() { if (lastDetailDate) openAddPicker(lastDetailDate); }

function toggleMonthPicker() {
    document.getElementById('monthPicker').classList.toggle('hidden');
}
document.addEventListener('click', e => {
    const trigger = document.getElementById('monthPickerTrigger');
    const picker = document.getElementById('monthPicker');
    if (trigger && picker && !trigger.contains(e.target) && !picker.contains(e.target)) {
        picker.classList.add('hidden');
    }
});

@if($canEditCalendar)
function closeEventModal() {
    document.getElementById('eventModal').classList.add('hidden');
    document.body.style.overflow = '';
}
function setEventScope(scope) {
    const picker = document.getElementById('eventCustomPicker');
    if (!picker) return;
    if (scope === 'custom') picker.classList.remove('hidden');
    else picker.classList.add('hidden');
}
document.querySelectorAll('.event-custom-cb').forEach(cb => {
    cb.addEventListener('change', () => {
        const pill = cb.closest('.staff-pill');
        if (pill) pill.classList.toggle('is-selected', cb.checked);
    });
});
@endif

@if($canManage)

let bulkYear = {{ $year }};
let bulkMonth = {{ $month }};
let bulkActiveShift = null;
let bulkStaged = {};
let bulkBaseUserId = baseUserId;
let bulkBaseUserName = baseUserName;

function openBulkAssign(overrideUserId, overrideUserName) {
    if (overrideUserId) {
        bulkBaseUserId = overrideUserId;
        bulkBaseUserName = overrideUserName || '';
        const lbl = document.getElementById('bulkBaseStaffName');
        if (lbl) lbl.textContent = bulkBaseUserName;
    } else {
        bulkBaseUserId = baseUserId;
        bulkBaseUserName = baseUserName;
    }
    document.querySelectorAll('.bulk-staff-pill').forEach(p => {
        p.classList.remove('is-selected', 'is-base');
        if (parseInt(p.dataset.staffId) === bulkBaseUserId) {
            p.classList.add('is-base');
            p.style.display = 'none';
        } else {
            p.style.display = '';
        }
    });
    document.getElementById('bulkModal').classList.remove('hidden');
    document.body.style.overflow = 'hidden';
    bulkStaged = {};
    document.querySelectorAll('.bulk-st-btn').forEach(b => b.classList.remove('is-selected'));
    bulkActiveShift = null;
    const today = new Date();
    bulkYear = today.getFullYear();
    bulkMonth = today.getMonth() + 1;
    renderBulkGrid();
    refreshBulkStaged();
}
function closeBulkAssign() {
    document.getElementById('bulkModal').classList.add('hidden');
    document.body.style.overflow = '';
}
function isPastMonth(y, m) {
    const today = new Date();
    const ty = today.getFullYear();
    const tm = today.getMonth() + 1;
    return y < ty || (y === ty && m < tm);
}
function bulkPrevMonth() {
    let ny = bulkYear, nm = bulkMonth - 1;
    if (nm < 1) { nm = 12; ny--; }
    if (isPastMonth(ny, nm)) return;
    bulkYear = ny; bulkMonth = nm;
    renderBulkGrid();
}
function bulkNextMonth() { bulkMonth++; if (bulkMonth > 12) { bulkMonth = 1; bulkYear++; } renderBulkGrid(); }

function renderBulkGrid() {
    document.getElementById('bulkMonthLabel').textContent = new Date(bulkYear, bulkMonth - 1, 1).toLocaleDateString('en-US', { month: 'long', year: 'numeric' });
    const prevBtn = document.querySelector('button[onclick="bulkPrevMonth()"]');
    if (prevBtn) {
        let ny = bulkYear, nm = bulkMonth - 1;
        if (nm < 1) { nm = 12; ny--; }
        const past = isPastMonth(ny, nm);
        prevBtn.disabled = past;
        prevBtn.style.opacity = past ? '.35' : '';
        prevBtn.style.cursor = past ? 'not-allowed' : '';
    }
    const grid = document.getElementById('bulkGrid');
    grid.innerHTML = '';
    const first = new Date(bulkYear, bulkMonth - 1, 1);
    const startWeekday = first.getDay();
    const start = new Date(first);
    start.setDate(1 - startWeekday);
    const last = new Date(bulkYear, bulkMonth, 0);
    const endWeekday = last.getDay();
    const end = new Date(last);
    end.setDate(last.getDate() + (6 - endWeekday));
    const today = new Date();
    today.setHours(0,0,0,0);
    const todayStr = today.toISOString().slice(0, 10);
    let cursor = new Date(start);
    while (cursor <= end) {
        const y = cursor.getFullYear();
        const m = String(cursor.getMonth() + 1).padStart(2, '0');
        const d = String(cursor.getDate()).padStart(2, '0');
        const dStr = `${y}-${m}-${d}`;
        const inMonth = cursor.getMonth() + 1 === bulkMonth;
        const cellDate = new Date(cursor);
        cellDate.setHours(0,0,0,0);
        const isPast = cellDate < today;
        const isToday = dStr === todayStr;
        const staged = bulkStaged[dStr];
        let cls = 'bulk-cell';
        if (!inMonth) cls += ' is-other-month';
        if (isToday) cls += ' is-today';
        if (isPast) cls += ' is-past';
        if (staged) cls += ' is-selected';
        const letter = staged ? staged.letter : '';
        const click = (isPast || !inMonth) ? '' : `onclick="onBulkCellClick('${dStr}')"`;
        grid.insertAdjacentHTML('beforeend',
            `<div class="${cls}" data-date="${dStr}" data-shift-letter="${letter}" ${click}>${cursor.getDate()}</div>`
        );
        cursor.setDate(cursor.getDate() + 1);
    }
}

document.querySelectorAll('.bulk-st-btn').forEach(btn => {
    btn.addEventListener('click', () => {
        document.querySelectorAll('.bulk-st-btn').forEach(b => b.classList.remove('is-selected'));
        btn.classList.add('is-selected');
        const isCustom = btn.dataset.st === 'custom';
        const customWrap = document.getElementById('bulkCustomTimes');
        if (customWrap) customWrap.classList.toggle('hidden', !isCustom);
        bulkActiveShift = {
            st: btn.dataset.st,
            letter: btn.dataset.letter,
            start: isCustom ? (document.getElementById('bulkCustomStart').value || '08:00') : btn.dataset.start,
            end: isCustom ? (document.getElementById('bulkCustomEnd').value || '17:00') : btn.dataset.end,
            label: btn.querySelector('span').textContent,
        };
    });
});
function bulkUpdateCustomTimes() {
    if (!bulkActiveShift || bulkActiveShift.st !== 'custom') return;
    bulkActiveShift.start = document.getElementById('bulkCustomStart').value || '08:00';
    bulkActiveShift.end = document.getElementById('bulkCustomEnd').value || '17:00';
}
function filterBulkStaff() {
    const q = (document.getElementById('bulkStaffSearch').value || '').toLowerCase().trim();
    let totalShown = 0;
    document.querySelectorAll('.bulk-role-group').forEach(grp => {
        let groupShown = 0;
        grp.querySelectorAll('.bulk-staff-pill').forEach(pill => {
            if (pill.classList.contains('is-base')) { pill.style.display = 'none'; return; }
            const name = (pill.dataset.staffName || '').toLowerCase();
            const role = (pill.dataset.role || '').toLowerCase();
            const match = !q || name.includes(q) || role.includes(q);
            pill.style.display = match ? '' : 'none';
            if (match) groupShown++;
        });
        grp.style.display = groupShown > 0 ? '' : 'none';
        totalShown += groupShown;
    });
    document.getElementById('bulkStaffEmpty').classList.toggle('hidden', totalShown > 0);
}

function onBulkCellClick(dStr) {
    if (!bulkActiveShift) {
        alert('Pick a shift type first.');
        return;
    }
    if (bulkStaged[dStr] && bulkStaged[dStr].st === bulkActiveShift.st) {
        delete bulkStaged[dStr];
    } else {
        bulkStaged[dStr] = { ...bulkActiveShift };
    }
    renderBulkGrid();
    refreshBulkStaged();
}
function bulkClearStaged() {
    bulkStaged = {};
    renderBulkGrid();
    refreshBulkStaged();
}
function refreshBulkStaged() {
    const entries = Object.entries(bulkStaged).sort((a,b) => a[0].localeCompare(b[0]));
    const count = entries.length;
    const wrap = document.getElementById('bulkStagedWrap');
    const list = document.getElementById('bulkStagedList');
    const summary = document.getElementById('bulkSummary');
    const btn = document.getElementById('bulkSubmitBtn');
    document.getElementById('bulkStagedCount').textContent = count > 0 ? `(${count})` : '';
    if (count === 0) {
        wrap.classList.add('hidden');
        summary.textContent = 'No shifts staged.';
        btn.disabled = true;
        return;
    }
    wrap.classList.remove('hidden');
    list.innerHTML = entries.map(([dStr, s]) =>
        `<div class="flex items-center justify-between gap-2 px-3 py-2 rounded-lg bg-white dark:bg-slate-900 text-sm">
            <div class="flex-1 min-w-0">
                <p class="font-semibold text-gray-800 dark:text-gray-100 truncate">${fmtDate(dStr)}</p>
                <p class="text-xs text-gray-500 dark:text-gray-400">${s.label} · ${s.start}–${s.end}</p>
            </div>
            <button type="button" onclick="delete bulkStaged['${dStr}']; renderBulkGrid(); refreshBulkStaged();" class="icon-btn-sm icon-btn-rose" title="Remove">
                <i class="fa-solid fa-trash"></i>
            </button>
        </div>`
    ).join('');
    const extras = document.querySelectorAll('.bulk-staff-pill.is-selected').length;
    const staffTotal = 1 + extras;
    summary.textContent = `${count} shift${count===1?'':'s'} · ${staffTotal} staff member${staffTotal===1?'':'s'}`;
    btn.disabled = false;
}
function onBulkStaffPillClick(pill) {
    if (pill.classList.contains('is-base')) return;
    pill.classList.toggle('is-selected');
    refreshBulkStaged();
}
function bulkToggleStaffPicker() {
    const p = document.getElementById('bulkStaffPicker');
    p.classList.toggle('hidden');
    document.getElementById('bulkStaffChevron').classList.toggle('fa-chevron-right');
    document.getElementById('bulkStaffChevron').classList.toggle('fa-chevron-down');
}

async function bulkSubmit() {
    const entries = Object.entries(bulkStaged).map(([dStr, s]) => ({
        shift_date: dStr,
        shift_type: s.st,
        start_time: s.start,
        end_time: s.end,
    }));
    if (entries.length === 0) return;
    const extraIds = Array.from(document.querySelectorAll('.bulk-staff-pill.is-selected')).map(p => parseInt(p.dataset.staffId));
    const userIds = [bulkBaseUserId, ...extraIds];

    const summaryLines = [
        `${entries.length} shift${entries.length===1?'':'s'}`,
        `${userIds.length} staff member${userIds.length===1?'':'s'} (including ${bulkBaseUserName})`,
    ];
    if (!confirm('Save these shifts?\n\n' + summaryLines.join('\n'))) return;

    const btn = document.getElementById('bulkSubmitBtn');
    btn.disabled = true;
    btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Saving…';
    try {
        const r = await fetch('{{ route("staff.shifts.bulk") }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                'Accept': 'application/json',
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ entries, user_ids: userIds }),
        });
        if (!r.ok) throw new Error('HTTP ' + r.status);
        location.reload();
    } catch (err) {
        btn.disabled = false;
        btn.innerHTML = '<i class="fa-solid fa-check"></i> Save';
        alert('Save failed: ' + err.message);
    }
}

if (location.hash === '#bulk') {
    setTimeout(() => {
        openBulkAssign();
        const params = new URLSearchParams(location.search);
        const extras = (params.get('bulk_extras') || '').split(',').map(s => parseInt(s.trim())).filter(Boolean);
        if (extras.length) {
            document.getElementById('bulkStaffPicker').classList.remove('hidden');
            const chev = document.getElementById('bulkStaffChevron');
            if (chev) { chev.classList.remove('fa-chevron-right'); chev.classList.add('fa-chevron-down'); }
            extras.forEach(id => {
                const pill = document.querySelector('.bulk-staff-pill[data-staff-id="' + id + '"]');
                if (pill && !pill.classList.contains('is-base')) pill.classList.add('is-selected');
            });
            refreshBulkStaged();
        }
    }, 30);
    history.replaceState(null, '', location.pathname);
}
@endif

@if($canEditCalendar)
let evbYear = new Date().getFullYear();
let evbMonth = new Date().getMonth() + 1;
let evbStaged = new Set();
let evbScope = 'personal';

function openEventBulk() {
    document.getElementById('eventBulkModal').classList.remove('hidden');
    document.body.style.overflow = 'hidden';
    document.getElementById('evbTitle').value = '';
    document.getElementById('evbDescription').value = '';
    document.getElementById('evbColor').value = 'info';
    evbStaged = new Set();
    evbScope = 'personal';
    document.querySelectorAll('.evb-scope-btn').forEach(b => b.classList.toggle('is-selected', b.dataset.scope === 'personal'));
    const picker = document.getElementById('evbStaffPicker');
    if (picker) picker.classList.add('hidden');
    document.querySelectorAll('.evb-staff-pill').forEach(p => {
        p.classList.remove('is-selected');
        if (p.classList.contains('is-base')) p.style.display = 'none';
        else p.style.display = '';
    });
    const today = new Date();
    evbYear = today.getFullYear();
    evbMonth = today.getMonth() + 1;
    renderEvbGrid();
    refreshEvbStaged();
}
function closeEventBulk() {
    document.getElementById('eventBulkModal').classList.add('hidden');
    document.body.style.overflow = '';
}
function evbPrevMonth() {
    let ny = evbYear, nm = evbMonth - 1;
    if (nm < 1) { nm = 12; ny--; }
    if (isPastMonth(ny, nm)) return;
    evbYear = ny; evbMonth = nm;
    renderEvbGrid();
}
function evbNextMonth() { evbMonth++; if (evbMonth > 12) { evbMonth = 1; evbYear++; } renderEvbGrid(); }
function renderEvbGrid() {
    document.getElementById('evbMonthLabel').textContent = new Date(evbYear, evbMonth - 1, 1).toLocaleDateString('en-US', { month: 'long', year: 'numeric' });
    const grid = document.getElementById('evbGrid');
    grid.innerHTML = '';
    const first = new Date(evbYear, evbMonth - 1, 1);
    const startWeekday = first.getDay();
    const start = new Date(first);
    start.setDate(1 - startWeekday);
    const last = new Date(evbYear, evbMonth, 0);
    const endWeekday = last.getDay();
    const end = new Date(last);
    end.setDate(last.getDate() + (6 - endWeekday));
    const today = new Date();
    today.setHours(0,0,0,0);
    const todayStr = today.toISOString().slice(0, 10);
    let cursor = new Date(start);
    while (cursor <= end) {
        const y = cursor.getFullYear();
        const m = String(cursor.getMonth() + 1).padStart(2, '0');
        const d = String(cursor.getDate()).padStart(2, '0');
        const dStr = `${y}-${m}-${d}`;
        const inMonth = cursor.getMonth() + 1 === evbMonth;
        const cellDate = new Date(cursor);
        cellDate.setHours(0,0,0,0);
        const isPast = cellDate < today;
        const isToday = dStr === todayStr;
        const staged = evbStaged.has(dStr);
        let cls = 'bulk-cell';
        if (!inMonth) cls += ' is-other-month';
        if (isToday) cls += ' is-today';
        if (isPast) cls += ' is-past';
        if (staged) cls += ' is-selected';
        const click = (isPast || !inMonth) ? '' : `onclick="onEvbCellClick('${dStr}')"`;
        grid.insertAdjacentHTML('beforeend',
            `<div class="${cls}" data-date="${dStr}" ${click}>${cursor.getDate()}</div>`
        );
        cursor.setDate(cursor.getDate() + 1);
    }
}
function onEvbCellClick(dStr) {
    if (evbStaged.has(dStr)) evbStaged.delete(dStr);
    else evbStaged.add(dStr);
    renderEvbGrid();
    refreshEvbStaged();
}
function evbClearStaged() {
    evbStaged.clear();
    renderEvbGrid();
    refreshEvbStaged();
}
function refreshEvbStaged() {
    const arr = Array.from(evbStaged).sort();
    const count = arr.length;
    const wrap = document.getElementById('evbStagedWrap');
    const list = document.getElementById('evbStagedList');
    const sum = document.getElementById('evbSummary');
    const btn = document.getElementById('evbSubmitBtn');
    document.getElementById('evbStagedCount').textContent = count > 0 ? `(${count})` : '';
    if (count === 0) {
        wrap.classList.add('hidden');
        sum.textContent = 'No dates staged.';
        btn.disabled = true;
        return;
    }
    wrap.classList.remove('hidden');
    list.innerHTML = arr.map(d =>
        `<span class="inline-flex items-center gap-1.5 px-2.5 py-1.5 rounded-lg bg-pink-100 dark:bg-pink-900/35 text-pink-700 dark:text-pink-200 text-xs font-semibold">
            ${fmtDate(d)}
            <button type="button" onclick="evbStaged.delete('${d}'); renderEvbGrid(); refreshEvbStaged();" class="hover:text-rose-600" title="Remove"><i class="fa-solid fa-xmark"></i></button>
        </span>`
    ).join('');
    let staffCount = 1;
    @if($canManage)
    if (evbScope === 'global') staffCount = 0;
    else if (evbScope === 'custom') staffCount = 1 + document.querySelectorAll('.evb-staff-pill.is-selected').length;
    @endif
    sum.textContent = staffCount === 0
        ? `${count} clinic-wide date${count===1?'':'s'}`
        : `${count} date${count===1?'':'s'} · ${staffCount} staff member${staffCount===1?'':'s'}`;
    btn.disabled = false;
}

@if($canManage)
document.querySelectorAll('.evb-scope-btn').forEach(btn => {
    btn.addEventListener('click', () => {
        document.querySelectorAll('.evb-scope-btn').forEach(b => b.classList.remove('is-selected'));
        btn.classList.add('is-selected');
        evbScope = btn.dataset.scope;
        document.getElementById('evbStaffPicker').classList.toggle('hidden', evbScope !== 'custom');
        refreshEvbStaged();
    });
});
function onEvbStaffClick(pill) {
    if (pill.classList.contains('is-base')) return;
    pill.classList.toggle('is-selected');
    refreshEvbStaged();
}
function filterEvbStaff() {
    const q = (document.getElementById('evbStaffSearch').value || '').toLowerCase().trim();
    let total = 0;
    document.querySelectorAll('.evb-role-group').forEach(grp => {
        let shown = 0;
        grp.querySelectorAll('.evb-staff-pill').forEach(pill => {
            if (pill.classList.contains('is-base')) { pill.style.display = 'none'; return; }
            const name = (pill.dataset.staffName || '').toLowerCase();
            const match = !q || name.includes(q);
            pill.style.display = match ? '' : 'none';
            if (match) shown++;
        });
        grp.style.display = shown > 0 ? '' : 'none';
        total += shown;
    });
    document.getElementById('evbStaffEmpty').classList.toggle('hidden', total > 0);
}
@endif

async function evbSubmit() {
    const title = (document.getElementById('evbTitle').value || '').trim();
    if (!title) { alert('Title is required.'); return; }
    const dates = Array.from(evbStaged);
    if (dates.length === 0) return;
    const description = (document.getElementById('evbDescription').value || '').trim();
    const color = document.getElementById('evbColor').value;
    const userIds = Array.from(document.querySelectorAll('.evb-staff-pill.is-selected')).map(p => parseInt(p.dataset.staffId));

    const btn = document.getElementById('evbSubmitBtn');
    btn.disabled = true;
    btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Saving…';
    try {
        const r = await fetch(`/staff/${baseUserId}/events/bulk`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                'Accept': 'application/json',
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                title, description: description || null, color,
                scope: evbScope, dates, user_ids: userIds,
            }),
        });
        if (!r.ok) throw new Error('HTTP ' + r.status);
        location.reload();
    } catch (err) {
        btn.disabled = false;
        btn.innerHTML = '<i class="fa-solid fa-check"></i> Save';
        alert('Save failed: ' + err.message);
    }
}
@endif

document.addEventListener('keydown', e => {
    if (e.key === 'Escape') {
        @if($canEditCalendar)
        closeEventModal(); closeEventBulk();
        @endif
        @if($canManage)
        closeBulkAssign();
        @endif
    }
});
</script>
<style>
.bulk-st-btn.is-selected, .evb-scope-btn.is-selected { border-color: #0d9488 !important; background: linear-gradient(135deg,#f0fdfa,#ccfbf1); color: #0d9488; }
.dark .bulk-st-btn.is-selected, .dark .evb-scope-btn.is-selected { background: linear-gradient(135deg,rgba(20,184,166,.18),rgba(13,148,136,.28)); color: #5eead4; border-color: #14b8a6 !important; }
</style>
@endpush
@endsection
