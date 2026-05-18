<?php

namespace App\Models;

use App\Enums\Criticidad;
use Database\Factories\ReporteFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'fecha',
    'semana',
    'detalle_observacion',
    'criticidad',
    'se_corrigio',
    'accion_inmediata',
    'requiere_plan_accion',
    'recomendacion_salus',
    'comedor_id',
    'servicio_id',
    'tipo_incidente_id',
    'clasificacion_id',
    'analisis_causa_id',
    'reportado_por_id',
])]
class Reporte extends Model
{
    /** @use HasFactory<ReporteFactory> */
    use HasFactory;

    /**
     * @return BelongsTo<Comedor, $this>
     */
    public function comedor(): BelongsTo
    {
        return $this->belongsTo(Comedor::class);
    }

    /**
     * @return BelongsTo<Servicio, $this>
     */
    public function servicio(): BelongsTo
    {
        return $this->belongsTo(Servicio::class);
    }

    /**
     * @return BelongsTo<TipoIncidente, $this>
     */
    public function tipoIncidente(): BelongsTo
    {
        return $this->belongsTo(TipoIncidente::class);
    }

    /**
     * @return BelongsTo<ClasificacionIncidente, $this>
     */
    public function clasificacion(): BelongsTo
    {
        return $this->belongsTo(ClasificacionIncidente::class, 'clasificacion_id');
    }

    /**
     * @return BelongsTo<AnalisisCausa, $this>
     */
    public function analisisCausa(): BelongsTo
    {
        return $this->belongsTo(AnalisisCausa::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function reportadoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reportado_por_id');
    }

    /**
     * @return HasMany<Evidencia, $this>
     */
    public function evidencias(): HasMany
    {
        return $this->hasMany(Evidencia::class);
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'fecha' => 'date',
            'criticidad' => Criticidad::class,
            'se_corrigio' => 'boolean',
            'requiere_plan_accion' => 'boolean',
        ];
    }
}
