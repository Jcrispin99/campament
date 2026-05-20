<?php

namespace App\Models;

use Database\Factories\TipoProductoFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['nombre', 'activo'])]
class TipoProducto extends Model
{
    /** @use HasFactory<TipoProductoFactory> */
    use HasFactory;

    protected $table = 'tipos_producto';

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
