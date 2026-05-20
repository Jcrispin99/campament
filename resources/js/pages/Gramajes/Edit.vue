<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import GramajeForm from '@/components/Gramajes/GramajeForm.vue';
import { edit, index } from '@/routes/gramajes';
import type { Gramaje, GramajeCatalogos } from '@/types/gramajes';

const props = defineProps<{
    gramaje: Gramaje;
    catalogos: GramajeCatalogos;
}>();

defineOptions({
    layout: (props: { gramaje: Gramaje }) => ({
        breadcrumbs: [
            { title: 'Gramajes', href: index() },
            {
                title: `Gramaje #${props.gramaje.id}`,
                href: edit(props.gramaje.id),
            },
        ],
    }),
});
</script>

<template>
    <Head :title="`Editar gramaje #${props.gramaje.id}`" />

    <div class="flex h-full flex-1 flex-col gap-6 p-4">
        <div>
            <h1 class="text-2xl font-semibold tracking-tight">
                Editar gramaje #{{ props.gramaje.id }}
            </h1>
            <p class="text-sm text-muted-foreground">
                Modifica las medidas y datos del registro. Promedio, variación y
                estatus se recalculan automáticamente.
            </p>
        </div>

        <GramajeForm mode="edit" :gramaje="gramaje" :catalogos="catalogos" />
    </div>
</template>
