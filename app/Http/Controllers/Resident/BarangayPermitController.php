<?php

namespace App\Http\Controllers\Resident;

use App\Http\Controllers\Controller;
use App\Http\Requests\Resident\StoreBarangayPermitRequest;
use App\Repositories\PSGCRepository;
use App\Repositories\BussinessPermitRepository;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Inertia\Inertia;

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
            return Inertia::render('Resident/BarangayPermit/StatusMessage', [
                'permit' => $latest,
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
}
