<?php

namespace App\Models;

use Database\Factories\AnalisisCausaFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['nombre', 'activo'])]
class AnalisisCausa extends Model
{
    /** @use HasFactory<AnalisisCausaFactory> */
    use HasFactory;

    protected $table = 'analisis_causas';

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
