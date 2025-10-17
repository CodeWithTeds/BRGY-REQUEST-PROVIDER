<?php

namespace App\Http\Controllers\Resident;

use App\Http\Controllers\Controller;
use App\Http\Requests\Resident\CertificateOfResidencyRequest;
use App\Models\CertificateOfResidency;
use App\Models\SupportingDocument;
use App\Models\Appointment;
use App\Repositories\CertificateOfResidencyRepository;
use App\Repositories\PSGCRepository;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request as BaseRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Http\Resources\ResidencyCertificateDetailResource;

class CertificateOfResidencyController extends Controller
{
    public function __construct(
        private readonly CertificateOfResidencyRepository $certificateOfResidencyRepository,
        private readonly PSGCRepository $psgcRepository,
    ) {}

    public function create()
    {
        $pending = $this->certificateOfResidencyRepository->getPending(Auth::id());

        if ($pending) {
            return Inertia::render('Resident/CertificateOfResidency/Pending', [
                'residency' => $pending,
            ]);
        }

        // If latest residency is approved or rejected, show status message page
        $latest = $this->certificateOfResidencyRepository->getLatest(Auth::id());
        if ($latest && in_array($latest->status, ['approved', 'rejected'])) {
            $latestAppointment = method_exists($latest, 'appointments') ? $latest->appointments()->orderByDesc('appointment_at')->first() : null;
            $appointmentsCount = method_exists($latest, 'appointments') ? $latest->appointments()->count() : 0;

            return Inertia::render('Resident/CertificateOfResidency/StatusMessage', [
                'residency' => [
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

        return Inertia::render('Resident/CertificateOfResidency/Create', [
            'regions' => $this->psgcRepository->getRegions(),
            'applicantProfile' => $apData,
        ]);
    }

    public function store(CertificateOfResidencyRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $cert = $this->certificateOfResidencyRepository->createApplication($data, Auth::id());
        // Persist applicant profile to user
        $user = Auth::user();
        $profile = $user->applicantProfile()->firstOrNew();
        $profile->fill([
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
        ]);
        $profile->user_id = $user->id;
        $profile->save();
        // Persist address to user for selected address_type
        $addressValues = [
            'type' => $data['address_type'] ?? 'present',
            'house_no' => $data['house_no'] ?? null,
            'street' => $data['street'] ?? null,
            'purok' => $data['purok'] ?? null,
            'barangay_code' => $data['barangay_code'] ?? null,
            'city_code' => $data['city_code'] ?? null,
            'province_code' => $data['province_code'] ?? null,
            'region_code' => $data['region_code'] ?? null,
            'zip_code' => $data['zip_code'] ?? null,
        ];
        $existingAddr = $user->addresses()->where('type', $addressValues['type'])->first();
        if ($existingAddr) {
            $existingAddr->fill($addressValues);
            $existingAddr->save();
        } else {
            $user->addresses()->create($addressValues);
        }
        // Handle supporting documents if they exist; keys must match request inputs
        /** @var BaseRequest $req */
        $req = $request;

        if ($req->hasFile('valid_government_id_document')) {
            $path = $req->file('valid_government_id_document')->store('supporting_documents', 'public');
            SupportingDocument::create([
                'user_id' => Auth::id(),
                'certificate_of_residency_id' => $cert->id,
                'document_type' => 'valid_government_id',
                'file_path' => $path,
                'verified' => false,
            ]);
        }

        if ($req->hasFile('proof_of_residence_document')) {
            $path = $req->file('proof_of_residence_document')->store('supporting_documents', 'public');
            SupportingDocument::create([
                'user_id' => Auth::id(),
                'certificate_of_residency_id' => $cert->id,
                'document_type' => 'proof_of_residence',
                'file_path' => $path,
                'verified' => false,
            ]);
        }

        if ($req->hasFile('lease_contract_document')) {
            $path = $req->file('lease_contract_document')->store('supporting_documents', 'public');
            SupportingDocument::create([
                'user_id' => Auth::id(),
                'certificate_of_residency_id' => $cert->id,
                'document_type' => 'lease_contract',
                'file_path' => $path,
                'verified' => false,
            ]);
        }

        if ($req->hasFile('authorization_letter_document')) {
            $path = $req->file('authorization_letter_document')->store('supporting_documents', 'public');
            SupportingDocument::create([
                'user_id' => Auth::id(),
                'certificate_of_residency_id' => $cert->id,
                'document_type' => 'authorization_letter',
                'file_path' => $path,
                'verified' => false,
            ]);
        }

        return redirect()->route('resident.certificate-of-residency.create')
            ->with('success', 'Certificate of Residency application submitted successfully.');
    }

    public function schedule()
    {
        $latest = $this->certificateOfResidencyRepository->getLatest(Auth::id());

        if (!$latest || $latest->status !== 'approved') {
            return redirect()->route('resident.certificate-of-residency.create')
                ->with('error', 'Scheduling is only available after approval.');
        }

        $latestAppointment = $latest->appointments()->orderByDesc('appointment_at')->first();
        $appointmentsCount = $latest->appointments()->count();

        return Inertia::render('Resident/CertificateOfResidency/Schedule', [
            'residency' => [
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
            'residency_id' => ['required', 'integer', 'exists:certificate_of_residencies,id'],
            'date' => ['required', 'date', 'after_or_equal:today'],
            'time' => ['required', 'date_format:H:i'],
        ]);

        $residency = CertificateOfResidency::query()
            ->where('id', $data['residency_id'])
            ->where('user_id', Auth::id())
            ->firstOrFail();

        if ($residency->status !== 'approved') {
            return redirect()->route('resident.certificate-of-residency.create')
                ->with('error', 'Scheduling is only available for approved residency certificates.');
        }

        // Enforce: only one reschedule allowed (max 2 appointments total)
        $appointmentsCount = $residency->appointments()->count();
        if ($appointmentsCount >= 2) {
            return redirect()->route('resident.certificate-of-residency.schedule')
                ->with('error', 'Rescheduling is allowed only once. Please contact your barangay office for further changes.');
        }

        // Interpret user input as Asia/Manila and convert to UTC for storage
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
        $residency->appointments()->create([
            'appointment_at' => $dt,
            'status' => 'scheduled',
        ]);

        return redirect()->route('resident.certificate-of-residency.create')
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
     * Download an approved certificate of residency as PDF for the current resident.
     */
    public function downloadPdf(int $id)
    {
        $certificate = $this->certificateOfResidencyRepository->getWithAllRelations($id);

        if ($certificate->user_id !== Auth::id()) {
            abort(403);
        }

        if ($certificate->status !== 'approved') {
            return redirect()->route('resident.certificate-of-residency.create')
                ->with('error', 'PDF is available only after approval.');
        }

        $data = (new ResidencyCertificateDetailResource($certificate))->toArray(request());
        $viewData = [
            'certificate' => $data,
            'logoPath' => public_path('images/brg.png'),
        ];

        $pdf = Pdf::setPaper('A4')->loadView('pdf.certificate_of_residency', $viewData);
        return $pdf->download('certificate-of-residency-' . $certificate->id . '.pdf');
    }
}
