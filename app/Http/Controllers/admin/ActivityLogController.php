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
    public function revert(Request $request, int $log)
    {
        $activityLog = StaffActivityLog::findOrFail($log);

        if ($activityLog->action !== 'status_updated') {
            return back()->with('error', 'Only status updates can be reverted.');
        }

        $subjectType = $activityLog->subject_type;
        $subjectId = $activityLog->subject_id;
        if (!$subjectType || !$subjectId) {
            return back()->with('error', 'Missing subject for log entry.');
        }
        if (!class_exists($subjectType)) {
            return back()->with('error', 'Unknown subject type: '.$subjectType);
        }

        $model = $subjectType::find($subjectId);
        if (!$model) {
            return back()->with('error', 'Subject record not found.');
        }

        $meta = is_array($activityLog->metadata) ? $activityLog->metadata : [];
        $from = $meta['from'] ?? null;
        $to = $meta['to'] ?? null;
        if ($from === null || $to === null) {
            return back()->with('error', 'Log entry lacks status change metadata.');
        }

        $current = $model->getAttribute('status');
        if ($current !== $to) {
            return back()->with('error', 'Subject status changed since; cannot revert.');
        }

        $model->setAttribute('status', $from);
        $model->save();

        \App\Services\ActivityLogger::log('status_reverted', $model, [
            'from' => $current,
            'to' => $from,
            'reverted_log_id' => $activityLog->id,
        ], 'Admin reverted clerk status update');

        return back()->with('success', 'Status reverted to '.$from);
    }
}