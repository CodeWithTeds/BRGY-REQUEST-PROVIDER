<?php

namespace App\Http\Controllers\Resident;

use App\Http\Controllers\Controller;
use App\Http\Requests\Resident\StoreBarangayPermitRequest;
use App\Repositories\PSGCRepository;
use App\Repositories\BussinessPermitRepository;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class BarangayPermitController extends Controller
{   
    public function __construct(
        protected PSGCRepository $psgcRepository,
        protected BussinessPermitRepository $bussinessPermitRepository
    ) {}

    public function create()
    {
        return Inertia::render('Resident/BarangayPermit/Create', [
            'barangays' => $this->psgcRepository->getBarangaysByIslandGroup(),
            'regions' => $this->psgcRepository->getRegions(),
        ]);
    }

    public function store(StoreBarangayPermitRequest $request)
    {
        try {
            $validated = $request->validated();
            $user = Auth::user();

            $this->bussinessPermitRepository->createPermitApplication($validated, $user->id);

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
