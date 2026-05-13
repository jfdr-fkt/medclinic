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

    // Build calendar grid for current month
    $shiftsByDate = $shifts->keyBy(fn($s) => \Carbon\Carbon::parse($s->shift_date)->toDateString());
    $shiftTypeColors = [
        'morning'   => 'bg-amber-100 text-amber-700 border-amber-300',
        'afternoon' => 'bg-orange-100 text-orange-700 border-orange-300',
        'night'     => 'bg-indigo-100 text-indigo-700 border-indigo-300',
        'on_call'   => 'bg-purple-100 text-purple-700 border-purple-300',
    ];
@endphp

<div class="space-y-5 max-w-5xl">

    <!-- Back link -->
    <div class="flex items-center gap-3">
        <a href="{{ route('staff.index') }}" class="w-9 h-9 flex items-center justify-center rounded-xl text-gray-400 hover:text-gray-700 hover:bg-gray-100">
            <i class="fa-solid fa-arrow-left"></i>
        </a>
        <span class="text-sm text-gray-400">Back to Staff Directory</span>
    </div>

    <!-- Profile header -->
    <div class="card overflow-hidden">
        <div class="h-28 bg-gradient-to-r {{ $cfg['grad'] }}"></div>
        <div class="px-6 pb-5 -mt-14">
            <div class="flex items-end gap-4 flex-wrap">
                <div class="h-24 w-24 rounded-2xl bg-gradient-to-br {{ $cfg['grad'] }} flex items-center justify-center text-white text-3xl font-bold ring-4 ring-white shadow-lg">
                    {{ strtoupper(substr($user->name, 0, 2)) }}
                </div>
                <div class="flex-1 min-w-0 pb-1">
                    <h2 class="text-2xl font-bold text-gray-900">{{ $user->name }}</h2>
                    <div class="flex items-center gap-2 mt-1 flex-wrap">
                        <span class="inline-flex items-center gap-1.5 {{ $cfg['bg'] }} {{ $cfg['text'] }} px-2.5 py-1 rounded-full text-xs font-semibold">
                            <i class="fa-solid {{ $cfg['icon'] }} text-[10px]"></i> {{ $user->roleLabel() }}
                        </span>
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium {{ $user->isOnline() ? 'bg-emerald-100 text-emerald-700' : 'bg-gray-100 text-gray-500' }}">
                            <span class="w-1.5 h-1.5 rounded-full {{ $user->isOnline() ? 'bg-emerald-400 animate-pulse' : 'bg-gray-300' }}"></span>
                            {{ $user->isOnline() ? 'Online' : 'Offline' }}
                        </span>
                        @if($user->specialization)
                        <span class="text-sm text-gray-500">&bull; {{ $user->specialization }}</span>
                        @endif
                    </div>
                </div>
                <div class="flex gap-2 pb-1">
                    <a href="{{ route('chat.index', ['with' => $user->id]) }}"
                       class="inline-flex items-center gap-2 px-4 py-2 bg-purple-100 text-purple-700 hover:bg-purple-200 rounded-xl text-sm font-semibold transition-colors">
                        <i class="fa-solid fa-comment"></i> Message
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Contact info + bio -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
        <div class="card p-5">
            <h3 class="font-bold text-gray-800 text-sm mb-3 flex items-center gap-2">
                <i class="fa-solid fa-address-card text-brand-500"></i> Contact
            </h3>
            <div class="space-y-2.5 text-sm">
                <div class="flex items-center gap-2">
                    <i class="fa-solid fa-envelope w-4 text-gray-400"></i>
                    <span class="text-gray-700 truncate">{{ $user->email }}</span>
                </div>
                <div class="flex items-center gap-2">
                    <i class="fa-solid fa-phone w-4 text-gray-400"></i>
                    <span class="text-gray-700">{{ $user->phone ?? 'No phone' }}</span>
                </div>
                @if($user->last_seen_at)
                <div class="flex items-center gap-2">
                    <i class="fa-solid fa-clock w-4 text-gray-400"></i>
                    <span class="text-xs text-gray-500">Last seen {{ $user->last_seen_at->diffForHumans() }}</span>
                </div>
                @endif
            </div>
        </div>

        <div class="card p-5 md:col-span-2">
            <h3 class="font-bold text-gray-800 text-sm mb-3 flex items-center gap-2">
                <i class="fa-solid fa-circle-info text-brand-500"></i> About
            </h3>
            <p class="text-sm text-gray-700 leading-relaxed">{{ $user->bio ?? 'No bio provided.' }}</p>
            <p class="text-xs text-gray-400 mt-3">Joined {{ $user->created_at?->format('F j, Y') }}</p>
        </div>
    </div>

    <!-- Monthly shift schedule -->
    <div class="card p-5">
        <div class="flex items-center justify-between mb-4 flex-wrap gap-2">
            <h3 class="font-bold text-gray-800 text-sm flex items-center gap-2">
                <i class="fa-solid fa-calendar-days text-brand-500"></i> {{ $monthStart->format('F Y') }} Schedule
                <span class="text-xs text-gray-500 font-normal">({{ $shifts->count() }} shifts)</span>
            </h3>
            <div class="flex flex-wrap gap-1.5 text-xs">
                <span class="inline-flex items-center gap-1 bg-amber-100 text-amber-700 px-2 py-0.5 rounded-full">Day</span>
                <span class="inline-flex items-center gap-1 bg-orange-100 text-orange-700 px-2 py-0.5 rounded-full">Evening</span>
                <span class="inline-flex items-center gap-1 bg-indigo-100 text-indigo-700 px-2 py-0.5 rounded-full">Night</span>
                <span class="inline-flex items-center gap-1 bg-purple-100 text-purple-700 px-2 py-0.5 rounded-full">On Call</span>
            </div>
        </div>

        <!-- Calendar grid -->
        <div class="grid grid-cols-7 gap-1.5 mb-1">
            @foreach(['Sun','Mon','Tue','Wed','Thu','Fri','Sat'] as $dow)
            <div class="text-[10px] font-bold text-gray-400 uppercase tracking-wider text-center py-1">{{ $dow }}</div>
            @endforeach
        </div>
        <div class="grid grid-cols-7 gap-1.5">
            @php
                $cursor = $monthStart->copy()->startOfWeek(\Carbon\Carbon::SUNDAY);
                $end    = $monthEnd->copy()->endOfWeek(\Carbon\Carbon::SATURDAY);
            @endphp
            @while($cursor <= $end)
                @php
                    $isCurrentMonth = $cursor->month === $monthStart->month;
                    $isToday = $cursor->isToday();
                    $shift = $shiftsByDate->get($cursor->toDateString());
                    $shiftClass = $shift ? ($shiftTypeColors[$shift->shift_type] ?? '') : '';
                @endphp
                <div class="aspect-square min-h-[60px] rounded-lg border p-1.5 text-xs
                    {{ !$isCurrentMonth ? 'bg-gray-50/50 border-gray-100 text-gray-300' : ($isToday ? 'border-brand-500 ring-2 ring-brand-200 bg-brand-50/30' : 'border-gray-200 bg-white') }}">
                    <div class="font-bold {{ $isToday ? 'text-brand-700' : ($isCurrentMonth ? 'text-gray-700' : 'text-gray-300') }}">{{ $cursor->day }}</div>
                    @if($shift && $isCurrentMonth)
                    <div class="mt-0.5 px-1 py-0.5 rounded border text-[9px] font-bold capitalize text-center {{ $shiftClass }}">
                        {{ $shift->shift_type === 'on_call' ? 'On Call' : $shift->shift_type }}
                    </div>
                    <div class="text-[9px] text-gray-500 mt-0.5">{{ \Carbon\Carbon::parse($shift->start_time)->format('ga') }}–{{ \Carbon\Carbon::parse($shift->end_time)->format('ga') }}</div>
                    @endif
                </div>
                @php $cursor->addDay(); @endphp
            @endwhile
        </div>
    </div>

    <!-- Upcoming shifts beyond this month -->
    @if($upcoming->count() > 0)
    <div class="card p-5">
        <h3 class="font-bold text-gray-800 text-sm mb-3 flex items-center gap-2">
            <i class="fa-solid fa-forward text-brand-500"></i> Upcoming (Next Month)
        </h3>
        <div class="space-y-2">
            @foreach($upcoming as $s)
            <div class="flex items-center justify-between p-3 bg-gray-50 rounded-xl text-sm">
                <div>
                    <p class="font-medium text-gray-800">{{ \Carbon\Carbon::parse($s->shift_date)->format('l, F j') }}</p>
                    <p class="text-xs text-gray-500 capitalize">
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
