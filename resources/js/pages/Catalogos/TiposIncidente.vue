<script setup lang="ts">
import { Head, router, useForm } from '@inertiajs/vue3';
import { Pencil, Plus, Trash2 } from 'lucide-vue-next';
import { ref, watch } from 'vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogClose,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    destroy,
    index,
    store,
    update,
} from '@/routes/catalogos/tipos-incidente';
import type { TipoIncidenteItem } from '@/types/catalogos';

defineProps<{ items: TipoIncidenteItem[] }>();

defineOptions({
    layout: () => ({
        breadcrumbs: [{ title: 'Tipos de incidente', href: index() }],
    }),
});

const dialogOpen = ref(false);
const editing = ref<TipoIncidenteItem | null>(null);

const form = useForm({
    nombre: '',
});

const openCreate = () => {
    editing.value = null;
    form.reset();
    form.clearErrors();
    dialogOpen.value = true;
};

const openEdit = (item: TipoIncidenteItem) => {
    editing.value = item;
    form.clearErrors();
    form.nombre = item.nombre;
    dialogOpen.value = true;
};

const submit = () => {
    if (editing.value) {
        form.put(update(editing.value.id).url, {
            preserveScroll: true,
            onSuccess: () => (dialogOpen.value = false),
        });
    } else {
        form.post(store().url, {
            preserveScroll: true,
            onSuccess: () => (dialogOpen.value = false),
        });
    }
};

const eliminar = (item: TipoIncidenteItem) => {
    if (
        !confirm(
            `¿Eliminar «${item.nombre}»? Si tiene clasificaciones o reportes asociados, no se podrá eliminar.`,
        )
    ) {
        return;
    }
    router.delete(destroy(item.id).url, { preserveScroll: true });
};

watch(dialogOpen, (open) => {
    if (!open) {
        editing.value = null;
        form.reset();
        form.clearErrors();
    }
});
</script>

<template>
    <Head title="Catálogo de tipos de incidente" />

    <div class="flex h-full flex-1 flex-col gap-6 p-4">
        <div
            class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between"
        >
            <div>
                <h1 class="text-2xl font-semibold tracking-tight">
                    Tipos de incidente
                </h1>
                <p class="text-sm text-muted-foreground">
                    Categoría principal de los hallazgos (Inocuidad, Servicio,
                    Seguridad…).
                </p>
            </div>
            <Button class="w-full sm:w-auto" @click="openCreate">
                <Plus class="mr-2 h-4 w-4" /> Nuevo
            </Button>
        </div>

        <div class="overflow-hidden rounded-xl border">
            <table class="w-full text-sm">
                <thead class="bg-muted/50 text-left">
                    <tr>
                        <th class="px-4 py-3 font-medium">Nombre</th>
                        <th class="px-4 py-3 text-right font-medium">
                            Clasificaciones
                        </th>
                        <th class="px-4 py-3 text-right font-medium">
                            Acciones
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <tr
                        v-for="item in items"
                        :key="item.id"
                        class="border-t hover:bg-muted/30"
                    >
                        <td class="px-4 py-3">{{ item.nombre }}</td>
                        <td class="px-4 py-3 text-right">
                            {{ item.clasificaciones_count }}
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex justify-end gap-1">
                                <Button
                                    variant="ghost"
                                    size="sm"
                                    title="Editar"
                                    @click="openEdit(item)"
                                >
                                    <Pencil class="h-4 w-4" />
                                </Button>
                                <Button
                                    variant="ghost"
                                    size="sm"
                                    title="Eliminar"
                                    @click="eliminar(item)"
                                >
                                    <Trash2 class="h-4 w-4 text-red-600" />
                                </Button>
                            </div>
                        </td>
                    </tr>
                    <tr v-if="items.length === 0">
                        <td
                            colspan="3"
                            class="px-4 py-12 text-center text-muted-foreground"
                        >
                            Aún no hay tipos de incidente.
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <Dialog v-model:open="dialogOpen">
            <DialogContent>
                <form @submit.prevent="submit">
                    <DialogHeader>
                        <DialogTitle>
                            {{
                                editing
                                    ? 'Editar tipo de incidente'
                                    : 'Nuevo tipo de incidente'
                            }}
                        </DialogTitle>
                        <DialogDescription>
                            Solo se requiere el nombre.
                        </DialogDescription>
                    </DialogHeader>

                    <div class="space-y-4 py-4">
                        <div class="space-y-2">
                            <Label for="ti-nombre">Nombre *</Label>
                            <Input
                                id="ti-nombre"
                                v-model="form.nombre"
                                maxlength="50"
                                required
                                autofocus
                            />
                            <InputError :message="form.errors.nombre" />
                        </div>
                    </div>

                    <DialogFooter class="gap-2">
                        <DialogClose as-child>
                            <Button type="button" variant="secondary">
                                Cancelar
                            </Button>
                        </DialogClose>
                        <Button type="submit" :disabled="form.processing">
                            {{ editing ? 'Guardar cambios' : 'Crear' }}
                        </Button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>
    </div>
</template>
