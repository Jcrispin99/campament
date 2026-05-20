<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import CatalogoSimpleManager from '@/components/Catalogos/CatalogoSimpleManager.vue';
import {
    destroy,
    index,
    store,
    update,
} from '@/routes/catalogos/analisis-causas';
import type { CatalogoSimpleItem } from '@/types/catalogos';

defineProps<{ items: CatalogoSimpleItem[] }>();

defineOptions({
    layout: () => ({
        breadcrumbs: [{ title: 'Análisis de causas', href: index() }],
    }),
});
</script>

<template>
    <Head title="Catálogo de análisis de causa" />

    <CatalogoSimpleManager
        :items="items"
        singular="análisis de causa"
        plural="Análisis de causa"
        descripcion="Causas raíz que pueden explicar un incidente reportado."
        :max-length="200"
        :store-url="store().url"
        :update-url-fn="(id) => update(id).url"
        :destroy-url-fn="(id) => destroy(id).url"
    />
</template>
