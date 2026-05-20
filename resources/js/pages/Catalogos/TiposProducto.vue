<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import CatalogoSimpleManager from '@/components/Catalogos/CatalogoSimpleManager.vue';
import {
    destroy,
    index,
    store,
    update,
} from '@/routes/catalogos/tipos-producto';
import type { CatalogoSimpleItem } from '@/types/catalogos';

defineProps<{ items: CatalogoSimpleItem[] }>();

defineOptions({
    layout: () => ({
        breadcrumbs: [{ title: 'Tipos de producto', href: index() }],
    }),
});
</script>

<template>
    <Head title="Catálogo de tipos de producto" />

    <CatalogoSimpleManager
        :items="items"
        singular="tipo de producto"
        plural="Tipos de producto"
        descripcion="Categorías de producto recibido (abarrotes, congelados, frutas, etc)."
        :max-length="100"
        :store-url="store().url"
        :update-url-fn="(id) => update(id).url"
        :destroy-url-fn="(id) => destroy(id).url"
    />
</template>
