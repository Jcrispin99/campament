<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import ReporteForm from '@/components/Reportes/ReporteForm.vue';
import { edit, index } from '@/routes/reportes';
import type { Reporte, ReporteCatalogos } from '@/types/reportes';

const props = defineProps<{
    reporte: Reporte;
    catalogos: ReporteCatalogos;
}>();

defineOptions({
    layout: (props: { reporte: Reporte }) => ({
        breadcrumbs: [
            { title: 'Reportes', href: index() },
            {
                title: `Reporte #${props.reporte.id}`,
                href: edit(props.reporte.id),
            },
        ],
    }),
});
</script>

<template>
    <Head :title="`Editar reporte #${props.reporte.id}`" />

    <div class="flex h-full flex-1 flex-col gap-6 p-4">
        <div>
            <h1 class="text-2xl font-semibold tracking-tight">
                Editar reporte #{{ props.reporte.id }}
            </h1>
            <p class="text-sm text-muted-foreground">
                Modifica los datos del reporte o gestiona sus evidencias.
            </p>
        </div>

        <ReporteForm mode="edit" :reporte="reporte" :catalogos="catalogos" />
    </div>
</template>
