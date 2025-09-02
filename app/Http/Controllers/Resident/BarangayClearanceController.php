<?php

namespace App\Http\Controllers\Resident;

use App\Http\Controllers\Controller;
use App\Repositories\PSGCRepository;
use App\Repositories\BarangayClearanceRepository;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Inertia\Inertia;

class BarangayClearanceController extends Controller
{
    public function __construct(
        protected PSGCRepository $psgcRepository,
        protected BarangayClearanceRepository $barangayClearanceRepository
    ) {}

    public function index()
    {
        return Inertia::render('Resident/BarangayClearance/Index', [
            'clearances' => Auth::user()->barangayClearances()
                ->with(['applicantProfile', 'address', 'supportingDocument'])
                ->latest()
                ->get()
        ]);
    }

    public function create()
    {
        return Inertia::render('Resident/BarangayClearance/Create', [
            'barangays' => $this->psgcRepository->getBarangaysByIslandGroup(),
            'regions' => $this->psgcRepository->getRegions(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'purpose' => 'required|string|max:500',
            'first_name' => 'required|string|max:255',
            'middle_name' => 'nullable|string|max:255',
            'last_name' => 'required|string|max:255',
            'suffix' => 'nullable|string|max:255',
            'date_of_birth' => 'required|date',
            'place_of_birth' => 'required|string|max:255',
            'civil_status' => 'required|string|in:single,married,widowed,separated',
            'gender' => 'required|string|in:male,female,other',
            'citizenship' => 'required|string|max:255',
            'contact_number' => 'required|string|max:255',
            'address_type' => 'required|string|in:present,permanent',
            'house_no' => 'required|string|max:255',
            'street' => 'required|string|max:255',
            'purok' => 'nullable|string|max:255',
            'region_code' => 'required|string|exists:regions,code',
            'province_code' => 'required|string|exists:provinces,code',
            'city_code' => 'required|string|exists:cities,code',
            'barangay_code' => 'required|string|exists:barangays,code',
            'zip_code' => 'required|string|max:20',
            'document_type' => 'required|string|in:certificate_of_residency,lease_contract,utility_bill',
            'document' => 'required|file|mimes:pdf,jpg,jpeg,png|max:2048'
        ]);

        $clearance = $this->barangayClearanceRepository->createClearanceApplication(
            $validated,
            Auth::id()
        );

        return redirect()->route('resident.barangay-clearance.show', $clearance)
            ->with('success', 'Barangay Clearance application submitted successfully.');
    }

    public function show(int $id)
    {
        $clearance = Auth::user()->barangayClearances()
            ->with(['applicantProfile', 'address', 'supportingDocument'])
            ->findOrFail($id);

        return Inertia::render('Resident/BarangayClearance/Show', [
            'clearance' => $clearance
        ]);
    }
}