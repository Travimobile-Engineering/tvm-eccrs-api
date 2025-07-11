<?php

namespace App\Enums;

enum ReportType: string
{
    case MANIFEST = 'manifest';
    case HOTEL = 'hotel';
    case TRANSPORT = 'transport';
    case USER = 'user';

    public function label(): string
    {
        return match ($this) {
            self::MANIFEST => 'Manifest',
            self::HOTEL => 'Hotel',
            self::TRANSPORT => 'Transport',
            self::USER => 'User',
        };
    }
}
