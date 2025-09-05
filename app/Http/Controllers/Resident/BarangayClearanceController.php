<?php

namespace App\Http\Controllers\Resident;

use App\Http\Controllers\Controller;
use App\Http\Requests\Resident\StoreBarangayClearanceRequest;
use App\Repositories\PSGCRepository;
use App\Repositories\BarangayClearanceRepository;
use Illuminate\Support\Facades\Auth;
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

        return Inertia::render('Resident/BarangayClearance/Create', [
            'barangays' => $this->psgcRepository->getBarangaysByIslandGroup(),
            'regions' => $this->psgcRepository->getRegions(),
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
            return redirect()->route('resident.barangay-clearance.show', $clearance)
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
        return Inertia::render('Resident/BarangayClearance/Show', [
            'clearance' => $this->barangayClearanceRepository->getClearance($id, Auth::id())
        ]);
    }
}