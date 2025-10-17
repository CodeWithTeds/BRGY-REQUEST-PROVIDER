<?php

namespace App\Services;

use App\Models\StaffActivityLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ActivityLogger
{
    /**
     * Record a staff activity log entry.
     *
     * @param string $action Short action key, e.g. 'status_updated'
     * @param Model|array|null $subject Model instance being acted on, or array with 'type' and 'id'.
     * @param array $metadata Arbitrary metadata payload to store as JSON.
     * @param string|null $description Optional human-readable description.
     */
    public static function log(string $action, Model|array|null $subject = null, array $metadata = [], ?string $description = null): void
    {
        try {
            $user = Auth::user();
            $subjectType = null;
            $subjectId = null;
            if ($subject instanceof Model) {
                $subjectType = get_class($subject);
                $subjectId = method_exists($subject, 'getKey') ? $subject->getKey() : null;
            } elseif (is_array($subject)) {
                $subjectType = $subject['type'] ?? null;
                $subjectId = $subject['id'] ?? null;
            }

            StaffActivityLog::create([
                'user_id' => $user?->id,
                'clerk_id' => $user?->clerk_id,
                'action' => $action,
                'subject_type' => $subjectType,
                'subject_id' => $subjectId,
                'description' => $description,
                'metadata' => $metadata,
                'ip_address' => request()->ip(),
                'user_agent' => request()->header('User-Agent'),
            ]);
        } catch (\Throwable $e) {
            // Avoid breaking the flow if table not migrated; log silently
            Log::debug('ActivityLogger failed: '.$e->getMessage());
        }
    }
}