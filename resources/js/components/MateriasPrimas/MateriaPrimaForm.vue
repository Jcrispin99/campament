<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { computed, watch } from 'vue';
import ConformidadBadge from '@/components/MateriasPrimas/ConformidadBadge.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { destroy, show, store, update } from '@/routes/materias-primas';
import type {
    ConformidadMp,
    MateriaPrima,
    MateriaPrimaCatalogos,
} from '@/types/materias-primas';

type Mode = 'create' | 'edit';

const props = defineProps<{
    mode: Mode;
    catalogos: MateriaPrimaCatalogos;
    materiaPrima?: MateriaPrima | null;
}>();

const initial = props.materiaPrima;

const calcSemana = (fecha: string): number => {
    if (!fecha) return 1;
    const d = new Date(fecha + 'T00:00:00Z');
    const yearStart = new Date(Date.UTC(d.getUTCFullYear(), 0, 1));
    const diff = (d.getTime() - yearStart.getTime()) / 86400000;
    return Math.max(
        1,
        Math.min(53, Math.ceil((diff + yearStart.getUTCDay() + 1) / 7)),
    );
};

const todayISO = new Date().toISOString().slice(0, 10);

const form = useForm({
    fecha: initial?.fecha?.slice(0, 10) ?? todayISO,
    semana: initial?.semana ?? calcSemana(todayISO),
    tipo_producto_id: initial?.tipo_producto_id ?? (null as number | null),
    proveedor_id: initial?.proveedor_id ?? (null as number | null),
    origen_id: initial?.origen_id ?? (null as number | null),
    conformidad_mp: (initial?.conformidad_mp ?? 'CONFORME') as ConformidadMp,
    conformidad_documentacion: (initial?.conformidad_documentacion ??
        'CONFORME') as ConformidadMp,
    conformidad_vehiculo: (initial?.conformidad_vehiculo ??
        'CONFORME') as ConformidadMp,
    causa_nc_observacion: initial?.causa_nc_observacion ?? '',
    productos_afectados: initial?.productos_afectados ?? '',
    accion_realizada: initial?.accion_realizada ?? '',
    crear_otro: false,
});

watch(
    () => form.fecha,
    (nuevaFecha) => {
        if (nuevaFecha) form.semana = calcSemana(nuevaFecha);
    },
);

const hayNoConformidad = computed(
    () =>
        form.conformidad_mp === 'NO_CONFORME' ||
        form.conformidad_documentacion === 'NO_CONFORME' ||
        form.conformidad_vehiculo === 'NO_CONFORME',
);

const submit = (seguirRegistrando: boolean = false) => {
    if (props.mode === 'create') {
        form.crear_otro = seguirRegistrando;
        form.post(store().url, { preserveScroll: true });
    } else if (initial) {
        form.put(update(initial.id).url, { preserveScroll: true });
    }
};

const cancelar = () => {
    if (props.mode === 'edit' && initial) {
        window.location.href = show(initial.id).url;
    } else {
        window.history.back();
    }
};

const eliminarMp = () => {
    if (!initial) return;
    if (!confirm('¿Eliminar este registro? Esta acción no se puede deshacer.')) {
        return;
    }
    form.delete(destroy(initial.id).url);
};
</script>

<template>
    <form class="space-y-8" @submit.prevent="submit()">
        <!-- Información general -->
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
            </div>
        </section>

        <!-- Origen del producto -->
        <section class="space-y-4 rounded-lg border p-6">
            <h3 class="text-base font-semibold">Origen del producto</h3>

            <div class="grid gap-4 md:grid-cols-3">
                <div class="space-y-2">
                    <Label for="tipo_producto">Tipo de producto *</Label>
                    <Select
                        :model-value="form.tipo_producto_id?.toString()"
                        @update:model-value="
                            form.tipo_producto_id = Number($event)
                        "
                    >
                        <SelectTrigger id="tipo_producto" class="w-full">
                            <SelectValue placeholder="Selecciona un tipo" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem
                                v-for="tp in catalogos.tiposProducto"
                                :key="tp.id"
                                :value="tp.id.toString()"
                            >
                                {{ tp.nombre }}
                            </SelectItem>
                        </SelectContent>
                    </Select>
                    <InputError :message="form.errors.tipo_producto_id" />
                </div>
                <div class="space-y-2">
                    <Label for="proveedor">Proveedor *</Label>
                    <Select
                        :model-value="form.proveedor_id?.toString()"
                        @update:model-value="form.proveedor_id = Number($event)"
                    >
                        <SelectTrigger id="proveedor" class="w-full">
                            <SelectValue placeholder="Selecciona un proveedor" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem
                                v-for="p in catalogos.proveedores"
                                :key="p.id"
                                :value="p.id.toString()"
                            >
                                {{ p.nombre }}
                            </SelectItem>
                        </SelectContent>
                    </Select>
                    <InputError :message="form.errors.proveedor_id" />
                </div>
                <div class="space-y-2">
                    <Label for="origen">Origen *</Label>
                    <Select
                        :model-value="form.origen_id?.toString()"
                        @update:model-value="form.origen_id = Number($event)"
                    >
                        <SelectTrigger id="origen" class="w-full">
                            <SelectValue placeholder="Selecciona un origen" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem
                                v-for="o in catalogos.origenes"
                                :key="o.id"
                                :value="o.id.toString()"
                            >
                                {{ o.nombre }}
                            </SelectItem>
                        </SelectContent>
                    </Select>
                    <InputError :message="form.errors.origen_id" />
                </div>
            </div>
        </section>

        <!-- Conformidades -->
        <section class="space-y-4 rounded-lg border p-6">
            <h3 class="text-base font-semibold">Conformidades</h3>

            <div class="grid gap-4 md:grid-cols-3">
                <div class="space-y-2">
                    <div class="flex items-center justify-between">
                        <Label for="conf_mp">Materia prima *</Label>
                        <ConformidadBadge :value="form.conformidad_mp" />
                    </div>
                    <Select
                        :model-value="form.conformidad_mp"
                        @update:model-value="
                            form.conformidad_mp = $event as ConformidadMp
                        "
                    >
                        <SelectTrigger id="conf_mp" class="w-full">
                            <SelectValue />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem
                                v-for="opt in catalogos.conformidades"
                                :key="opt.value"
                                :value="opt.value"
                            >
                                {{ opt.label }}
                            </SelectItem>
                        </SelectContent>
                    </Select>
                    <InputError :message="form.errors.conformidad_mp" />
                </div>
                <div class="space-y-2">
                    <div class="flex items-center justify-between">
                        <Label for="conf_doc">Documentación *</Label>
                        <ConformidadBadge
                            :value="form.conformidad_documentacion"
                        />
                    </div>
                    <Select
                        :model-value="form.conformidad_documentacion"
                        @update:model-value="
                            form.conformidad_documentacion =
                                $event as ConformidadMp
                        "
                    >
                        <SelectTrigger id="conf_doc" class="w-full">
                            <SelectValue />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem
                                v-for="opt in catalogos.conformidades"
                                :key="opt.value"
                                :value="opt.value"
                            >
                                {{ opt.label }}
                            </SelectItem>
                        </SelectContent>
                    </Select>
                    <InputError
                        :message="form.errors.conformidad_documentacion"
                    />
                </div>
                <div class="space-y-2">
                    <div class="flex items-center justify-between">
                        <Label for="conf_veh">Vehículo *</Label>
                        <ConformidadBadge :value="form.conformidad_vehiculo" />
                    </div>
                    <Select
                        :model-value="form.conformidad_vehiculo"
                        @update:model-value="
                            form.conformidad_vehiculo =
                                $event as ConformidadMp
                        "
                    >
                        <SelectTrigger id="conf_veh" class="w-full">
                            <SelectValue />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem
                                v-for="opt in catalogos.conformidades"
                                :key="opt.value"
                                :value="opt.value"
                            >
                                {{ opt.label }}
                            </SelectItem>
                        </SelectContent>
                    </Select>
                    <InputError :message="form.errors.conformidad_vehiculo" />
                </div>
            </div>

            <p
                v-if="hayNoConformidad"
                class="rounded-md border border-red-200 bg-red-50 p-3 text-sm text-red-700 dark:border-red-900 dark:bg-red-950 dark:text-red-400"
            >
                Hay al menos una No Conformidad. Asegurate de detallar la
                causa, los productos afectados y la acción realizada.
            </p>
        </section>

        <!-- Causa, productos afectados y acción -->
        <section class="space-y-4 rounded-lg border p-6">
            <h3 class="text-base font-semibold">
                Detalle de la recepción
            </h3>

            <div class="space-y-2">
                <Label for="causa">
                    Causa de NC / Observación *
                </Label>
                <textarea
                    id="causa"
                    v-model="form.causa_nc_observacion"
                    rows="3"
                    required
                    class="w-full rounded-md border bg-background px-3 py-2 text-sm focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring"
                    placeholder="Si todo conforme, indica «Sin novedad»."
                ></textarea>
                <InputError :message="form.errors.causa_nc_observacion" />
            </div>

            <div class="space-y-2">
                <Label for="afectados">Productos afectados *</Label>
                <textarea
                    id="afectados"
                    v-model="form.productos_afectados"
                    rows="2"
                    required
                    class="w-full rounded-md border bg-background px-3 py-2 text-sm focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring"
                    placeholder="Si todo conforme, indica «Ninguno»."
                ></textarea>
                <InputError :message="form.errors.productos_afectados" />
            </div>

            <div class="space-y-2">
                <Label for="accion">Acción realizada *</Label>
                <textarea
                    id="accion"
                    v-model="form.accion_realizada"
                    rows="3"
                    required
                    class="w-full rounded-md border bg-background px-3 py-2 text-sm focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring"
                    placeholder="Recepción normal, rechazo, cuarentena, etc."
                ></textarea>
                <InputError :message="form.errors.accion_realizada" />
            </div>
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
                @click="eliminarMp"
            >
                Eliminar registro
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
                        mode === 'create'
                            ? 'Crear registro'
                            : 'Guardar cambios'
                    }}
                </Button>
            </div>
        </div>
    </form>
</template>
