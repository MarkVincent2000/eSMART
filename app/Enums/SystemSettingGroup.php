<?php

namespace App\Enums;

enum SystemSettingGroup: string
{
    case BRANDING = 'Branding';
    case GENERAL = 'General';
    case AUTHENTICATION = 'Authentication';
    case LANDING_PAGE = 'Landing Page';

    /**
     * Get the display label for the group.
     */
    public function label(): string
    {
        return $this->value;
    }

    /**
     * Get all groups as an array for dropdowns.
     */
    public static function options(): array
    {
        return array_map(
            fn($case) => ['value' => $case->value, 'label' => $case->label()],
            self::cases()
        );
    }

    /**
     * Get all group values.
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
