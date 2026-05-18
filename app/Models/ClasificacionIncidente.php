<?php

namespace App\Models;

use Database\Factories\ClasificacionIncidenteFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['tipo_incidente_id', 'nombre', 'activo'])]
class ClasificacionIncidente extends Model
{
    /** @use HasFactory<ClasificacionIncidenteFactory> */
    use HasFactory;

    protected $table = 'clasificaciones_incidente';

    /**
     * @return BelongsTo<TipoIncidente, $this>
     */
    public function tipoIncidente(): BelongsTo
    {
        return $this->belongsTo(TipoIncidente::class);
    }

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
