<?php

namespace App\Enums;

enum DocumentStatus: string
{
    case Pending    = 'pending';
    case Processing = 'processing';
    case Approved   = 'approved';
    case Rejected   = 'rejected';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public static function validationRule(): string
    {
        return 'in:' . implode(',', self::values());
    }
}
