<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import MenuForm from '@/components/Menus/MenuForm.vue';
import { edit, index } from '@/routes/menus';
import type { Menu, MenuCatalogos } from '@/types/menus';

const props = defineProps<{
    menu: Menu;
    catalogos: MenuCatalogos;
}>();

defineOptions({
    layout: (props: { menu: Menu }) => ({
        breadcrumbs: [
            { title: 'Cambios de menú', href: index() },
            {
                title: `Cambio #${props.menu.id}`,
                href: edit(props.menu.id),
            },
        ],
    }),
});
</script>

<template>
    <Head :title="`Editar cambio #${props.menu.id}`" />

    <div class="flex h-full flex-1 flex-col gap-6 p-4">
        <div>
            <h1 class="text-2xl font-semibold tracking-tight">
                Editar cambio de menú #{{ props.menu.id }}
            </h1>
            <p class="text-sm text-muted-foreground">
                Modifica los datos. Los días de previsión se recalculan al
                cambiar las fechas.
            </p>
        </div>

        <MenuForm mode="edit" :menu="menu" :catalogos="catalogos" />
    </div>
</template>
