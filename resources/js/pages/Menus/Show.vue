<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { ArrowLeft, ArrowRight, Pencil } from 'lucide-vue-next';
import { computed } from 'vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { formatFechaLocal } from '@/lib/fecha';
import { edit, index, show } from '@/routes/menus';
import type { Menu } from '@/types/menus';

const props = defineProps<{ menu: Menu }>();

defineOptions({
    layout: (props: { menu: Menu }) => ({
        breadcrumbs: [
            { title: 'Cambios de menú', href: index() },
            { title: `Cambio #${props.menu.id}`, href: show(props.menu.id) },
        ],
    }),
});

const formatFecha = (iso: string): string =>
    formatFechaLocal(iso, { year: 'numeric', month: 'long', day: 'numeric' });

const conformidadClass = computed<string>(() => {
    const v = props.menu.conformidad.toLowerCase();
    if (v.includes('conforme') && !v.includes('inconforme'))
        return 'border-emerald-500 bg-emerald-50 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-400';
    if (v.includes('inconforme'))
        return 'border-red-500 bg-red-50 text-red-700 dark:bg-red-950 dark:text-red-400';
    return 'border-amber-500 bg-amber-50 text-amber-700 dark:bg-amber-950 dark:text-amber-400';
});
</script>

<template>
    <Head :title="`Cambio de menú #${props.menu.id}`" />

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
                    Cambio de menú #{{ props.menu.id }}
                </h1>
                <Badge variant="outline" :class="conformidadClass">
                    {{ props.menu.conformidad }}
                </Badge>
            </div>
            <Button as-child class="w-full sm:w-auto">
                <Link :href="edit(props.menu.id).url">
                    <Pencil class="mr-2 h-4 w-4" /> Editar
                </Link>
            </Button>
        </div>

        <!-- Cambio destacado -->
        <section class="rounded-lg border p-6">
            <p class="text-xs uppercase text-muted-foreground">Cambio</p>
            <div
                class="mt-2 flex flex-col gap-3 sm:flex-row sm:items-center sm:gap-4"
            >
                <div class="flex-1">
                    <p class="text-xs text-muted-foreground">Programado</p>
                    <p class="text-lg font-medium line-through opacity-70">
                        {{ menu.programado }}
                    </p>
                </div>
                <ArrowRight
                    class="hidden h-6 w-6 text-muted-foreground sm:block"
                />
                <div class="flex-1">
                    <p class="text-xs text-muted-foreground">Propuesta</p>
                    <p class="text-lg font-medium">
                        {{ menu.propuesta }}
                    </p>
                </div>
            </div>
        </section>

        <!-- Fechas y datos base -->
        <section class="grid gap-4 rounded-lg border p-6 md:grid-cols-2">
            <div>
                <p class="text-xs uppercase text-muted-foreground">Fecha</p>
                <p class="font-medium">{{ formatFecha(menu.fecha) }}</p>
            </div>
            <div>
                <p class="text-xs uppercase text-muted-foreground">Semana</p>
                <p class="font-medium">{{ menu.semana }}</p>
            </div>
            <div>
                <p class="text-xs uppercase text-muted-foreground">
                    Fecha de solicitud
                </p>
                <p class="font-medium">
                    {{ formatFecha(menu.fecha_solicitud) }}
                </p>
            </div>
            <div>
                <p class="text-xs uppercase text-muted-foreground">
                    Fecha de cambio
                </p>
                <p class="font-medium">
                    {{ formatFecha(menu.fecha_cambio) }}
                </p>
            </div>
            <div>
                <p class="text-xs uppercase text-muted-foreground">
                    Días de previsión
                </p>
                <p class="font-medium">
                    {{ menu.dias_prevision }}
                    {{ menu.dias_prevision === 1 ? 'día' : 'días' }}
                </p>
            </div>
            <div>
                <p class="text-xs uppercase text-muted-foreground">
                    Reportado por
                </p>
                <p class="font-medium">
                    {{ menu.reportado_por?.name ?? '—' }}
                </p>
            </div>
            <div>
                <p class="text-xs uppercase text-muted-foreground">Servicio</p>
                <p class="font-medium">{{ menu.servicio?.nombre ?? '—' }}</p>
            </div>
            <div>
                <p class="text-xs uppercase text-muted-foreground">
                    Componente
                </p>
                <p class="font-medium">
                    {{ menu.componente?.nombre ?? '—' }}
                </p>
            </div>
        </section>

        <!-- Motivo y comentario -->
        <section class="space-y-4 rounded-lg border p-6">
            <div>
                <p class="text-xs uppercase text-muted-foreground">Motivo</p>
                <p class="whitespace-pre-wrap">{{ menu.motivo }}</p>
            </div>
            <div v-if="menu.comentario">
                <p class="text-xs uppercase text-muted-foreground">
                    Comentario
                </p>
                <p class="whitespace-pre-wrap">{{ menu.comentario }}</p>
            </div>
        </section>

        <!-- Análisis -->
        <section class="space-y-2 rounded-lg border p-6">
            <p class="text-xs uppercase text-muted-foreground">Análisis</p>
            <p class="whitespace-pre-wrap">{{ menu.analisis }}</p>
        </section>
    </div>
</template>
