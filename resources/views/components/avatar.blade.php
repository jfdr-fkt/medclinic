@props([
    'user',
    'size' => 'md',          // sm (h-7), md (h-9), lg (h-10), xl (h-14)
    'gradient' => 'from-brand-400 to-brand-600',
    'rounded' => 'rounded-full',
    'showStatus' => false,
])
@php
    $sizes = [
        'sm' => ['box' => 'h-7 w-7',   'text' => 'text-xs'],
        'md' => ['box' => 'h-9 w-9',   'text' => 'text-xs'],
        'lg' => ['box' => 'h-10 w-10', 'text' => 'text-sm'],
        'xl' => ['box' => 'h-14 w-14', 'text' => 'text-lg'],
    ];
    $s = $sizes[$size] ?? $sizes['md'];
@endphp
<div class="relative flex-shrink-0">
    @if($user && $user->avatarUrl())
    <img src="{{ $user->avatarUrl() }}" alt="{{ $user->name }}"
         class="{{ $s['box'] }} {{ $rounded }} object-cover">
    @else
    <div class="{{ $s['box'] }} {{ $rounded }} bg-gradient-to-br {{ $gradient }} flex items-center justify-center text-white {{ $s['text'] }} font-bold">
        {{ $user ? strtoupper(substr($user->name, 0, 2)) : '??' }}
    </div>
    @endif
    @if($showStatus && $user)
    @php
        $cAv = $user->statusColor();
        $dotAv = match($cAv) {
            'emerald' => 'bg-emerald-400',
            'red' => 'bg-red-500',
            'amber' => 'bg-amber-400',
            default => 'bg-gray-300',
        };
    @endphp
    <span class="absolute -bottom-0.5 -right-0.5 h-3 w-3 rounded-full border-2 border-white {{ $dotAv }}" title="{{ $user->statusLabel() }}"></span>
    @endif
</div>
