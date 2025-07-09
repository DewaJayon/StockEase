<?php

namespace App\Enums;

class UnitEnum
{
    public const UNITS = [
        'pcs'   => 'Buah (pcs)',
        'box'   => 'Kotak (box)',
        'pack'  => 'Bungkus (pack)',
        'set'   => 'Set',
        'botol' => 'Botol',
        'liter' => 'Liter',
        'ml'    => 'Mililiter (ml)',
        'kg'    => 'Kilogram (kg)',
        'gram'  => 'Gram',
        'meter' => 'Meter',
        'cm'    => 'Sentimeter (cm)',
        'roll'  => 'Gulungan (roll)',
        'lusin' => 'Lusin (12 pcs)',
        'rim'   => 'Rim (500 lembar)',
        'karung' => 'Karung',
    ];

    public static function options(): array
    {
        return collect(self::UNITS)
            ->map(fn($label, $value) => [
                'value' => $value,
                'label' => $label,
            ])
            ->values()
            ->toArray();
    }

    public static function keys(): array
    {
        return array_keys(self::UNITS);
    }
}
