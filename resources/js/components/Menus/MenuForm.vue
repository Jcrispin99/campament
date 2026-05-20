<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { computed, watch } from 'vue';
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
import { destroy, show, store, update } from '@/routes/menus';
import type { Menu, MenuCatalogos } from '@/types/menus';

type Mode = 'create' | 'edit';

const props = defineProps<{
    mode: Mode;
    catalogos: MenuCatalogos;
    menu?: Menu | null;
}>();

const initialMenu = props.menu;

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
    fecha: initialMenu?.fecha?.slice(0, 10) ?? todayISO,
    semana: initialMenu?.semana ?? calcSemana(todayISO),
    fecha_solicitud:
        initialMenu?.fecha_solicitud?.slice(0, 10) ?? todayISO,
    fecha_cambio: initialMenu?.fecha_cambio?.slice(0, 10) ?? todayISO,
    servicio_id: initialMenu?.servicio_id ?? (null as number | null),
    componente_id: initialMenu?.componente_id ?? (null as number | null),
    programado: initialMenu?.programado ?? '',
    propuesta: initialMenu?.propuesta ?? '',
    motivo: initialMenu?.motivo ?? '',
    comentario: initialMenu?.comentario ?? '',
    conformidad: initialMenu?.conformidad ?? '',
    analisis: initialMenu?.analisis ?? '',
    crear_otro: false,
});

watch(
    () => form.fecha,
    (nuevaFecha) => {
        if (nuevaFecha) form.semana = calcSemana(nuevaFecha);
    },
);

const diasPrevisionPreview = computed(() => {
    if (!form.fecha_solicitud || !form.fecha_cambio) return 0;
    const a = new Date(form.fecha_solicitud + 'T00:00:00Z').getTime();
    const b = new Date(form.fecha_cambio + 'T00:00:00Z').getTime();
    if (b < a) return 0;
    return Math.round((b - a) / 86400000);
});

const rangoFechasInvalido = computed(
    () =>
        !!form.fecha_solicitud &&
        !!form.fecha_cambio &&
        form.fecha_cambio < form.fecha_solicitud,
);

const submit = (seguirRegistrando: boolean = false) => {
    if (props.mode === 'create') {
        form.crear_otro = seguirRegistrando;
        form.post(store().url, { preserveScroll: true });
    } else if (initialMenu) {
        form.put(update(initialMenu.id).url, { preserveScroll: true });
    }
};

const cancelar = () => {
    if (props.mode === 'edit' && initialMenu) {
        window.location.href = show(initialMenu.id).url;
    } else {
        window.history.back();
    }
};

const eliminarMenu = () => {
    if (!initialMenu) return;
    if (
        !confirm(
            '¿Eliminar este cambio de menú? Esta acción no se puede deshacer.',
        )
    ) {
        return;
    }
    form.delete(destroy(initialMenu.id).url);
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
                <div class="space-y-2">
                    <Label for="fecha_solicitud">Fecha de solicitud *</Label>
                    <Input
                        id="fecha_solicitud"
                        v-model="form.fecha_solicitud"
                        type="date"
                        required
                    />
                    <InputError :message="form.errors.fecha_solicitud" />
                </div>
                <div class="space-y-2">
                    <Label for="fecha_cambio">Fecha de cambio *</Label>
                    <Input
                        id="fecha_cambio"
                        v-model="form.fecha_cambio"
                        type="date"
                        :min="form.fecha_solicitud"
                        required
                    />
                    <InputError :message="form.errors.fecha_cambio" />
                    <p
                        v-if="rangoFechasInvalido"
                        class="text-xs text-red-600 dark:text-red-500"
                    >
                        La fecha de cambio debe ser igual o posterior a la de
                        solicitud.
                    </p>
                </div>
            </div>

            <div
                class="rounded-md bg-muted/40 p-4 text-sm"
                :class="rangoFechasInvalido ? 'opacity-50' : ''"
            >
                <p class="text-xs uppercase text-muted-foreground">
                    Días de previsión (calculado)
                </p>
                <p class="text-lg font-semibold">
                    {{ diasPrevisionPreview }}
                    {{
                        diasPrevisionPreview === 1
                            ? 'día'
                            : 'días'
                    }}
                </p>
            </div>
        </section>

        <!-- Servicio y componente -->
        <section class="space-y-4 rounded-lg border p-6">
            <h3 class="text-base font-semibold">Servicio y componente</h3>

            <div class="grid gap-4 md:grid-cols-2">
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
                <div class="space-y-2">
                    <Label for="componente">Componente *</Label>
                    <Select
                        :model-value="form.componente_id?.toString()"
                        @update:model-value="
                            form.componente_id = Number($event)
                        "
                    >
                        <SelectTrigger id="componente" class="w-full">
                            <SelectValue
                                placeholder="Selecciona un componente"
                            />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem
                                v-for="c in catalogos.componentes"
                                :key="c.id"
                                :value="c.id.toString()"
                            >
                                {{ c.nombre }}
                            </SelectItem>
                        </SelectContent>
                    </Select>
                    <InputError :message="form.errors.componente_id" />
                </div>
            </div>
        </section>

        <!-- Detalles del cambio -->
        <section class="space-y-4 rounded-lg border p-6">
            <h3 class="text-base font-semibold">Detalles del cambio</h3>

            <div class="grid gap-4 md:grid-cols-2">
                <div class="space-y-2">
                    <Label for="programado">Programado *</Label>
                    <Input
                        id="programado"
                        v-model="form.programado"
                        maxlength="200"
                        required
                        placeholder="Lo que estaba originalmente"
                    />
                    <InputError :message="form.errors.programado" />
                </div>
                <div class="space-y-2">
                    <Label for="propuesta">Propuesta *</Label>
                    <Input
                        id="propuesta"
                        v-model="form.propuesta"
                        maxlength="200"
                        required
                        placeholder="Lo que se propone como reemplazo"
                    />
                    <InputError :message="form.errors.propuesta" />
                </div>
            </div>

            <div class="space-y-2">
                <Label for="motivo">Motivo *</Label>
                <textarea
                    id="motivo"
                    v-model="form.motivo"
                    rows="3"
                    required
                    class="w-full rounded-md border bg-background px-3 py-2 text-sm focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring"
                ></textarea>
                <InputError :message="form.errors.motivo" />
            </div>

            <div class="space-y-2">
                <Label for="comentario">
                    Comentario
                    <span class="text-xs text-muted-foreground">
                        (opcional)
                    </span>
                </Label>
                <textarea
                    id="comentario"
                    v-model="form.comentario"
                    rows="2"
                    class="w-full rounded-md border bg-background px-3 py-2 text-sm focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring"
                ></textarea>
                <InputError :message="form.errors.comentario" />
            </div>
        </section>

        <!-- Análisis y conformidad -->
        <section class="space-y-4 rounded-lg border p-6">
            <h3 class="text-base font-semibold">Análisis y conformidad</h3>

            <div class="space-y-2">
                <Label for="conformidad">Conformidad *</Label>
                <Input
                    id="conformidad"
                    v-model="form.conformidad"
                    maxlength="50"
                    required
                    placeholder="Conforme, Inconforme, Pendiente, etc."
                />
                <InputError :message="form.errors.conformidad" />
            </div>

            <div class="space-y-2">
                <Label for="analisis">Análisis *</Label>
                <textarea
                    id="analisis"
                    v-model="form.analisis"
                    rows="4"
                    required
                    class="w-full rounded-md border bg-background px-3 py-2 text-sm focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring"
                ></textarea>
                <InputError :message="form.errors.analisis" />
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
                @click="eliminarMenu"
            >
                Eliminar cambio de menú
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
                            ? 'Crear cambio de menú'
                            : 'Guardar cambios'
                    }}
                </Button>
            </div>
        </div>
    </form>
</template>
