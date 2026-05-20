<script setup lang="ts">
import { useForm, router } from '@inertiajs/vue3';
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
import type { CatalogoSimpleItem } from '@/types/catalogos';

type Props = {
    items: CatalogoSimpleItem[];
    singular: string;
    plural: string;
    descripcion?: string;
    maxLength?: number;
    storeUrl: string;
    updateUrlFn: (id: number) => string;
    destroyUrlFn: (id: number) => string;
};

const props = withDefaults(defineProps<Props>(), {
    descripcion: '',
    maxLength: 100,
});

const dialogOpen = ref(false);
const editing = ref<CatalogoSimpleItem | null>(null);

const form = useForm({
    nombre: '',
    activo: true,
});

const openCreate = () => {
    editing.value = null;
    form.reset();
    form.clearErrors();
    form.activo = true;
    dialogOpen.value = true;
};

const openEdit = (item: CatalogoSimpleItem) => {
    editing.value = item;
    form.clearErrors();
    form.nombre = item.nombre;
    form.activo = item.activo;
    dialogOpen.value = true;
};

const submit = () => {
    if (editing.value) {
        form.put(props.updateUrlFn(editing.value.id), {
            preserveScroll: true,
            onSuccess: () => (dialogOpen.value = false),
        });
    } else {
        form.post(props.storeUrl, {
            preserveScroll: true,
            onSuccess: () => (dialogOpen.value = false),
        });
    }
};

const toggleActivo = (item: CatalogoSimpleItem) => {
    router.put(
        props.updateUrlFn(item.id),
        { nombre: item.nombre, activo: !item.activo },
        { preserveScroll: true },
    );
};

const eliminar = (item: CatalogoSimpleItem) => {
    if (
        !confirm(
            `¿Eliminar «${item.nombre}»? Si está en uso, no se podrá eliminar.`,
        )
    ) {
        return;
    }
    router.delete(props.destroyUrlFn(item.id), { preserveScroll: true });
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
    <div class="flex h-full flex-1 flex-col gap-6 p-4">
        <div
            class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between"
        >
            <div>
                <h1 class="text-2xl font-semibold tracking-tight">
                    {{ plural }}
                </h1>
                <p v-if="descripcion" class="text-sm text-muted-foreground">
                    {{ descripcion }}
                </p>
            </div>
            <Button class="w-full sm:w-auto" @click="openCreate">
                <Plus class="mr-2 h-4 w-4" /> Nuevo
            </Button>
        </div>

        <!-- Mobile: cards -->
        <div class="space-y-2 md:hidden">
            <div
                v-if="items.length === 0"
                class="rounded-lg border p-8 text-center text-muted-foreground"
            >
                Aún no hay registros. Toca «Nuevo» para crear el primero.
            </div>
            <article
                v-for="item in items"
                :key="item.id"
                class="flex items-center justify-between gap-3 rounded-lg border bg-card p-3"
            >
                <div class="flex-1 min-w-0">
                    <p class="truncate font-medium">{{ item.nombre }}</p>
                    <button
                        type="button"
                        class="mt-1 text-xs"
                        :class="
                            item.activo
                                ? 'text-emerald-700 dark:text-emerald-400'
                                : 'text-muted-foreground'
                        "
                        @click="toggleActivo(item)"
                    >
                        {{ item.activo ? '● Activo' : '○ Inactivo' }}
                    </button>
                </div>
                <Button
                    variant="ghost"
                    size="sm"
                    class="h-9 w-9 p-0"
                    @click="openEdit(item)"
                >
                    <Pencil class="h-4 w-4" />
                </Button>
                <Button
                    variant="ghost"
                    size="sm"
                    class="h-9 w-9 p-0"
                    @click="eliminar(item)"
                >
                    <Trash2 class="h-4 w-4 text-red-600" />
                </Button>
            </article>
        </div>

        <!-- Desktop: table -->
        <div class="hidden overflow-hidden rounded-xl border md:block">
            <table class="w-full text-sm">
                <thead class="bg-muted/50 text-left">
                    <tr>
                        <th class="px-4 py-3 font-medium">Nombre</th>
                        <th class="px-4 py-3 font-medium">Estado</th>
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
                        <td class="px-4 py-3">
                            <button
                                type="button"
                                class="text-xs"
                                :class="
                                    item.activo
                                        ? 'text-emerald-700 dark:text-emerald-400'
                                        : 'text-muted-foreground'
                                "
                                @click="toggleActivo(item)"
                            >
                                {{
                                    item.activo
                                        ? '● Activo'
                                        : '○ Inactivo'
                                }}
                            </button>
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
                            Aún no hay registros.
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
                            {{ editing ? `Editar ${singular}` : `Nuevo ${singular}` }}
                        </DialogTitle>
                        <DialogDescription>
                            {{
                                editing
                                    ? 'Modifica los datos y guarda los cambios.'
                                    : `Registra un nuevo ${singular.toLowerCase()}.`
                            }}
                        </DialogDescription>
                    </DialogHeader>

                    <div class="space-y-4 py-4">
                        <div class="space-y-2">
                            <Label for="cat-nombre">Nombre *</Label>
                            <Input
                                id="cat-nombre"
                                v-model="form.nombre"
                                :maxlength="maxLength"
                                required
                                autofocus
                            />
                            <InputError :message="form.errors.nombre" />
                        </div>

                        <div class="flex items-center gap-2">
                            <input
                                id="cat-activo"
                                v-model="form.activo"
                                type="checkbox"
                                class="h-4 w-4 rounded border"
                            />
                            <Label for="cat-activo">Activo</Label>
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
