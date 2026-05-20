<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import ClasificacionManager from '@/components/Catalogos/ClasificacionManager.vue';
import { index } from '@/routes/catalogos/clasificaciones';
import type { ClasificacionItem } from '@/types/catalogos';

defineProps<{
    items: ClasificacionItem[];
    tiposIncidente: { id: number; nombre: string }[];
}>();

defineOptions({
    layout: () => ({
        breadcrumbs: [{ title: 'Clasificaciones', href: index() }],
    }),
});
</script>

<template>
    <Head title="Catálogo de clasificaciones" />

    <ClasificacionManager :items="items" :tipos-incidente="tiposIncidente" />
</template>
