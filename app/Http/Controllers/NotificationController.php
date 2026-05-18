<?php
namespace App\Http\Controllers;

use App\Models\UserNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    public function markRead(UserNotification $notification)
    {
        if ($notification->user_id !== Auth::id()) abort(403);
        if (!$notification->read_at) {
            $notification->read_at = now();
            $notification->save();
        }
        return response()->json(['ok' => true]);
    }

    public function markAllRead()
    {
        UserNotification::where('user_id', Auth::id())->whereNull('read_at')->update(['read_at' => now()]);
        return response()->json(['ok' => true]);
    }
}
