<?php

namespace App\Models;

use Database\Factories\PlatoFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['nombre', 'activo'])]
class Plato extends Model
{
    /** @use HasFactory<PlatoFactory> */
    use HasFactory;

    protected $table = 'platos';

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
