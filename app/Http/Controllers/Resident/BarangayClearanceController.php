<?php

namespace App\Http\Controllers\Resident;

use App\Http\Controllers\Controller;
use App\Http\Requests\Resident\StoreBarangayClearanceRequest;
use App\Repositories\PSGCRepository;
use App\Repositories\BarangayClearanceRepository;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use App\Http\Resources\BarangayClearanceDetailResource;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Carbon;
use App\Models\Appointment;
use App\Models\AvailabilityWindow;

class BarangayClearanceController extends Controller
{
    public function __construct(
        protected PSGCRepository $psgcRepository,
        protected BarangayClearanceRepository $barangayClearanceRepository
    ) {}

    public function index()
    {
        return Inertia::render('Resident/BarangayClearance/Index', [
            'clearances' => $this->barangayClearanceRepository->getClearances(Auth::id())
        ]);
    }

    public function create()
    {
        $pendingClearance = $this->barangayClearanceRepository->getPendingClearance(Auth::id());

        if ($pendingClearance) {
            return Inertia::render('Resident/BarangayClearance/Pending', [
                'clearance' => $pendingClearance,
            ]);
        }

        // If latest clearance is approved or rejected, show status message page
        $latest = $this->barangayClearanceRepository->getLatestClearance(Auth::id());
        if ($latest && in_array($latest->status, ['approved', 'rejected'])) {
            $latestAppointment = method_exists($latest, 'appointments') ? $latest->appointments()->orderByDesc('appointment_at')->first() : null;
            $appointmentsCount = method_exists($latest, 'appointments') ? $latest->appointments()->count() : 0;

            return Inertia::render('Resident/BarangayClearance/StatusMessage', [
                'clearance' => [
                    'id' => $latest->id,
                    'status' => $latest->status,
                    'remarks' => $latest->remarks,
                    'application_date' => $latest->application_date,
                    'issue_date' => $latest->issue_date,
                    'expiry_date' => $latest->expiry_date,
                    'clearance_number' => $latest->clearance_number,
                    // Use null-safe chaining to avoid calling methods on null
                    'appointment_at' => $latestAppointment?->appointment_at?->copy()?->setTimezone('Asia/Manila')?->toIso8601String(),
                    'appointment_status' => $latestAppointment?->status,
                ],
                'rescheduleAllowed' => $appointmentsCount < 2,
            ]);
        }

        // Prefill from user's saved ApplicantProfile when available
        $ap = Auth::user()->applicantProfile;
        $apData = $ap ? [
            'first_name' => $ap->first_name,
            'middle_name' => $ap->middle_name,
            'last_name' => $ap->last_name,
            'suffix' => $ap->suffix,
            'date_of_birth' => optional($ap->date_of_birth)?->toDateString(),
            'place_of_birth' => $ap->place_of_birth,
            'civil_status' => $ap->civil_status,
            'gender' => $ap->gender,
            'citizenship' => $ap->citizenship,
            'contact_number' => $ap->contact_number,
        ] : null;

        return Inertia::render('Resident/BarangayClearance/Create', [
            'barangays' => $this->psgcRepository->getBarangaysByIslandGroup(),
            'regions' => $this->psgcRepository->getRegions(),
            'applicantProfile' => $apData,
        ]);
    }

    public function store(StoreBarangayClearanceRequest $request)
    {
        try {
            $validated = $request->validated();
            $user = Auth::user();

            $clearance = $this->barangayClearanceRepository->createClearanceApplication(
                $validated,
                $user->id
            );
            return redirect()->route('barangay-clearance.show', $clearance->id)
                ->with('success', 'Barangay Clearance application submitted successfully.');
        } catch (\Exception $e) {
            report($e);
            return redirect()->back()
                ->withInput()
                ->withErrors(['error' => 'Failed to submit barangay clearance application: ' . $e->getMessage()]);
        }
    }

    public function show(int $id)
    {
        $clearance = $this->barangayClearanceRepository->getClearance($id, Auth::id());
        $data = (new BarangayClearanceDetailResource($clearance))->toArray(request());

        return Inertia::render('Resident/BarangayClearance/Show', [
            'clearance' => $data,
        ]);
    }

    /**
     * Download an approved barangay clearance as PDF for the current resident.
     */
    public function downloadPdf(int $id)
    {
        $clearance = $this->barangayClearanceRepository->getWithAllRelations($id);

        if ($clearance->user_id !== Auth::id()) {
            abort(403);
        }

        // Only allow PDF download when approved
        if ($clearance->status !== 'approved') {
            return redirect()->route('barangay-clearance.create')
                ->with('error', 'PDF is available only after approval.');
        }

        $data = (new BarangayClearanceDetailResource($clearance))->toArray(request());
        $viewData = [
            'clearance' => $data,
            'logoPath' => public_path('images/brg.png'),
        ];

        $pdf = Pdf::setPaper('A4')->loadView('pdf.barangay_clearance', $viewData);
        return $pdf->download('barangay-clearance-' . $clearance->id . '.pdf');
    }

    /**
     * Show appointment scheduling page for approved clearances.
     */
    public function schedule()
    {
        $latest = $this->barangayClearanceRepository->getLatestClearance(Auth::id());

        if (!$latest || $latest->status !== 'approved') {
            return redirect()->route('barangay-clearance.create')
                ->with('error', 'Scheduling is only available after approval.');
        }

        $latestAppointment = $latest->appointments()->orderByDesc('appointment_at')->first();
        $appointmentsCount = $latest->appointments()->count();

        return Inertia::render('Resident/BarangayClearance/Schedule', [
            'clearance' => [
                'id' => $latest->id,
                'status' => $latest->status,
                'application_date' => $latest->application_date,
                // Use null-safe chaining to avoid calling methods on null
                'appointment_at' => $latestAppointment?->appointment_at?->copy()?->setTimezone('Asia/Manila')?->toIso8601String(),
                'appointment_status' => $latestAppointment?->status,
            ],
            'rescheduleAllowed' => $appointmentsCount < 2,
        ]);
    }

    /**
     * Store appointment schedule between 08:00 and 17:00 for clearances.
     */
    public function scheduleStore(\Illuminate\Http\Request $request)
    {
        $data = $request->validate([
            'clearance_id' => ['required', 'integer', 'exists:barangay_clearances,id'],
            'date' => ['required', 'date', 'after_or_equal:today'],
            'time' => ['required', 'date_format:H:i'],
        ]);

        $clearance = \App\Models\BarangayClearance::query()
            ->where('id', $data['clearance_id'])
            ->where('user_id', Auth::id())
            ->firstOrFail();

        if ($clearance->status !== 'approved') {
            return redirect()->route('barangay-clearance.create')
                ->with('error', 'Scheduling is only available for approved clearances.');
        }

        // Enforce: only one reschedule allowed (max 2 appointments total)
        $appointmentsCount = method_exists($clearance, 'appointments') ? $clearance->appointments()->count() : 0;
        if ($appointmentsCount >= 2) {
            return redirect()->route('barangay-clearance.schedule')
                ->with('error', 'Rescheduling is allowed only once. Please contact your barangay office for further changes.');
        }

        // Interpret user input as Asia/Manila and convert to UTC for storage
        $dtLocal = Carbon::createFromFormat('Y-m-d H:i', $data['date'].' '.$data['time'], 'Asia/Manila');
        // Reject weekends
        if ($dtLocal->isWeekend()) {
            return back()->withErrors(['date' => 'Appointments are only available Monday to Friday.'])->withInput();
        }
        $dt = $dtLocal->copy()->setTimezone('UTC');
        $hhmm = $dtLocal->format('H:i');
        if ($hhmm < '08:00' || $hhmm > '17:00') {
            return back()->withErrors(['time' => 'Appointment must be between 08:00 and 17:00.'])
                ->withInput();
        }

        // Capacity-based booking from configured availability window
        $window = AvailabilityWindow::forDate($data['date']);
        if (!$window) {
            $window = AvailabilityWindow::create([
                'date' => $data['date'],
                'start_time' => '08:00',
                'end_time' => '17:00',
                'slot_interval_minutes' => 30,
                'capacity_per_slot' => 10,
                'is_active' => true,
            ]);
        }
        $capacity = (int)($window->capacity_per_slot);
        $count = Appointment::query()
            ->where('appointment_at', $dt)
            ->where('status', 'scheduled')
            ->count();
        if ($count >= $capacity) {
            return back()->withErrors(['time' => 'Selected time slot is full. Please choose another time.'])
                ->withInput();
        }

        // Create appointment record in the shared appointments table
        $clearance->appointments()->create([
            'appointment_at' => $dt,
            'status' => 'scheduled',
            'availability_window_id' => $window->id,
        ]);

        return redirect()->route('barangay-clearance.create')
            ->with('success', 'Appointment scheduled successfully.');
    }

    /**
     * JSON availability for a given local date (Asia/Manila). Returns occupied HH:mm slots.
     */
    public function availability(\Illuminate\Http\Request $request)
    {
        $request->validate([
            'date' => ['required', 'date'],
        ]);

        $date = $request->input('date');
        $window = AvailabilityWindow::forDate($date);
        $capacity = (int)($window?->capacity_per_slot ?? 10);

        $dayLocalStart = Carbon::createFromFormat('Y-m-d H:i', $date.' 00:00', 'Asia/Manila');
        $dayLocalEnd = Carbon::createFromFormat('Y-m-d H:i', $date.' 23:59', 'Asia/Manila');
        $startUtc = $dayLocalStart->copy()->setTimezone('UTC');
        $endUtc = $dayLocalEnd->copy()->setTimezone('UTC');

        $appointments = Appointment::query()
            ->whereBetween('appointment_at', [$startUtc, $endUtc])
            ->where('status', 'scheduled')
            ->get();

        $counts = $appointments
            ->map(function ($appt) {
                return optional($appt->appointment_at)->copy()->setTimezone('Asia/Manila')->format('H:i');
            })
            ->countBy()
            ->toArray();

        $occupied = collect($counts)
            ->filter(function ($c) use ($capacity) { return $c >= $capacity; })
            ->keys()
            ->values();

        $remaining = collect($counts)
            ->map(function ($c) use ($capacity) { return max($capacity - (int)$c, 0); })
            ->toArray();

        return response()->json([
            'counts' => $counts,
            'capacity' => $capacity,
            'totalScheduled' => $appointments->count(),
            'occupied' => $occupied,
            'remainingPerSlot' => $remaining,
        ]);
    }
}