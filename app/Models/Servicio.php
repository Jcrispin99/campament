<?php

namespace App\Models;

use Database\Factories\ServicioFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['nombre', 'activo'])]
class Servicio extends Model
{
    /** @use HasFactory<ServicioFactory> */
    use HasFactory;

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
