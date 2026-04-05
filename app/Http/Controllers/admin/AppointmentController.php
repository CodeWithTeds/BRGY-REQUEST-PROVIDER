<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateAppointmentStatusRequest;
use App\Http\Requests\Admin\RescheduleAppointmentRequest;
use App\Http\Requests\Admin\AppointmentAvailabilityRequest;
use App\Services\AppointmentService;
use Carbon\Carbon;
use Inertia\Inertia;

class AppointmentController extends Controller
{
    public function __construct(protected AppointmentService $service) {}

    public function index(AppointmentAvailabilityRequest $request)
    {
        $year = $request->integer('calendar_year') ?: Carbon::now('Asia/Manila')->year;
        $data = $this->service->indexData($request->only(['status', 'type', 'date_from', 'date_to', 'q']), $year);
        return Inertia::render('Admin/Appointments', $data);
    }

    public function show(int $id)
    {
        return Inertia::render('Admin/AppointmentView', ['appointment' => $this->service->showData($id)]);
    }

    public function updateStatus(UpdateAppointmentStatusRequest $request, int $id)
    {
        $appointment = $this->service->updateStatus($id, $request->validated('status'));
        return redirect()->route('admin.appointments.show', $appointment->id)->with('success', 'Appointment status updated.');
    }

    public function reschedule(RescheduleAppointmentRequest $request, int $id)
    {
        $v     = $request->validated();
        $error = $this->service->reschedule($id, $v['date'], $v['time'], $v['remarks'] ?? null);
        return $error
            ? back()->withErrors(['time' => $error])->withInput()
            : redirect()->route('admin.appointments.show', $id)->with('success', 'Appointment rescheduled successfully.');
    }

    public function availability(AppointmentAvailabilityRequest $request)
    {
        return response()->json($this->service->slotAvailability($request->validated('date')));
    }
}
