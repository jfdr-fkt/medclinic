@extends('layouts.app')
@section('title', 'Staff Chat')
@section('page-title', 'Staff Chat')

@section('content')
<div class="flex h-[calc(100vh-8rem)] card overflow-hidden">

    <!-- ── User list sidebar grouped by role ── -->
    <div class="w-72 flex-shrink-0 border-r border-gray-100 flex flex-col">
        <div class="px-4 py-4 border-b border-gray-100 bg-gray-50/60">
            <h2 class="font-bold text-gray-800 text-sm">Staff Members</h2>
            <p class="text-xs text-gray-400 mt-0.5">Pick someone to chat with</p>
        </div>

        <div class="flex-1 overflow-y-auto py-2">
            @php
                $roleConfig = [
                    'admin'     => ['label'=>'Administrators','icon'=>'fa-user-shield','accent'=>'brand',  'bg'=>'bg-brand-50',  'border'=>'border-brand-200',  'text'=>'text-brand-700',  'gradient'=>'from-brand-400 to-brand-600'],
                    'doctor'    => ['label'=>'Doctors',       'icon'=>'fa-user-doctor','accent'=>'purple', 'bg'=>'bg-purple-50', 'border'=>'border-purple-200', 'text'=>'text-purple-700', 'gradient'=>'from-purple-400 to-purple-600'],
                    'nurse'     => ['label'=>'Nurses',        'icon'=>'fa-user-nurse', 'accent'=>'pink',   'bg'=>'bg-pink-50',   'border'=>'border-pink-200',   'text'=>'text-pink-700',   'gradient'=>'from-pink-400 to-pink-600'],
                    'assistant' => ['label'=>'Assistants',    'icon'=>'fa-user',       'accent'=>'amber',  'bg'=>'bg-amber-50',  'border'=>'border-amber-200',  'text'=>'text-amber-700',  'gradient'=>'from-amber-400 to-amber-600'],
                ];
            @endphp

            @foreach($roleConfig as $role => $cfg)
                @if(isset($users[$role]) && $users[$role]->count() > 0)
                <!-- Role section -->
                <div class="mb-3">
                    <div class="flex items-center gap-2 px-4 py-2 {{ $cfg['bg'] }} border-y {{ $cfg['border'] }}">
                        <i class="fa-solid {{ $cfg['icon'] }} {{ $cfg['text'] }} text-xs"></i>
                        <p class="text-xs font-bold {{ $cfg['text'] }} uppercase tracking-wider">{{ $cfg['label'] }}</p>
                        <span class="ml-auto text-xs {{ $cfg['text'] }} bg-white/60 px-1.5 rounded-md font-semibold">{{ $users[$role]->count() }}</span>
                    </div>
                    @foreach($users[$role] as $u)
                    <a href="{{ route('chat.index', ['with' => $u->id]) }}"
                       class="flex items-center gap-3 px-4 py-3 transition-colors border-b border-gray-50
                           {{ isset($withUser) && $withUser->id === $u->id
                                ? $cfg['bg'].' border-l-2 border-l-'.$cfg['accent'].'-500'
                                : 'hover:bg-gray-50' }}">
                        <div class="relative flex-shrink-0">
                            <div class="h-10 w-10 rounded-full bg-gradient-to-br {{ $cfg['gradient'] }} flex items-center justify-center text-white text-sm font-bold">
                                {{ strtoupper(substr($u->name, 0, 2)) }}
                            </div>
                            <span class="absolute -bottom-0.5 -right-0.5 h-3 w-3 rounded-full border-2 border-white
                                {{ $u->isOnline() ? 'bg-emerald-400' : 'bg-gray-300' }}"></span>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-semibold text-gray-800 truncate">{{ $u->name }}</p>
                            <p class="text-xs text-gray-400 truncate">{{ $u->specialization ?? ucfirst($u->role) }}</p>
                        </div>
                        @if(($unreadCounts[$u->id] ?? 0) > 0)
                        <span class="flex-shrink-0 bg-red-500 text-white text-xs font-bold rounded-full px-1.5 py-0.5 min-w-[20px] text-center leading-tight">
                            {{ $unreadCounts[$u->id] }}
                        </span>
                        @endif
                    </a>
                    @endforeach
                </div>
                @endif
            @endforeach

            @if($users->flatten()->count() === 0)
            <div class="text-center py-12 px-4">
                <i class="fa-solid fa-users text-gray-200 text-3xl mb-2"></i>
                <p class="text-sm text-gray-400">No other staff members yet</p>
            </div>
            @endif
        </div>
    </div>

    <!-- ── Chat area ── -->
    <div class="flex-1 flex flex-col overflow-hidden">
        @if(isset($withUser))

        @php
            $cfg = $roleConfig[$withUser->role] ?? $roleConfig['admin'];
        @endphp

        <!-- Chat header -->
        <div class="flex items-center gap-3 px-6 py-4 border-b border-gray-100 bg-gray-50/50 flex-shrink-0">
            <div class="relative">
                <div class="h-10 w-10 rounded-full bg-gradient-to-br {{ $cfg['gradient'] }} flex items-center justify-center text-white text-sm font-bold">
                    {{ strtoupper(substr($withUser->name, 0, 2)) }}
                </div>
                <span class="absolute -bottom-0.5 -right-0.5 h-3 w-3 rounded-full border-2 border-white {{ $withUser->isOnline() ? 'bg-emerald-400' : 'bg-gray-300' }}"></span>
            </div>
            <div>
                <p class="font-bold text-gray-900">{{ $withUser->name }}</p>
                <p class="text-xs {{ $withUser->isOnline() ? 'text-emerald-600' : 'text-gray-400' }} flex items-center gap-1">
                    <span class="w-1.5 h-1.5 rounded-full inline-block {{ $withUser->isOnline() ? 'bg-emerald-400' : 'bg-gray-300' }}"></span>
                    {{ $withUser->isOnline() ? 'Online now' : ($withUser->last_seen_at ? 'Last seen '.$withUser->last_seen_at->diffForHumans() : 'Offline') }}
                </p>
            </div>
            <div class="ml-auto">
                <span class="text-xs {{ $cfg['bg'] }} {{ $cfg['text'] }} px-2.5 py-1 rounded-full capitalize font-medium">{{ $withUser->role }}</span>
            </div>
        </div>

        <!-- Messages -->
        <div class="flex-1 overflow-y-auto px-6 py-5 space-y-4" id="messagesContainer">
            @forelse($messages as $msg)
            @php $isMine = $msg->sender_id === Auth::id(); @endphp
            <div class="flex {{ $isMine ? 'justify-end' : 'justify-start' }} items-end gap-2">
                @if(!$isMine)
                <div class="h-7 w-7 rounded-full bg-gradient-to-br {{ $cfg['gradient'] }} flex items-center justify-center text-white text-xs font-bold flex-shrink-0">
                    {{ strtoupper(substr($msg->sender->name, 0, 2)) }}
                </div>
                @endif
                <div class="max-w-xs lg:max-w-md">
                    <div class="px-4 py-2.5 rounded-2xl text-sm {{ $isMine ? 'bg-brand-600 text-white rounded-br-sm' : 'bg-gray-100 text-gray-800 rounded-bl-sm' }}">
                        {{ $msg->body }}
                    </div>
                    <p class="text-xs text-gray-400 mt-1 {{ $isMine ? 'text-right' : 'text-left' }}">
                        {{ $msg->created_at->format('g:i A') }}
                        @if($isMine && $msg->is_read)
                        <i class="fa-solid fa-check-double text-brand-400 ml-1"></i>
                        @endif
                    </p>
                </div>
            </div>
            @empty
            <div class="flex flex-col items-center justify-center h-full py-16 text-center">
                <div class="w-14 h-14 bg-brand-50 rounded-2xl flex items-center justify-center mb-3">
                    <i class="fa-solid fa-comment-dots text-brand-300 text-xl"></i>
                </div>
                <p class="text-gray-500 font-medium text-sm">No messages yet</p>
                <p class="text-gray-400 text-xs mt-1">Send the first message below</p>
            </div>
            @endforelse
            <div id="messagesEnd"></div>
        </div>

        <!-- Message input -->
        <div class="px-6 py-4 border-t border-gray-100 bg-white flex-shrink-0">
            <div class="flex items-center gap-3">
                <div class="flex-1 flex items-center gap-2 bg-gray-100 rounded-xl px-4 py-2.5 focus-within:ring-2 focus-within:ring-brand-500 focus-within:bg-white transition-all border border-transparent focus-within:border-brand-300">
                    <input type="text" id="messageInput" placeholder="Type a message…"
                           class="flex-1 bg-transparent text-sm text-gray-800 outline-none placeholder-gray-400"
                           onkeydown="if(event.key==='Enter'&&!event.shiftKey){sendMessage();event.preventDefault();}">
                </div>
                <button onclick="sendMessage()"
                        class="w-11 h-11 bg-brand-600 text-white rounded-xl hover:bg-brand-700 transition-colors flex items-center justify-center flex-shrink-0 shadow-sm">
                    <i class="fa-solid fa-paper-plane text-sm"></i>
                </button>
            </div>
            <p class="text-xs text-gray-400 mt-1.5 text-center">Press Enter to send &bull; Auto-refreshes every 3 seconds</p>
        </div>

        @else
        <!-- No conversation selected -->
        <div class="flex-1 flex flex-col items-center justify-center text-center p-8">
            <div class="w-20 h-20 bg-brand-50 rounded-3xl flex items-center justify-center mb-5">
                <i class="fa-solid fa-comments text-brand-300 text-3xl"></i>
            </div>
            <h3 class="text-lg font-bold text-gray-700">Select a conversation</h3>
            <p class="text-sm text-gray-400 mt-1.5 max-w-xs">Pick a staff member from the left panel — grouped by role — to start chatting</p>
        </div>
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script>
@if(isset($withUser))
const WITH_USER_ID = {{ $withUser->id }};
let lastMessageId  = {{ $messages->last()?->id ?? 0 }};

function scrollBottom() {
    const c = document.getElementById('messagesContainer');
    if (c) c.scrollTop = c.scrollHeight;
}
scrollBottom();

function sendMessage() {
    const input = document.getElementById('messageInput');
    const body  = input.value.trim();
    if (!body) return;
    input.value = '';

    fetch('{{ route("chat.send") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
        },
        body: JSON.stringify({ receiver_id: WITH_USER_ID, body }),
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            appendMessage(data.message, true);
            lastMessageId = data.message.id;
        }
    });
}

function appendMessage(msg, isMine) {
    const container = document.getElementById('messagesContainer');
    const div = document.createElement('div');
    div.className = `flex ${isMine ? 'justify-end' : 'justify-start'} items-end gap-2`;

    if (isMine) {
        div.innerHTML = `
            <div class="max-w-xs lg:max-w-md">
                <div class="px-4 py-2.5 rounded-2xl rounded-br-sm text-sm bg-brand-600 text-white">${escHtml(msg.body)}</div>
                <p class="text-xs text-gray-400 mt-1 text-right">${msg.created_at}</p>
            </div>`;
    } else {
        const initials = msg.sender ? msg.sender.substring(0, 2).toUpperCase() : '??';
        div.innerHTML = `
            <div class="h-7 w-7 rounded-full bg-gradient-to-br from-purple-400 to-purple-600 flex items-center justify-center text-white text-xs font-bold flex-shrink-0">
                ${initials}
            </div>
            <div class="max-w-xs lg:max-w-md">
                <div class="px-4 py-2.5 rounded-2xl rounded-bl-sm text-sm bg-gray-100 text-gray-800">${escHtml(msg.body)}</div>
                <p class="text-xs text-gray-400 mt-1">${msg.created_at}</p>
            </div>`;
    }

    document.getElementById('messagesEnd').before(div);
    scrollBottom();
}

function escHtml(str) {
    const div = document.createElement('div');
    div.appendChild(document.createTextNode(str));
    return div.innerHTML;
}

setInterval(() => {
    fetch(`{{ route('chat.messages') }}?with=${WITH_USER_ID}&since=${lastMessageId}`, {
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json',
        }
    })
    .then(r => r.json())
    .then(msgs => {
        msgs.forEach(m => {
            if (m.id > lastMessageId) {
                appendMessage(m, m.is_mine);
                lastMessageId = m.id;
            }
        });
    })
    .catch(() => {});
}, 3000);
@endif
</script>
@endpush
