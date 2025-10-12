<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BarangayPermit;
use App\Models\SupportingDocument;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Illuminate\Support\Facades\File;

class BusinessPermitController extends Controller
{
    public function index()
    {
        $collection = BarangayPermit::with(['applicantProfile', 'user', 'address.barangay'])
            ->latest()
            ->get();

        $permits = $collection->map(function ($permit) {
            $ap = $permit->applicantProfile;
            $addr = $permit->address;

            $fullName = trim(collect([
                $ap?->first_name,
                $ap?->middle_name,
                $ap?->last_name,
                $ap?->suffix,
            ])->filter()->join(' '));

            if ($fullName === '') {
                $fullName = $permit->user?->name;
            }

            $addressLine = trim(collect([
                $addr?->house_no,
                $addr?->street,
                $addr?->purok,
            ])->filter()->join(', '));

            return [
                'id' => $permit->id,
                'full_name' => $fullName,
                'application_date' => $permit->application_date,
                'status' => $permit->status,
                'created_at' => optional($permit->created_at)?->toDateTimeString(),
                'updated_at' => optional($permit->updated_at)?->toDateTimeString(),
                'gender' => $ap?->gender,
                'citizenship' => $ap?->citizenship,
                'contact_number' => $ap?->contact_number,
                'barangay' => $addr?->barangay?->name,
                'address_line' => $addressLine,
                'remarks' => $permit->remarks,
            ];
        });

        $stats = [
            'total' => $collection->count(),
            'approved' => $collection->where('status', 'approved')->count(),
            'pending' => $collection->where('status', 'pending')->count(),
            'rejected' => $collection->where('status', 'rejected')->count(),
        ];

        return Inertia::render('Admin/BusinessPermits', [
            'permits' => $permits,
            'stats' => $stats,
        ]);
    }

    public function show($id)
    {
        $permit = BarangayPermit::with([
            'applicantProfile',
            'user',
            'addresses.barangay',
            'addresses.city',
            'addresses.province',
            'addresses.region',
            'supportingDocuments',
        ])->findOrFail($id);

        $ap = $permit->applicantProfile;

        $fullName = trim(collect([
            $ap?->first_name,
            $ap?->middle_name,
            $ap?->last_name,
            $ap?->suffix,
        ])->filter()->join(' '));
        if ($fullName === '') {
            $fullName = $permit->user?->name;
        }

        $addresses = $permit->addresses->map(function ($addr) {
            $line = trim(collect([
                $addr?->house_no,
                $addr?->street,
                $addr?->purok,
            ])->filter()->join(', '));

            return [
                'id' => $addr->id,
                'type' => $addr->type,
                'line' => $line,
                'barangay' => $addr?->barangay?->name,
                'city' => $addr?->city?->name,
                'province' => $addr?->province?->name,
                'region' => $addr?->region?->name,
                'zip_code' => $addr?->zip_code,
            ];
        });

        $documents = $permit->supportingDocuments->map(function ($doc) {
            return [
                'id' => $doc->id,
                'document_type' => $doc->document_type,
                'file_path' => $doc->file_path,
                'verified' => (bool) $doc->verified,
            ];
        });

        $data = [
            'id' => $permit->id,
            'full_name' => $fullName,
            'application_date' => $permit->application_date,
            'status' => $permit->status,
            'created_at' => optional($permit->created_at)?->toDateTimeString(),
            'updated_at' => optional($permit->updated_at)?->toDateTimeString(),
            'gender' => $ap?->gender,
            'citizenship' => $ap?->citizenship,
            'contact_number' => $ap?->contact_number,
            'user' => [
                'id' => $permit->user?->id,
                'name' => $permit->user?->name,
                'email' => $permit->user?->email,
            ],
            'remarks' => $permit->remarks,
            'applicant_profile' => [
                'first_name' => $ap?->first_name,
                'middle_name' => $ap?->middle_name,
                'last_name' => $ap?->last_name,
                'suffix' => $ap?->suffix,
                'date_of_birth' => optional($ap?->date_of_birth)?->toDateString(),
                'place_of_birth' => $ap?->place_of_birth,
                'civil_status' => $ap?->civil_status,
            ],
            'addresses' => $addresses,
            'supporting_documents' => $documents,
        ];

        // Global status counts
        $stats = [
            'total' => BarangayPermit::count(),
            'pending' => BarangayPermit::where('status', 'pending')->count(),
            'processing' => BarangayPermit::where('status', 'processing')->count(),
            'approved' => BarangayPermit::where('status', 'approved')->count(),
            'rejected' => BarangayPermit::where('status', 'rejected')->count(),
        ];

        return Inertia::render('Admin/BusinessPermitView', [
            'permit' => $data,
            'stats' => $stats,
        ]);
    }

    public function viewDocument($id, $docId)
    {
        $permit = BarangayPermit::findOrFail($id);
        $document = SupportingDocument::where('id', $docId)
            ->where('barangay_permit_id', $permit->id)
            ->firstOrFail();

        $path = $document->file_path ?? null; // e.g. 'supporting_documents/xyz.jpg'
        if (!$path || !Storage::disk('public')->exists($path)) {
            abort(404, 'File not found');
        }

        // Build absolute path on public disk and stream inline
        $fullPath = Storage::disk('public')->path($path);
        $mime = File::mimeType($fullPath) ?? 'application/octet-stream';
        $filename = basename($fullPath);
        return response()->file($fullPath, [
            'Content-Type' => $mime,
            'Content-Disposition' => 'inline; filename="' . $filename . '"',
        ]);
    }

    public function updateStatus(Request $request, $id)
    {
        $validated = $request->validate([
            'status' => 'required|string|in:pending,processing,approved,rejected',
            'remarks' => 'nullable|string',
        ]);

        $permit = BarangayPermit::findOrFail($id);
        $permit->status = $validated['status'];
        if (isset($validated['remarks'])) {
            $permit->remarks = $validated['remarks'];
        }
        $permit->save();

        // redirect back to the show page
        return redirect()->route('admin.business-permits.show', ['id' => $id]);
    }

    public function destroy($id)
    {
        $permit = BarangayPermit::with(['supportingDocuments', 'addresses'])->findOrFail($id);

        foreach ($permit->supportingDocuments as $doc) {
            $path = $doc->file_path;
            if ($path && Storage::disk('public')->exists($path)) {
                Storage::disk('public')->delete($path);
            }
            $doc->delete();
        }

        // Delete related addresses
        if (method_exists($permit, 'addresses')) {
            $permit->addresses()->delete();
        }

        $permit->delete();

        return redirect()->route('admin.business-permits')->with('success', 'Barangay Business Permit deleted.');
    }
}
