<?php
namespace App\Http\Controllers;

use App\Models\ChatGroup;
use App\Models\Message;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ChatController extends Controller
{
    public function index(Request $request)
    {
        $users = User::where('id', '!=', Auth::id())
            ->orderByRaw("FIELD(role, 'admin', 'doctor', 'nurse', 'assistant')")
            ->orderBy('name')
            ->get()
            ->groupBy('role');

        // Groups the current user is a member of
        $groups = Auth::user()->chatGroups()->with('members')->get();

        $withUser  = null;
        $withGroup = null;
        $messages  = collect();

        if ($request->filled('with')) {
            $withUser = User::findOrFail($request->with);
            $messages = Message::whereNull('group_id')
                ->where(function ($q) use ($withUser) {
                    $q->where(function ($s) use ($withUser) {
                        $s->where('sender_id', Auth::id())->where('receiver_id', $withUser->id);
                    })->orWhere(function ($s) use ($withUser) {
                        $s->where('sender_id', $withUser->id)->where('receiver_id', Auth::id());
                    });
                })
                ->with('sender')->orderBy('created_at')->take(100)->get();

            Message::where('sender_id', $withUser->id)
                ->where('receiver_id', Auth::id())
                ->where('is_read', false)
                ->update(['is_read' => true]);
        } elseif ($request->filled('group')) {
            $withGroup = ChatGroup::with('members')->findOrFail($request->group);
            // verify membership
            if (!$withGroup->members->contains(Auth::id())) {
                abort(403, 'You are not a member of this group.');
            }
            $messages = Message::where('group_id', $withGroup->id)
                ->with('sender')->orderBy('created_at')->take(100)->get();

            // mark group as read for current user
            DB::table('chat_group_members')
                ->where('group_id', $withGroup->id)
                ->where('user_id', Auth::id())
                ->update(['last_read_at' => now()]);
        }

        $unreadCounts = Message::where('receiver_id', Auth::id())
            ->where('is_read', false)
            ->whereNull('group_id')
            ->selectRaw('sender_id, count(*) as cnt')
            ->groupBy('sender_id')
            ->pluck('cnt', 'sender_id');

        $groupUnread = $this->groupUnreadCounts();

        return view('chat.index', compact(
            'users', 'groups', 'withUser', 'withGroup', 'messages', 'unreadCounts', 'groupUnread'
        ));
    }

    public function send(Request $request)
    {
        $request->validate([
            'receiver_id' => 'nullable|exists:users,id',
            'group_id'    => 'nullable|exists:chat_groups,id',
            'body'        => 'required|string|max:1000',
        ]);

        if (!$request->receiver_id && !$request->group_id) {
            return response()->json(['success' => false, 'error' => 'No target specified'], 422);
        }

        // Membership check for groups
        if ($request->group_id) {
            $group = ChatGroup::findOrFail($request->group_id);
            if (!$group->members->contains(Auth::id())) {
                return response()->json(['success' => false, 'error' => 'Not a member'], 403);
            }
        }

        $message = Message::create([
            'sender_id'   => Auth::id(),
            'receiver_id' => $request->receiver_id,
            'group_id'    => $request->group_id,
            'body'        => $request->body,
        ]);

        return response()->json([
            'success' => true,
            'message' => [
                'id'         => $message->id,
                'body'       => $message->body,
                'sender_id'  => $message->sender_id,
                'sender'     => Auth::user()->name,
                'created_at' => $message->created_at->format('g:i A'),
            ],
        ]);
    }

    public function messages(Request $request)
    {
        $since = $request->get('since', 0);

        if ($request->filled('group')) {
            $group = ChatGroup::findOrFail($request->group);
            if (!$group->members->contains(Auth::id())) abort(403);

            $messages = Message::where('group_id', $group->id)
                ->when($since, fn($q) => $q->where('id', '>', $since))
                ->with('sender')->orderBy('created_at')->take(50)->get();

            DB::table('chat_group_members')
                ->where('group_id', $group->id)
                ->where('user_id', Auth::id())
                ->update(['last_read_at' => now()]);
        } else {
            $request->validate(['with' => 'required|exists:users,id']);
            $withUserId = $request->with;

            $messages = Message::whereNull('group_id')
                ->where(function ($q) use ($withUserId) {
                    $q->where(function ($s) use ($withUserId) {
                        $s->where('sender_id', Auth::id())->where('receiver_id', $withUserId);
                    })->orWhere(function ($s) use ($withUserId) {
                        $s->where('sender_id', $withUserId)->where('receiver_id', Auth::id());
                    });
                })
                ->when($since, fn($q) => $q->where('id', '>', $since))
                ->with('sender')->orderBy('created_at')->take(50)->get();

            Message::where('sender_id', $withUserId)
                ->where('receiver_id', Auth::id())
                ->where('is_read', false)
                ->update(['is_read' => true]);
        }

        return response()->json($messages->map(fn($m) => [
            'id'         => $m->id,
            'body'       => $m->body,
            'sender_id'  => $m->sender_id,
            'sender'     => $m->sender->name,
            'created_at' => $m->created_at->format('g:i A'),
            'is_mine'    => $m->sender_id === Auth::id(),
        ]));
    }

    /**
     * Sidebar polling endpoint: unread counts + most recent message previews.
     */
    public function sidebar()
    {
        $dmUnread    = Message::where('receiver_id', Auth::id())
            ->where('is_read', false)
            ->whereNull('group_id')
            ->selectRaw('sender_id, count(*) as cnt')
            ->groupBy('sender_id')
            ->pluck('cnt', 'sender_id');

        return response()->json([
            'dm_unread'    => $dmUnread,
            'group_unread' => $this->groupUnreadCounts(),
            'read_receipt' => Message::where('sender_id', Auth::id())
                ->where('is_read', true)
                ->latest('updated_at')->first()?->id,
        ]);
    }

    public function storeGroup(Request $request)
    {
        $validated = $request->validate([
            'name'      => 'required|string|max:255',
            'members'   => 'required|array|min:1',
            'members.*' => 'exists:users,id',
        ]);

        $group = ChatGroup::create([
            'name'       => $validated['name'],
            'created_by' => Auth::id(),
        ]);

        $memberIds = collect($validated['members'])->push(Auth::id())->unique()->values()->all();
        $group->members()->attach($memberIds);

        return redirect()->route('chat.index', ['group' => $group->id])
            ->with('success', "Group '{$group->name}' created!");
    }

    public function addToGroup(Request $request, ChatGroup $group)
    {
        if (!$group->members->contains(Auth::id())) abort(403);
        $request->validate(['user_id' => 'required|exists:users,id']);
        $group->members()->syncWithoutDetaching([$request->user_id]);
        return back()->with('success', 'Member added to group.');
    }

    private function groupUnreadCounts()
    {
        return DB::table('chat_group_members as m')
            ->where('m.user_id', Auth::id())
            ->leftJoin('messages', function ($join) {
                $join->on('messages.group_id', '=', 'm.group_id')
                     ->where('messages.sender_id', '!=', Auth::id())
                     ->whereColumn('messages.created_at', '>', DB::raw('COALESCE(m.last_read_at, m.created_at)'));
            })
            ->whereNotNull('messages.id')
            ->select('m.group_id', DB::raw('count(messages.id) as cnt'))
            ->groupBy('m.group_id')
            ->pluck('cnt', 'm.group_id');
    }
}
