<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import MateriaPrimaForm from '@/components/MateriasPrimas/MateriaPrimaForm.vue';
import { create, index } from '@/routes/materias-primas';
import type { MateriaPrimaCatalogos } from '@/types/materias-primas';

defineProps<{ catalogos: MateriaPrimaCatalogos }>();

defineOptions({
    layout: () => ({
        breadcrumbs: [
            { title: 'Materia prima', href: index() },
            { title: 'Nuevo', href: create() },
        ],
    }),
});
</script>

<template>
    <Head title="Nueva recepción de materia prima" />

    <div class="flex h-full flex-1 flex-col gap-6 p-4">
        <div>
            <h1 class="text-2xl font-semibold tracking-tight">
                Nueva recepción de materia prima
            </h1>
            <p class="text-sm text-muted-foreground">
                Registra la conformidad de la recepción y las observaciones
                detectadas.
            </p>
        </div>

        <MateriaPrimaForm mode="create" :catalogos="catalogos" />
    </div>
</template>
