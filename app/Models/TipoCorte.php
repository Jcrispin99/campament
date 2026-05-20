<?php

namespace App\Models;

use Database\Factories\TipoCorteFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['nombre', 'activo'])]
class TipoCorte extends Model
{
    /** @use HasFactory<TipoCorteFactory> */
    use HasFactory;

    protected $table = 'tipos_corte';

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'activo' => 'boolean',
        ];
    }
}
