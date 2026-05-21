<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { ArrowLeft, Pencil } from 'lucide-vue-next';
import { computed } from 'vue';
import ConformidadBadge from '@/components/MateriasPrimas/ConformidadBadge.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { formatFechaLocal } from '@/lib/fecha';
import { edit, index, show } from '@/routes/materias-primas';
import type { MateriaPrima } from '@/types/materias-primas';

const props = defineProps<{ materiaPrima: MateriaPrima }>();

defineOptions({
    layout: (props: { materiaPrima: MateriaPrima }) => ({
        breadcrumbs: [
            { title: 'Materia prima', href: index() },
            {
                title: `Recepción #${props.materiaPrima.id}`,
                href: show(props.materiaPrima.id),
            },
        ],
    }),
});

const formatFecha = (iso: string): string =>
    formatFechaLocal(iso, { year: 'numeric', month: 'long', day: 'numeric' });

const todasConformes = computed(
    () =>
        props.materiaPrima.conformidad_mp === 'CONFORME' &&
        props.materiaPrima.conformidad_documentacion === 'CONFORME' &&
        props.materiaPrima.conformidad_vehiculo === 'CONFORME',
);
</script>

<template>
    <Head :title="`Recepción #${props.materiaPrima.id}`" />

    <div class="flex h-full flex-1 flex-col gap-6 p-4">
        <div
            class="flex flex-col gap-3 sm:flex-row sm:flex-wrap sm:items-center sm:justify-between"
        >
            <div class="flex flex-wrap items-center gap-3">
                <Button as-child variant="outline" size="sm">
                    <Link :href="index().url">
                        <ArrowLeft class="mr-2 h-4 w-4" /> Volver
                    </Link>
                </Button>
                <h1 class="text-xl font-semibold tracking-tight sm:text-2xl">
                    Recepción #{{ props.materiaPrima.id }}
                </h1>
                <Badge
                    v-if="todasConformes"
                    variant="outline"
                    class="border-emerald-500 bg-emerald-50 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-400"
                >
                    Todo conforme
                </Badge>
                <Badge
                    v-else
                    variant="outline"
                    class="border-red-500 bg-red-50 text-red-700 dark:bg-red-950 dark:text-red-400"
                >
                    Con no conformidad
                </Badge>
            </div>
            <Button as-child class="w-full sm:w-auto">
                <Link :href="edit(props.materiaPrima.id).url">
                    <Pencil class="mr-2 h-4 w-4" /> Editar
                </Link>
            </Button>
        </div>

        <!-- Conformidades destacadas -->
        <section
            class="grid gap-3 rounded-lg border p-6 sm:grid-cols-3"
        >
            <div>
                <p class="text-xs uppercase text-muted-foreground">
                    Materia prima
                </p>
                <div class="mt-1">
                    <ConformidadBadge
                        :value="materiaPrima.conformidad_mp"
                    />
                </div>
            </div>
            <div>
                <p class="text-xs uppercase text-muted-foreground">
                    Documentación
                </p>
                <div class="mt-1">
                    <ConformidadBadge
                        :value="materiaPrima.conformidad_documentacion"
                    />
                </div>
            </div>
            <div>
                <p class="text-xs uppercase text-muted-foreground">Vehículo</p>
                <div class="mt-1">
                    <ConformidadBadge
                        :value="materiaPrima.conformidad_vehiculo"
                    />
                </div>
            </div>
        </section>

        <!-- Datos -->
        <section class="grid gap-4 rounded-lg border p-6 md:grid-cols-2">
            <div>
                <p class="text-xs uppercase text-muted-foreground">Fecha</p>
                <p class="font-medium">
                    {{ formatFecha(materiaPrima.fecha) }}
                </p>
            </div>
            <div>
                <p class="text-xs uppercase text-muted-foreground">Semana</p>
                <p class="font-medium">{{ materiaPrima.semana }}</p>
            </div>
            <div>
                <p class="text-xs uppercase text-muted-foreground">
                    Tipo de producto
                </p>
                <p class="font-medium">
                    {{ materiaPrima.tipo_producto?.nombre ?? '—' }}
                </p>
            </div>
            <div>
                <p class="text-xs uppercase text-muted-foreground">
                    Proveedor
                </p>
                <p class="font-medium">
                    {{ materiaPrima.proveedor?.nombre ?? '—' }}
                </p>
            </div>
            <div>
                <p class="text-xs uppercase text-muted-foreground">Origen</p>
                <p class="font-medium">
                    {{ materiaPrima.origen?.nombre ?? '—' }}
                </p>
            </div>
            <div>
                <p class="text-xs uppercase text-muted-foreground">
                    Reportado por
                </p>
                <p class="font-medium">
                    {{ materiaPrima.reportado_por?.name ?? '—' }}
                </p>
            </div>
        </section>

        <!-- Detalle -->
        <section class="space-y-4 rounded-lg border p-6">
            <div>
                <p class="text-xs uppercase text-muted-foreground">
                    Causa de NC / Observación
                </p>
                <p class="whitespace-pre-wrap">
                    {{ materiaPrima.causa_nc_observacion }}
                </p>
            </div>
            <div>
                <p class="text-xs uppercase text-muted-foreground">
                    Productos afectados
                </p>
                <p class="whitespace-pre-wrap">
                    {{ materiaPrima.productos_afectados }}
                </p>
            </div>
            <div>
                <p class="text-xs uppercase text-muted-foreground">
                    Acción realizada
                </p>
                <p class="whitespace-pre-wrap">
                    {{ materiaPrima.accion_realizada }}
                </p>
            </div>
        </section>
    </div>
</template>
