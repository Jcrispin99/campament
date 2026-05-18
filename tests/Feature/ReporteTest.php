<?php

namespace Tests\Feature;

use App\Enums\Criticidad;
use App\Models\AnalisisCausa;
use App\Models\ClasificacionIncidente;
use App\Models\Comedor;
use App\Models\Evidencia;
use App\Models\Reporte;
use App\Models\Servicio;
use App\Models\TipoIncidente;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ReporteTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_access_reportes_index(): void
    {
        $this->get(route('reportes.index'))->assertRedirect(route('login'));
    }

    public function test_authenticated_user_can_create_reporte_with_evidence(): void
    {
        Storage::fake('public');

        $user = User::factory()->create();
        [$tipo, $clasificacion] = $this->makeTipoYClasificacion();

        $payload = $this->validPayload($tipo, $clasificacion) + [
            'evidencias' => [
                [
                    'imagen' => UploadedFile::fake()->image('foto1.jpg'),
                    'descripcion' => 'Vista frontal',
                ],
                [
                    'imagen' => UploadedFile::fake()->image('foto2.jpg'),
                    'descripcion' => 'Detalle',
                ],
            ],
        ];

        $response = $this->actingAs($user)->post(route('reportes.store'), $payload);

        $reporte = Reporte::sole();
        $response->assertRedirect(route('reportes.show', $reporte));

        $this->assertSame($user->id, $reporte->reportado_por_id);
        $this->assertSame(Criticidad::Critico, $reporte->criticidad);
        $this->assertCount(2, $reporte->evidencias);

        foreach ($reporte->evidencias as $evidencia) {
            Storage::disk('public')->assertExists($evidencia->imagen);
        }
    }

    public function test_clasificacion_must_belong_to_selected_tipo_incidente(): void
    {
        $user = User::factory()->create();
        [$tipoA] = $this->makeTipoYClasificacion();
        [, $clasificacionOtroTipo] = $this->makeTipoYClasificacion();

        $payload = $this->validPayload($tipoA, $clasificacionOtroTipo);

        $response = $this->actingAs($user)
            ->from(route('reportes.create'))
            ->post(route('reportes.store'), $payload);

        $response->assertSessionHasErrors('clasificacion_id');
        $this->assertSame(0, Reporte::count());
    }

    public function test_criticidad_must_be_valid_enum(): void
    {
        $user = User::factory()->create();
        [$tipo, $clasificacion] = $this->makeTipoYClasificacion();

        $payload = ['criticidad' => 'EXTREMO'] + $this->validPayload($tipo, $clasificacion);

        $response = $this->actingAs($user)
            ->from(route('reportes.create'))
            ->post(route('reportes.store'), $payload);

        $response->assertSessionHasErrors('criticidad');
    }

    public function test_update_replaces_evidencias(): void
    {
        Storage::fake('public');

        $user = User::factory()->create();
        [$tipo, $clasificacion] = $this->makeTipoYClasificacion();

        $reporte = Reporte::factory()->create([
            'tipo_incidente_id' => $tipo->id,
            'clasificacion_id' => $clasificacion->id,
            'reportado_por_id' => $user->id,
        ]);

        Storage::disk('public')->put('evidencias/keep.jpg', 'x');
        Storage::disk('public')->put('evidencias/remove.jpg', 'x');

        $keep = Evidencia::factory()->create(['reporte_id' => $reporte->id, 'imagen' => 'evidencias/keep.jpg']);
        $remove = Evidencia::factory()->create(['reporte_id' => $reporte->id, 'imagen' => 'evidencias/remove.jpg']);

        $payload = $this->validPayload($tipo, $clasificacion) + [
            'evidencias_a_eliminar' => [$remove->id],
            'evidencias_nuevas' => [
                ['imagen' => UploadedFile::fake()->image('new.jpg'), 'descripcion' => 'Nueva'],
            ],
        ];

        $response = $this->actingAs($user)
            ->put(route('reportes.update', $reporte), $payload);

        $response->assertRedirect(route('reportes.show', $reporte));

        $this->assertDatabaseMissing('reporte_evidencias', ['id' => $remove->id]);
        $this->assertDatabaseHas('reporte_evidencias', ['id' => $keep->id]);
        Storage::disk('public')->assertMissing('evidencias/remove.jpg');
        $this->assertSame(2, $reporte->refresh()->evidencias()->count());
    }

    public function test_destroy_deletes_reporte_and_physical_files(): void
    {
        Storage::fake('public');

        $user = User::factory()->create();
        $reporte = Reporte::factory()->create();
        Storage::disk('public')->put('evidencias/x.jpg', 'x');
        Evidencia::factory()->create(['reporte_id' => $reporte->id, 'imagen' => 'evidencias/x.jpg']);

        $this->actingAs($user)
            ->delete(route('reportes.destroy', $reporte))
            ->assertRedirect(route('reportes.index'));

        $this->assertDatabaseMissing('reportes', ['id' => $reporte->id]);
        $this->assertSame(0, Evidencia::count());
        Storage::disk('public')->assertMissing('evidencias/x.jpg');
    }

    public function test_index_eager_loads_relations(): void
    {
        $this->withoutVite();

        $user = User::factory()->create();
        Reporte::factory()->count(3)->create();

        $this->actingAs($user)
            ->get(route('reportes.index'))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Reportes/Index')
                ->has('reportes.data', 3)
            );
    }

    /**
     * @return array{0: TipoIncidente, 1: ClasificacionIncidente}
     */
    private function makeTipoYClasificacion(): array
    {
        $tipo = TipoIncidente::factory()->create();
        $clasificacion = ClasificacionIncidente::factory()->create(['tipo_incidente_id' => $tipo->id]);

        return [$tipo, $clasificacion];
    }

    /**
     * @return array<string, mixed>
     */
    private function validPayload(TipoIncidente $tipo, ClasificacionIncidente $clasificacion): array
    {
        return [
            'fecha' => now()->toDateString(),
            'semana' => (int) now()->format('W'),
            'detalle_observacion' => 'Se observó incumplimiento de BPM en la línea de servicio.',
            'criticidad' => Criticidad::Critico->value,
            'se_corrigio' => true,
            'accion_inmediata' => 'Se corrigió en el momento.',
            'requiere_plan_accion' => false,
            'recomendacion_salus' => 'Reforzar capacitación.',
            'comedor_id' => Comedor::factory()->create()->id,
            'servicio_id' => Servicio::factory()->create()->id,
            'tipo_incidente_id' => $tipo->id,
            'clasificacion_id' => $clasificacion->id,
            'analisis_causa_id' => AnalisisCausa::factory()->create()->id,
        ];
    }
}
