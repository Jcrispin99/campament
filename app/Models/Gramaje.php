<?php

namespace App\Models;

use App\Enums\EstatusGramaje;
use Database\Factories\GramajeFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'fecha',
    'semana',
    'fecha_produccion',
    'comedor_id',
    'servicio_id',
    'plato_id',
    'componente_id',
    'tipo_corte_id',
    'gramaje_esperado',
    'cantidad_muestreada',
    'peso_promedio',
    'variacion_pct',
    'estatus',
    'reportado_por_id',
])]
class Gramaje extends Model
{
    /** @use HasFactory<GramajeFactory> */
    use HasFactory;

    protected $table = 'gramajes';

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
     * @return BelongsTo<Plato, $this>
     */
    public function plato(): BelongsTo
    {
        return $this->belongsTo(Plato::class);
    }

    /**
     * @return BelongsTo<Componente, $this>
     */
    public function componente(): BelongsTo
    {
        return $this->belongsTo(Componente::class);
    }

    /**
     * @return BelongsTo<TipoCorte, $this>
     */
    public function tipoCorte(): BelongsTo
    {
        return $this->belongsTo(TipoCorte::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function reportadoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reportado_por_id');
    }

    /**
     * @return HasMany<GramajeMedida, $this>
     */
    public function medidas(): HasMany
    {
        return $this->hasMany(GramajeMedida::class)->orderBy('orden');
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'fecha' => 'date',
            'fecha_produccion' => 'date',
            'gramaje_esperado' => 'decimal:2',
            'peso_promedio' => 'decimal:2',
            'variacion_pct' => 'decimal:2',
            'estatus' => EstatusGramaje::class,
        ];
    }
}
