<?php
namespace App\Http\Controllers;

use App\Models\Message;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ChatController extends Controller
{
    public function index(Request $request)
    {
        $users = User::where('id', '!=', Auth::id())
            ->orderByRaw("FIELD(role, 'admin', 'doctor', 'nurse', 'assistant')")
            ->orderBy('name')
            ->get()
            ->groupBy('role');

        // Default: open conversation with first online user, or first user
        $withUser = null;
        $messages = collect();

        if ($request->filled('with')) {
            $withUser = User::findOrFail($request->with);
            $messages = Message::where(function ($q) use ($withUser) {
                    $q->where('sender_id', Auth::id())->where('receiver_id', $withUser->id);
                })->orWhere(function ($q) use ($withUser) {
                    $q->where('sender_id', $withUser->id)->where('receiver_id', Auth::id());
                })->with('sender')
                ->orderBy('created_at')
                ->take(100)->get();

            // Mark as read
            Message::where('sender_id', $withUser->id)
                ->where('receiver_id', Auth::id())
                ->where('is_read', false)
                ->update(['is_read' => true]);
        }

        // Unread count per user
        $unreadCounts = Message::where('receiver_id', Auth::id())
            ->where('is_read', false)
            ->selectRaw('sender_id, count(*) as cnt')
            ->groupBy('sender_id')
            ->pluck('cnt', 'sender_id');

        return view('chat.index', compact('users', 'withUser', 'messages', 'unreadCounts'));
    }

    public function send(Request $request)
    {
        $request->validate([
            'receiver_id' => 'required|exists:users,id',
            'body'        => 'required|string|max:1000',
        ]);

        $message = Message::create([
            'sender_id'   => Auth::id(),
            'receiver_id' => $request->receiver_id,
            'body'        => $request->body,
        ]);

        return response()->json([
            'success'    => true,
            'message'    => [
                'id'         => $message->id,
                'body'       => $message->body,
                'sender_id'  => $message->sender_id,
                'created_at' => $message->created_at->format('g:i A'),
            ],
        ]);
    }

    public function messages(Request $request)
    {
        $request->validate(['with' => 'required|exists:users,id']);

        $withUserId = $request->with;
        $since      = $request->get('since', 0);

        $messages = Message::where(function ($q) use ($withUserId) {
                $q->where('sender_id', Auth::id())->where('receiver_id', $withUserId);
            })->orWhere(function ($q) use ($withUserId) {
                $q->where('sender_id', $withUserId)->where('receiver_id', Auth::id());
            })->when($since, fn($q) => $q->where('id', '>', $since))
            ->with('sender')
            ->orderBy('created_at')
            ->take(50)->get()
            ->map(fn($m) => [
                'id'         => $m->id,
                'body'       => $m->body,
                'sender_id'  => $m->sender_id,
                'sender'     => $m->sender->name,
                'created_at' => $m->created_at->format('g:i A'),
                'is_mine'    => $m->sender_id === Auth::id(),
            ]);

        // Mark new ones as read
        Message::where('sender_id', $withUserId)
            ->where('receiver_id', Auth::id())
            ->where('is_read', false)
            ->update(['is_read' => true]);

        return response()->json($messages);
    }

    public function users()
    {
        $users = User::where('id', '!=', Auth::id())
            ->orderBy('name')
            ->get()
            ->map(fn($u) => [
                'id'        => $u->id,
                'name'      => $u->name,
                'role'      => $u->role,
                'is_online' => $u->isOnline(),
                'unread'    => Message::where('sender_id', $u->id)
                                ->where('receiver_id', Auth::id())
                                ->where('is_read', false)->count(),
            ]);
        return response()->json($users);
    }
}
