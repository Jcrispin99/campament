<?php

namespace App\Models;

use Database\Factories\EvidenciaFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

#[Fillable(['reporte_id', 'imagen', 'descripcion'])]
class Evidencia extends Model
{
    /** @use HasFactory<EvidenciaFactory> */
    use HasFactory;

    protected $table = 'reporte_evidencias';

    /** @var list<string> */
    protected $appends = ['imagen_url'];

    /**
     * @return BelongsTo<Reporte, $this>
     */
    public function reporte(): BelongsTo
    {
        return $this->belongsTo(Reporte::class);
    }

    /**
     * @return Attribute<string, never>
     */
    protected function imagenUrl(): Attribute
    {
        return Attribute::get(fn (): string => Storage::disk('public')->url($this->imagen));
    }
}
