<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import GramajeForm from '@/components/Gramajes/GramajeForm.vue';
import { create, index } from '@/routes/gramajes';
import type { GramajeCatalogos } from '@/types/gramajes';

defineProps<{ catalogos: GramajeCatalogos }>();

defineOptions({
    layout: () => ({
        breadcrumbs: [
            { title: 'Gramajes', href: index() },
            { title: 'Nuevo', href: create() },
        ],
    }),
});
</script>

<template>
    <Head title="Nuevo gramaje" />

    <div class="flex h-full flex-1 flex-col gap-6 p-4">
        <div>
            <h1 class="text-2xl font-semibold tracking-tight">
                Nuevo gramaje
            </h1>
            <p class="text-sm text-muted-foreground">
                Registra el control de peso del componente servido.
            </p>
        </div>

        <GramajeForm mode="create" :catalogos="catalogos" />
    </div>
</template>
