<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\StaffActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class ActivityLogController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $query = StaffActivityLog::query()
            ->where('clerk_id', $user?->clerk_id)
            ->orderByDesc('created_at');

        if ($request->filled('action')) {
            $query->where('action', $request->string('action'));
        }
        if ($request->filled('subject_type')) {
            $query->where('subject_type', $request->string('subject_type'));
        }

        $logs = $query->paginate(20)->through(function ($log) {
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
            ];
        });

        return Inertia::render('Staff/ActivityLog', [
            'logs' => $logs,
            'filters' => [
                'action' => $request->string('action')->toString(),
                'subject_type' => $request->string('subject_type')->toString(),
            ],
        ]);
    }
}