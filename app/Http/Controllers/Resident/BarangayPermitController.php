<?php

namespace App\Http\Controllers\Resident;

use App\Http\Controllers\Controller;
use App\Http\Requests\Resident\StoreBarangayPermitRequest;
use App\Models\Address;
use App\Models\ApplicantProfile;
use App\Models\Barangay;
use App\Models\BarangayPermit;
use App\Models\SupportingDocument;
use App\Models\City;
use App\Models\Province;
use App\Models\Region;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Inertia\Inertia;

class BarangayPermitController extends Controller
{
    public function create()
    {
        $barangays = Barangay::query()->orderBy('name')->get(['code', 'name']);
        $regions = Region::query()->orderBy('name')->get(['code', 'name']);

        return Inertia::render('Resident/BarangayPermit/Create', [
            'barangays' => $barangays,
            'regions' => $regions,
        ]);
    }

    public function store(StoreBarangayPermitRequest $request)
    {
        $validated = $request->validated();
        $user = Auth::user();

        DB::transaction(function () use ($request, $user, $validated) {
            // Create Applicant Profile
            ApplicantProfile::updateOrCreate(
                ['user_id' => $user->id],
                Arr::only($validated, [
                    'first_name', 'middle_name', 'last_name', 'suffix',
                    'date_of_birth', 'place_of_birth', 'civil_status',
                    'gender', 'citizenship', 'contact_number',
                ])
            );

            // Resolve PSGC barangay id (if possible)
            $barangayId = null;
            if (!empty($validated['barangay_code'])) {
                $barangayId = DB::table('psgc_barangays')->where('code', $validated['barangay_code'])->value('id');
            }

            // Create/Update Address with new codes
            Address::updateOrCreate(
                ['user_id' => $user->id, 'type' => $validated['address_type']],
                array_merge(
                    Arr::only($validated, [
                        'house_no', 'street', 'purok',
                        'barangay_code', 'city_code', 'province_code', 'region_code', 'zip_code',
                    ]),
                    ['barangay_id' => $barangayId]
                )
            );

            // Handle file upload
            if (isset($validated['document']) && $validated['document']->isValid()) {
                $filePath = $validated['document']->store('supporting_documents', 'public');
                SupportingDocument::create([
                    'user_id' => $user->id,
                    'document_type' => $validated['document_type'],
                    'file_path' => $filePath,
                ]);
            }

            // Create Barangay Permit
            BarangayPermit::create([
                'user_id' => $user->id,
                'application_date' => now(),
                'status' => 'pending',
            ]);
        });

        return redirect()->route('resident.dashboard')->with('success', 'Barangay permit application submitted successfully.');
    }

    public function barangaysByIslandGroup(string $code)
    {
        $barangays = Barangay::query()->orderBy('name')->get(['code', 'name']);
        return response()->json([
            'barangays' => $barangays,
        ]);
    }

    public function barangaysByCity(string $code)
    {
        $barangays = Barangay::query()
            ->select('code', 'name')
            ->where('city_code', $code)
            ->orderBy('name')
            ->get();

        return response()->json($barangays);
    }

    public function regions()
    {
        $regions = Region::query()->orderBy('name')->get(['code', 'name']);
        return response()->json($regions);
    }

    public function provincesByRegion(string $code)
    {
        $provinces = Province::query()
            ->select('code', 'name')
            ->where('region_code', $code)
            ->orderBy('name')
            ->get();
        return response()->json($provinces);
    }

    public function citiesByProvince(string $code)
    {
        $cities = City::query()
            ->select('code', 'name')
            ->where('province_code', $code)
            ->orderBy('name')
            ->get();
        return response()->json($cities);
    }

    public function citiesByRegion(string $code)
    {
        $cities = City::query()
            ->select('code', 'name')
            ->where('region_code', $code)
            ->orderBy('name')
            ->get();
        return response()->json($cities);
    }
}
