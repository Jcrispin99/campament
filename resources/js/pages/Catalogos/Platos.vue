<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import CatalogoSimpleManager from '@/components/Catalogos/CatalogoSimpleManager.vue';
import { destroy, index, store, update } from '@/routes/catalogos/platos';
import type { CatalogoSimpleItem } from '@/types/catalogos';

defineProps<{ items: CatalogoSimpleItem[] }>();

defineOptions({
    layout: () => ({
        breadcrumbs: [{ title: 'Platos', href: index() }],
    }),
});
</script>

<template>
    <Head title="Catálogo de platos" />

    <CatalogoSimpleManager
        :items="items"
        singular="plato"
        plural="Platos"
        descripcion="Categorías de plato para los registros de gramaje (fast food, fondo, sopa, etc)."
        :max-length="100"
        :store-url="store().url"
        :update-url-fn="(id) => update(id).url"
        :destroy-url-fn="(id) => destroy(id).url"
    />
</template>
