<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { Download, Eye, Pencil, Plus, Trash2 } from 'lucide-vue-next';
import { ref } from 'vue';
import ExportarRangoFechasModal from '@/components/Exports/ExportarRangoFechasModal.vue';
import EstatusBadge from '@/components/Gramajes/EstatusBadge.vue';
import { Button } from '@/components/ui/button';
import {
    create,
    destroy,
    edit,
    exportMethod,
    index,
    show,
} from '@/routes/gramajes';
import type { PaginatedGramajes, UnidadGramaje } from '@/types/gramajes';

defineProps<{ gramajes: PaginatedGramajes }>();

const exportarOpen = ref(false);

defineOptions({
    layout: () => ({
        breadcrumbs: [{ title: 'Gramajes', href: index() }],
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

const abreviado = (unidad: UnidadGramaje | undefined): string =>
    unidad === 'UNIDADES' ? 'u' : 'g';

const confirmarEliminar = (id: number) => {
    if (
        !confirm(
            '¿Eliminar este registro de gramaje? Esta acción no se puede deshacer.',
        )
    ) {
        return;
    }
    router.delete(destroy(id).url, { preserveScroll: true });
};
</script>

<template>
    <Head title="Gramajes" />

    <div class="flex h-full flex-1 flex-col gap-6 p-4">
        <div
            class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between"
        >
            <div>
                <h1 class="text-2xl font-semibold tracking-tight">Gramajes</h1>
                <p class="text-sm text-muted-foreground">
                    Control de pesos y porciones de componentes por servicio.
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
                        <Plus class="mr-2 h-4 w-4" /> Nuevo gramaje
                    </Link>
                </Button>
            </div>
        </div>

        <ExportarRangoFechasModal
            :open="exportarOpen"
            titulo="Exportar gramajes a Excel"
            :export-url-fn="(query) => exportMethod({ query }).url"
            @update:open="exportarOpen = $event"
        />

        <!-- Mobile: cards -->
        <div class="space-y-3 md:hidden">
            <div
                v-if="gramajes.data.length === 0"
                class="rounded-lg border p-8 text-center text-muted-foreground"
            >
                Aún no hay registros. Crea el primero con el botón «Nuevo
                gramaje».
            </div>

            <article
                v-for="g in gramajes.data"
                :key="g.id"
                class="rounded-lg border bg-card p-4 shadow-sm"
            >
                <Link :href="show(g.id).url" class="block space-y-3">
                    <div class="flex items-start justify-between gap-2">
                        <div>
                            <p class="text-sm text-muted-foreground">
                                {{ formatFecha(g.fecha) }} · Sem.
                                {{ g.semana }}
                            </p>
                            <p class="mt-0.5 font-medium leading-tight">
                                {{ g.componente?.nombre ?? '—' }}
                            </p>
                        </div>
                        <EstatusBadge :value="g.estatus" />
                    </div>

                    <dl class="grid grid-cols-2 gap-x-3 gap-y-2 text-sm">
                        <div>
                            <dt class="text-xs uppercase text-muted-foreground">
                                Comedor
                            </dt>
                            <dd class="truncate">
                                {{ g.comedor?.nombre ?? '—' }}
                            </dd>
                        </div>
                        <div>
                            <dt class="text-xs uppercase text-muted-foreground">
                                Servicio
                            </dt>
                            <dd class="truncate">
                                {{ g.servicio?.nombre ?? '—' }}
                            </dd>
                        </div>
                        <div>
                            <dt class="text-xs uppercase text-muted-foreground">
                                Plato
                            </dt>
                            <dd class="truncate">
                                {{ g.plato?.nombre ?? '—' }}
                            </dd>
                        </div>
                        <div>
                            <dt class="text-xs uppercase text-muted-foreground">
                                Variación
                            </dt>
                            <dd>{{ g.variacion_pct }} %</dd>
                        </div>
                        <div>
                            <dt class="text-xs uppercase text-muted-foreground">
                                Esperado
                            </dt>
                            <dd>
                                {{ g.gramaje_esperado }}
                                {{ abreviado(g.componente?.unidad) }}
                            </dd>
                        </div>
                        <div>
                            <dt class="text-xs uppercase text-muted-foreground">
                                Promedio
                            </dt>
                            <dd>
                                {{ g.peso_promedio }}
                                {{ abreviado(g.componente?.unidad) }}
                                ({{ g.cantidad_muestreada }})
                            </dd>
                        </div>
                    </dl>
                </Link>

                <div class="mt-3 grid grid-cols-3 gap-2 border-t pt-3">
                    <Button as-child variant="ghost" size="default" class="h-11">
                        <Link :href="show(g.id).url">
                            <Eye class="mr-1 h-4 w-4" /> Ver
                        </Link>
                    </Button>
                    <Button as-child variant="ghost" size="default" class="h-11">
                        <Link :href="edit(g.id).url">
                            <Pencil class="mr-1 h-4 w-4" /> Editar
                        </Link>
                    </Button>
                    <Button
                        variant="ghost"
                        size="default"
                        class="h-11 text-red-600 hover:text-red-700"
                        @click="confirmarEliminar(g.id)"
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
                        <th class="px-4 py-3 font-medium">Plato</th>
                        <th class="px-4 py-3 font-medium">Componente</th>
                        <th class="px-4 py-3 text-right font-medium">
                            Esperado
                        </th>
                        <th class="px-4 py-3 text-right font-medium">
                            Promedio
                        </th>
                        <th class="px-4 py-3 text-right font-medium">
                            Variación
                        </th>
                        <th class="px-4 py-3 font-medium">Estatus</th>
                        <th class="px-4 py-3 text-right font-medium">
                            Acciones
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <tr
                        v-for="g in gramajes.data"
                        :key="g.id"
                        class="border-t hover:bg-muted/30"
                    >
                        <td class="px-4 py-3">{{ formatFecha(g.fecha) }}</td>
                        <td class="px-4 py-3">{{ g.semana }}</td>
                        <td class="px-4 py-3">
                            {{ g.comedor?.nombre ?? '—' }}
                        </td>
                        <td class="px-4 py-3">
                            {{ g.servicio?.nombre ?? '—' }}
                        </td>
                        <td class="px-4 py-3">
                            {{ g.plato?.nombre ?? '—' }}
                        </td>
                        <td class="px-4 py-3">
                            {{ g.componente?.nombre ?? '—' }}
                        </td>
                        <td class="px-4 py-3 text-right">
                            {{ g.gramaje_esperado }}
                            {{ abreviado(g.componente?.unidad) }}
                        </td>
                        <td class="px-4 py-3 text-right">
                            {{ g.peso_promedio }}
                            {{ abreviado(g.componente?.unidad) }}
                            <span class="text-xs text-muted-foreground">
                                ({{ g.cantidad_muestreada }})
                            </span>
                        </td>
                        <td class="px-4 py-3 text-right">
                            {{ g.variacion_pct }} %
                        </td>
                        <td class="px-4 py-3">
                            <EstatusBadge :value="g.estatus" />
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex justify-end gap-1">
                                <Button
                                    as-child
                                    variant="ghost"
                                    size="sm"
                                    title="Ver"
                                >
                                    <Link :href="show(g.id).url">
                                        <Eye class="h-4 w-4" />
                                    </Link>
                                </Button>
                                <Button
                                    as-child
                                    variant="ghost"
                                    size="sm"
                                    title="Editar"
                                >
                                    <Link :href="edit(g.id).url">
                                        <Pencil class="h-4 w-4" />
                                    </Link>
                                </Button>
                                <Button
                                    variant="ghost"
                                    size="sm"
                                    title="Eliminar"
                                    @click="confirmarEliminar(g.id)"
                                >
                                    <Trash2 class="h-4 w-4 text-red-600" />
                                </Button>
                            </div>
                        </td>
                    </tr>
                    <tr v-if="gramajes.data.length === 0">
                        <td
                            colspan="11"
                            class="px-4 py-12 text-center text-muted-foreground"
                        >
                            Aún no hay registros. Crea el primero con el botón
                            «Nuevo gramaje».
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div
            v-if="gramajes.last_page > 1"
            class="flex flex-col items-stretch gap-3 sm:flex-row sm:items-center sm:justify-between"
        >
            <p class="text-sm text-muted-foreground">
                Mostrando {{ gramajes.data.length }} de {{ gramajes.total }}
                registros
            </p>
            <div class="flex flex-wrap justify-center gap-1 sm:justify-end">
                <Link
                    v-for="(link, idx) in gramajes.links"
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
