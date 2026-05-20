<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import CatalogoSimpleManager from '@/components/Catalogos/CatalogoSimpleManager.vue';
import { destroy, index, store, update } from '@/routes/catalogos/tipos-corte';
import type { CatalogoSimpleItem } from '@/types/catalogos';

defineProps<{ items: CatalogoSimpleItem[] }>();

defineOptions({
    layout: () => ({
        breadcrumbs: [{ title: 'Tipos de corte', href: index() }],
    }),
});
</script>

<template>
    <Head title="Catálogo de tipos de corte" />

    <CatalogoSimpleManager
        :items="items"
        singular="tipo de corte"
        plural="Tipos de corte"
        descripcion="Cortes de cárnicos usados en los registros de gramaje."
        :max-length="100"
        :store-url="store().url"
        :update-url-fn="(id) => update(id).url"
        :destroy-url-fn="(id) => destroy(id).url"
    />
</template>
