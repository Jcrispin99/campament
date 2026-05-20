<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import ComponenteManager from '@/components/Catalogos/ComponenteManager.vue';
import { index } from '@/routes/catalogos/componentes';
import type { ComponenteItem, UnidadOption } from '@/types/catalogos';

defineProps<{
    items: ComponenteItem[];
    unidades: UnidadOption[];
}>();

defineOptions({
    layout: () => ({
        breadcrumbs: [{ title: 'Componentes', href: index() }],
    }),
});
</script>

<template>
    <Head title="Catálogo de componentes" />

    <ComponenteManager :items="items" :unidades="unidades" />
</template>
