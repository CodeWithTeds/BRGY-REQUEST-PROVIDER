<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\StaffActivityLog;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ActivityLogController extends Controller
{
    public function index(Request $request)
    {
        $query = StaffActivityLog::query()->with(['user', 'clerk']);

        if ($request->filled('action')) {
            $query->where('action', $request->string('action'));
        }
        if ($request->filled('subject_type')) {
            $query->where('subject_type', $request->string('subject_type'));
        }
        if ($request->filled('clerk_id')) {
            $query->where('clerk_id', $request->integer('clerk_id'));
        }
        if ($request->filled('date_from')) {
            $query->where('created_at', '>=', $request->date('date_from')->format('Y-m-d').' 00:00:00');
        }
        if ($request->filled('date_to')) {
            $query->where('created_at', '<=', $request->date('date_to')->format('Y-m-d').' 23:59:59');
        }

        $logs = $query->orderByDesc('created_at')->paginate(20)->through(function ($log) {
            return [
                'id' => $log->id,
                'action' => $log->action,
                'subject_type' => $log->subject_type,
                'subject_id' => $log->subject_id,
                'description' => $log->description,
                'metadata' => $log->metadata,
                'ip_address' => $log->ip_address,
                'user_agent' => $log->user_agent,
                'created_at' => optional($log->created_at)->toDateTimeString(),
                'user' => $log->user ? [
                    'id' => $log->user->id,
                    'name' => $log->user->name,
                    'email' => $log->user->email,
                ] : null,
                'clerk' => $log->clerk ? [
                    'id' => $log->clerk->id,
                    'name' => $log->clerk->name ?? $log->user?->name,
                ] : null,
            ];
        });

        return Inertia::render('Admin/ActivityLog', [
            'logs' => $logs,
            'filters' => [
                'action' => $request->get('action'),
                'subject_type' => $request->get('subject_type'),
                'clerk_id' => $request->get('clerk_id'),
                'date_from' => $request->get('date_from'),
                'date_to' => $request->get('date_to'),
            ],
        ]);
    }
}