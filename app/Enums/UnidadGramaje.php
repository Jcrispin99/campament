<?php

namespace App\Enums;

enum UnidadGramaje: string
{
    case Gramos = 'GRAMOS';
    case Unidades = 'UNIDADES';

    public function label(): string
    {
        return match ($this) {
            self::Gramos => 'gramos',
            self::Unidades => 'unidades',
        };
    }

    public function abreviado(): string
    {
        return match ($this) {
            self::Gramos => 'g',
            self::Unidades => 'u',
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
