<?php

namespace App\Models;

use Database\Factories\TipoIncidenteFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['nombre'])]
class TipoIncidente extends Model
{
    /** @use HasFactory<TipoIncidenteFactory> */
    use HasFactory;

    protected $table = 'tipos_incidente';

    /**
     * @return HasMany<ClasificacionIncidente, $this>
     */
    public function clasificaciones(): HasMany
    {
        return $this->hasMany(ClasificacionIncidente::class);
    }
}
