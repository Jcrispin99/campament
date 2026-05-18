<?php

namespace App\Enums;

enum Criticidad: string
{
    case Leve = 'LEVE';
    case Moderado = 'MODERADO';
    case Critico = 'CRITICO';

    public function label(): string
    {
        return match ($this) {
            self::Leve => 'Leve',
            self::Moderado => 'Moderado',
            self::Critico => 'Crítico',
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
