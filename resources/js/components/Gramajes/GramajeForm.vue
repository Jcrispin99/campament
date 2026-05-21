<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { Plus, Trash2 } from 'lucide-vue-next';
import { computed, watch } from 'vue';
import EstatusBadge from '@/components/Gramajes/EstatusBadge.vue';
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
import { todayLocalISO } from '@/lib/fecha';
import { destroy, show, store, update } from '@/routes/gramajes';
import type {
    EstatusGramaje,
    Gramaje,
    GramajeCatalogos,
} from '@/types/gramajes';

type Mode = 'create' | 'edit';

const props = defineProps<{
    mode: Mode;
    catalogos: GramajeCatalogos;
    gramaje?: Gramaje | null;
}>();

const initialGramaje = props.gramaje;

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

const todayISO = todayLocalISO();

const initialMedidas: number[] = initialGramaje?.medidas?.length
    ? initialGramaje.medidas.map((m) => Number(m.peso))
    : [];

const form = useForm({
    fecha: initialGramaje?.fecha?.slice(0, 10) ?? todayISO,
    semana: initialGramaje?.semana ?? calcSemana(todayISO),
    fecha_produccion:
        initialGramaje?.fecha_produccion?.slice(0, 10) ?? todayISO,
    comedor_id: initialGramaje?.comedor_id ?? (null as number | null),
    servicio_id: initialGramaje?.servicio_id ?? (null as number | null),
    plato_id: initialGramaje?.plato_id ?? (null as number | null),
    componente_id: initialGramaje?.componente_id ?? (null as number | null),
    tipo_corte_id: initialGramaje?.tipo_corte_id ?? (null as number | null),
    gramaje_esperado: initialGramaje?.gramaje_esperado
        ? Number(initialGramaje.gramaje_esperado)
        : (null as number | null),
    medidas: initialMedidas,
    crear_otro: false,
});

watch(
    () => form.fecha,
    (nuevaFecha) => {
        if (nuevaFecha) form.semana = calcSemana(nuevaFecha);
    },
);

const componenteSeleccionado = computed(() =>
    props.catalogos.componentes.find((c) => c.id === form.componente_id),
);

const unidadActual = computed(
    () => componenteSeleccionado.value?.unidad ?? 'GRAMOS',
);

const unidadLabel = computed(() =>
    unidadActual.value === 'UNIDADES' ? 'u' : 'g',
);

watch(
    () => form.componente_id,
    (nuevo, anterior) => {
        if (nuevo !== anterior && componenteSeleccionado.value) {
            form.gramaje_esperado = Number(
                componenteSeleccionado.value.gramaje_sugerido,
            );
        }
    },
);

const agregarMedida = () => {
    form.medidas.push(0);
};

const quitarMedida = (idx: number) => {
    form.medidas.splice(idx, 1);
};

const medidasValidas = computed(() =>
    form.medidas.filter((m) => typeof m === 'number' && m > 0),
);

const pesoPromedio = computed(() => {
    if (medidasValidas.value.length === 0) return 0;
    const suma = medidasValidas.value.reduce((acc, n) => acc + n, 0);
    return Math.round((suma / medidasValidas.value.length) * 100) / 100;
});

const variacionPct = computed(() => {
    const esperado = Number(form.gramaje_esperado);
    if (!esperado || esperado <= 0 || pesoPromedio.value === 0) return 0;
    return Math.round((pesoPromedio.value / esperado) * 100 * 100) / 100;
});

const estatusPreview = computed<EstatusGramaje>(() =>
    variacionPct.value >= 100 ? 'CONFORME' : 'INCONFORME',
);

const submit = (seguirRegistrando: boolean = false) => {
    if (props.mode === 'create') {
        form.crear_otro = seguirRegistrando;
        form.post(store().url, { preserveScroll: true });
    } else if (initialGramaje) {
        form.put(update(initialGramaje.id).url, { preserveScroll: true });
    }
};

const cancelar = () => {
    if (props.mode === 'edit' && initialGramaje) {
        window.location.href = show(initialGramaje.id).url;
    } else {
        window.history.back();
    }
};

const eliminarGramaje = () => {
    if (!initialGramaje) return;
    if (
        !confirm(
            '¿Eliminar este registro de gramaje? Esta acción no se puede deshacer.',
        )
    ) {
        return;
    }
    form.delete(destroy(initialGramaje.id).url);
};
</script>

<template>
    <form class="space-y-8" @submit.prevent="submit()">
        <!-- Sección: Información general -->
        <section class="space-y-4 rounded-lg border p-6">
            <h3 class="text-base font-semibold">Información general</h3>

            <div class="grid gap-4 md:grid-cols-2">
                <div class="space-y-2">
                    <Label for="fecha">Fecha de muestreo *</Label>
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
                    <Label for="fecha_produccion">Fecha de producción *</Label>
                    <Input
                        id="fecha_produccion"
                        v-model="form.fecha_produccion"
                        type="date"
                        :max="form.fecha"
                        required
                    />
                    <InputError :message="form.errors.fecha_produccion" />
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

        <!-- Sección: Plato y componente -->
        <section class="space-y-4 rounded-lg border p-6">
            <h3 class="text-base font-semibold">Plato y componente</h3>

            <div class="grid gap-4 md:grid-cols-2">
                <div class="space-y-2">
                    <Label for="plato">Plato *</Label>
                    <Select
                        :model-value="form.plato_id?.toString()"
                        @update:model-value="form.plato_id = Number($event)"
                    >
                        <SelectTrigger id="plato" class="w-full">
                            <SelectValue placeholder="Selecciona un plato" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem
                                v-for="p in catalogos.platos"
                                :key="p.id"
                                :value="p.id.toString()"
                            >
                                {{ p.nombre }}
                            </SelectItem>
                        </SelectContent>
                    </Select>
                    <InputError :message="form.errors.plato_id" />
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
                                {{ c.nombre }} ({{ c.gramaje_sugerido }}
                                {{ c.unidad === 'UNIDADES' ? 'u' : 'g' }})
                            </SelectItem>
                        </SelectContent>
                    </Select>
                    <InputError :message="form.errors.componente_id" />
                    <p
                        v-if="componenteSeleccionado?.observacion"
                        class="text-xs text-muted-foreground"
                    >
                        Nota: {{ componenteSeleccionado.observacion }}
                    </p>
                </div>
                <div class="space-y-2">
                    <Label for="tipo_corte">
                        Tipo de corte
                        <span class="text-xs text-muted-foreground">
                            (opcional, solo aplica a cárnicos)
                        </span>
                    </Label>
                    <Select
                        :model-value="form.tipo_corte_id?.toString() ?? ''"
                        @update:model-value="
                            form.tipo_corte_id = $event
                                ? Number($event)
                                : null
                        "
                    >
                        <SelectTrigger id="tipo_corte" class="w-full">
                            <SelectValue
                                placeholder="Sin tipo de corte"
                            />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem
                                v-for="tc in catalogos.tiposCorte"
                                :key="tc.id"
                                :value="tc.id.toString()"
                            >
                                {{ tc.nombre }}
                            </SelectItem>
                        </SelectContent>
                    </Select>
                    <InputError :message="form.errors.tipo_corte_id" />
                </div>
                <div class="space-y-2">
                    <Label for="gramaje_esperado">
                        Gramaje esperado * ({{ unidadLabel }})
                    </Label>
                    <Input
                        id="gramaje_esperado"
                        :model-value="form.gramaje_esperado ?? ''"
                        type="number"
                        step="0.01"
                        min="0.01"
                        required
                        @update:model-value="
                            form.gramaje_esperado =
                                $event === '' || $event === null
                                    ? null
                                    : Number($event)
                        "
                    />
                    <p class="text-xs text-muted-foreground">
                        Se pre-rellena desde el componente. Editable si
                        cambió ese día.
                    </p>
                    <InputError :message="form.errors.gramaje_esperado" />
                </div>
            </div>
        </section>

        <!-- Sección: Medidas -->
        <section class="space-y-4 rounded-lg border p-6">
            <div
                class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between"
            >
                <div>
                    <h3 class="text-base font-semibold">
                        Medidas muestreadas
                    </h3>
                    <p class="text-sm text-muted-foreground">
                        Agrega el peso de cada porción pesada. El promedio y la
                        variación se calculan automáticamente.
                    </p>
                </div>
                <Button
                    type="button"
                    variant="outline"
                    size="sm"
                    class="w-full sm:w-auto"
                    @click="agregarMedida"
                >
                    <Plus class="mr-2 h-4 w-4" /> Agregar medida
                </Button>
            </div>

            <div v-if="form.medidas.length === 0" class="rounded-md border border-dashed p-6 text-center text-sm text-muted-foreground">
                Aún no se han agregado medidas. Toca «Agregar medida» para
                empezar.
            </div>

            <div v-else class="space-y-2">
                <div
                    v-for="(_, idx) in form.medidas"
                    :key="idx"
                    class="grid grid-cols-[auto_1fr_auto_auto] items-center gap-2 rounded-md border p-2"
                >
                    <span class="w-8 text-center text-sm text-muted-foreground">
                        #{{ idx + 1 }}
                    </span>
                    <Input
                        v-model.number="form.medidas[idx]"
                        type="number"
                        step="0.01"
                        min="0.01"
                        :placeholder="`Peso en ${unidadLabel}`"
                    />
                    <span class="text-sm text-muted-foreground">
                        {{ unidadLabel }}
                    </span>
                    <Button
                        type="button"
                        variant="ghost"
                        size="sm"
                        @click="quitarMedida(idx)"
                    >
                        <Trash2 class="h-4 w-4 text-red-600" />
                    </Button>
                </div>
                <InputError :message="form.errors.medidas" />
                <InputError
                    v-for="(_, idx) in form.medidas"
                    :key="`err-${idx}`"
                    :message="
                        (form.errors as Record<string, string>)[
                            `medidas.${idx}`
                        ]
                    "
                />
            </div>

            <!-- Resumen calculado -->
            <div
                class="grid gap-3 rounded-lg bg-muted/40 p-4 sm:grid-cols-2 md:grid-cols-4"
            >
                <div>
                    <p class="text-xs uppercase text-muted-foreground">
                        Cantidad
                    </p>
                    <p class="text-lg font-semibold">
                        {{ medidasValidas.length }}
                    </p>
                </div>
                <div>
                    <p class="text-xs uppercase text-muted-foreground">
                        Peso promedio
                    </p>
                    <p class="text-lg font-semibold">
                        {{ pesoPromedio }} {{ unidadLabel }}
                    </p>
                </div>
                <div>
                    <p class="text-xs uppercase text-muted-foreground">
                        Variación
                    </p>
                    <p class="text-lg font-semibold">
                        {{ variacionPct }} %
                    </p>
                </div>
                <div>
                    <p class="text-xs uppercase text-muted-foreground">
                        Estatus
                    </p>
                    <div class="mt-1">
                        <EstatusBadge
                            v-if="medidasValidas.length > 0"
                            :value="estatusPreview"
                        />
                        <span v-else class="text-sm text-muted-foreground">
                            —
                        </span>
                    </div>
                </div>
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
                @click="eliminarGramaje"
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
