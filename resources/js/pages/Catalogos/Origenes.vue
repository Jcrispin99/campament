<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import CatalogoSimpleManager from '@/components/Catalogos/CatalogoSimpleManager.vue';
import { destroy, index, store, update } from '@/routes/catalogos/origenes';
import type { CatalogoSimpleItem } from '@/types/catalogos';

defineProps<{ items: CatalogoSimpleItem[] }>();

defineOptions({
    layout: () => ({
        breadcrumbs: [{ title: 'Orígenes', href: index() }],
    }),
});
</script>

<template>
    <Head title="Catálogo de orígenes" />

    <CatalogoSimpleManager
        :items="items"
        singular="origen"
        plural="Orígenes"
        descripcion="Ciudades o regiones desde donde llega la materia prima."
        :max-length="100"
        :store-url="store().url"
        :update-url-fn="(id) => update(id).url"
        :destroy-url-fn="(id) => destroy(id).url"
    />
</template>
