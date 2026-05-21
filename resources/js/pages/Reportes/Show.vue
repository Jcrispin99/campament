<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { ArrowLeft, Pencil } from 'lucide-vue-next';
import CriticidadBadge from '@/components/Reportes/CriticidadBadge.vue';
import { Button } from '@/components/ui/button';
import { formatFechaLocal } from '@/lib/fecha';
import { edit, index, show } from '@/routes/reportes';
import type { Reporte } from '@/types/reportes';

const props = defineProps<{ reporte: Reporte }>();

defineOptions({
    layout: (props: { reporte: Reporte }) => ({
        breadcrumbs: [
            { title: 'Reportes', href: index() },
            {
                title: `Reporte #${props.reporte.id}`,
                href: show(props.reporte.id),
            },
        ],
    }),
});

const formatFecha = (iso: string): string =>
    formatFechaLocal(iso, { year: 'numeric', month: 'long', day: 'numeric' });

const yesNo = (b: boolean) => (b ? 'Sí' : 'No');
</script>

<template>
    <Head :title="`Reporte #${props.reporte.id}`" />

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
                    Reporte #{{ props.reporte.id }}
                </h1>
                <CriticidadBadge :value="props.reporte.criticidad" />
            </div>
            <Button as-child class="w-full sm:w-auto">
                <Link :href="edit(props.reporte.id).url">
                    <Pencil class="mr-2 h-4 w-4" /> Editar
                </Link>
            </Button>
        </div>

        <section class="grid gap-4 rounded-lg border p-6 md:grid-cols-2">
            <div>
                <p class="text-xs uppercase text-muted-foreground">Fecha</p>
                <p class="font-medium">{{ formatFecha(reporte.fecha) }}</p>
            </div>
            <div>
                <p class="text-xs uppercase text-muted-foreground">Semana</p>
                <p class="font-medium">{{ reporte.semana }}</p>
            </div>
            <div>
                <p class="text-xs uppercase text-muted-foreground">Comedor</p>
                <p class="font-medium">{{ reporte.comedor?.nombre ?? '—' }}</p>
            </div>
            <div>
                <p class="text-xs uppercase text-muted-foreground">Servicio</p>
                <p class="font-medium">
                    {{ reporte.servicio?.nombre ?? '—' }}
                </p>
            </div>
            <div>
                <p class="text-xs uppercase text-muted-foreground">
                    Tipo de incidente
                </p>
                <p class="font-medium">
                    {{ reporte.tipo_incidente?.nombre ?? '—' }}
                </p>
            </div>
            <div>
                <p class="text-xs uppercase text-muted-foreground">
                    Clasificación
                </p>
                <p class="font-medium">
                    {{ reporte.clasificacion?.nombre ?? '—' }}
                </p>
            </div>
            <div>
                <p class="text-xs uppercase text-muted-foreground">
                    Análisis de causa
                </p>
                <p class="font-medium">
                    {{ reporte.analisis_causa?.nombre ?? '—' }}
                </p>
            </div>
            <div>
                <p class="text-xs uppercase text-muted-foreground">
                    Reportado por
                </p>
                <p class="font-medium">
                    {{ reporte.reportado_por?.name ?? '—' }}
                </p>
            </div>
        </section>

        <section class="space-y-4 rounded-lg border p-6">
            <div>
                <p class="text-xs uppercase text-muted-foreground">
                    Detalle de la observación
                </p>
                <p class="whitespace-pre-wrap">
                    {{ reporte.detalle_observacion }}
                </p>
            </div>
            <div>
                <p class="text-xs uppercase text-muted-foreground">
                    Acción inmediata
                </p>
                <p class="whitespace-pre-wrap">
                    {{ reporte.accion_inmediata }}
                </p>
            </div>
            <div>
                <p class="text-xs uppercase text-muted-foreground">
                    Recomendación Salus
                </p>
                <p class="whitespace-pre-wrap">
                    {{ reporte.recomendacion_salus }}
                </p>
            </div>
            <div class="flex flex-wrap gap-6 text-sm">
                <span
                    ><strong>Se corrigió:</strong>
                    {{ yesNo(reporte.se_corrigio) }}</span
                >
                <span
                    ><strong>Requiere plan de acción:</strong>
                    {{ yesNo(reporte.requiere_plan_accion) }}</span
                >
            </div>
        </section>

        <section
            v-if="reporte.evidencias && reporte.evidencias.length > 0"
            class="space-y-3 rounded-lg border p-6"
        >
            <h3 class="text-base font-semibold">
                Evidencias ({{ reporte.evidencias.length }})
            </h3>
            <div class="grid grid-cols-2 gap-3 md:grid-cols-3">
                <figure
                    v-for="ev in reporte.evidencias"
                    :key="ev.id"
                    class="overflow-hidden rounded-md border"
                >
                    <img
                        v-if="ev.imagen_url"
                        :src="ev.imagen_url"
                        :alt="ev.descripcion"
                        class="aspect-video w-full object-cover"
                    />
                    <figcaption class="p-2 text-sm">
                        {{ ev.descripcion }}
                    </figcaption>
                </figure>
            </div>
        </section>
    </div>
</template>
