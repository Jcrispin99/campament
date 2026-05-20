<?php

namespace App\Http\Requests\Gramajes;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreGramajeRequest extends FormRequest
{
    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'fecha' => ['required', 'date', 'before_or_equal:today'],
            'semana' => ['required', 'integer', 'between:1,53'],
            'fecha_produccion' => ['required', 'date', 'before_or_equal:fecha'],

            'comedor_id' => ['required', 'integer', 'exists:comedores,id'],
            'servicio_id' => ['required', 'integer', 'exists:servicios,id'],
            'plato_id' => ['required', 'integer', 'exists:platos,id'],
            'componente_id' => ['required', 'integer', 'exists:componentes,id'],
            'tipo_corte_id' => ['nullable', 'integer', 'exists:tipos_corte,id'],

            'gramaje_esperado' => ['required', 'numeric', 'gt:0'],

            'medidas' => ['required', 'array', 'min:1'],
            'medidas.*' => ['required', 'numeric', 'gt:0'],
        ];
    }
}
