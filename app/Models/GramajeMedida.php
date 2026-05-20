<?php

namespace App\Models;

use Database\Factories\GramajeMedidaFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['gramaje_id', 'peso', 'orden'])]
class GramajeMedida extends Model
{
    /** @use HasFactory<GramajeMedidaFactory> */
    use HasFactory;

    protected $table = 'gramaje_medidas';

    /**
     * @return BelongsTo<Gramaje, $this>
     */
    public function gramaje(): BelongsTo
    {
        return $this->belongsTo(Gramaje::class);
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'peso' => 'decimal:2',
        ];
    }
}
