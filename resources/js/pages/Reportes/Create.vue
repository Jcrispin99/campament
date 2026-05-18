<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import ReporteForm from '@/components/Reportes/ReporteForm.vue';
import { create, index } from '@/routes/reportes';
import type { ReporteCatalogos } from '@/types/reportes';

defineProps<{ catalogos: ReporteCatalogos }>();

defineOptions({
    layout: () => ({
        breadcrumbs: [
            { title: 'Reportes', href: index() },
            { title: 'Nuevo', href: create() },
        ],
    }),
});
</script>

<template>
    <Head title="Nuevo reporte" />

    <div class="flex h-full flex-1 flex-col gap-6 p-4">
        <div>
            <h1 class="text-2xl font-semibold tracking-tight">
                Nuevo reporte
            </h1>
            <p class="text-sm text-muted-foreground">
                Registra una inspección, hallazgo o incidente del comedor.
            </p>
        </div>

        <ReporteForm mode="create" :catalogos="catalogos" />
    </div>
</template>
