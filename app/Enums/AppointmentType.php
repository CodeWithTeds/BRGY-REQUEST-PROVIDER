<?php

namespace App\Enums;

use App\Models\BarangayPermit;
use App\Models\BarangayClearance;
use App\Models\CertificateOfResidency;
use App\Models\CertificateOfIndigency;

enum AppointmentType: string
{
    case Permit    = 'permit';
    case Clearance = 'clearance';
    case Residency = 'residency';
    case Indigency = 'indigency';

    public function modelClass(): string
    {
        return match ($this) {
            self::Permit    => BarangayPermit::class,
            self::Clearance => BarangayClearance::class,
            self::Residency => CertificateOfResidency::class,
            self::Indigency => CertificateOfIndigency::class,
        };
    }

    public function label(): string
    {
        return match ($this) {
            self::Permit    => 'Permit',
            self::Clearance => 'Clearance',
            self::Residency => 'Residency',
            self::Indigency => 'Indigency',
        };
    }

    /** Resolve label from a fully-qualified class name. */
    public static function labelFromClass(string $class): string
    {
        foreach (self::cases() as $case) {
            if ($case->modelClass() === $class) {
                return $case->label();
            }
        }
        return 'Unknown';
    }

    /** Resolve model class from slug; returns null when unknown. */
    public static function modelClassFromSlug(string $slug): ?string
    {
        $case = self::tryFrom($slug);
        return $case?->modelClass();
    }

    public static function allModelClasses(): array
    {
        return array_map(fn($c) => $c->modelClass(), self::cases());
    }

    public static function filterOptions(): array
    {
        return array_map(fn($c) => ['value' => $c->value, 'label' => $c->label()], self::cases());
    }
}
