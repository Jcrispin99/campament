<?php

namespace App\Models;

use App\Enums\UnidadGramaje;
use Database\Factories\ComponenteFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['nombre', 'gramaje_sugerido', 'unidad', 'observacion', 'activo'])]
class Componente extends Model
{
    /** @use HasFactory<ComponenteFactory> */
    use HasFactory;

    protected $table = 'componentes';

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'gramaje_sugerido' => 'decimal:2',
            'unidad' => UnidadGramaje::class,
            'activo' => 'boolean',
        ];
    }
}
