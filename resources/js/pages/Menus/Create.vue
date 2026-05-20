<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import MenuForm from '@/components/Menus/MenuForm.vue';
import { create, index } from '@/routes/menus';
import type { MenuCatalogos } from '@/types/menus';

defineProps<{ catalogos: MenuCatalogos }>();

defineOptions({
    layout: () => ({
        breadcrumbs: [
            { title: 'Cambios de menú', href: index() },
            { title: 'Nuevo', href: create() },
        ],
    }),
});
</script>

<template>
    <Head title="Nuevo cambio de menú" />

    <div class="flex h-full flex-1 flex-col gap-6 p-4">
        <div>
            <h1 class="text-2xl font-semibold tracking-tight">
                Nuevo cambio de menú
            </h1>
            <p class="text-sm text-muted-foreground">
                Registra una solicitud de cambio con su motivo y análisis.
            </p>
        </div>

        <MenuForm mode="create" :catalogos="catalogos" />
    </div>
</template>
