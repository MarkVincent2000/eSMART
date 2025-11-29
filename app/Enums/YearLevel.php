<?php

namespace App\Enums;

enum YearLevel: int
{
    case GRADE_7 = 7;
    case GRADE_8 = 8;
    case GRADE_9 = 9;
    case GRADE_10 = 10;
    case GRADE_11 = 11;
    case GRADE_12 = 12;

    /**
     * Get the display label for the year level.
     */
    public function label(): string
    {
        return match($this) {
            self::GRADE_7 => 'Grade 7',
            self::GRADE_8 => 'Grade 8',
            self::GRADE_9 => 'Grade 9',
            self::GRADE_10 => 'Grade 10',
            self::GRADE_11 => 'Grade 11',
            self::GRADE_12 => 'Grade 12',
        };
    }

    /**
     * Get all year levels as an array for dropdowns.
     */
    public static function options(): array
    {
        return array_map(
            fn($case) => ['value' => $case->value, 'label' => $case->label()],
            self::cases()
        );
    }

    /**
     * Get all year level values.
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}

