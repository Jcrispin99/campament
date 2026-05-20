<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import MateriaPrimaForm from '@/components/MateriasPrimas/MateriaPrimaForm.vue';
import { edit, index } from '@/routes/materias-primas';
import type {
    MateriaPrima,
    MateriaPrimaCatalogos,
} from '@/types/materias-primas';

const props = defineProps<{
    materiaPrima: MateriaPrima;
    catalogos: MateriaPrimaCatalogos;
}>();

defineOptions({
    layout: (props: { materiaPrima: MateriaPrima }) => ({
        breadcrumbs: [
            { title: 'Materia prima', href: index() },
            {
                title: `Recepción #${props.materiaPrima.id}`,
                href: edit(props.materiaPrima.id),
            },
        ],
    }),
});
</script>

<template>
    <Head :title="`Editar recepción #${props.materiaPrima.id}`" />

    <div class="flex h-full flex-1 flex-col gap-6 p-4">
        <div>
            <h1 class="text-2xl font-semibold tracking-tight">
                Editar recepción #{{ props.materiaPrima.id }}
            </h1>
            <p class="text-sm text-muted-foreground">
                Modifica los datos del registro.
            </p>
        </div>

        <MateriaPrimaForm
            mode="edit"
            :materia-prima="materiaPrima"
            :catalogos="catalogos"
        />
    </div>
</template>
