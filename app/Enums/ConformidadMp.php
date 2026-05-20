<?php

namespace App\Enums;

enum ConformidadMp: string
{
    case Conforme = 'CONFORME';
    case NoConforme = 'NO_CONFORME';

    public function label(): string
    {
        return match ($this) {
            self::Conforme => 'Conforme',
            self::NoConforme => 'No Conforme',
        };
    }

    /**
     * @return array<array{value: string, label: string}>
     */
    public static function options(): array
    {
        return collect(self::cases())
            ->map(fn (self $c) => ['value' => $c->value, 'label' => $c->label()])
            ->values()
            ->toArray();
    }
}
