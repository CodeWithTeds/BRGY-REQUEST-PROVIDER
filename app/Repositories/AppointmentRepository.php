<?php

namespace App\Repositories;

use App\Enums\AppointmentStatus;
use App\Enums\AppointmentType;
use App\Models\Appointment;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class AppointmentRepository extends Repository
{
    public function __construct(Appointment $model)
    {
        parent::__construct($model);
    }

    /** Paginated list with optional filters. */
    public function listWithFilters(array $filters, int $perPage = 15): LengthAwarePaginator
    {
        $query = $this->model->newQuery()
            ->with(['appointable' => fn($m) => $m->with('user')])
            ->orderByDesc('appointment_at');

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['type'])) {
            $typeClass = AppointmentType::modelClassFromSlug($filters['type'])
                ?? (class_exists($filters['type']) ? $filters['type'] : null);
            if ($typeClass) {
                $query->where('appointable_type', $typeClass);
            }
        }

        if (!empty($filters['date_from'])) {
            try {
                $query->where('appointment_at', '>=', Carbon::parse($filters['date_from'], 'Asia/Manila')->setTimezone('UTC'));
            } catch (\Throwable) {}
        }

        if (!empty($filters['date_to'])) {
            try {
                $query->where('appointment_at', '<=', Carbon::parse($filters['date_to'], 'Asia/Manila')->endOfDay()->setTimezone('UTC'));
            } catch (\Throwable) {}
        }

        if (!empty($filters['q'])) {
            $q = $filters['q'];
            $query->where(function ($qb) use ($q) {
                $qb->where('id', $q)
                   ->orWhereHasMorph('appointable', AppointmentType::allModelClasses(), function ($m) use ($q) {
                       $m->whereHas('user', fn($u) => $u->where('name', 'like', "%$q%"));
                   });
            });
        }

        return $query->paginate($perPage)->withQueryString();
    }

    /** Global status counts. */
    public function stats(): array
    {
        return collect(AppointmentStatus::cases())
            ->mapWithKeys(fn($s) => [$s->value => Appointment::where('status', $s->value)->count()])
            ->prepend(Appointment::count(), 'total')
            ->all();
    }

    /** Busy dates (scheduled) within a calendar year, returned as local Y-m-d strings. */
    public function busyDatesForYear(int $year): Collection
    {
        $startLocal = Carbon::create($year, 1, 1, 0, 0, 0, 'Asia/Manila');
        $endLocal   = $startLocal->copy()->endOfYear();

        return $this->model->newQuery()
            ->whereBetween('appointment_at', [
                $startLocal->copy()->setTimezone('UTC'),
                $endLocal->copy()->setTimezone('UTC'),
            ])
            ->where('status', AppointmentStatus::Scheduled->value)
            ->get(['appointment_at'])
            ->map(fn($a) => optional($a->appointment_at)->copy()->setTimezone('Asia/Manila')->toDateString())
            ->unique()
            ->values();
    }

    /** Slot occupancy counts for a specific date (local Y-m-d). */
    public function slotCountsForDate(string $date): array
    {
        $startUtc = Carbon::createFromFormat('Y-m-d H:i', $date . ' 00:00', 'Asia/Manila')->setTimezone('UTC');
        $endUtc   = Carbon::createFromFormat('Y-m-d H:i', $date . ' 23:59', 'Asia/Manila')->setTimezone('UTC');

        return $this->model->newQuery()
            ->whereBetween('appointment_at', [$startUtc, $endUtc])
            ->where('status', AppointmentStatus::Scheduled->value)
            ->get()
            ->map(fn($a) => optional($a->appointment_at)->copy()->setTimezone('Asia/Manila')->format('H:i'))
            ->countBy()
            ->toArray();
    }

    /** Count scheduled appointments at a specific UTC datetime, excluding one ID. */
    public function countAtSlot(\DateTimeInterface $utcDateTime, int $excludeId): int
    {
        return $this->model->newQuery()
            ->where('appointment_at', $utcDateTime)
            ->where('status', AppointmentStatus::Scheduled->value)
            ->where('id', '!=', $excludeId)
            ->count();
    }
}
