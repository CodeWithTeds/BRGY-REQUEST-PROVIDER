<?php

namespace App\Services;

use App\Enums\AppointmentStatus;
use App\Enums\AppointmentType;
use App\Models\Appointment;
use App\Models\AvailabilityWindow;
use App\Repositories\AppointmentRepository;
use Carbon\Carbon;

class AppointmentService
{
    public function __construct(protected AppointmentRepository $repo) {}

    /** Build paginated list data for the index page. */
    public function indexData(array $filters, int $calendarYear): array
    {
        $paginator = $this->repo->listWithFilters($filters);

        $items = $paginator->getCollection()
            ->map(fn(Appointment $a) => $this->formatItem($a))
            ->values();

        return [
            'items'      => $items,
            'stats'      => $this->repo->stats(),
            'pagination' => [
                'current_page' => $paginator->currentPage(),
                'per_page'     => $paginator->perPage(),
                'last_page'    => $paginator->lastPage(),
                'total'        => $paginator->total(),
            ],
            'filters' => [
                'statusOptions' => AppointmentStatus::values(),
                'typeOptions'   => AppointmentType::filterOptions(),
            ],
            'query' => $filters,
            'calendar' => [
                'busyDates' => $this->repo->busyDatesForYear($calendarYear),
                'year'      => $calendarYear,
            ],
        ];
    }

    /** Build detail data for the show page. */
    public function showData(int $id): array
    {
        $appointment = Appointment::with(['appointable' => fn($m) => $m->with('user')])->findOrFail($id);
        $local = optional($appointment->appointment_at)->copy()->setTimezone('Asia/Manila');

        return [
            'id'               => $appointment->id,
            'status'           => $appointment->status,
            'remarks'          => $appointment->remarks,
            'appointment_at'   => $local?->toIso8601String(),
            'type'             => AppointmentType::labelFromClass($appointment->appointable_type),
            'appointable_type' => $appointment->appointable_type,
            'appointable_id'   => optional($appointment->appointable)->id,
            'applicant_name'   => optional(optional($appointment->appointable)->user)->name,
        ];
    }

    /** Update only the status of an appointment. */
    public function updateStatus(int $id, string $status): Appointment
    {
        $appointment = Appointment::findOrFail($id);
        $appointment->status = $status;
        $appointment->save();
        return $appointment;
    }

    /**
     * Reschedule an appointment; returns an error string or null on success.
     */
    public function reschedule(int $id, string $date, string $time, ?string $remarks): ?string
    {
        $dtLocal = Carbon::createFromFormat('Y-m-d H:i', $date . ' ' . $time, 'Asia/Manila');
        $hhmm    = $dtLocal->format('H:i');

        if ($hhmm < '08:00' || $hhmm > '17:00') {
            return 'Appointment must be between 08:00 and 17:00.';
        }

        $dt     = $dtLocal->copy()->setTimezone('UTC');
        $window = AvailabilityWindow::forDate($date) ?? AvailabilityWindow::create([
            'date'                  => $date,
            'start_time'            => '08:00',
            'end_time'              => '17:00',
            'slot_interval_minutes' => 30,
            'capacity_per_slot'     => 10,
            'is_active'             => true,
        ]);

        $appointment = Appointment::findOrFail($id);
        $capacity    = (int) $window->capacity_per_slot;

        if ($this->repo->countAtSlot($dt, $appointment->id) >= $capacity) {
            return 'Selected time slot is full. Please choose another time.';
        }

        $appointment->appointment_at          = $dt;
        $appointment->availability_window_id  = $window->id;
        $appointment->status                  = AppointmentStatus::Scheduled->value;
        if ($remarks) {
            $appointment->remarks = $remarks;
        }
        $appointment->save();

        $appointable = $appointment->appointable;
        if ($appointable && property_exists($appointable, 'appointment_at')) {
            $appointable->appointment_at = $dt;
            $appointable->save();
        }

        return null;
    }

    /** Return slot availability data for a given date. */
    public function slotAvailability(string $date): array
    {
        $window   = AvailabilityWindow::forDate($date);
        $capacity = (int) ($window?->capacity_per_slot ?? 10);
        $counts   = $this->repo->slotCountsForDate($date);

        $occupied  = collect($counts)->filter(fn($c) => $c >= $capacity)->keys()->values()->all();
        $remaining = collect($counts)->map(fn($c) => max($capacity - (int) $c, 0))->toArray();

        return [
            'counts'           => $counts,
            'capacity'         => $capacity,
            'totalScheduled'   => array_sum($counts),
            'occupied'         => $occupied,
            'remainingPerSlot' => $remaining,
        ];
    }

    private function formatItem(Appointment $a): array
    {
        $local = optional($a->appointment_at)->copy()->setTimezone('Asia/Manila');
        return [
            'id'               => $a->id,
            'type'             => AppointmentType::labelFromClass($a->appointable_type),
            'appointable_type' => $a->appointable_type,
            'status'           => $a->status,
            'appointment_at'   => $local?->toIso8601String(),
            'appointable_id'   => optional($a->appointable)->id,
            'applicant_name'   => optional(optional($a->appointable)->user)->name,
        ];
    }
}
