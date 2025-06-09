<?php

namespace App\Enums;

enum UserType: string
{
    case SECURITY = 'security';
    case SUPER_ADMIN = 'super_admin';

    /**
     * Return only specific user type group values
     */
    public static function group(array $cases): array
    {
        return array_map(fn (self $case) => $case->value, $cases);
    }

    /**
     * Group of all regular app users
     */
    public static function appUsers(): array
    {
        return [
            self::SUPER_ADMIN,
        ];
    }

    /**
     * Group for all security agency roles
     */
    public static function agencyUsers(): array
    {
        return [
            self::SECURITY,
        ];
    }
}
