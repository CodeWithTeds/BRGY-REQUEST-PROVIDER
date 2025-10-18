<?php

namespace App\Http\Controllers\Resident;

use App\Http\Controllers\Controller;
use App\Http\Requests\Resident\CertificateOfIndigencyRequest;
use App\Repositories\CertificateOfIndigencyRepository;
use App\Repositories\PSGCRepository;
use App\Models\SupportingDocument;
use App\Models\CertificateOfIndigency;
use App\Models\Appointment;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Http\Resources\IndigencyCertificateDetailResource;

class CertificateOfIndigencyController extends Controller
{
    public function __construct(
        protected CertificateOfIndigencyRepository $repository,
        protected PSGCRepository $psgcRepository,
    ) {}

    public function create()
    {
        $pending = $this->repository->getPending(Auth::id());

        if ($pending) {
            return Inertia::render('Resident/CertificateOfIndigency/Pending', [
                'indigency' => $pending,
            ]);
        }

        // If latest indigency is approved or rejected, show status message page
        $latest = $this->repository->getLatest(Auth::id());
        if ($latest && in_array($latest->status, ['approved', 'rejected'])) {
            $latestAppointment = method_exists($latest, 'appointments') ? $latest->appointments()->orderByDesc('appointment_at')->first() : null;
            $appointmentsCount = method_exists($latest, 'appointments') ? $latest->appointments()->count() : 0;

            return Inertia::render('Resident/CertificateOfIndigency/StatusMessage', [
                'indigency' => [
                    'id' => $latest->id,
                    'status' => $latest->status,
                    'remarks' => $latest->remarks,
                    'application_date' => $latest->application_date,
                    'appointment_at' => $latestAppointment?->appointment_at?->copy()?->setTimezone('Asia/Manila')?->toIso8601String(),
                ],
                'rescheduleAllowed' => $appointmentsCount < 2,
            ]);
        }

        // Prefill applicant profile data if available
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

        return Inertia::render('Resident/CertificateOfIndigency/Create', [
            'regions' => $this->psgcRepository->getRegions(),
            'applicantProfile' => $apData,
        ]);
    }

    public function store(CertificateOfIndigencyRequest $request)
    {
        /** @var Request $request */
        $data = $request->validated();
        $cert = $this->repository->createApplication($data, Auth::id());

        // Persist applicant profile to user (if provided)
        $user = Auth::user();
        $profileData = [
            'first_name' => $data['first_name'] ?? null,
            'middle_name' => $data['middle_name'] ?? null,
            'last_name' => $data['last_name'] ?? null,
            'suffix' => $data['suffix'] ?? null,
            'date_of_birth' => $data['date_of_birth'] ?? null,
            'place_of_birth' => $data['place_of_birth'] ?? null,
            'civil_status' => $data['civil_status'] ?? null,
            'gender' => $data['gender'] ?? null,
            'citizenship' => $data['citizenship'] ?? null,
            'contact_number' => $data['contact_number'] ?? null,
        ];
        $presentProfileData = array_filter($profileData, fn($v) => !is_null($v) && $v !== '');
        if (!empty($presentProfileData)) {
            $profile = $user->applicantProfile()->first();
            if ($profile) {
                // Only update fields provided; do not overwrite existing values with nulls
                $profile->fill($presentProfileData);
                $profile->save();
            } else {
                // Create profile only if all mandatory fields are present
                $required = ['first_name','last_name','date_of_birth','place_of_birth','civil_status','gender','citizenship','contact_number'];
                $hasRequired = collect($required)->every(fn($k) => array_key_exists($k, $presentProfileData));
                if ($hasRequired) {
                    $user->applicantProfile()->create($presentProfileData);
                }
            }
        }

        // Persist address to user for selected address_type (if provided)
        $addressValues = [
            'type' => $data['address_type'] ?? null,
            'house_no' => $data['house_no'] ?? null,
            'street' => $data['street'] ?? null,
            'purok' => $data['purok'] ?? null,
            'barangay_code' => $data['barangay_code'] ?? null,
            'city_code' => $data['city_code'] ?? null,
            'province_code' => $data['province_code'] ?? null,
            'region_code' => $data['region_code'] ?? null,
            'zip_code' => $data['zip_code'] ?? null,
        ];
        if ($addressValues['type']) {
            $existingAddr = $user->addresses()->where('type', $addressValues['type'])->first();
            if ($existingAddr) {
                $existingAddr->fill($addressValues);
                $existingAddr->save();
            } else {
                $user->addresses()->create($addressValues);
            }
        }

        // Require and store only one valid government ID
        if ($request->hasFile('valid_government_id_document')) {
            $path = $request->file('valid_government_id_document')->store('supporting-documents', 'public');
            \App\Models\SupportingDocument::create([
                'user_id' => Auth::id(),
                'certificate_of_indigency_id' => $cert->id,
                'document_type' => 'valid_government_id',
                'file_path' => $path,
                'verified' => false,
            ]);
        }

        // Optional: Proof of Income document
        if ($request->hasFile('proof_of_income_document')) {
            $path = $request->file('proof_of_income_document')->store('supporting-documents', 'public');
            \App\Models\SupportingDocument::create([
                'user_id' => Auth::id(),
                'certificate_of_indigency_id' => $cert->id,
                'document_type' => 'proof_of_income',
                'file_path' => $path,
                'verified' => false,
            ]);
        }

        return redirect()->route('resident.certificate-of-indigency.create');
    }

    public function schedule()
    {
        $latest = $this->repository->getLatest(Auth::id());

        if (!$latest || $latest->status !== 'approved') {
            return redirect()->route('resident.certificate-of-indigency.create')
                ->with('error', 'Scheduling is only available after approval.');
        }

        $latestAppointment = $latest->appointments()->orderByDesc('appointment_at')->first();
        $appointmentsCount = $latest->appointments()->count();

        return Inertia::render('Resident/CertificateOfIndigency/Schedule', [
            'indigency' => [
                'id' => $latest->id,
                'status' => $latest->status,
                'application_date' => $latest->application_date,
                'appointment_at' => $latestAppointment ? optional($latestAppointment->appointment_at)->copy()->setTimezone('Asia/Manila')->toIso8601String() : null,
            ],
            'rescheduleAllowed' => $appointmentsCount < 2,
        ]);
    }

    public function scheduleStore(Request $request)
    {
        $data = $request->validate([
            'indigency_id' => ['required', 'integer', 'exists:certificate_of_indigencies,id'],
            'date' => ['required', 'date', 'after_or_equal:today'],
            'time' => ['required', 'date_format:H:i'],
        ]);

        $indigency = CertificateOfIndigency::query()
            ->where('id', $data['indigency_id'])
            ->where('user_id', Auth::id())
            ->firstOrFail();

        if ($indigency->status !== 'approved') {
            return redirect()->route('resident.certificate-of-indigency.create')
                ->with('error', 'Scheduling is only available for approved indigency certificates.');
        }

        // Enforce: only one reschedule allowed (max 2 appointments total)
        $appointmentsCount = $indigency->appointments()->count();
        if ($appointmentsCount >= 2) {
            return redirect()->route('resident.certificate-of-indigency.schedule')
                ->with('error', 'Rescheduling is allowed only once. Please contact your barangay office for further changes.');
        }

        // Interpret user input as Asia/Manila and convert to UTC for storage
        $dtLocal = Carbon::createFromFormat('Y-m-d H:i', $data['date'].' '.$data['time'], 'Asia/Manila');
        $dt = $dtLocal->copy()->setTimezone('UTC');
        
        // Block weekends
        if ($dtLocal->isWeekend()) {
            return back()->withErrors(['date' => 'Appointments are only Monday–Friday.'])->withInput();
        }
        
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
        $indigency->appointments()->create([
            'appointment_at' => $dt,
            'status' => 'scheduled',
        ]);

        return redirect()->route('resident.certificate-of-indigency.create')
            ->with('success', 'Appointment scheduled successfully.');
    }

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

    /**
     * Download an approved certificate of indigency as PDF for the current resident.
     */
    public function downloadPdf(int $id)
    {
        $certificate = $this->repository->getWithAllRelations($id);

        if ($certificate->user_id !== Auth::id()) {
            abort(403);
        }

        if ($certificate->status !== 'approved') {
            return redirect()->route('resident.certificate-of-indigency.create')
                ->with('error', 'PDF is available only after approval.');
        }

        $data = (new IndigencyCertificateDetailResource($certificate))->toArray(request());
        $viewData = [
            'certificate' => $data,
            'logoPath' => public_path('images/brg.png'),
        ];

        $pdf = Pdf::setPaper('A4')->loadView('pdf.certificate_of_indigency', $viewData);
        return $pdf->download('certificate-of-indigency-' . $certificate->id . '.pdf');
    }
}