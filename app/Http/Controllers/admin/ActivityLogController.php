<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ActivityLogFilterRequest;
use App\Services\ActivityLogService;
use Inertia\Inertia;

class ActivityLogController extends Controller
{
    public function __construct(protected ActivityLogService $service) {}

    public function index(ActivityLogFilterRequest $request)
    {
        $data = $this->service->paginatedList($request->filters());
        return Inertia::render('Admin/ActivityLog', $data);
    }

    public function revert(int $log)
    {
        $result = $this->service->revert($log);
        return isset($result['error']) ? back()->with('error', $result['error']) : back()->with('success', $result['success']);
    }
}
