<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import CatalogoSimpleManager from '@/components/Catalogos/CatalogoSimpleManager.vue';
import { destroy, index, store, update } from '@/routes/catalogos/proveedores';
import type { CatalogoSimpleItem } from '@/types/catalogos';

defineProps<{ items: CatalogoSimpleItem[] }>();

defineOptions({
    layout: () => ({
        breadcrumbs: [{ title: 'Proveedores', href: index() }],
    }),
});
</script>

<template>
    <Head title="Catálogo de proveedores" />

    <CatalogoSimpleManager
        :items="items"
        singular="proveedor"
        plural="Proveedores"
        descripcion="Empresas que abastecen materia prima al campamento."
        :max-length="100"
        :store-url="store().url"
        :update-url-fn="(id) => update(id).url"
        :destroy-url-fn="(id) => destroy(id).url"
    />
</template>
