<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\BarangayPermit;
use App\Models\BarangayClearance;
use App\Models\CertificateOfResidency;
use App\Models\CertificateOfIndigency;
use App\Models\AvailabilityWindow;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AppointmentController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->string('status')->toString();
        $type = $request->string('type')->toString(); // permit|clearance|residency|indigency or FQCN
        $dateFrom = $request->string('date_from')->toString();
        $dateTo = $request->string('date_to')->toString();
        $q = $request->string('q')->toString();

        $typeMap = [
            'permit' => BarangayPermit::class,
            'clearance' => BarangayClearance::class,
            'residency' => CertificateOfResidency::class,
            'indigency' => CertificateOfIndigency::class,
        ];
        $typeClass = $typeMap[$type] ?? ($type && class_exists($type) ? $type : null);

        $query = Appointment::query()
            ->with(['appointable' => function ($m) {
                $m->with('user');
            }])
            ->orderByDesc('appointment_at');

        if ($status) {
            $query->where('status', $status);
        }
        if ($typeClass) {
            $query->where('appointable_type', $typeClass);
        }
        if ($dateFrom) {
            try {
                $df = Carbon::parse($dateFrom, 'Asia/Manila')->setTimezone('UTC');
                $query->where('appointment_at', '>=', $df);
            } catch (\Throwable $e) {}
        }
        if ($dateTo) {
            try {
                $dt = Carbon::parse($dateTo, 'Asia/Manila')->endOfDay()->setTimezone('UTC');
                $query->where('appointment_at', '<=', $dt);
            } catch (\Throwable $e) {}
        }
        if ($q) {
            $query->where(function ($qb) use ($q) {
                $qb->where('id', $q)
                   ->orWhereHasMorph('appointable', [BarangayPermit::class, BarangayClearance::class, CertificateOfResidency::class, CertificateOfIndigency::class], function ($m) use ($q) {
                       $m->whereHas('user', function ($u) use ($q) {
                           $u->where('name', 'like', "%$q%");
                       });
                   });
            });
        }

        $appointments = $query->paginate(15)->withQueryString();

        $items = $appointments->getCollection()->map(function (Appointment $a) {
            $typeLabel = match ($a->appointable_type) {
                BarangayPermit::class => 'Permit',
                BarangayClearance::class => 'Clearance',
                CertificateOfResidency::class => 'Residency',
                CertificateOfIndigency::class => 'Indigency',
                default => 'Unknown',
            };
            $local = optional($a->appointment_at)->copy()->setTimezone('Asia/Manila');
            return [
                'id' => $a->id,
                'type' => $typeLabel,
                'appointable_type' => $a->appointable_type,
                'status' => $a->status,
                'appointment_at' => $local?->toIso8601String(),
                'appointable_id' => optional($a->appointable)->id,
                'applicant_name' => optional(optional($a->appointable)->user)->name,
            ];
        })->values();

        $stats = [
            'total' => Appointment::count(),
            'scheduled' => Appointment::where('status', 'scheduled')->count(),
            'completed' => Appointment::where('status', 'completed')->count(),
            'cancelled' => Appointment::where('status', 'cancelled')->count(),
            'no_show' => Appointment::where('status', 'no_show')->count(),
        ];

        // Calendar: busy dates for the selected (or current) year
        $calendarYear = $request->integer('calendar_year') ?: Carbon::now('Asia/Manila')->year;
        $startLocal = Carbon::create($calendarYear, 1, 1, 0, 0, 0, 'Asia/Manila');
        $endLocal = $startLocal->copy()->endOfYear();

        $busyDates = Appointment::query()
            ->whereBetween('appointment_at', [
                $startLocal->copy()->setTimezone('UTC'),
                $endLocal->copy()->setTimezone('UTC'),
            ])
            ->where('status', 'scheduled')
            ->get(['appointment_at'])
            ->map(function (Appointment $a) {
                return optional($a->appointment_at)->copy()->setTimezone('Asia/Manila')->toDateString();
            })
            ->unique()
            ->values();

        return Inertia::render('Admin/Appointments', [
            'items' => $items,
            'stats' => $stats,
            'pagination' => [
                'current_page' => $appointments->currentPage(),
                'per_page' => $appointments->perPage(),
                'last_page' => $appointments->lastPage(),
                'total' => $appointments->total(),
            ],
            'filters' => [
                'statusOptions' => ['scheduled', 'completed', 'cancelled', 'no_show'],
                'typeOptions' => [
                    ['value' => 'permit', 'label' => 'Permit'],
                    ['value' => 'clearance', 'label' => 'Clearance'],
                    ['value' => 'residency', 'label' => 'Residency'],
                    ['value' => 'indigency', 'label' => 'Indigency'],
                ],
            ],
            'query' => [
                'status' => $status,
                'type' => $type,
                'date_from' => $dateFrom,
                'date_to' => $dateTo,
                'q' => $q,
            ],
            'calendar' => [
                'busyDates' => $busyDates,
                'year' => $calendarYear,
            ],
        ]);
    }

    public function show(int $id)
    {
        $appointment = Appointment::query()
            ->with(['appointable' => function ($m) {
                $m->with(['user']);
            }])
            ->findOrFail($id);

        $typeLabel = match ($appointment->appointable_type) {
            BarangayPermit::class => 'Permit',
            BarangayClearance::class => 'Clearance',
            CertificateOfResidency::class => 'Residency',
            CertificateOfIndigency::class => 'Indigency',
            default => 'Unknown',
        };

        $local = optional($appointment->appointment_at)->copy()->setTimezone('Asia/Manila');

        return Inertia::render('Admin/AppointmentView', [
            'appointment' => [
                'id' => $appointment->id,
                'status' => $appointment->status,
                'remarks' => $appointment->remarks,
                'appointment_at' => $local?->toIso8601String(),
                'type' => $typeLabel,
                'appointable_type' => $appointment->appointable_type,
                'appointable_id' => optional($appointment->appointable)->id,
                'applicant_name' => optional(optional($appointment->appointable)->user)->name,
            ],
        ]);
    }

    public function updateStatus(Request $request, int $id)
    {
        $data = $request->validate([
            'status' => 'required|string|in:scheduled,completed,cancelled,no_show',
        ]);

        $appointment = Appointment::findOrFail($id);
        $appointment->status = $data['status'];
        $appointment->save();

        return redirect()->route('admin.appointments.show', $appointment->id)
            ->with('success', 'Appointment status updated.');
    }

    public function reschedule(Request $request, int $id)
    {
        $data = $request->validate([
            'date' => ['required', 'date', 'after_or_equal:today'],
            'time' => ['required', 'date_format:H:i'],
            'remarks' => ['nullable', 'string'],
        ]);

        $appointment = Appointment::findOrFail($id);

        $dtLocal = Carbon::createFromFormat('Y-m-d H:i', $data['date'].' '.$data['time'], 'Asia/Manila');
        $dt = $dtLocal->copy()->setTimezone('UTC');
        $hhmm = $dtLocal->format('H:i');
        if ($hhmm < '08:00' || $hhmm > '17:00') {
            return back()->withErrors(['time' => 'Appointment must be between 08:00 and 17:00.'])
                ->withInput();
        }

        // Respect capacity per slot from availability window
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

        $countInSlot = Appointment::query()
            ->where('appointment_at', $dt)
            ->where('status', 'scheduled')
            ->where('id', '!=', $appointment->id)
            ->count();
        if ($countInSlot >= $capacity) {
            return back()->withErrors(['time' => 'Selected time slot is full. Please choose another time.'])
                ->withInput();
        }

        $appointment->appointment_at = $dt;
        $appointment->availability_window_id = $window?->id;
        if (!empty($data['remarks'])) {
            $appointment->remarks = $data['remarks'];
        }
        $appointment->status = 'scheduled';
        $appointment->save();

        // Update legacy column on permit if applicable
        $appointable = $appointment->appointable;
        if ($appointable && property_exists($appointable, 'appointment_at')) {
            $appointable->appointment_at = $dt;
            $appointable->save();
        }

        return redirect()->route('admin.appointments.show', $appointment->id)
            ->with('success', 'Appointment rescheduled successfully.');
    }

    public function availability(Request $request)
    {
        $data = $request->validate([
            'date' => ['required', 'date'],
        ]);

        $date = $data['date'];
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