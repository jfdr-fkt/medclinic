<?php
namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuditLogController extends Controller
{
    public function index(Request $request)
    {
        if (!Auth::user()->can_('audit.view')) abort(403, 'You cannot view the audit trail.');

        $query = ActivityLog::with(['user', 'patient'])->latest();

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }
        if ($request->filled('action')) {
            $query->where('action', $request->action);
        }
        if ($request->filled('search')) {
            $term = $request->search;
            $query->where(function ($q) use ($term) {
                $q->where('details', 'LIKE', "%{$term}%")
                  ->orWhereHas('user', fn($u) => $u->where('name', 'LIKE', "%{$term}%"));
            });
        }

        $range = $request->get('range');
        $from = $request->get('from');
        $to = $request->get('to');
        if ($range && $range !== 'custom') {
            $now = now();
            switch ($range) {
                case 'today':
                    $query->whereDate('created_at', $now->toDateString());
                    break;
                case 'yesterday':
                    $query->whereDate('created_at', $now->copy()->subDay()->toDateString());
                    break;
                case '7d':
                    $query->where('created_at', '>=', $now->copy()->subDays(7));
                    break;
                case '30d':
                    $query->where('created_at', '>=', $now->copy()->subDays(30));
                    break;
            }
        } elseif ($range === 'custom') {
            if ($from) $query->whereDate('created_at', '>=', $from);
            if ($to) $query->whereDate('created_at', '<=', $to);
        }

        $logs = $query->paginate(25)->withQueryString();
        $users = User::orderBy('name')->get();
        $actions = ActivityLog::select('action')->distinct()->pluck('action');

        return view('audit.index', compact('logs', 'users', 'actions', 'range', 'from', 'to'));
    }
}
