<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { Trash2, UploadCloud } from 'lucide-vue-next';
import { computed, ref, watch } from 'vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { destroy, show, store, update } from '@/routes/reportes';
import type {
    Evidencia,
    Reporte,
    ReporteCatalogos,
} from '@/types/reportes';

type Mode = 'create' | 'edit';

const props = defineProps<{
    mode: Mode;
    catalogos: ReporteCatalogos;
    reporte?: Reporte | null;
}>();

const initialReporte = props.reporte;

const calcSemana = (fecha: string): number => {
    if (!fecha) return 1;
    const d = new Date(fecha + 'T00:00:00Z');
    const yearStart = new Date(Date.UTC(d.getUTCFullYear(), 0, 1));
    const diff = (d.getTime() - yearStart.getTime()) / 86400000;
    return Math.max(1, Math.min(53, Math.ceil((diff + yearStart.getUTCDay() + 1) / 7)));
};

const todayISO = new Date().toISOString().slice(0, 10);

const form = useForm({
    fecha: initialReporte?.fecha?.slice(0, 10) ?? todayISO,
    semana: initialReporte?.semana ?? calcSemana(todayISO),
    detalle_observacion: initialReporte?.detalle_observacion ?? '',
    criticidad: initialReporte?.criticidad ?? 'LEVE',
    se_corrigio: initialReporte?.se_corrigio ?? false,
    accion_inmediata: initialReporte?.accion_inmediata ?? '',
    requiere_plan_accion: initialReporte?.requiere_plan_accion ?? false,
    recomendacion_salus: initialReporte?.recomendacion_salus ?? '',
    comedor_id: initialReporte?.comedor_id ?? null,
    servicio_id: initialReporte?.servicio_id ?? null,
    tipo_incidente_id: initialReporte?.tipo_incidente_id ?? null,
    clasificacion_id: initialReporte?.clasificacion_id ?? null,
    analisis_causa_id: initialReporte?.analisis_causa_id ?? null,
    evidencias: [] as { imagen: File | null; descripcion: string }[],
    evidencias_nuevas: [] as { imagen: File | null; descripcion: string }[],
    evidencias_a_eliminar: [] as number[],
    crear_otro: false,
});

watch(
    () => form.fecha,
    (nuevaFecha) => {
        if (nuevaFecha) form.semana = calcSemana(nuevaFecha);
    },
);

const tipoSeleccionado = computed(() =>
    props.catalogos.tiposIncidente.find(
        (t) => t.id === form.tipo_incidente_id,
    ),
);

const clasificacionesDisponibles = computed(
    () => tipoSeleccionado.value?.clasificaciones ?? [],
);

watch(
    () => form.tipo_incidente_id,
    (nuevo, anterior) => {
        if (nuevo !== anterior) {
            const pertenece = clasificacionesDisponibles.value.some(
                (c) => c.id === form.clasificacion_id,
            );
            if (!pertenece) form.clasificacion_id = null;
        }
    },
);

const evidenciasFile = ref<HTMLInputElement | null>(null);

const evidenciasField = computed(() =>
    props.mode === 'edit' ? 'evidencias_nuevas' : 'evidencias',
);

const agregarEvidencia = () => {
    form[evidenciasField.value].push({ imagen: null, descripcion: '' });
};

const quitarEvidenciaNueva = (idx: number) => {
    form[evidenciasField.value].splice(idx, 1);
};

const onFileChange = (idx: number, e: Event) => {
    const target = e.target as HTMLInputElement;
    if (target.files && target.files[0]) {
        form[evidenciasField.value][idx].imagen = target.files[0];
    }
};

const marcarParaEliminar = (evidencia: Evidencia) => {
    if (!form.evidencias_a_eliminar.includes(evidencia.id)) {
        form.evidencias_a_eliminar.push(evidencia.id);
    }
};

const desmarcarEliminacion = (id: number) => {
    form.evidencias_a_eliminar = form.evidencias_a_eliminar.filter(
        (x) => x !== id,
    );
};

const evidenciasExistentes = computed<Evidencia[]>(
    () => initialReporte?.evidencias ?? [],
);

const submit = (seguirRegistrando: boolean = false) => {
    if (props.mode === 'create') {
        form.crear_otro = seguirRegistrando;
        form.post(store().url, {
            forceFormData: true,
            preserveScroll: true,
        });
    } else if (initialReporte) {
        form.transform((data) => ({ ...data, _method: 'put' })).post(
            update(initialReporte.id).url,
            { forceFormData: true, preserveScroll: true },
        );
    }
};

const cancelar = () => {
    if (props.mode === 'edit' && initialReporte) {
        window.location.href = show(initialReporte.id).url;
    } else {
        window.history.back();
    }
};

const eliminarReporte = () => {
    if (!initialReporte) return;
    if (!confirm('¿Eliminar este reporte? Esta acción no se puede deshacer.')) {
        return;
    }
    form.delete(destroy(initialReporte.id).url);
};
</script>

<template>
    <form class="space-y-8" @submit.prevent="submit()">
        <!-- Sección: Información general -->
        <section class="space-y-4 rounded-lg border p-6">
            <h3 class="text-base font-semibold">Información general</h3>

            <div class="grid gap-4 md:grid-cols-2">
                <div class="space-y-2">
                    <Label for="fecha">Fecha *</Label>
                    <Input
                        id="fecha"
                        v-model="form.fecha"
                        type="date"
                        :max="todayISO"
                        required
                    />
                    <InputError :message="form.errors.fecha" />
                </div>
                <div class="space-y-2">
                    <Label for="semana">Semana *</Label>
                    <Input
                        id="semana"
                        v-model.number="form.semana"
                        type="number"
                        min="1"
                        max="53"
                        required
                    />
                    <InputError :message="form.errors.semana" />
                </div>
                <div class="space-y-2">
                    <Label for="comedor">Comedor *</Label>
                    <Select
                        :model-value="form.comedor_id?.toString()"
                        @update:model-value="form.comedor_id = Number($event)"
                    >
                        <SelectTrigger id="comedor" class="w-full">
                            <SelectValue placeholder="Selecciona un comedor" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem
                                v-for="c in catalogos.comedores"
                                :key="c.id"
                                :value="c.id.toString()"
                            >
                                {{ c.nombre }}
                            </SelectItem>
                        </SelectContent>
                    </Select>
                    <InputError :message="form.errors.comedor_id" />
                </div>
                <div class="space-y-2">
                    <Label for="servicio">Servicio *</Label>
                    <Select
                        :model-value="form.servicio_id?.toString()"
                        @update:model-value="form.servicio_id = Number($event)"
                    >
                        <SelectTrigger id="servicio" class="w-full">
                            <SelectValue placeholder="Selecciona un servicio" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem
                                v-for="s in catalogos.servicios"
                                :key="s.id"
                                :value="s.id.toString()"
                            >
                                {{ s.nombre }}
                            </SelectItem>
                        </SelectContent>
                    </Select>
                    <InputError :message="form.errors.servicio_id" />
                </div>
            </div>
        </section>

        <!-- Sección: Incidente -->
        <section class="space-y-4 rounded-lg border p-6">
            <h3 class="text-base font-semibold">Incidente</h3>

            <div class="grid gap-4 md:grid-cols-2">
                <div class="space-y-2">
                    <Label for="tipo">Tipo de incidente *</Label>
                    <Select
                        :model-value="form.tipo_incidente_id?.toString()"
                        @update:model-value="
                            form.tipo_incidente_id = Number($event)
                        "
                    >
                        <SelectTrigger id="tipo" class="w-full">
                            <SelectValue placeholder="Selecciona un tipo" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem
                                v-for="t in catalogos.tiposIncidente"
                                :key="t.id"
                                :value="t.id.toString()"
                            >
                                {{ t.nombre }}
                            </SelectItem>
                        </SelectContent>
                    </Select>
                    <InputError :message="form.errors.tipo_incidente_id" />
                </div>
                <div class="space-y-2">
                    <Label for="clasificacion">Clasificación *</Label>
                    <Select
                        :model-value="form.clasificacion_id?.toString()"
                        :disabled="!form.tipo_incidente_id"
                        @update:model-value="
                            form.clasificacion_id = Number($event)
                        "
                    >
                        <SelectTrigger id="clasificacion" class="w-full">
                            <SelectValue
                                :placeholder="
                                    form.tipo_incidente_id
                                        ? 'Selecciona una clasificación'
                                        : 'Primero elige un tipo'
                                "
                            />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem
                                v-for="c in clasificacionesDisponibles"
                                :key="c.id"
                                :value="c.id.toString()"
                            >
                                {{ c.nombre }}
                            </SelectItem>
                        </SelectContent>
                    </Select>
                    <InputError :message="form.errors.clasificacion_id" />
                </div>
                <div class="space-y-2">
                    <Label for="analisis">Análisis de causa *</Label>
                    <Select
                        :model-value="form.analisis_causa_id?.toString()"
                        @update:model-value="
                            form.analisis_causa_id = Number($event)
                        "
                    >
                        <SelectTrigger id="analisis" class="w-full">
                            <SelectValue placeholder="Selecciona una causa" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem
                                v-for="a in catalogos.analisisCausas"
                                :key="a.id"
                                :value="a.id.toString()"
                            >
                                {{ a.nombre }}
                            </SelectItem>
                        </SelectContent>
                    </Select>
                    <InputError :message="form.errors.analisis_causa_id" />
                </div>
                <div class="space-y-2">
                    <Label for="criticidad">Criticidad *</Label>
                    <Select
                        :model-value="form.criticidad"
                        @update:model-value="
                            form.criticidad = $event as typeof form.criticidad
                        "
                    >
                        <SelectTrigger id="criticidad" class="w-full">
                            <SelectValue placeholder="Nivel" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem
                                v-for="opt in catalogos.criticidades"
                                :key="opt.value"
                                :value="opt.value"
                            >
                                {{ opt.label }}
                            </SelectItem>
                        </SelectContent>
                    </Select>
                    <InputError :message="form.errors.criticidad" />
                </div>
            </div>
        </section>

        <!-- Sección: Observación y acción -->
        <section class="space-y-4 rounded-lg border p-6">
            <h3 class="text-base font-semibold">Observación y acción</h3>

            <div class="space-y-2">
                <Label for="detalle">Detalle de la observación *</Label>
                <textarea
                    id="detalle"
                    v-model="form.detalle_observacion"
                    rows="4"
                    required
                    class="w-full rounded-md border bg-background px-3 py-2 text-sm focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring"
                ></textarea>
                <InputError :message="form.errors.detalle_observacion" />
            </div>

            <div class="space-y-2">
                <Label for="accion">Acción inmediata *</Label>
                <textarea
                    id="accion"
                    v-model="form.accion_inmediata"
                    rows="3"
                    required
                    class="w-full rounded-md border bg-background px-3 py-2 text-sm focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring"
                ></textarea>
                <InputError :message="form.errors.accion_inmediata" />
            </div>

            <div class="space-y-2">
                <Label for="recomendacion">Recomendación Salus *</Label>
                <textarea
                    id="recomendacion"
                    v-model="form.recomendacion_salus"
                    rows="3"
                    required
                    class="w-full rounded-md border bg-background px-3 py-2 text-sm focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring"
                ></textarea>
                <InputError :message="form.errors.recomendacion_salus" />
            </div>

            <div class="flex flex-wrap gap-6">
                <div class="flex items-center gap-2">
                    <Checkbox
                        id="se_corrigio"
                        :model-value="form.se_corrigio"
                        @update:model-value="
                            form.se_corrigio = $event === true
                        "
                    />
                    <Label for="se_corrigio">Se corrigió</Label>
                </div>
                <div class="flex items-center gap-2">
                    <Checkbox
                        id="requiere_plan"
                        :model-value="form.requiere_plan_accion"
                        @update:model-value="
                            form.requiere_plan_accion = $event === true
                        "
                    />
                    <Label for="requiere_plan">Requiere plan de acción</Label>
                </div>
            </div>
        </section>

        <!-- Sección: Evidencias -->
        <section class="space-y-4 rounded-lg border p-6">
            <div class="flex items-center justify-between">
                <h3 class="text-base font-semibold">Evidencias</h3>
                <Button
                    type="button"
                    variant="outline"
                    size="sm"
                    @click="agregarEvidencia"
                >
                    <UploadCloud class="mr-2 h-4 w-4" /> Agregar imagen
                </Button>
            </div>

            <!-- Existentes (modo edit) -->
            <div
                v-if="mode === 'edit' && evidenciasExistentes.length > 0"
                class="space-y-2"
            >
                <p class="text-sm font-medium text-muted-foreground">
                    Evidencias actuales
                </p>
                <div class="grid gap-3 md:grid-cols-2">
                    <div
                        v-for="ev in evidenciasExistentes"
                        :key="ev.id"
                        :class="[
                            'flex items-center gap-3 rounded-md border p-3',
                            form.evidencias_a_eliminar.includes(ev.id)
                                ? 'border-red-300 bg-red-50 opacity-60 dark:bg-red-900/20'
                                : '',
                        ]"
                    >
                        <img
                            v-if="ev.imagen_url"
                            :src="ev.imagen_url"
                            :alt="ev.descripcion"
                            class="h-16 w-16 rounded object-cover"
                        />
                        <div class="flex-1 text-sm">
                            <p class="font-medium">{{ ev.descripcion }}</p>
                            <p class="text-xs text-muted-foreground">
                                ID #{{ ev.id }}
                            </p>
                        </div>
                        <Button
                            v-if="!form.evidencias_a_eliminar.includes(ev.id)"
                            type="button"
                            variant="ghost"
                            size="sm"
                            @click="marcarParaEliminar(ev)"
                        >
                            <Trash2 class="h-4 w-4 text-red-600" />
                        </Button>
                        <Button
                            v-else
                            type="button"
                            variant="ghost"
                            size="sm"
                            @click="desmarcarEliminacion(ev.id)"
                        >
                            Deshacer
                        </Button>
                    </div>
                </div>
            </div>

            <!-- Nuevas -->
            <div
                v-if="form[evidenciasField].length > 0"
                class="space-y-3"
            >
                <p class="text-sm font-medium text-muted-foreground">
                    Nuevas evidencias
                </p>
                <div
                    v-for="(ev, idx) in form[evidenciasField]"
                    :key="idx"
                    class="grid gap-2 rounded-md border p-3 md:grid-cols-[1fr_2fr_auto]"
                >
                    <input
                        ref="evidenciasFile"
                        type="file"
                        accept="image/*"
                        @change="onFileChange(idx, $event)"
                        class="text-sm"
                    />
                    <Input
                        v-model="ev.descripcion"
                        placeholder="Descripción"
                        maxlength="200"
                    />
                    <Button
                        type="button"
                        variant="ghost"
                        size="sm"
                        @click="quitarEvidenciaNueva(idx)"
                    >
                        <Trash2 class="h-4 w-4 text-red-600" />
                    </Button>
                </div>
            </div>

            <p
                v-if="
                    evidenciasExistentes.length === 0 &&
                    form[evidenciasField].length === 0
                "
                class="text-sm text-muted-foreground"
            >
                Aún no se han adjuntado evidencias.
            </p>
        </section>

        <!-- Acciones -->
        <div
            class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between"
        >
            <Button
                v-if="mode === 'edit'"
                type="button"
                variant="destructive"
                class="w-full sm:w-auto"
                @click="eliminarReporte"
            >
                Eliminar reporte
            </Button>
            <span v-else class="hidden sm:block"></span>

            <div class="flex flex-col gap-3 sm:flex-row">
                <Button
                    type="button"
                    variant="outline"
                    class="w-full sm:w-auto"
                    @click="cancelar"
                >
                    Cancelar
                </Button>
                <Button
                    v-if="mode === 'create'"
                    type="button"
                    variant="secondary"
                    :disabled="form.processing"
                    class="w-full sm:w-auto"
                    @click="submit(true)"
                >
                    Crear y registrar otro
                </Button>
                <Button
                    type="submit"
                    :disabled="form.processing"
                    class="w-full sm:w-auto"
                >
                    {{
                        mode === 'create' ? 'Crear reporte' : 'Guardar cambios'
                    }}
                </Button>
            </div>
        </div>
    </form>
</template>
