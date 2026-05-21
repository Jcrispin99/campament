<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import {
    ArrowRight,
    Download,
    Eye,
    Pencil,
    Plus,
    Trash2,
} from 'lucide-vue-next';
import { ref } from 'vue';
import ExportarRangoFechasModal from '@/components/Exports/ExportarRangoFechasModal.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { formatFechaLocal } from '@/lib/fecha';
import {
    create,
    destroy,
    edit,
    exportMethod,
    index,
    show,
} from '@/routes/menus';
import type { PaginatedMenus } from '@/types/menus';

defineProps<{ menus: PaginatedMenus }>();

const exportarOpen = ref(false);

defineOptions({
    layout: () => ({
        breadcrumbs: [{ title: 'Cambios de menú', href: index() }],
    }),
});

const formatFecha = (iso: string): string =>
    formatFechaLocal(iso, { year: 'numeric', month: '2-digit', day: '2-digit' });

const conformidadClass = (valor: string): string => {
    const v = valor.toLowerCase();
    if (v.includes('conforme') && !v.includes('inconforme'))
        return 'border-emerald-500 bg-emerald-50 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-400';
    if (v.includes('inconforme'))
        return 'border-red-500 bg-red-50 text-red-700 dark:bg-red-950 dark:text-red-400';
    return 'border-amber-500 bg-amber-50 text-amber-700 dark:bg-amber-950 dark:text-amber-400';
};

const confirmarEliminar = (id: number) => {
    if (!confirm('¿Eliminar este cambio de menú?')) return;
    router.delete(destroy(id).url, { preserveScroll: true });
};
</script>

<template>
    <Head title="Cambios de menú" />

    <div class="flex h-full flex-1 flex-col gap-6 p-4">
        <div
            class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between"
        >
            <div>
                <h1 class="text-2xl font-semibold tracking-tight">
                    Cambios de menú
                </h1>
                <p class="text-sm text-muted-foreground">
                    Solicitudes de cambio de menú con su análisis y
                    conformidad.
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
                        <Plus class="mr-2 h-4 w-4" /> Nuevo cambio
                    </Link>
                </Button>
            </div>
        </div>

        <ExportarRangoFechasModal
            :open="exportarOpen"
            titulo="Exportar cambios de menú a Excel"
            :export-url-fn="(query) => exportMethod({ query }).url"
            @update:open="exportarOpen = $event"
        />

        <!-- Mobile: cards -->
        <div class="space-y-3 md:hidden">
            <div
                v-if="menus.data.length === 0"
                class="rounded-lg border p-8 text-center text-muted-foreground"
            >
                Aún no hay cambios de menú registrados.
            </div>

            <article
                v-for="m in menus.data"
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
                                {{ m.componente?.nombre ?? '—' }}
                            </p>
                        </div>
                        <Badge
                            variant="outline"
                            :class="conformidadClass(m.conformidad)"
                        >
                            {{ m.conformidad }}
                        </Badge>
                    </div>

                    <div class="space-y-2 text-sm">
                        <div>
                            <p class="text-xs uppercase text-muted-foreground">
                                Servicio
                            </p>
                            <p>{{ m.servicio?.nombre ?? '—' }}</p>
                        </div>
                        <div>
                            <p class="text-xs uppercase text-muted-foreground">
                                Cambio
                            </p>
                            <p class="flex flex-wrap items-center gap-1">
                                <span class="line-through opacity-60">
                                    {{ m.programado }}
                                </span>
                                <ArrowRight class="h-3 w-3" />
                                <span class="font-medium">
                                    {{ m.propuesta }}
                                </span>
                            </p>
                        </div>
                        <div>
                            <p class="text-xs uppercase text-muted-foreground">
                                Previsión
                            </p>
                            <p>
                                {{ m.dias_prevision }}
                                {{
                                    m.dias_prevision === 1
                                        ? 'día'
                                        : 'días'
                                }}
                            </p>
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
                        <th class="px-4 py-3 font-medium">Servicio</th>
                        <th class="px-4 py-3 font-medium">Componente</th>
                        <th class="px-4 py-3 font-medium">Cambio</th>
                        <th class="px-4 py-3 text-right font-medium">
                            Previsión
                        </th>
                        <th class="px-4 py-3 font-medium">Conformidad</th>
                        <th class="px-4 py-3 text-right font-medium">
                            Acciones
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <tr
                        v-for="m in menus.data"
                        :key="m.id"
                        class="border-t hover:bg-muted/30"
                    >
                        <td class="px-4 py-3">{{ formatFecha(m.fecha) }}</td>
                        <td class="px-4 py-3">{{ m.semana }}</td>
                        <td class="px-4 py-3">
                            {{ m.servicio?.nombre ?? '—' }}
                        </td>
                        <td class="px-4 py-3">
                            {{ m.componente?.nombre ?? '—' }}
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-2">
                                <span class="line-through opacity-60">
                                    {{ m.programado }}
                                </span>
                                <ArrowRight
                                    class="h-3 w-3 text-muted-foreground"
                                />
                                <span class="font-medium">
                                    {{ m.propuesta }}
                                </span>
                            </div>
                        </td>
                        <td class="px-4 py-3 text-right">
                            {{ m.dias_prevision }}
                            {{
                                m.dias_prevision === 1
                                    ? 'día'
                                    : 'días'
                            }}
                        </td>
                        <td class="px-4 py-3">
                            <Badge
                                variant="outline"
                                :class="conformidadClass(m.conformidad)"
                            >
                                {{ m.conformidad }}
                            </Badge>
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
                    <tr v-if="menus.data.length === 0">
                        <td
                            colspan="8"
                            class="px-4 py-12 text-center text-muted-foreground"
                        >
                            Aún no hay cambios de menú registrados.
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div
            v-if="menus.last_page > 1"
            class="flex flex-col items-stretch gap-3 sm:flex-row sm:items-center sm:justify-between"
        >
            <p class="text-sm text-muted-foreground">
                Mostrando {{ menus.data.length }} de {{ menus.total }}
                registros
            </p>
            <div class="flex flex-wrap justify-center gap-1 sm:justify-end">
                <Link
                    v-for="(link, idx) in menus.links"
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
