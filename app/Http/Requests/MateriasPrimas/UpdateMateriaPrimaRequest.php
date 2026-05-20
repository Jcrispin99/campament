<?php

namespace App\Http\Requests\MateriasPrimas;

use App\Enums\ConformidadMp;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateMateriaPrimaRequest extends FormRequest
{
    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'fecha' => ['required', 'date', 'before_or_equal:today'],
            'semana' => ['required', 'integer', 'between:1,53'],

            'tipo_producto_id' => ['required', 'integer', 'exists:tipos_producto,id'],
            'proveedor_id' => ['required', 'integer', 'exists:proveedores,id'],
            'origen_id' => ['required', 'integer', 'exists:origenes,id'],

            'conformidad_mp' => ['required', Rule::enum(ConformidadMp::class)],
            'conformidad_documentacion' => ['required', Rule::enum(ConformidadMp::class)],
            'conformidad_vehiculo' => ['required', Rule::enum(ConformidadMp::class)],

            'causa_nc_observacion' => ['required', 'string'],
            'productos_afectados' => ['required', 'string'],
            'accion_realizada' => ['required', 'string'],
        ];
    }
}
