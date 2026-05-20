<?php

namespace App\Models;

use Database\Factories\OrigenFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['nombre', 'activo'])]
class Origen extends Model
{
    /** @use HasFactory<OrigenFactory> */
    use HasFactory;

    protected $table = 'origenes';

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
