<?php

namespace App\Enums;

enum EstatusGramaje: string
{
    case Conforme = 'CONFORME';
    case Inconforme = 'INCONFORME';

    public function label(): string
    {
        return match ($this) {
            self::Conforme => 'Conforme',
            self::Inconforme => 'Inconforme',
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

    public static function fromVariacion(float $variacionPct): self
    {
        return $variacionPct >= 100.0 ? self::Conforme : self::Inconforme;
    }
}
