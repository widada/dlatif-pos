<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ActivityLogController extends Controller
{
    public function index(Request $request): Response
    {
        $logs = ActivityLog::with('user:id,name,username,role')
            ->when($request->input('user_id'), fn ($q, $id) => $q->where('user_id', $id))
            ->when($request->input('module'), fn ($q, $m) => $q->where('module', $m))
            ->when($request->input('action'), fn ($q, $a) => $q->where('action', $a))
            ->when($request->input('search'), fn ($q, $s) => $q->where('description', 'like', "%{$s}%"))
            ->when($request->input('start_date'), fn ($q, $d) => $q->where('created_at', '>=', $d))
            ->when($request->input('end_date'), fn ($q, $d) => $q->where('created_at', '<=', $d.' 23:59:59'))
            ->orderByDesc('created_at')
            ->paginate(30)
            ->withQueryString();

        $users = User::orderBy('name')->get(['id', 'name', 'username']);
        $modules = ActivityLog::distinct()->pluck('module')->sort()->values();
        $actions = ActivityLog::distinct()->pluck('action')->sort()->values();

        return Inertia::render('ActivityLogs/Index', [
            'logs' => $logs,
            'users' => $users,
            'modules' => $modules,
            'actions' => $actions,
            'filters' => [
                'user_id' => $request->input('user_id', ''),
                'module' => $request->input('module', ''),
                'action' => $request->input('action', ''),
                'search' => $request->input('search', ''),
                'start_date' => $request->input('start_date', ''),
                'end_date' => $request->input('end_date', ''),
            ],
        ]);
    }
}
