<?php

namespace App\Http\Controllers\Resident;

use App\Http\Controllers\Controller;
use App\Http\Requests\Resident\StoreBarangayPermitRequest;
use App\Repositories\PSGCRepository;
use App\Repositories\BussinessPermitRepository;
use App\Http\Resources\BarangayPermitDetailResource;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Inertia\Inertia;
use App\Models\Appointment;

class BarangayPermitController extends Controller
{   
    public function __construct(
        protected PSGCRepository $psgcRepository,
        protected BussinessPermitRepository $bussinessPermitRepository
    ) {}

    public function create()
    {
        $pendingPermit = $this->bussinessPermitRepository->getPendingPermit(Auth::id());

        if ($pendingPermit) {
            return Inertia::render('Resident/BarangayPermit/Pending', [
                'permit' => $pendingPermit,
            ]);
        }

        // If there is an approved or rejected latest permit, show a status message instead of create form
        $latest = $this->bussinessPermitRepository->getLatestPermit(Auth::id());
        if ($latest && in_array($latest->status, ['approved', 'rejected'])) {
            // Determine if reschedule is allowed (max 1 reschedule; i.e., 2 total appointments)
            $latestAppointment = method_exists($latest, 'appointments') ? $latest->appointments()->orderByDesc('appointment_at')->first() : null;
            $appointmentsCount = method_exists($latest, 'appointments') ? $latest->appointments()->count() : 0;

            return Inertia::render('Resident/BarangayPermit/StatusMessage', [
                'permit' => [
                    'id' => $latest->id,
                    'status' => $latest->status,
                    'remarks' => $latest->remarks,
                    'application_date' => $latest->application_date,
                    // Prefer appointments table; fallback to legacy column. Display in Asia/Manila. Use null-safe chaining.
                    'appointment_at' => ($latestAppointment?->appointment_at ?? $latest->appointment_at)?->copy()?->setTimezone('Asia/Manila')?->toIso8601String(),
                ],
                'rescheduleAllowed' => $appointmentsCount < 2,
            ]);
        }

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

        return Inertia::render('Resident/BarangayPermit/Create', [
            'barangays' => $this->psgcRepository->getBarangaysByIslandGroup(),
            'regions' => $this->psgcRepository->getRegions(),
            'applicantProfile' => $apData,
        ]);
    }

    /**
     * Show appointment scheduling page for approved permits.
     */
    public function schedule(Request $request)
    {
        $latest = $this->bussinessPermitRepository->getLatestPermit(Auth::id());

        if (!$latest || $latest->status !== 'approved') {
            return redirect()->route('barangay-permit.create')
                ->with('error', 'Scheduling is only available after approval.');
        }

        $latestAppointment = $latest->appointments()->orderByDesc('appointment_at')->first();
        $appointmentsCount = $latest->appointments()->count();

        return Inertia::render('Resident/BarangayPermit/Schedule', [
            'permit' => [
                'id' => $latest->id,
                'status' => $latest->status,
                'application_date' => $latest->application_date,
                // Prefer the appointments table; fallback to legacy column for compatibility. Display in Asia/Manila. Use null-safe chaining.
                'appointment_at' => ($latestAppointment?->appointment_at ?? $latest->appointment_at)?->copy()?->setTimezone('Asia/Manila')?->toIso8601String(),
            ],
            'rescheduleAllowed' => $appointmentsCount < 2,
        ]);
    }

    /**
     * Store appointment schedule between 08:00 and 17:00.
     */
    public function scheduleStore(Request $request)
    {
        $data = $request->validate([
            'permit_id' => ['required', 'integer', 'exists:barangay_permits,id'],
            'date' => ['required', 'date', 'after_or_equal:today'],
            'time' => ['required', 'date_format:H:i'],
        ]);

        $permit = \App\Models\BarangayPermit::query()
            ->where('id', $data['permit_id'])
            ->where('user_id', Auth::id())
            ->firstOrFail();

        if ($permit->status !== 'approved') {
            return redirect()->route('barangay-permit.create')
                ->with('error', 'Scheduling is only available for approved permits.');
        }

        // Enforce: only one reschedule allowed (max 2 appointments total)
        $appointmentsCount = method_exists($permit, 'appointments') ? $permit->appointments()->count() : 0;
        if ($appointmentsCount >= 2) {
            return redirect()->route('barangay-permit.schedule')
                ->with('error', 'Rescheduling is allowed only once. Please contact your barangay office for further changes.');
        }

        $dtLocal = Carbon::createFromFormat('Y-m-d H:i', $data['date'].' '.$data['time'], 'Asia/Manila');
        $dt = $dtLocal->copy()->setTimezone('UTC');
        $hhmm = $dtLocal->format('H:i');
        if ($hhmm < '08:00' || $hhmm > '17:00') {
            return back()->withErrors(['time' => 'Appointment must be between 08:00 and 17:00.'])
                ->withInput();
        }

        // Prevent double-booking: reject if slot already taken
        $conflict = Appointment::query()
            ->where('appointment_at', $dt)
            ->where('status', 'scheduled')
            ->exists();
        if ($conflict) {
            return back()->withErrors(['time' => 'Selected time slot is already taken. Please choose another time.'])
                ->withInput();
        }

        // Create appointment record in the shared appointments table
        $permit->appointments()->create([
            'appointment_at' => $dt,
            'status' => 'scheduled',
        ]);

        // Keep legacy column updated for existing front-end compatibility
        $permit->appointment_at = $dt;
        $permit->save();

        return redirect()->route('barangay-permit.create')
            ->with('success', 'Appointment scheduled successfully.');
    }

    /**
     * JSON availability for a given local date (Asia/Manila). Returns occupied HH:mm slots.
     */
    public function availability(Request $request)
    {
        $request->validate([
            'date' => ['required', 'date'],
        ]);

        $date = $request->input('date');
        $dayLocalStart = Carbon::createFromFormat('Y-m-d H:i', $date.' 00:00', 'Asia/Manila');
        $dayLocalEnd = Carbon::createFromFormat('Y-m-d H:i', $date.' 23:59', 'Asia/Manila');
        $startUtc = $dayLocalStart->copy()->setTimezone('UTC');
        $endUtc = $dayLocalEnd->copy()->setTimezone('UTC');

        $occupied = Appointment::query()
            ->whereBetween('appointment_at', [$startUtc, $endUtc])
            ->where('status', 'scheduled')
            ->get()
            ->map(function ($appt) {
                return optional($appt->appointment_at)->copy()->setTimezone('Asia/Manila')->format('H:i');
            })
            ->unique()
            ->values();

        return response()->json(['occupied' => $occupied]);
    }

    public function store(StoreBarangayPermitRequest $request)
    {
        try {
            /** @var Request $request */
            $validated = $request->validated();
            $user = Auth::user();

            // Create the main permit and related base records
            $permit = $this->bussinessPermitRepository->createPermitApplication($validated, $user->id);

            // Save optional additional supporting documents as separate records
            if ($request->hasFile('valid_id_document')) {
                $this->bussinessPermitRepository->createSupportingDocumentForPermit(
                    $user->id,
                    $permit->id,
                    $request->file('valid_id_document'),
                    'valid_id'
                );
            }
            if ($request->hasFile('barangay_clearance_business_document')) {
                $this->bussinessPermitRepository->createSupportingDocumentForPermit(
                    $user->id,
                    $permit->id,
                    $request->file('barangay_clearance_business_document'),
                    'barangay_clearance_business'
                );
            }
            if ($request->hasFile('lease_contract_document')) {
                $this->bussinessPermitRepository->createSupportingDocumentForPermit(
                    $user->id,
                    $permit->id,
                    $request->file('lease_contract_document'),
                    'lease_contract'
                );
            }

            return redirect()->route('resident.dashboard')
                ->with('success', 'Barangay permit application submitted successfully.');
        } catch (\Exception $e) {
            report($e);
            return redirect()->back()
                ->withInput()
                ->withErrors(['error' => 'Failed to submit barangay permit application: ' . $e->getMessage()]);
        }
    }

    public function barangaysByIslandGroup(string $code)
    {
        return response()->json([
            'barangays' => $this->psgcRepository->getBarangaysByIslandGroup(),
        ]);
    }

    public function barangaysByCity(string $code)
    {
        return response()->json($this->psgcRepository->getBarangaysByCity($code));
    }

    public function regions()
    {
        return response()->json($this->psgcRepository->getRegions());
    }

    public function provincesByRegion(string $code)
    {
        return response()->json($this->psgcRepository->getProvincesByRegion($code));
    } 

    public function citiesByProvince(string $code)
    {
        return response()->json($this->psgcRepository->getCitiesByProvince($code));
    }

    public function citiesByRegion(string $code)
    {
        return response()->json($this->psgcRepository->getCitiesByRegion($code));
    }

    /**
     * Download an approved barangay permit as PDF for the current resident.
     */
    public function downloadPdf(int $id)
    {
        $permit = $this->bussinessPermitRepository->getWithAllRelations($id);

        if ($permit->user_id !== Auth::id()) {
            abort(403);
        }

        // Only allow PDF download when approved
        if ($permit->status !== 'approved') {
            return redirect()->route('barangay-permit.create')
                ->with('error', 'PDF is available only after approval.');
        }

        $data = (new BarangayPermitDetailResource($permit))->toArray(request());
        $viewData = [
            'permit' => $data,
            // Use the barangay seal image from public/images; replace file as needed
            'logoPath' => public_path('images/brg.png'),
        ];

        $pdf = Pdf::setPaper('A4')->loadView('pdf.barangay_permit', $viewData);
        return $pdf->download('barangay-permit-' . $permit->id . '.pdf');
    }

}
