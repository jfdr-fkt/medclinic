@extends('layouts.app')
@section('title', 'Staff Chat')
@section('page-title', 'Staff Chat')

@section('content')
@php
    // Role styling — light + dark variants kept side-by-side so blade can splat them inline.
    $roleConfig = [
        'admin'       => ['label'=>'Admins',        'icon'=>'fa-user-shield',                'accent'=>'slate',   'bg'=>'bg-slate-50',   'darkBg'=>'dark:bg-slate-800/40',   'border'=>'border-slate-200',   'darkBorder'=>'dark:border-slate-700',     'text'=>'text-slate-700',   'darkText'=>'dark:text-slate-200',   'gradient'=>'from-slate-500 to-slate-700'],
        'clinic_head' => ['label'=>'Clinic Heads',  'icon'=>'fa-user-tie',                   'accent'=>'purple',  'bg'=>'bg-purple-50',  'darkBg'=>'dark:bg-purple-900/25',  'border'=>'border-purple-200',  'darkBorder'=>'dark:border-purple-800/60', 'text'=>'text-purple-700',  'darkText'=>'dark:text-purple-300',  'gradient'=>'from-purple-500 to-purple-700'],
        'doctor'      => ['label'=>'Doctors',       'icon'=>'fa-user-doctor',                'accent'=>'blue',    'bg'=>'bg-blue-50',    'darkBg'=>'dark:bg-blue-900/25',    'border'=>'border-blue-200',    'darkBorder'=>'dark:border-blue-800/60',   'text'=>'text-blue-700',    'darkText'=>'dark:text-blue-300',    'gradient'=>'from-blue-500 to-blue-700'],
        'pharmacist'  => ['label'=>'Pharmacists',   'icon'=>'fa-prescription-bottle-medical','accent'=>'green',   'bg'=>'bg-green-50',   'darkBg'=>'dark:bg-green-900/25',   'border'=>'border-green-200',   'darkBorder'=>'dark:border-green-800/60',  'text'=>'text-green-700',   'darkText'=>'dark:text-green-300',   'gradient'=>'from-green-500 to-green-700'],
        'nurse'       => ['label'=>'Nurses',        'icon'=>'fa-user-nurse',                 'accent'=>'teal',    'bg'=>'bg-cyan-50',    'darkBg'=>'dark:bg-cyan-900/25',    'border'=>'border-cyan-200',    'darkBorder'=>'dark:border-cyan-800/60',   'text'=>'text-teal-700',    'darkText'=>'dark:text-teal-300',    'gradient'=>'from-cyan-500 to-teal-600'],
        'secretary'   => ['label'=>'Secretaries',   'icon'=>'fa-id-badge',                   'accent'=>'amber',   'bg'=>'bg-amber-50',   'darkBg'=>'dark:bg-amber-900/25',   'border'=>'border-amber-200',   'darkBorder'=>'dark:border-amber-800/60',  'text'=>'text-amber-700',   'darkText'=>'dark:text-amber-300',   'gradient'=>'from-amber-400 to-amber-600'],
        'assistant'   => ['label'=>'Assistants',    'icon'=>'fa-user',                       'accent'=>'emerald', 'bg'=>'bg-emerald-50', 'darkBg'=>'dark:bg-emerald-900/25', 'border'=>'border-emerald-200', 'darkBorder'=>'dark:border-emerald-800/60','text'=>'text-emerald-700', 'darkText'=>'dark:text-emerald-300', 'gradient'=>'from-emerald-400 to-emerald-600'],
    ];
@endphp

@push('head')
<style>
/* Send button — sized to match the input pill, gentle lift on hover */
.send-btn {
    width: 3.5rem; height: 3.5rem;
    border-radius: 1rem;
    background: linear-gradient(135deg, #0d9488 0%, #0f766e 100%);
    color: #fff;
    display: inline-flex; align-items: center; justify-content: center;
    box-shadow: 0 6px 18px rgba(13,148,136,.35);
    transition: transform .12s, box-shadow .15s, background .15s;
    flex-shrink: 0;
    cursor: pointer;
    border: none;
}
.send-btn:hover { background: linear-gradient(135deg, #0f766e 0%, #115e59 100%); box-shadow: 0 8px 22px rgba(13,148,136,.45); transform: translateY(-1px); }
.send-btn:active { transform: translateY(0); }
.send-btn:disabled { opacity: .45; cursor: not-allowed; transform: none; box-shadow: none; }

/* Chat input pill */
.chat-input-pill {
    border-radius: 1rem;
    border: 2px solid transparent;
    background: #f3f4f6;
    transition: background .15s, border-color .15s, box-shadow .15s;
}
.chat-input-pill:focus-within {
    background: #ffffff;
    border-color: #14b8a6;
    box-shadow: 0 0 0 3px rgba(20,184,166,.18);
}
.dark .chat-input-pill { background: #0f1a2e; }
.dark .chat-input-pill:focus-within { background: #1a2438; border-color: #14b8a6; box-shadow: 0 0 0 3px rgba(20,184,166,.22); }

/* Conversation list item — selected state pill */
.conv-item.is-active {
    background-image: linear-gradient(90deg, rgba(13,148,136,.10), transparent);
    border-left: 3px solid #14b8a6;
}
.dark .conv-item.is-active {
    background-image: linear-gradient(90deg, rgba(20,184,166,.18), transparent);
}

/* Conversation section — sticky header + clearer divisions in light and dark */
.conv-section { position: relative; }
.conv-section + .conv-section { margin-top: 0.5rem; }
.conv-section-header {
    position: sticky;
    top: 0;
    z-index: 5;
    backdrop-filter: blur(6px);
    -webkit-backdrop-filter: blur(6px);
    box-shadow: 0 1px 0 rgba(0,0,0,0.04), 0 -1px 0 rgba(0,0,0,0.04);
    cursor: pointer;
    user-select: none;
}
.dark .conv-section-header {
    box-shadow: 0 1px 0 rgba(0,0,0,0.6), 0 -1px 0 rgba(255,255,255,0.04);
}
.conv-section-chevron {
    transition: transform .15s ease;
    flex-shrink: 0;
}
.conv-section.is-collapsed .conv-section-chevron { transform: rotate(-90deg); }
.conv-section.is-collapsed .conv-rows { display: none; }
.conv-section.is-hidden { display: none; }

/* Order toggle buttons */
.conv-order-btn {
    color: #6b7280;
}
.dark .conv-order-btn { color: #94a3b8; }
.conv-order-btn.is-active {
    background: #fff;
    color: #0d9488;
    box-shadow: 0 1px 2px rgba(0,0,0,.06);
}
.dark .conv-order-btn.is-active {
    background: #1a2438;
    color: #14b8a6;
}

/* Message bubble subtle entry animation */
@keyframes msgIn { from { opacity: 0; transform: translateY(4px); } to { opacity: 1; transform: translateY(0); } }
.msg-row { animation: msgIn .15s ease; }
</style>
@endpush

<div class="flex h-[calc(100vh-8rem)] rounded-2xl bg-white dark:bg-slate-900 border-2 border-gray-100 dark:border-slate-700 overflow-hidden shadow-sm">

    <!-- ── Sidebar ── -->
    <aside class="w-72 flex-shrink-0 border-r border-gray-100 dark:border-slate-700 flex flex-col bg-gray-50/40 dark:bg-slate-900/60">
        <div class="px-4 py-4 border-b border-gray-100 dark:border-slate-700 flex items-center justify-between">
            <div>
                <h2 class="font-bold text-gray-800 dark:text-white text-sm">Conversations</h2>
                <p class="text-xs text-gray-400 dark:text-gray-500 mt-0.5">Pick a chat to start</p>
            </div>
            <button onclick="openCreateGroupModal()"
                    class="p-2.5 rounded-xl bg-brand-100 dark:bg-brand-900/40 text-brand-600 dark:text-brand-300 hover:bg-brand-200 dark:hover:bg-brand-900/60 transition-colors"
                    title="New group chat">
                <i class="fa-solid fa-users-rectangle text-sm"></i>
            </button>
        </div>

        <!-- ── Conversation search + order toggle ── -->
        <div class="px-3 pt-3 pb-2 border-b border-gray-100 dark:border-slate-700 space-y-2">
            <div class="relative">
                <i class="fa-solid fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 dark:text-gray-500 text-xs pointer-events-none"></i>
                <input type="text" id="convSearch" autocomplete="off"
                       placeholder="Search people or groups"
                       class="w-full h-9 pl-8 pr-8 text-xs rounded-lg bg-gray-100 dark:bg-slate-800 border-2 border-transparent focus:bg-white dark:focus:bg-slate-900 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 outline-none text-gray-800 dark:text-gray-100 placeholder-gray-400 dark:placeholder-gray-500 transition-all">
                <button type="button" id="convSearchClear" onclick="clearConvSearch()"
                        class="hidden absolute right-2 top-1/2 -translate-y-1/2 w-6 h-6 rounded-md text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 hover:bg-gray-200 dark:hover:bg-slate-700 flex items-center justify-center transition-colors"
                        title="Clear">
                    <i class="fa-solid fa-xmark text-[10px]"></i>
                </button>
            </div>
            <div class="flex items-center gap-1 bg-gray-100 dark:bg-slate-800 rounded-lg p-1">
                <button type="button" data-order="hierarchy" onclick="setConvOrder('hierarchy')"
                        class="conv-order-btn flex-1 text-[11px] font-bold py-1.5 rounded-md transition-colors">
                    <i class="fa-solid fa-layer-group mr-1"></i> By Role
                </button>
                <button type="button" data-order="alpha" onclick="setConvOrder('alpha')"
                        class="conv-order-btn flex-1 text-[11px] font-bold py-1.5 rounded-md transition-colors">
                    <i class="fa-solid fa-arrow-down-a-z mr-1"></i> A–Z
                </button>
            </div>
            <p id="convSearchEmpty" class="hidden mt-2 text-center text-xs text-gray-400 dark:text-gray-500 italic">No matches found</p>
        </div>

        <div class="flex-1 overflow-y-auto py-2" id="convScroll">

            <!-- ── Groups ── -->
            @if($groups->count() > 0)
            <div class="mb-4 conv-section" data-section="groups" data-sort-key="0_groups">
                <div class="conv-section-header flex items-center gap-2 px-4 py-2.5 bg-indigo-100/80 dark:bg-indigo-900/40 border-y-2 border-indigo-300 dark:border-indigo-700/70"
                     onclick="toggleSection(this.parentElement)">
                    <i class="fa-solid fa-chevron-down conv-section-chevron text-indigo-700 dark:text-indigo-300 text-[10px]"></i>
                    <i class="fa-solid fa-users-rectangle text-indigo-700 dark:text-indigo-300 text-xs"></i>
                    <p class="text-[11px] font-extrabold text-indigo-800 dark:text-indigo-200 uppercase tracking-wider">Group Chats</p>
                    <span class="ml-auto text-[11px] text-indigo-800 dark:text-indigo-200 bg-white dark:bg-slate-900/70 px-2 rounded-md font-bold border border-indigo-200 dark:border-indigo-800">{{ $groups->count() }}</span>
                </div>
                <div class="conv-rows">
                @foreach($groups as $g)
                <a href="{{ route('chat.index', ['group' => $g->id]) }}"
                   data-group-id="{{ $g->id }}"
                   data-conv-search="{{ strtolower($g->name.' '.$g->members->pluck('name')->implode(' ').' group') }}"
                   class="conv-item flex items-center gap-3 px-4 py-3 transition-colors border-b border-gray-50 dark:border-slate-800
                       {{ isset($withGroup) && $withGroup->id === $g->id ? 'is-active' : 'hover:bg-gray-100/70 dark:hover:bg-slate-800/70' }}">
                    <div class="w-10 h-10 rounded-full bg-gradient-to-br from-indigo-400 to-indigo-600 flex items-center justify-center text-white text-sm font-bold flex-shrink-0 shadow-sm">
                        <i class="fa-solid fa-users text-sm"></i>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-semibold text-gray-800 dark:text-gray-100 truncate">{{ $g->name }}</p>
                        <p class="text-xs text-gray-400 dark:text-gray-500 truncate">{{ $g->members->count() }} members</p>
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
            </div>
            @endif

            <!-- ── Direct messages by role ── -->
            @foreach($roleConfig as $role => $cfg)
                @if(isset($users[$role]) && $users[$role]->count() > 0)
                <div class="mb-4 conv-section" data-section="{{ $role }}" data-sort-key="{{ $loop->iteration }}_{{ $role }}">
                    <div class="conv-section-header flex items-center gap-2 px-4 py-2.5 border-y-2 {{ $cfg['bg'] }} {{ $cfg['darkBg'] }} {{ $cfg['border'] }} {{ $cfg['darkBorder'] }}"
                         style="filter: saturate(1.1);"
                         onclick="toggleSection(this.parentElement)">
                        <i class="fa-solid fa-chevron-down conv-section-chevron {{ $cfg['text'] }} {{ $cfg['darkText'] }} text-[10px]"></i>
                        <i class="fa-solid {{ $cfg['icon'] }} {{ $cfg['text'] }} {{ $cfg['darkText'] }} text-xs"></i>
                        <p class="text-[11px] font-extrabold {{ $cfg['text'] }} {{ $cfg['darkText'] }} uppercase tracking-wider">{{ $cfg['label'] }}</p>
                        <span class="ml-auto text-[11px] {{ $cfg['text'] }} {{ $cfg['darkText'] }} bg-white dark:bg-slate-900/70 px-2 rounded-md font-bold border {{ $cfg['border'] }} {{ $cfg['darkBorder'] }}">{{ $users[$role]->count() }}</span>
                    </div>
                    <div class="conv-rows">
                    @foreach($users[$role] as $u)
                    <a href="{{ route('chat.index', ['with' => $u->id]) }}"
                       data-user-id="{{ $u->id }}"
                       data-conv-search="{{ strtolower($u->name.' '.($u->specialization ?? '').' '.$cfg['label']) }}"
                       class="conv-item flex items-center gap-3 px-4 py-3 transition-colors border-b border-gray-50 dark:border-slate-800
                           {{ isset($withUser) && $withUser->id === $u->id ? 'is-active' : 'hover:bg-gray-100/70 dark:hover:bg-slate-800/70' }}">
                        <div class="relative flex-shrink-0">
                            @if($u->avatarUrl())
                            <img src="{{ $u->avatarUrl() }}" alt="{{ $u->name }}" class="h-10 w-10 rounded-full object-cover">
                            @else
                            <div class="h-10 w-10 rounded-full bg-gradient-to-br {{ $cfg['gradient'] }} flex items-center justify-center text-white text-sm font-bold shadow-sm">
                                {{ strtoupper(substr($u->name, 0, 2)) }}
                            </div>
                            @endif
                            <span class="absolute -bottom-0.5 -right-0.5 h-3 w-3 rounded-full border-2 border-white dark:border-slate-900 {{ $u->isOnline() ? 'bg-emerald-400' : 'bg-gray-300 dark:bg-slate-600' }}"></span>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-semibold text-gray-800 dark:text-gray-100 truncate">{{ $u->name }}</p>
                            <p class="text-xs text-gray-400 dark:text-gray-500 truncate">{{ $u->specialization ?? $u->roleLabel() }}</p>
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
                </div>
                @endif
            @endforeach
        </div>
    </aside>

    <!-- ── Chat area ── -->
    <main class="flex-1 flex flex-col overflow-hidden bg-white dark:bg-slate-900">

        @if(isset($withUser))
            @php $cfg = $roleConfig[$withUser->role] ?? $roleConfig['admin']; @endphp
            <!-- DM header -->
            <div class="flex items-center gap-3 px-6 py-4 border-b border-gray-100 dark:border-slate-700 bg-gray-50/60 dark:bg-slate-900/60 flex-shrink-0">
                <div class="relative">
                    @if($withUser->avatarUrl())
                    <img src="{{ $withUser->avatarUrl() }}" alt="{{ $withUser->name }}" class="h-11 w-11 rounded-full object-cover ring-2 ring-white dark:ring-slate-800 shadow-sm">
                    @else
                    <div class="h-11 w-11 rounded-full bg-gradient-to-br {{ $cfg['gradient'] }} flex items-center justify-center text-white text-sm font-bold shadow-sm">
                        {{ strtoupper(substr($withUser->name, 0, 2)) }}
                    </div>
                    @endif
                    <span class="absolute -bottom-0.5 -right-0.5 h-3.5 w-3.5 rounded-full border-2 border-white dark:border-slate-900 {{ $withUser->isOnline() ? 'bg-emerald-400' : 'bg-gray-300 dark:bg-slate-600' }}"></span>
                </div>
                <div class="min-w-0">
                    <p class="font-bold text-gray-900 dark:text-white truncate">{{ $withUser->name }}</p>
                    <p class="text-xs {{ $withUser->isOnline() ? 'text-emerald-600 dark:text-emerald-400' : 'text-gray-400 dark:text-gray-500' }}">
                        @if($withUser->isOnline())
                            <span class="inline-block w-1.5 h-1.5 rounded-full bg-emerald-500 mr-1 align-middle"></span> Online now
                        @else
                            {{ $withUser->last_seen_at ? 'Last seen '.$withUser->last_seen_at->diffForHumans() : 'Offline' }}
                        @endif
                    </p>
                </div>
                <span class="ml-auto text-xs {{ $cfg['bg'] }} {{ $cfg['darkBg'] }} {{ $cfg['text'] }} {{ $cfg['darkText'] }} px-2.5 py-1 rounded-full font-semibold border {{ $cfg['border'] }} {{ $cfg['darkBorder'] }}">
                    {{ $withUser->roleLabel() }}
                </span>
            </div>
        @elseif(isset($withGroup))
            <!-- Group header -->
            <div class="flex items-center gap-3 px-6 py-4 border-b border-gray-100 dark:border-slate-700 bg-gray-50/60 dark:bg-slate-900/60 flex-shrink-0">
                <div class="h-11 w-11 rounded-full bg-gradient-to-br from-indigo-400 to-indigo-600 flex items-center justify-center text-white shadow-sm">
                    <i class="fa-solid fa-users text-sm"></i>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="font-bold text-gray-900 dark:text-white truncate">{{ $withGroup->name }}</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400 truncate">
                        {{ $withGroup->members->pluck('name')->take(4)->implode(', ') }}{{ $withGroup->members->count() > 4 ? ' & '.($withGroup->members->count()-4).' more' : '' }}
                    </p>
                </div>
                <button onclick="openAddMemberModal()"
                        class="inline-flex items-center gap-1.5 text-xs px-3 py-2 bg-indigo-100 dark:bg-indigo-900/40 text-indigo-700 dark:text-indigo-300 hover:bg-indigo-200 dark:hover:bg-indigo-900/60 rounded-xl font-semibold transition-colors">
                    <i class="fa-solid fa-user-plus"></i> Add
                </button>
            </div>
        @endif

        @if(isset($withUser) || isset($withGroup))
        <!-- Messages -->
        <div class="flex-1 overflow-y-auto px-6 py-5 space-y-4 bg-gray-50/30 dark:bg-slate-950/40" id="messagesContainer">
            @forelse($messages as $msg)
                @php $isMine = $msg->sender_id === Auth::id(); @endphp
                @if(isset($withGroup) && !$isMine)
                <div class="flex justify-start items-end gap-2 msg-row">
                    <div class="h-7 w-7 rounded-full bg-gradient-to-br from-purple-400 to-purple-600 flex items-center justify-center text-white text-xs font-bold flex-shrink-0 shadow-sm">
                        {{ strtoupper(substr($msg->sender->name, 0, 2)) }}
                    </div>
                    <div class="max-w-xs lg:max-w-md">
                        <p class="text-xs text-gray-500 dark:text-gray-400 mb-0.5 ml-1">{{ $msg->sender->name }}</p>
                        <div class="px-4 py-2.5 rounded-2xl rounded-bl-md text-sm bg-white dark:bg-slate-800 text-gray-800 dark:text-gray-100 border border-gray-100 dark:border-slate-700 shadow-sm">{{ $msg->body }}</div>
                        <p class="text-xs text-gray-400 dark:text-gray-500 mt-1 ml-1">{{ $msg->created_at->format('g:i A') }}</p>
                    </div>
                </div>
                @else
                <div class="flex {{ $isMine ? 'justify-end' : 'justify-start' }} items-end gap-2 msg-row"
                     @if($isMine) data-msg-mine data-msg-id="{{ $msg->id }}" @endif>
                    @if(!$isMine)
                    <div class="h-7 w-7 rounded-full bg-gradient-to-br from-purple-400 to-purple-600 flex items-center justify-center text-white text-xs font-bold flex-shrink-0 shadow-sm">
                        {{ strtoupper(substr($msg->sender->name, 0, 2)) }}
                    </div>
                    @endif
                    <div class="max-w-xs lg:max-w-md {{ $isMine ? 'flex flex-col items-end' : '' }}">
                        <div class="px-4 py-2.5 rounded-2xl text-sm shadow-sm
                            {{ $isMine
                                ? 'bg-gradient-to-br from-brand-500 to-brand-700 text-white rounded-br-md'
                                : 'bg-white dark:bg-slate-800 text-gray-800 dark:text-gray-100 border border-gray-100 dark:border-slate-700 rounded-bl-md' }}">
                            {{ $msg->body }}
                        </div>
                        <p class="text-xs text-gray-400 dark:text-gray-500 mt-1 {{ $isMine ? 'text-right' : 'text-left' }}">
                            {{ $msg->created_at->format('g:i A') }}
                            @if($isMine && !isset($withGroup))
                                @if($msg->is_read)
                                <i class="fa-solid fa-check-double msg-status-icon text-brand-400 ml-1" title="Seen"></i>
                                @else
                                <i class="fa-solid fa-check msg-status-icon text-gray-300 dark:text-gray-600 ml-1" title="Sent"></i>
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
                <div class="w-16 h-16 bg-brand-50 dark:bg-brand-900/30 rounded-2xl flex items-center justify-center mb-3 shadow-sm">
                    <i class="fa-solid fa-comment-dots text-brand-400 dark:text-brand-300 text-2xl"></i>
                </div>
                <p class="text-gray-600 dark:text-gray-300 font-semibold text-sm">No messages yet</p>
                <p class="text-gray-400 dark:text-gray-500 text-xs mt-1">Send the first message below</p>
            </div>
            @endforelse
            <div id="messagesEnd"></div>
        </div>

        <!-- Input row -->
        <div class="px-6 py-4 border-t border-gray-100 dark:border-slate-700 bg-white dark:bg-slate-900 flex-shrink-0">
            <div class="flex items-end gap-3">
                <label class="chat-input-pill flex-1 flex items-center gap-2 px-4 py-3.5">
                    <i class="fa-solid fa-comment-dots text-gray-400 dark:text-gray-500 text-sm"></i>
                    <input type="text" id="messageInput" placeholder="Type a message" autofocus
                           class="flex-1 bg-transparent text-sm text-gray-800 dark:text-gray-100 outline-none placeholder-gray-400 dark:placeholder-gray-500"
                           onkeydown="if(event.key==='Enter'&&!event.shiftKey){sendMessage();event.preventDefault();}">
                </label>
                <button type="button" onclick="sendMessage()" class="send-btn" title="Send message (Enter)">
                    <i class="fa-solid fa-paper-plane text-base"></i>
                </button>
            </div>
        </div>

        @else
        <!-- Empty state -->
        <div class="flex-1 flex flex-col items-center justify-center text-center p-8 bg-gradient-to-br from-gray-50/30 to-white dark:from-slate-900 dark:to-slate-950">
            <div class="w-20 h-20 bg-brand-50 dark:bg-brand-900/30 rounded-3xl flex items-center justify-center mb-5 shadow-sm">
                <i class="fa-solid fa-comments text-brand-400 dark:text-brand-300 text-3xl"></i>
            </div>
            <h3 class="text-lg font-bold text-gray-700 dark:text-gray-200">Select a conversation</h3>
            <p class="text-sm text-gray-400 dark:text-gray-500 mt-1.5 max-w-xs">
                Pick a person or group from the left, or click
                <i class="fa-solid fa-users-rectangle text-brand-500 mx-1"></i>
                to create a new group chat
            </p>
        </div>
        @endif
    </main>
</div>

<!-- ── Create Group Modal ── -->
<div id="createGroupModal" class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4 backdrop-blur-sm">
    <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-2xl w-full max-w-md max-h-[90vh] overflow-y-auto border-2 border-gray-100 dark:border-slate-700">
        <div class="bg-gradient-to-r from-indigo-500 to-indigo-700 px-6 py-5 text-white">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-lg font-bold">New Group Chat</h3>
                    <p class="text-xs text-white/80 mt-0.5">Pick the people you want to chat with</p>
                </div>
                <button onclick="closeCreateGroupModal()" class="w-9 h-9 rounded-xl flex items-center justify-center text-white/80 hover:text-white hover:bg-white/20 transition-colors">
                    <i class="fa-solid fa-xmark"></i>
                </button>
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
                <div class="space-y-1 max-h-64 overflow-y-auto border-2 border-gray-200 dark:border-slate-700 rounded-xl p-2 bg-gray-50/40 dark:bg-slate-900/60">
                    @foreach($users as $role => $list)
                    <p class="text-[10px] font-bold text-gray-500 dark:text-gray-400 uppercase px-2 pt-2">{{ $roleConfig[$role]['label'] ?? ucfirst($role) }}</p>
                    @foreach($list as $u)
                    <label class="flex items-center gap-3 px-2 py-1.5 hover:bg-white dark:hover:bg-slate-800 rounded-lg cursor-pointer transition-colors">
                        <input type="checkbox" name="members[]" value="{{ $u->id }}" class="w-4 h-4 rounded border-gray-300 dark:border-slate-600 text-brand-600 focus:ring-brand-500">
                        <div class="h-7 w-7 rounded-full bg-gradient-to-br {{ $roleConfig[$role]['gradient'] }} flex items-center justify-center text-white text-xs font-bold">{{ strtoupper(substr($u->name, 0, 2)) }}</div>
                        <span class="text-sm text-gray-800 dark:text-gray-100">{{ $u->name }}</span>
                    </label>
                    @endforeach
                    @endforeach
                </div>
            </div>
            <div class="flex justify-between gap-3 pt-3 border-t border-gray-100 dark:border-slate-700">
                <button type="button" onclick="closeCreateGroupModal()" class="btn-secondary"><i class="fa-solid fa-xmark"></i> Cancel</button>
                <button type="submit" class="btn-primary"><i class="fa-solid fa-users-rectangle"></i> Create Group</button>
            </div>
        </form>
    </div>
</div>

@if(isset($withGroup))
<!-- ── Add Member Modal ── -->
<div id="addMemberModal" class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4 backdrop-blur-sm">
    <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-2xl w-full max-w-md border-2 border-gray-100 dark:border-slate-700">
        <div class="px-6 py-5 border-b border-gray-100 dark:border-slate-700">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-bold text-gray-900 dark:text-white">Add Member to {{ $withGroup->name }}</h3>
                <button onclick="closeAddMemberModal()" class="w-9 h-9 rounded-xl flex items-center justify-center text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 hover:bg-gray-100 dark:hover:bg-slate-800 transition-colors">
                    <i class="fa-solid fa-xmark"></i>
                </button>
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
            <div class="flex justify-between gap-3 pt-3 border-t border-gray-100 dark:border-slate-700">
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

// ═══════════════════════════════════════════════════════
// Conversation list search — client-side filter on people/groups
// ═══════════════════════════════════════════════════════
function applyConvSearch() {
    const input  = document.getElementById('convSearch');
    const clear  = document.getElementById('convSearchClear');
    const empty  = document.getElementById('convSearchEmpty');
    if (!input) return;
    const q = input.value.trim().toLowerCase();
    clear.classList.toggle('hidden', q.length === 0);

    let totalVisible = 0;
    document.querySelectorAll('.conv-section').forEach(section => {
        let sectionVisible = 0;
        section.querySelectorAll('[data-conv-search]').forEach(row => {
            const hay = row.dataset.convSearch || '';
            const show = !q || hay.includes(q);
            row.style.display = show ? '' : 'none';
            if (show) sectionVisible++;
        });
        section.classList.toggle('is-hidden', sectionVisible === 0);
        totalVisible += sectionVisible;
    });

    empty.classList.toggle('hidden', !(q && totalVisible === 0));
}
function clearConvSearch() {
    const input = document.getElementById('convSearch');
    if (!input) return;
    input.value = '';
    applyConvSearch();
    input.focus();
}
document.getElementById('convSearch')?.addEventListener('input', applyConvSearch);
document.getElementById('convSearch')?.addEventListener('keydown', e => {
    if (e.key === 'Escape') clearConvSearch();
});

// ═══════════════════════════════════════════════════════
// Conversation section collapse + order toggle
// ═══════════════════════════════════════════════════════
const COLLAPSE_KEY = 'chat_collapsed_sections';
const ORDER_KEY    = 'chat_section_order';

function getCollapsedSet() {
    try { return new Set(JSON.parse(localStorage.getItem(COLLAPSE_KEY) || '[]')); }
    catch (_) { return new Set(); }
}
function saveCollapsedSet(set) {
    localStorage.setItem(COLLAPSE_KEY, JSON.stringify([...set]));
}

function toggleSection(sectionEl) {
    const key = sectionEl.dataset.section;
    if (!key) return;
    const set = getCollapsedSet();
    if (sectionEl.classList.toggle('is-collapsed')) set.add(key);
    else set.delete(key);
    saveCollapsedSet(set);
}

// Apply persisted collapse state on load
(function restoreCollapseState() {
    const set = getCollapsedSet();
    document.querySelectorAll('.conv-section').forEach(s => {
        if (set.has(s.dataset.section)) s.classList.add('is-collapsed');
    });
})();

function setConvOrder(mode) {
    localStorage.setItem(ORDER_KEY, mode);
    document.querySelectorAll('.conv-order-btn').forEach(btn => {
        btn.classList.toggle('is-active', btn.dataset.order === mode);
    });
    const scroll = document.getElementById('convScroll');
    if (!scroll) return;
    const sections = Array.from(scroll.querySelectorAll('.conv-section'));

    if (mode === 'alpha') {
        // Sort role sections alphabetically by their visible label; keep Groups pinned on top.
        sections.sort((a, b) => {
            const aGroup = a.dataset.section === 'groups';
            const bGroup = b.dataset.section === 'groups';
            if (aGroup && !bGroup) return -1;
            if (!aGroup && bGroup) return 1;
            const aLabel = a.querySelector('.conv-section-header p')?.textContent.trim().toLowerCase() || '';
            const bLabel = b.querySelector('.conv-section-header p')?.textContent.trim().toLowerCase() || '';
            return aLabel.localeCompare(bLabel);
        });
        // Sort people inside each section alphabetically by name
        sections.forEach(s => {
            const rowsContainer = s.querySelector('.conv-rows');
            if (!rowsContainer) return;
            const rows = Array.from(rowsContainer.children);
            rows.sort((a, b) => {
                const aName = a.querySelector('.text-sm.font-semibold')?.textContent.trim().toLowerCase() || '';
                const bName = b.querySelector('.text-sm.font-semibold')?.textContent.trim().toLowerCase() || '';
                return aName.localeCompare(bName);
            });
            rows.forEach(r => rowsContainer.appendChild(r));
        });
    } else {
        // Hierarchy mode — restore via data-sort-key (rendered order from server)
        sections.sort((a, b) => (a.dataset.sortKey || '').localeCompare(b.dataset.sortKey || ''));
    }
    sections.forEach(s => scroll.appendChild(s));
}

// Restore order preference on load
(function restoreOrder() {
    const mode = localStorage.getItem(ORDER_KEY) || 'hierarchy';
    setConvOrder(mode);
})();

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
                <div class="px-4 py-2.5 rounded-2xl rounded-br-md text-sm bg-gradient-to-br from-brand-500 to-brand-700 text-white shadow-sm">${escHtml(msg.body)}</div>
                <p class="text-xs text-gray-400 dark:text-gray-500 mt-1 text-right">
                    ${msg.created_at}
                    ${isGroup ? '' : '<i class="fa-solid fa-check msg-status-icon text-gray-300 dark:text-gray-600 ml-1" title="Sent"></i>'}
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
            <div class="h-7 w-7 rounded-full bg-gradient-to-br from-purple-400 to-purple-600 flex items-center justify-center text-white text-xs font-bold flex-shrink-0 shadow-sm">
                ${initials}
            </div>
            <div class="max-w-xs lg:max-w-md">
                ${isGroup ? `<p class="text-xs text-gray-500 dark:text-gray-400 mb-0.5 ml-1">${escHtml(msg.sender)}</p>` : ''}
                <div class="px-4 py-2.5 rounded-2xl rounded-bl-md text-sm bg-white dark:bg-slate-800 text-gray-800 dark:text-gray-100 border border-gray-100 dark:border-slate-700 shadow-sm">${escHtml(msg.body)}</div>
                <p class="text-xs text-gray-400 dark:text-gray-500 mt-1 ${isGroup ? 'ml-1' : ''}">${msg.created_at}</p>
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
                    icon.classList.remove('fa-check', 'text-gray-300', 'dark:text-gray-600');
                    icon.classList.add('fa-check-double', 'text-brand-400');
                    icon.title = 'Seen';
                } else {
                    icon.classList.remove('fa-check-double', 'text-brand-400');
                    icon.classList.add('fa-check', 'text-gray-300', 'dark:text-gray-600');
                    icon.title = 'Sent';
                }
            });
        }
    })
    .catch(() => {});
}, 3000);
</script>
@endpush
