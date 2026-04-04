<?php

namespace App\Services;

use App\Enums\AppointmentType;
use App\Repositories\ActivityLogRepository;
use App\Models\StaffActivityLog;

class ActivityLogService
{
    public function __construct(protected ActivityLogRepository $repo) {}

    /** Return paginated logs with UI-ready shape plus sidebar filters. */
    public function paginatedList(array $filters): array
    {
        $paginator = $this->repo->listWithFilters($filters);

        $logs = $paginator->through(fn($log) => $this->formatLog($log));

        return ['logs' => $logs, 'filters' => $filters];
    }

    /** Revert a status-update log entry back to the previous status. */
    public function revert(int $logId): array
    {
        /** @var StaffActivityLog $activityLog */
        $activityLog = $this->repo->model()->newQuery()->findOrFail($logId);

        if ($activityLog->action !== 'status_updated') {
            return ['error' => 'Only status updates can be reverted.'];
        }

        $subjectType = $activityLog->subject_type;
        $subjectId   = $activityLog->subject_id;

        if (!$subjectType || !$subjectId) {
            return ['error' => 'Missing subject for log entry.'];
        }
        if (!class_exists($subjectType)) {
            return ['error' => 'Unknown subject type: ' . $subjectType];
        }

        $model = $subjectType::find($subjectId);
        if (!$model) {
            return ['error' => 'Subject record not found.'];
        }

        $meta = is_array($activityLog->metadata) ? $activityLog->metadata : [];
        $from = $meta['from'] ?? null;
        $to   = $meta['to']   ?? null;

        if ($from === null || $to === null) {
            return ['error' => 'Log entry lacks status change metadata.'];
        }

        $current = $model->getAttribute('status');
        if ($current !== $to) {
            return ['error' => 'Subject status changed since; cannot revert.'];
        }

        $model->setAttribute('status', $from);
        $model->save();

        $activityLog->metadata    = array_merge($meta, ['reverted' => true, 'reverted_at' => now()->toDateTimeString()]);
        $activityLog->description = 'Status update reverted';
        $activityLog->save();

        ActivityLogger::log('status_reverted', $model, [
            'from'            => $current,
            'to'              => $from,
            'reverted_log_id' => $activityLog->id,
        ], 'Admin reverted clerk status update');

        return ['success' => 'Status reverted to ' . $from];
    }

    private function formatLog(StaffActivityLog $log): array
    {
        return [
            'id'           => $log->id,
            'action'       => $log->action,
            'subject_type' => $log->subject_type,
            'subject_id'   => $log->subject_id,
            'description'  => $log->description,
            'metadata'     => $log->metadata,
            'ip_address'   => $log->ip_address,
            'user_agent'   => $log->user_agent,
            'created_at'   => optional($log->created_at)->toDateTimeString(),
            'user'         => $log->user ? ['id' => $log->user->id, 'name' => $log->user->name, 'email' => $log->user->email] : null,
            'clerk'        => $log->clerk ? ['id' => $log->clerk->id, 'name' => $log->clerk->name ?? $log->user?->name] : null,
        ];
    }
}
