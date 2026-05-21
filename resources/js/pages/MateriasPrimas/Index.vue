<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { Download, Eye, Pencil, Plus, Trash2 } from 'lucide-vue-next';
import { computed, ref } from 'vue';
import ExportarRangoFechasModal from '@/components/Exports/ExportarRangoFechasModal.vue';
import ConformidadBadge from '@/components/MateriasPrimas/ConformidadBadge.vue';
import { Button } from '@/components/ui/button';
import { formatFechaLocal } from '@/lib/fecha';
import {
    create,
    destroy,
    edit,
    exportMethod,
    index,
    show,
} from '@/routes/materias-primas';
import type {
    MateriaPrimaListItem,
    PaginatedMateriasPrimas,
} from '@/types/materias-primas';

const props = defineProps<{ materiasPrimas: PaginatedMateriasPrimas }>();

const exportarOpen = ref(false);

defineOptions({
    layout: () => ({
        breadcrumbs: [{ title: 'Materia prima', href: index() }],
    }),
});

const formatFecha = (iso: string): string =>
    formatFechaLocal(iso, { year: 'numeric', month: '2-digit', day: '2-digit' });

const isFullConforme = (m: MateriaPrimaListItem): boolean =>
    m.conformidad_mp === 'CONFORME' &&
    m.conformidad_documentacion === 'CONFORME' &&
    m.conformidad_vehiculo === 'CONFORME';

const totalNoConforme = computed(() =>
    props.materiasPrimas.data.filter((m) => !isFullConforme(m)).length,
);

const confirmarEliminar = (id: number) => {
    if (!confirm('¿Eliminar este registro?')) return;
    router.delete(destroy(id).url, { preserveScroll: true });
};
</script>

<template>
    <Head title="Materia prima" />

    <div class="flex h-full flex-1 flex-col gap-6 p-4">
        <div
            class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between"
        >
            <div>
                <h1 class="text-2xl font-semibold tracking-tight">
                    Materia prima
                </h1>
                <p class="text-sm text-muted-foreground">
                    Recepciones de materia prima con conformidad de MP,
                    documentación y vehículo.
                    <span
                        v-if="totalNoConforme > 0"
                        class="ml-1 font-medium text-red-700 dark:text-red-400"
                    >
                        ({{ totalNoConforme }} con no conformidad)
                    </span>
                </p>
            </div>
            <div class="flex flex-col gap-2 sm:flex-row">
                <Button
                    variant="outline"
                    class="w-full sm:w-auto"
                    @click="exportarOpen = true"
                >
                    <Download class="mr-2 h-4 w-4" /> Exportar Excel
                </Button>
                <Button as-child class="w-full sm:w-auto">
                    <Link :href="create().url">
                        <Plus class="mr-2 h-4 w-4" /> Nueva recepción
                    </Link>
                </Button>
            </div>
        </div>

        <ExportarRangoFechasModal
            :open="exportarOpen"
            titulo="Exportar materia prima a Excel"
            :export-url-fn="(query) => exportMethod({ query }).url"
            @update:open="exportarOpen = $event"
        />

        <!-- Mobile: cards -->
        <div class="space-y-3 md:hidden">
            <div
                v-if="materiasPrimas.data.length === 0"
                class="rounded-lg border p-8 text-center text-muted-foreground"
            >
                Aún no hay recepciones registradas.
            </div>

            <article
                v-for="m in materiasPrimas.data"
                :key="m.id"
                class="rounded-lg border bg-card p-4 shadow-sm"
            >
                <Link :href="show(m.id).url" class="block space-y-3">
                    <div class="flex items-start justify-between gap-2">
                        <div>
                            <p class="text-sm text-muted-foreground">
                                {{ formatFecha(m.fecha) }} · Sem.
                                {{ m.semana }}
                            </p>
                            <p class="mt-0.5 font-medium leading-tight">
                                {{ m.proveedor?.nombre ?? '—' }}
                            </p>
                            <p class="text-xs text-muted-foreground">
                                {{ m.tipo_producto?.nombre ?? '—' }} ·
                                {{ m.origen?.nombre ?? '—' }}
                            </p>
                        </div>
                    </div>

                    <div class="space-y-1">
                        <div class="flex items-center justify-between text-xs">
                            <span class="text-muted-foreground">MP</span>
                            <ConformidadBadge :value="m.conformidad_mp" />
                        </div>
                        <div class="flex items-center justify-between text-xs">
                            <span class="text-muted-foreground">
                                Documentación
                            </span>
                            <ConformidadBadge
                                :value="m.conformidad_documentacion"
                            />
                        </div>
                        <div class="flex items-center justify-between text-xs">
                            <span class="text-muted-foreground">
                                Vehículo
                            </span>
                            <ConformidadBadge
                                :value="m.conformidad_vehiculo"
                            />
                        </div>
                    </div>
                </Link>

                <div class="mt-3 grid grid-cols-3 gap-2 border-t pt-3">
                    <Button as-child variant="ghost" size="default" class="h-11">
                        <Link :href="show(m.id).url">
                            <Eye class="mr-1 h-4 w-4" /> Ver
                        </Link>
                    </Button>
                    <Button as-child variant="ghost" size="default" class="h-11">
                        <Link :href="edit(m.id).url">
                            <Pencil class="mr-1 h-4 w-4" /> Editar
                        </Link>
                    </Button>
                    <Button
                        variant="ghost"
                        size="default"
                        class="h-11 text-red-600 hover:text-red-700"
                        @click="confirmarEliminar(m.id)"
                    >
                        <Trash2 class="mr-1 h-4 w-4" /> Borrar
                    </Button>
                </div>
            </article>
        </div>

        <!-- Desktop: tabla -->
        <div class="hidden overflow-hidden rounded-xl border md:block">
            <table class="w-full text-sm">
                <thead class="bg-muted/50 text-left">
                    <tr>
                        <th class="px-4 py-3 font-medium">Fecha</th>
                        <th class="px-4 py-3 font-medium">Sem.</th>
                        <th class="px-4 py-3 font-medium">Tipo</th>
                        <th class="px-4 py-3 font-medium">Proveedor</th>
                        <th class="px-4 py-3 font-medium">Origen</th>
                        <th class="px-4 py-3 font-medium">MP</th>
                        <th class="px-4 py-3 font-medium">Documentación</th>
                        <th class="px-4 py-3 font-medium">Vehículo</th>
                        <th class="px-4 py-3 text-right font-medium">
                            Acciones
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <tr
                        v-for="m in materiasPrimas.data"
                        :key="m.id"
                        class="border-t hover:bg-muted/30"
                    >
                        <td class="px-4 py-3">{{ formatFecha(m.fecha) }}</td>
                        <td class="px-4 py-3">{{ m.semana }}</td>
                        <td class="px-4 py-3">
                            {{ m.tipo_producto?.nombre ?? '—' }}
                        </td>
                        <td class="px-4 py-3">
                            {{ m.proveedor?.nombre ?? '—' }}
                        </td>
                        <td class="px-4 py-3">
                            {{ m.origen?.nombre ?? '—' }}
                        </td>
                        <td class="px-4 py-3">
                            <ConformidadBadge :value="m.conformidad_mp" />
                        </td>
                        <td class="px-4 py-3">
                            <ConformidadBadge
                                :value="m.conformidad_documentacion"
                            />
                        </td>
                        <td class="px-4 py-3">
                            <ConformidadBadge
                                :value="m.conformidad_vehiculo"
                            />
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex justify-end gap-1">
                                <Button
                                    as-child
                                    variant="ghost"
                                    size="sm"
                                    title="Ver"
                                >
                                    <Link :href="show(m.id).url">
                                        <Eye class="h-4 w-4" />
                                    </Link>
                                </Button>
                                <Button
                                    as-child
                                    variant="ghost"
                                    size="sm"
                                    title="Editar"
                                >
                                    <Link :href="edit(m.id).url">
                                        <Pencil class="h-4 w-4" />
                                    </Link>
                                </Button>
                                <Button
                                    variant="ghost"
                                    size="sm"
                                    title="Eliminar"
                                    @click="confirmarEliminar(m.id)"
                                >
                                    <Trash2 class="h-4 w-4 text-red-600" />
                                </Button>
                            </div>
                        </td>
                    </tr>
                    <tr v-if="materiasPrimas.data.length === 0">
                        <td
                            colspan="9"
                            class="px-4 py-12 text-center text-muted-foreground"
                        >
                            Aún no hay recepciones registradas.
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div
            v-if="materiasPrimas.last_page > 1"
            class="flex flex-col items-stretch gap-3 sm:flex-row sm:items-center sm:justify-between"
        >
            <p class="text-sm text-muted-foreground">
                Mostrando {{ materiasPrimas.data.length }} de
                {{ materiasPrimas.total }} registros
            </p>
            <div class="flex flex-wrap justify-center gap-1 sm:justify-end">
                <Link
                    v-for="(link, idx) in materiasPrimas.links"
                    :key="idx"
                    :href="link.url ?? '#'"
                    :class="[
                        'min-w-11 rounded-md border px-3 py-2 text-center text-sm',
                        link.active
                            ? 'bg-primary text-primary-foreground'
                            : '',
                        !link.url
                            ? 'pointer-events-none opacity-50'
                            : 'hover:bg-muted',
                    ]"
                    v-html="link.label"
                />
            </div>
        </div>
    </div>
</template>
