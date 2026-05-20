<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import CatalogoSimpleManager from '@/components/Catalogos/CatalogoSimpleManager.vue';
import { destroy, index, store, update } from '@/routes/catalogos/servicios';
import type { CatalogoSimpleItem } from '@/types/catalogos';

defineProps<{ items: CatalogoSimpleItem[] }>();

defineOptions({
    layout: () => ({
        breadcrumbs: [{ title: 'Servicios', href: index() }],
    }),
});
</script>

<template>
    <Head title="Catálogo de servicios" />

    <CatalogoSimpleManager
        :items="items"
        singular="servicio"
        plural="Servicios"
        descripcion="Tipos de servicio brindado (desayuno, almuerzo, cena, etc)."
        :max-length="100"
        :store-url="store().url"
        :update-url-fn="(id) => update(id).url"
        :destroy-url-fn="(id) => destroy(id).url"
    />
</template>
