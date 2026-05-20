<?php

namespace App\Models;

use Database\Factories\MenuFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'fecha',
    'semana',
    'fecha_solicitud',
    'fecha_cambio',
    'servicio_id',
    'componente_id',
    'programado',
    'propuesta',
    'motivo',
    'comentario',
    'dias_prevision',
    'conformidad',
    'analisis',
    'reportado_por_id',
])]
class Menu extends Model
{
    /** @use HasFactory<MenuFactory> */
    use HasFactory;

    protected $table = 'menus';

    /**
     * @return BelongsTo<Servicio, $this>
     */
    public function servicio(): BelongsTo
    {
        return $this->belongsTo(Servicio::class);
    }

    /**
     * @return BelongsTo<Componente, $this>
     */
    public function componente(): BelongsTo
    {
        return $this->belongsTo(Componente::class);
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
            'fecha_solicitud' => 'date',
            'fecha_cambio' => 'date',
        ];
    }
}
