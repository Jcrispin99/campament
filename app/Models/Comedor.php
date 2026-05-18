<?php

namespace App\Models;

use Database\Factories\ComedorFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['nombre', 'activo'])]
class Comedor extends Model
{
    /** @use HasFactory<ComedorFactory> */
    use HasFactory;

    protected $table = 'comedores';

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
