<?php

namespace App\Http\Requests\Menus;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateMenuRequest extends FormRequest
{
    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'fecha' => ['required', 'date', 'before_or_equal:today'],
            'semana' => ['required', 'integer', 'between:1,53'],
            'fecha_solicitud' => ['required', 'date'],
            'fecha_cambio' => ['required', 'date', 'after_or_equal:fecha_solicitud'],

            'servicio_id' => ['required', 'integer', 'exists:servicios,id'],
            'componente_id' => ['required', 'integer', 'exists:componentes,id'],

            'programado' => ['required', 'string', 'max:200'],
            'propuesta' => ['required', 'string', 'max:200'],
            'motivo' => ['required', 'string'],
            'comentario' => ['nullable', 'string'],
            'conformidad' => ['required', 'string', 'max:50'],
            'analisis' => ['required', 'string'],
        ];
    }
}
