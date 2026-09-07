<?php

namespace App\Enums;

/**
 * The six roles from the execution plan's Governance & Security sections.
 * Backed as Spatie permission roles (see RolesAndPermissionsSeeder) rather
 * than a closed PHP enum column, since role membership is data, not schema.
 */
enum RoleName: string
{
    case Admin = 'Admin';
    case FounderManagement = 'Founder/Management';
    case SalesManager = 'Sales Manager';
    case Sales = 'Sales';
    case TechnicalDelivery = 'Technical/Delivery';
    case Finance = 'Finance';

    public static function values(): array
    {
        return array_map(fn (self $case) => $case->value, self::cases());
    }
}
