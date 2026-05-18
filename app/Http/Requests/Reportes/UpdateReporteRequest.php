<?php

namespace App\Http\Requests\Reportes;

use App\Enums\Criticidad;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateReporteRequest extends FormRequest
{
    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $reporteId = $this->route('reporte')?->id ?? $this->route('reporte');

        return [
            'fecha' => ['required', 'date', 'before_or_equal:today'],
            'semana' => ['required', 'integer', 'between:1,53'],
            'detalle_observacion' => ['required', 'string'],
            'criticidad' => ['required', Rule::enum(Criticidad::class)],
            'se_corrigio' => ['required', 'boolean'],
            'accion_inmediata' => ['required', 'string'],
            'requiere_plan_accion' => ['required', 'boolean'],
            'recomendacion_salus' => ['required', 'string'],

            'comedor_id' => ['required', 'integer', 'exists:comedores,id'],
            'servicio_id' => ['required', 'integer', 'exists:servicios,id'],
            'tipo_incidente_id' => ['required', 'integer', 'exists:tipos_incidente,id'],
            'clasificacion_id' => [
                'required',
                'integer',
                Rule::exists('clasificaciones_incidente', 'id')
                    ->where('tipo_incidente_id', $this->input('tipo_incidente_id')),
            ],
            'analisis_causa_id' => ['required', 'integer', 'exists:analisis_causas,id'],

            'evidencias_a_eliminar' => ['nullable', 'array'],
            'evidencias_a_eliminar.*' => [
                'integer',
                Rule::exists('reporte_evidencias', 'id')->where('reporte_id', $reporteId),
            ],

            'evidencias_nuevas' => ['nullable', 'array'],
            'evidencias_nuevas.*.imagen' => ['required_with:evidencias_nuevas', 'image', 'max:5120'],
            'evidencias_nuevas.*.descripcion' => ['required_with:evidencias_nuevas', 'string', 'max:200'],
        ];
    }
}
