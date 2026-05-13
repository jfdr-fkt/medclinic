@extends('layouts.app')
@section('title', 'Staff Chat')
@section('page-title', 'Staff Chat')

@section('content')
@php
    // Standardized role colors:
    // Admin = Slate/Charcoal, Doctor = Royal Blue, Nurse = Teal/Cyan, Assistant = Emerald/Mint
    $roleConfig = [
        'admin'       => ['label'=>'Admins',        'icon'=>'fa-user-shield',             'accent'=>'slate',   'bg'=>'bg-slate-50',   'border'=>'border-slate-200',   'text'=>'text-slate-700',   'gradient'=>'from-slate-500 to-slate-700'],
        'clinic_head' => ['label'=>'Clinic Heads',  'icon'=>'fa-user-tie',                'accent'=>'purple',  'bg'=>'bg-purple-50',  'border'=>'border-purple-200',  'text'=>'text-purple-700',  'gradient'=>'from-purple-500 to-purple-700'],
        'doctor'      => ['label'=>'Doctors',        'icon'=>'fa-user-doctor',             'accent'=>'blue',    'bg'=>'bg-blue-50',    'border'=>'border-blue-200',    'text'=>'text-blue-700',    'gradient'=>'from-blue-500 to-blue-700'],
        'pharmacist'  => ['label'=>'Pharmacists',    'icon'=>'fa-prescription-bottle-medical','accent'=>'green','bg'=>'bg-green-50',  'border'=>'border-green-200',   'text'=>'text-green-700',   'gradient'=>'from-green-500 to-green-700'],
        'nurse'       => ['label'=>'Nurses',         'icon'=>'fa-user-nurse',              'accent'=>'teal',    'bg'=>'bg-cyan-50',    'border'=>'border-cyan-200',    'text'=>'text-teal-700',    'gradient'=>'from-cyan-500 to-teal-600'],
        'secretary'   => ['label'=>'Secretaries',    'icon'=>'fa-id-badge',                'accent'=>'amber',   'bg'=>'bg-amber-50',   'border'=>'border-amber-200',   'text'=>'text-amber-700',   'gradient'=>'from-amber-400 to-amber-600'],
        'assistant'   => ['label'=>'Assistants',     'icon'=>'fa-user',                    'accent'=>'emerald', 'bg'=>'bg-emerald-50', 'border'=>'border-emerald-200', 'text'=>'text-emerald-700', 'gradient'=>'from-emerald-400 to-emerald-600'],
    ];
@endphp

<div class="flex h-[calc(100vh-8rem)] card overflow-hidden">

    <!-- ── Sidebar ── -->
    <div class="w-72 flex-shrink-0 border-r border-gray-100 flex flex-col">
        <div class="px-4 py-3 border-b border-gray-100 bg-gray-50/60 flex items-center justify-between">
            <div>
                <h2 class="font-bold text-gray-800 text-sm">Conversations</h2>
                <p class="text-xs text-gray-400 mt-0.5">Pick a chat to start</p>
            </div>
            <button onclick="openCreateGroupModal()" class="p-2 rounded-lg bg-brand-100 text-brand-600 hover:bg-brand-200" title="New group chat">
                <i class="fa-solid fa-users-rectangle text-sm"></i>
            </button>
        </div>

        <div class="flex-1 overflow-y-auto py-2">

            <!-- ── Groups section ── -->
            @if($groups->count() > 0)
            <div class="mb-3">
                <div class="flex items-center gap-2 px-4 py-2 bg-indigo-50 border-y border-indigo-200">
                    <i class="fa-solid fa-users-rectangle text-indigo-700 text-xs"></i>
                    <p class="text-xs font-bold text-indigo-700 uppercase tracking-wider">Group Chats</p>
                    <span class="ml-auto text-xs text-indigo-700 bg-white/60 px-1.5 rounded-md font-semibold">{{ $groups->count() }}</span>
                </div>
                @foreach($groups as $g)
                <a href="{{ route('chat.index', ['group' => $g->id]) }}"
                   data-group-id="{{ $g->id }}"
                   class="flex items-center gap-3 px-4 py-3 transition-colors border-b border-gray-50
                       {{ isset($withGroup) && $withGroup->id === $g->id ? 'bg-indigo-50 border-l-2 border-l-indigo-500' : 'hover:bg-gray-50' }}">
                    <div class="w-10 h-10 rounded-full bg-gradient-to-br from-indigo-400 to-indigo-600 flex items-center justify-center text-white text-sm font-bold flex-shrink-0">
                        <i class="fa-solid fa-users text-sm"></i>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-semibold text-gray-800 truncate">{{ $g->name }}</p>
                        <p class="text-xs text-gray-400 truncate">{{ $g->members->count() }} members</p>
                    </div>
                    @if(($groupUnread[$g->id] ?? 0) > 0)
                    <span data-group-badge="{{ $g->id }}" class="flex-shrink-0 bg-red-500 text-white text-xs font-bold rounded-full px-1.5 py-0.5 min-w-[20px] text-center leading-tight">
                        {{ $groupUnread[$g->id] }}
                    </span>
                    @else
                    <span data-group-badge="{{ $g->id }}" class="hidden flex-shrink-0 bg-red-500 text-white text-xs font-bold rounded-full px-1.5 py-0.5 min-w-[20px] text-center leading-tight"></span>
                    @endif
                </a>
                @endforeach
            </div>
            @endif

            <!-- ── Direct messages by role ── -->
            @foreach($roleConfig as $role => $cfg)
                @if(isset($users[$role]) && $users[$role]->count() > 0)
                <div class="mb-3">
                    <div class="flex items-center gap-2 px-4 py-2 {{ $cfg['bg'] }} border-y {{ $cfg['border'] }}">
                        <i class="fa-solid {{ $cfg['icon'] }} {{ $cfg['text'] }} text-xs"></i>
                        <p class="text-xs font-bold {{ $cfg['text'] }} uppercase tracking-wider">{{ $cfg['label'] }}</p>
                        <span class="ml-auto text-xs {{ $cfg['text'] }} bg-white/60 px-1.5 rounded-md font-semibold">{{ $users[$role]->count() }}</span>
                    </div>
                    @foreach($users[$role] as $u)
                    <a href="{{ route('chat.index', ['with' => $u->id]) }}"
                       data-user-id="{{ $u->id }}"
                       class="flex items-center gap-3 px-4 py-3 transition-colors border-b border-gray-50
                           {{ isset($withUser) && $withUser->id === $u->id
                                ? $cfg['bg'].' border-l-2 border-l-'.$cfg['accent'].'-500'
                                : 'hover:bg-gray-50' }}">
                        <div class="relative flex-shrink-0">
                            @if($u->avatarUrl())
                            <img src="{{ $u->avatarUrl() }}" alt="{{ $u->name }}" class="h-10 w-10 rounded-full object-cover">
                            @else
                            <div class="h-10 w-10 rounded-full bg-gradient-to-br {{ $cfg['gradient'] }} flex items-center justify-center text-white text-sm font-bold">
                                {{ strtoupper(substr($u->name, 0, 2)) }}
                            </div>
                            @endif
                            <span class="absolute -bottom-0.5 -right-0.5 h-3 w-3 rounded-full border-2 border-white {{ $u->isOnline() ? 'bg-emerald-400' : 'bg-gray-300' }}"></span>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-semibold text-gray-800 truncate">{{ $u->name }}</p>
                            <p class="text-xs text-gray-400 truncate">{{ $u->specialization ?? $u->roleLabel() }}</p>
                        </div>
                        @if(($unreadCounts[$u->id] ?? 0) > 0)
                        <span data-dm-badge="{{ $u->id }}" class="flex-shrink-0 bg-red-500 text-white text-xs font-bold rounded-full px-1.5 py-0.5 min-w-[20px] text-center leading-tight">
                            {{ $unreadCounts[$u->id] }}
                        </span>
                        @else
                        <span data-dm-badge="{{ $u->id }}" class="hidden flex-shrink-0 bg-red-500 text-white text-xs font-bold rounded-full px-1.5 py-0.5 min-w-[20px] text-center leading-tight"></span>
                        @endif
                    </a>
                    @endforeach
                </div>
                @endif
            @endforeach
        </div>
    </div>

    <!-- ── Chat area ── -->
    <div class="flex-1 flex flex-col overflow-hidden">

        @if(isset($withUser))
            @php $cfg = $roleConfig[$withUser->role] ?? $roleConfig['admin']; @endphp
            <!-- DM header -->
            <div class="flex items-center gap-3 px-6 py-4 border-b border-gray-100 bg-gray-50/50 flex-shrink-0">
                <div class="relative">
                    @if($withUser->avatarUrl())
                    <img src="{{ $withUser->avatarUrl() }}" alt="{{ $withUser->name }}" class="h-10 w-10 rounded-full object-cover">
                    @else
                    <div class="h-10 w-10 rounded-full bg-gradient-to-br {{ $cfg['gradient'] }} flex items-center justify-center text-white text-sm font-bold">
                        {{ strtoupper(substr($withUser->name, 0, 2)) }}
                    </div>
                    @endif
                    <span class="absolute -bottom-0.5 -right-0.5 h-3 w-3 rounded-full border-2 border-white {{ $withUser->isOnline() ? 'bg-emerald-400' : 'bg-gray-300' }}"></span>
                </div>
                <div>
                    <p class="font-bold text-gray-900">{{ $withUser->name }}</p>
                    <p class="text-xs {{ $withUser->isOnline() ? 'text-emerald-600' : 'text-gray-400' }}">
                        {{ $withUser->isOnline() ? 'Online now' : ($withUser->last_seen_at ? 'Last seen '.$withUser->last_seen_at->diffForHumans() : 'Offline') }}
                    </p>
                </div>
                <span class="ml-auto text-xs {{ $cfg['bg'] }} {{ $cfg['text'] }} px-2.5 py-1 rounded-full font-medium">{{ $withUser->roleLabel() }}</span>
            </div>
        @elseif(isset($withGroup))
            <!-- Group header -->
            <div class="flex items-center gap-3 px-6 py-4 border-b border-gray-100 bg-gray-50/50 flex-shrink-0">
                <div class="h-10 w-10 rounded-full bg-gradient-to-br from-indigo-400 to-indigo-600 flex items-center justify-center text-white"><i class="fa-solid fa-users text-sm"></i></div>
                <div class="flex-1 min-w-0">
                    <p class="font-bold text-gray-900 truncate">{{ $withGroup->name }}</p>
                    <p class="text-xs text-gray-500 truncate">{{ $withGroup->members->pluck('name')->take(4)->implode(', ') }}{{ $withGroup->members->count() > 4 ? ' & '.($withGroup->members->count()-4).' more' : '' }}</p>
                </div>
                <button onclick="openAddMemberModal()" class="text-xs px-3 py-1.5 bg-indigo-100 text-indigo-700 hover:bg-indigo-200 rounded-lg font-medium">
                    <i class="fa-solid fa-user-plus"></i> Add
                </button>
            </div>
        @endif

        @if(isset($withUser) || isset($withGroup))
        <!-- Messages -->
        <div class="flex-1 overflow-y-auto px-6 py-5 space-y-4" id="messagesContainer">
            @forelse($messages as $msg)
                @php $isMine = $msg->sender_id === Auth::id(); @endphp
                @if(isset($withGroup) && !$isMine)
                <div class="flex justify-start items-end gap-2">
                    <div class="h-7 w-7 rounded-full bg-gradient-to-br from-purple-400 to-purple-600 flex items-center justify-center text-white text-xs font-bold flex-shrink-0">
                        {{ strtoupper(substr($msg->sender->name, 0, 2)) }}
                    </div>
                    <div class="max-w-xs lg:max-w-md">
                        <p class="text-xs text-gray-500 mb-0.5 ml-1">{{ $msg->sender->name }}</p>
                        <div class="px-4 py-2.5 rounded-2xl rounded-bl-sm text-sm bg-gray-100 text-gray-800">{{ $msg->body }}</div>
                        <p class="text-xs text-gray-400 mt-1 ml-1">{{ $msg->created_at->format('g:i A') }}</p>
                    </div>
                </div>
                @else
                <div class="flex {{ $isMine ? 'justify-end' : 'justify-start' }} items-end gap-2 msg-row"
                     @if($isMine) data-msg-mine data-msg-id="{{ $msg->id }}" @endif>
                    @if(!$isMine)
                    <div class="h-7 w-7 rounded-full bg-gradient-to-br from-purple-400 to-purple-600 flex items-center justify-center text-white text-xs font-bold flex-shrink-0">
                        {{ strtoupper(substr($msg->sender->name, 0, 2)) }}
                    </div>
                    @endif
                    <div class="max-w-xs lg:max-w-md {{ $isMine ? 'flex flex-col items-end' : '' }}">
                        <div class="px-4 py-2.5 rounded-2xl text-sm {{ $isMine ? 'bg-brand-600 text-white rounded-br-sm' : 'bg-gray-100 text-gray-800 rounded-bl-sm' }}">{{ $msg->body }}</div>
                        <p class="text-xs text-gray-400 mt-1 {{ $isMine ? 'text-right' : 'text-left' }}">
                            {{ $msg->created_at->format('g:i A') }}
                            @if($isMine && !isset($withGroup))
                                @if($msg->is_read)
                                <i class="fa-solid fa-check-double msg-status-icon text-brand-400 ml-1" title="Seen"></i>
                                @else
                                <i class="fa-solid fa-check msg-status-icon text-gray-300 ml-1" title="Sent"></i>
                                @endif
                            @endif
                            @if($isMine)
                            <button onclick="deleteMessage({{ $msg->id }}, this)"
                                    class="msg-del-btn ml-1.5 text-red-400 hover:text-red-600 transition-opacity"
                                    style="opacity:0;"
                                    title="Delete message">
                                <i class="fa-solid fa-trash-can text-[10px]"></i>
                            </button>
                            @endif
                        </p>
                    </div>
                </div>
                @endif
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

        <!-- Input -->
        <div class="px-6 py-4 border-t border-gray-100 bg-white flex-shrink-0">
            <div class="flex items-center gap-3">
                <div class="flex-1 flex items-center gap-2 bg-gray-100 rounded-xl px-4 py-2.5 focus-within:ring-2 focus-within:ring-brand-500 focus-within:bg-white transition-all border border-transparent focus-within:border-brand-300">
                    <input type="text" id="messageInput" placeholder="Type a message…" autofocus
                           class="flex-1 bg-transparent text-sm text-gray-800 outline-none placeholder-gray-400"
                           onkeydown="if(event.key==='Enter'&&!event.shiftKey){sendMessage();event.preventDefault();}">
                </div>
                <button onclick="sendMessage()" class="w-11 h-11 bg-brand-600 text-white rounded-xl hover:bg-brand-700 transition-colors flex items-center justify-center flex-shrink-0 shadow-sm">
                    <i class="fa-solid fa-paper-plane text-sm"></i>
                </button>
            </div>
        </div>

        @else
        <div class="flex-1 flex flex-col items-center justify-center text-center p-8">
            <div class="w-20 h-20 bg-brand-50 rounded-3xl flex items-center justify-center mb-5">
                <i class="fa-solid fa-comments text-brand-300 text-3xl"></i>
            </div>
            <h3 class="text-lg font-bold text-gray-700">Select a conversation</h3>
            <p class="text-sm text-gray-400 mt-1.5 max-w-xs">Pick a person or group from the left, or click <i class="fa-solid fa-users-rectangle text-brand-500 mx-1"></i> to create a new group chat</p>
        </div>
        @endif
    </div>
</div>

<!-- ── Create Group Modal ── -->
<div id="createGroupModal" class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4 backdrop-blur-sm">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md max-h-[90vh] overflow-y-auto">
        <div class="bg-gradient-to-r from-indigo-500 to-indigo-700 px-6 py-5 text-white">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-lg font-bold">New Group Chat</h3>
                    <p class="text-xs text-white/80 mt-0.5">Pick the people you want to chat with</p>
                </div>
                <button onclick="closeCreateGroupModal()" class="w-8 h-8 rounded-xl flex items-center justify-center text-white/80 hover:text-white hover:bg-white/20"><i class="fa-solid fa-xmark"></i></button>
            </div>
        </div>
        <form method="POST" action="{{ route('chat.groups.store') }}" class="px-6 py-5 space-y-4">
            @csrf
            <div>
                <label class="label">Group Name <span class="text-red-500">*</span></label>
                <input type="text" name="name" required class="input" placeholder="e.g. ER Night Shift">
            </div>
            <div>
                <label class="label">Members <span class="text-red-500">*</span></label>
                <div class="space-y-1 max-h-64 overflow-y-auto border-2 border-gray-200 rounded-xl p-2">
                    @foreach($users as $role => $list)
                    <p class="text-[10px] font-bold text-gray-500 uppercase px-2 pt-2">{{ ucfirst($role) }}s</p>
                    @foreach($list as $u)
                    <label class="flex items-center gap-3 px-2 py-1.5 hover:bg-gray-50 rounded-lg cursor-pointer">
                        <input type="checkbox" name="members[]" value="{{ $u->id }}" class="w-4 h-4 rounded border-gray-300 text-brand-600 focus:ring-brand-500">
                        <div class="h-7 w-7 rounded-full bg-gradient-to-br {{ $roleConfig[$role]['gradient'] }} flex items-center justify-center text-white text-xs font-bold">{{ strtoupper(substr($u->name, 0, 2)) }}</div>
                        <span class="text-sm text-gray-800">{{ $u->name }}</span>
                    </label>
                    @endforeach
                    @endforeach
                </div>
            </div>
            <div class="flex justify-between gap-3 pt-3 border-t border-gray-100">
                <button type="button" onclick="closeCreateGroupModal()" class="btn-secondary"><i class="fa-solid fa-xmark"></i> Cancel</button>
                <button type="submit" class="btn-primary"><i class="fa-solid fa-users-rectangle"></i> Create Group</button>
            </div>
        </form>
    </div>
</div>

@if(isset($withGroup))
<!-- ── Add Member Modal ── -->
<div id="addMemberModal" class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4 backdrop-blur-sm">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md">
        <div class="px-6 py-5 border-b border-gray-100">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-bold text-gray-900">Add Member to {{ $withGroup->name }}</h3>
                <button onclick="closeAddMemberModal()" class="w-8 h-8 rounded-xl flex items-center justify-center text-gray-400 hover:text-gray-700 hover:bg-gray-100"><i class="fa-solid fa-xmark"></i></button>
            </div>
        </div>
        <form method="POST" action="{{ route('chat.groups.add', $withGroup) }}" class="px-6 py-5 space-y-4">
            @csrf
            <div>
                <label class="label">Pick a member</label>
                @php $existingMemberIds = $withGroup->members->pluck('id'); @endphp
                <select name="user_id" required class="input">
                    @foreach($users as $role => $list)
                        @foreach($list as $u)
                            @if(!$existingMemberIds->contains($u->id))
                            <option value="{{ $u->id }}">{{ $u->name }} ({{ $u->roleLabel() }})</option>
                            @endif
                        @endforeach
                    @endforeach
                </select>
            </div>
            <div class="flex justify-between gap-3 pt-3 border-t border-gray-100">
                <button type="button" onclick="closeAddMemberModal()" class="btn-secondary">Cancel</button>
                <button type="submit" class="btn-primary"><i class="fa-solid fa-user-plus"></i> Add Member</button>
            </div>
        </form>
    </div>
</div>
@endif

@endsection

@push('scripts')
<script>
@if(isset($withUser))
const CHAT_TARGET = { type: 'dm', id: {{ $withUser->id }} };
@elseif(isset($withGroup))
const CHAT_TARGET = { type: 'group', id: {{ $withGroup->id }} };
@else
const CHAT_TARGET = null;
@endif

let lastMessageId = {{ isset($messages) ? ($messages->last()?->id ?? 0) : 0 }};

function openCreateGroupModal()  { document.getElementById('createGroupModal').classList.remove('hidden'); document.body.style.overflow='hidden'; }
function closeCreateGroupModal() { document.getElementById('createGroupModal').classList.add('hidden'); document.body.style.overflow=''; }
function openAddMemberModal()    { document.getElementById('addMemberModal')?.classList.remove('hidden'); document.body.style.overflow='hidden'; }
function closeAddMemberModal()   { document.getElementById('addMemberModal')?.classList.add('hidden'); document.body.style.overflow=''; }
document.addEventListener('keydown', e => { if(e.key==='Escape'){ closeCreateGroupModal(); closeAddMemberModal(); }});

function scrollBottom() {
    const c = document.getElementById('messagesContainer');
    if (c) c.scrollTop = c.scrollHeight;
}

function escHtml(str) {
    const div = document.createElement('div');
    div.appendChild(document.createTextNode(str));
    return div.innerHTML;
}

function deleteMessage(id, btn) {
    if (!confirm('Delete this message? This cannot be undone.')) return;
    fetch(`/chat/messages/${id}`, {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json',
        },
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            const row = btn.closest('[data-msg-mine]');
            if (row) row.remove();
        }
    })
    .catch(() => alert('Could not delete. Try again.'));
}

// Show/hide delete button on hover for own messages
document.addEventListener('mouseover', e => {
    const row = e.target.closest('.msg-row[data-msg-mine]');
    if (row) row.querySelector('.msg-del-btn')?.style.setProperty('opacity', '1');
});
document.addEventListener('mouseout', e => {
    const row = e.target.closest('.msg-row[data-msg-mine]');
    if (row && !row.contains(e.relatedTarget)) {
        row.querySelector('.msg-del-btn')?.style.setProperty('opacity', '0');
    }
});

function appendMessage(msg, isMine) {
    const container = document.getElementById('messagesContainer');
    if (!container) return;
    const isGroup = CHAT_TARGET?.type === 'group';
    const div = document.createElement('div');
    div.className = `flex ${isMine ? 'justify-end' : 'justify-start'} items-end gap-2 msg-row`;
    if (isMine) { div.dataset.msgMine = ''; div.dataset.msgId = msg.id; }

    if (isMine) {
        div.innerHTML = `
            <div class="max-w-xs lg:max-w-md flex flex-col items-end">
                <div class="px-4 py-2.5 rounded-2xl rounded-br-sm text-sm bg-brand-600 text-white">${escHtml(msg.body)}</div>
                <p class="text-xs text-gray-400 mt-1 text-right">
                    ${msg.created_at}
                    ${isGroup ? '' : '<i class="fa-solid fa-check msg-status-icon text-gray-300 ml-1" title="Sent"></i>'}
                    <button onclick="deleteMessage(${msg.id}, this)"
                            class="msg-del-btn ml-1.5 text-red-400 hover:text-red-600 transition-opacity"
                            style="opacity:0;"
                            title="Delete message">
                        <i class="fa-solid fa-trash-can text-[10px]"></i>
                    </button>
                </p>
            </div>`;
    } else {
        const initials = msg.sender ? msg.sender.substring(0, 2).toUpperCase() : '??';
        div.innerHTML = `
            <div class="h-7 w-7 rounded-full bg-gradient-to-br from-purple-400 to-purple-600 flex items-center justify-center text-white text-xs font-bold flex-shrink-0">
                ${initials}
            </div>
            <div class="max-w-xs lg:max-w-md">
                ${isGroup ? `<p class="text-xs text-gray-500 mb-0.5 ml-1">${escHtml(msg.sender)}</p>` : ''}
                <div class="px-4 py-2.5 rounded-2xl rounded-bl-sm text-sm bg-gray-100 text-gray-800">${escHtml(msg.body)}</div>
                <p class="text-xs text-gray-400 mt-1 ${isGroup ? 'ml-1' : ''}">${msg.created_at}</p>
            </div>`;
    }
    document.getElementById('messagesEnd').before(div);
    scrollBottom();
}

function sendMessage() {
    if (!CHAT_TARGET) return;
    const input = document.getElementById('messageInput');
    const body = input.value.trim();
    if (!body) return;
    input.value = '';

    const payload = { body };
    if (CHAT_TARGET.type === 'dm') payload.receiver_id = CHAT_TARGET.id;
    else payload.group_id = CHAT_TARGET.id;

    fetch('{{ route("chat.send") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json',
        },
        body: JSON.stringify(payload),
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            appendMessage(data.message, true);
            lastMessageId = data.message.id;
        }
    });
}

// Poll messages for current conversation
if (CHAT_TARGET) {
    scrollBottom();
    setInterval(() => {
        const params = CHAT_TARGET.type === 'dm'
            ? `with=${CHAT_TARGET.id}&since=${lastMessageId}`
            : `group=${CHAT_TARGET.id}&since=${lastMessageId}`;
        fetch(`{{ route('chat.messages') }}?${params}`, {
            headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'Accept': 'application/json' }
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
    }, 2000);
}

// Always: poll sidebar unread counts so badges update across conversations + own seen receipts
setInterval(() => {
    fetch('{{ route("chat.sidebar") }}', {
        headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'Accept': 'application/json' }
    })
    .then(r => r.json())
    .then(data => {
        // DM badges
        document.querySelectorAll('[data-dm-badge]').forEach(el => {
            const uid = el.dataset.dmBadge;
            const cnt = data.dm_unread?.[uid];
            if (cnt) {
                el.textContent = cnt;
                el.classList.remove('hidden');
            } else {
                el.classList.add('hidden');
            }
        });
        // Group badges
        document.querySelectorAll('[data-group-badge]').forEach(el => {
            const gid = el.dataset.groupBadge;
            const cnt = data.group_unread?.[gid];
            if (cnt) {
                el.textContent = cnt;
                el.classList.remove('hidden');
            } else {
                el.classList.add('hidden');
            }
        });

        // Per-message seen indicators: only mark messages as "Seen" if their ID
        // is <= the highest message ID the recipient has actually read.
        if (CHAT_TARGET?.type === 'dm') {
            const seenUntil = parseInt(data.seen_until?.[CHAT_TARGET.id] ?? 0);
            document.querySelectorAll('[data-msg-mine][data-msg-id]').forEach(row => {
                const id = parseInt(row.dataset.msgId);
                const icon = row.querySelector('.msg-status-icon');
                if (!icon) return;
                if (id <= seenUntil) {
                    icon.classList.remove('fa-check', 'text-gray-300');
                    icon.classList.add('fa-check-double', 'text-brand-400');
                    icon.title = 'Seen';
                } else {
                    icon.classList.remove('fa-check-double', 'text-brand-400');
                    icon.classList.add('fa-check', 'text-gray-300');
                    icon.title = 'Sent';
                }
            });
        }
    })
    .catch(() => {});
}, 3000);
</script>
@endpush
