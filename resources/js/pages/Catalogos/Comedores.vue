<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import CatalogoSimpleManager from '@/components/Catalogos/CatalogoSimpleManager.vue';
import { destroy, index, store, update } from '@/routes/catalogos/comedores';
import type { CatalogoSimpleItem } from '@/types/catalogos';

defineProps<{ items: CatalogoSimpleItem[] }>();

defineOptions({
    layout: () => ({
        breadcrumbs: [{ title: 'Comedores', href: index() }],
    }),
});
</script>

<template>
    <Head title="Catálogo de comedores" />

    <CatalogoSimpleManager
        :items="items"
        singular="comedor"
        plural="Comedores"
        descripcion="Lugares donde se brindan los servicios de alimentación."
        :max-length="100"
        :store-url="store().url"
        :update-url-fn="(id) => update(id).url"
        :destroy-url-fn="(id) => destroy(id).url"
    />
</template>
