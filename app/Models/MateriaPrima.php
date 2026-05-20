<?php

namespace App\Models;

use App\Enums\ConformidadMp;
use Database\Factories\MateriaPrimaFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'fecha',
    'semana',
    'tipo_producto_id',
    'proveedor_id',
    'origen_id',
    'conformidad_mp',
    'conformidad_documentacion',
    'conformidad_vehiculo',
    'causa_nc_observacion',
    'productos_afectados',
    'accion_realizada',
    'reportado_por_id',
])]
class MateriaPrima extends Model
{
    /** @use HasFactory<MateriaPrimaFactory> */
    use HasFactory;

    protected $table = 'materias_primas';

    /**
     * @return BelongsTo<TipoProducto, $this>
     */
    public function tipoProducto(): BelongsTo
    {
        return $this->belongsTo(TipoProducto::class);
    }

    /**
     * @return BelongsTo<Proveedor, $this>
     */
    public function proveedor(): BelongsTo
    {
        return $this->belongsTo(Proveedor::class);
    }

    /**
     * @return BelongsTo<Origen, $this>
     */
    public function origen(): BelongsTo
    {
        return $this->belongsTo(Origen::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function reportadoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reportado_por_id');
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'fecha' => 'date',
            'conformidad_mp' => ConformidadMp::class,
            'conformidad_documentacion' => ConformidadMp::class,
            'conformidad_vehiculo' => ConformidadMp::class,
        ];
    }
}
