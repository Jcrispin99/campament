<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { Download, Eye, Pencil, Plus, Trash2 } from 'lucide-vue-next';
import CriticidadBadge from '@/components/Reportes/CriticidadBadge.vue';
import { Button } from '@/components/ui/button';
import {
    create,
    destroy,
    edit,
    exportMethod,
    index,
    show,
} from '@/routes/reportes';
import type { PaginatedReportes } from '@/types/reportes';

defineProps<{ reportes: PaginatedReportes }>();

defineOptions({
    layout: () => ({
        breadcrumbs: [{ title: 'Reportes', href: index() }],
    }),
});

const formatFecha = (iso: string): string => {
    const d = new Date(iso);
    return d.toLocaleDateString('es-PE', {
        year: 'numeric',
        month: '2-digit',
        day: '2-digit',
    });
};

const confirmarEliminar = (id: number) => {
    if (!confirm('¿Eliminar este reporte? Esta acción no se puede deshacer.')) {
        return;
    }
    router.delete(destroy(id).url, { preserveScroll: true });
};
</script>

<template>
    <Head title="Reportes" />

    <div class="flex h-full flex-1 flex-col gap-6 p-4">
        <div
            class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between"
        >
            <div>
                <h1 class="text-2xl font-semibold tracking-tight">Reportes</h1>
                <p class="text-sm text-muted-foreground">
                    Inspecciones de comedores e incidentes registrados.
                </p>
            </div>
            <div class="flex flex-col gap-2 sm:flex-row">
                <Button
                    as-child
                    variant="outline"
                    class="w-full sm:w-auto"
                >
                    <a
                        :href="exportMethod().url"
                        :download="`reportes-${new Date().toISOString().slice(0, 10)}.xlsx`"
                    >
                        <Download class="mr-2 h-4 w-4" /> Exportar Excel
                    </a>
                </Button>
                <Button as-child class="w-full sm:w-auto">
                    <Link :href="create().url">
                        <Plus class="mr-2 h-4 w-4" /> Nuevo reporte
                    </Link>
                </Button>
            </div>
        </div>

        <!-- Mobile: lista de cards -->
        <div class="space-y-3 md:hidden">
            <div
                v-if="reportes.data.length === 0"
                class="rounded-lg border p-8 text-center text-muted-foreground"
            >
                No hay reportes aún. Crea el primero con el botón «Nuevo
                reporte».
            </div>

            <article
                v-for="r in reportes.data"
                :key="r.id"
                class="rounded-lg border bg-card p-4 shadow-sm"
            >
                <Link
                    :href="show(r.id).url"
                    class="block space-y-3"
                >
                    <div class="flex items-start justify-between gap-2">
                        <div>
                            <p class="text-sm text-muted-foreground">
                                {{ formatFecha(r.fecha) }} · Sem.
                                {{ r.semana }}
                            </p>
                            <p class="mt-0.5 font-medium leading-tight">
                                {{ r.comedor?.nombre ?? '—' }}
                            </p>
                        </div>
                        <CriticidadBadge :value="r.criticidad" />
                    </div>

                    <dl
                        class="grid grid-cols-2 gap-x-3 gap-y-2 text-sm"
                    >
                        <div>
                            <dt
                                class="text-xs uppercase text-muted-foreground"
                            >
                                Servicio
                            </dt>
                            <dd class="truncate">
                                {{ r.servicio?.nombre ?? '—' }}
                            </dd>
                        </div>
                        <div>
                            <dt
                                class="text-xs uppercase text-muted-foreground"
                            >
                                Tipo
                            </dt>
                            <dd class="truncate">
                                {{ r.tipo_incidente?.nombre ?? '—' }}
                            </dd>
                        </div>
                        <div class="col-span-2">
                            <dt
                                class="text-xs uppercase text-muted-foreground"
                            >
                                Reportado por
                            </dt>
                            <dd class="truncate">
                                {{ r.reportado_por?.name ?? '—' }}
                            </dd>
                        </div>
                    </dl>
                </Link>

                <div class="mt-3 grid grid-cols-3 gap-2 border-t pt-3">
                    <Button
                        as-child
                        variant="ghost"
                        size="default"
                        class="h-11"
                    >
                        <Link :href="show(r.id).url">
                            <Eye class="mr-1 h-4 w-4" /> Ver
                        </Link>
                    </Button>
                    <Button
                        as-child
                        variant="ghost"
                        size="default"
                        class="h-11"
                    >
                        <Link :href="edit(r.id).url">
                            <Pencil class="mr-1 h-4 w-4" /> Editar
                        </Link>
                    </Button>
                    <Button
                        variant="ghost"
                        size="default"
                        class="h-11 text-red-600 hover:text-red-700"
                        @click="confirmarEliminar(r.id)"
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
                        <th class="px-4 py-3 font-medium">Comedor</th>
                        <th class="px-4 py-3 font-medium">Servicio</th>
                        <th class="px-4 py-3 font-medium">Tipo</th>
                        <th class="px-4 py-3 font-medium">Criticidad</th>
                        <th class="px-4 py-3 font-medium">Reportado por</th>
                        <th class="px-4 py-3 text-right font-medium">
                            Acciones
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <tr
                        v-for="r in reportes.data"
                        :key="r.id"
                        class="border-t hover:bg-muted/30"
                    >
                        <td class="px-4 py-3">{{ formatFecha(r.fecha) }}</td>
                        <td class="px-4 py-3">{{ r.semana }}</td>
                        <td class="px-4 py-3">
                            {{ r.comedor?.nombre ?? '—' }}
                        </td>
                        <td class="px-4 py-3">
                            {{ r.servicio?.nombre ?? '—' }}
                        </td>
                        <td class="px-4 py-3">
                            {{ r.tipo_incidente?.nombre ?? '—' }}
                        </td>
                        <td class="px-4 py-3">
                            <CriticidadBadge :value="r.criticidad" />
                        </td>
                        <td class="px-4 py-3">
                            {{ r.reportado_por?.name ?? '—' }}
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex justify-end gap-1">
                                <Button
                                    as-child
                                    variant="ghost"
                                    size="sm"
                                    title="Ver"
                                >
                                    <Link :href="show(r.id).url">
                                        <Eye class="h-4 w-4" />
                                    </Link>
                                </Button>
                                <Button
                                    as-child
                                    variant="ghost"
                                    size="sm"
                                    title="Editar"
                                >
                                    <Link :href="edit(r.id).url">
                                        <Pencil class="h-4 w-4" />
                                    </Link>
                                </Button>
                                <Button
                                    variant="ghost"
                                    size="sm"
                                    title="Eliminar"
                                    @click="confirmarEliminar(r.id)"
                                >
                                    <Trash2 class="h-4 w-4 text-red-600" />
                                </Button>
                            </div>
                        </td>
                    </tr>
                    <tr v-if="reportes.data.length === 0">
                        <td
                            colspan="8"
                            class="px-4 py-12 text-center text-muted-foreground"
                        >
                            No hay reportes aún. Crea el primero con el botón
                            «Nuevo reporte».
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div
            v-if="reportes.last_page > 1"
            class="flex flex-col items-stretch gap-3 sm:flex-row sm:items-center sm:justify-between"
        >
            <p class="text-sm text-muted-foreground">
                Mostrando {{ reportes.data.length }} de
                {{ reportes.total }} reportes
            </p>
            <div class="flex flex-wrap justify-center gap-1 sm:justify-end">
                <Link
                    v-for="(link, idx) in reportes.links"
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
