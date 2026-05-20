<script setup lang="ts">
import { router, useForm } from '@inertiajs/vue3';
import { Pencil, Plus, Trash2 } from 'lucide-vue-next';
import { computed, ref, watch } from 'vue';
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
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { destroy, store, update } from '@/routes/catalogos/clasificaciones';
import type { ClasificacionItem } from '@/types/catalogos';

type Props = {
    items: ClasificacionItem[];
    tiposIncidente: { id: number; nombre: string }[];
};

const props = defineProps<Props>();

const dialogOpen = ref(false);
const editing = ref<ClasificacionItem | null>(null);
const filtroTipo = ref<number | null>(null);

const form = useForm<{
    tipo_incidente_id: number | null;
    nombre: string;
    activo: boolean;
}>({
    tipo_incidente_id: null,
    nombre: '',
    activo: true,
});

const itemsFiltrados = computed(() =>
    filtroTipo.value === null
        ? props.items
        : props.items.filter((i) => i.tipo_incidente_id === filtroTipo.value),
);

const openCreate = () => {
    editing.value = null;
    form.reset();
    form.clearErrors();
    form.activo = true;
    form.tipo_incidente_id = filtroTipo.value;
    dialogOpen.value = true;
};

const openEdit = (item: ClasificacionItem) => {
    editing.value = item;
    form.clearErrors();
    form.tipo_incidente_id = item.tipo_incidente_id;
    form.nombre = item.nombre;
    form.activo = item.activo;
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

const toggleActivo = (item: ClasificacionItem) => {
    router.put(
        update(item.id).url,
        {
            tipo_incidente_id: item.tipo_incidente_id,
            nombre: item.nombre,
            activo: !item.activo,
        },
        { preserveScroll: true },
    );
};

const eliminar = (item: ClasificacionItem) => {
    if (
        !confirm(
            `¿Eliminar «${item.nombre}»? Si está en uso, no se podrá eliminar.`,
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
    <div class="flex h-full flex-1 flex-col gap-6 p-4">
        <div
            class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between"
        >
            <div>
                <h1 class="text-2xl font-semibold tracking-tight">
                    Clasificaciones de incidente
                </h1>
                <p class="text-sm text-muted-foreground">
                    Hallazgos específicos agrupados por tipo de incidente.
                </p>
            </div>
            <Button class="w-full sm:w-auto" @click="openCreate">
                <Plus class="mr-2 h-4 w-4" /> Nueva
            </Button>
        </div>

        <!-- Filtro por tipo -->
        <div class="flex flex-wrap gap-2">
            <Button
                size="sm"
                :variant="filtroTipo === null ? 'default' : 'outline'"
                @click="filtroTipo = null"
            >
                Todos
            </Button>
            <Button
                v-for="t in tiposIncidente"
                :key="t.id"
                size="sm"
                :variant="filtroTipo === t.id ? 'default' : 'outline'"
                @click="filtroTipo = t.id"
            >
                {{ t.nombre }}
            </Button>
        </div>

        <div class="overflow-hidden rounded-xl border">
            <table class="w-full text-sm">
                <thead class="bg-muted/50 text-left">
                    <tr>
                        <th class="px-4 py-3 font-medium">Tipo</th>
                        <th class="px-4 py-3 font-medium">Nombre</th>
                        <th class="px-4 py-3 font-medium">Estado</th>
                        <th class="px-4 py-3 text-right font-medium">
                            Acciones
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <tr
                        v-for="item in itemsFiltrados"
                        :key="item.id"
                        class="border-t hover:bg-muted/30"
                    >
                        <td class="px-4 py-3">
                            {{ item.tipo_incidente?.nombre ?? '—' }}
                        </td>
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
                                        ? '● Activa'
                                        : '○ Inactiva'
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
                    <tr v-if="itemsFiltrados.length === 0">
                        <td
                            colspan="4"
                            class="px-4 py-12 text-center text-muted-foreground"
                        >
                            Aún no hay clasificaciones.
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
                                    ? 'Editar clasificación'
                                    : 'Nueva clasificación'
                            }}
                        </DialogTitle>
                        <DialogDescription>
                            Define el tipo de incidente al que pertenece y su
                            nombre.
                        </DialogDescription>
                    </DialogHeader>

                    <div class="space-y-4 py-4">
                        <div class="space-y-2">
                            <Label for="cla-tipo">Tipo de incidente *</Label>
                            <Select
                                :model-value="
                                    form.tipo_incidente_id?.toString()
                                "
                                @update:model-value="
                                    form.tipo_incidente_id = Number($event)
                                "
                            >
                                <SelectTrigger id="cla-tipo" class="w-full">
                                    <SelectValue placeholder="Selecciona un tipo" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem
                                        v-for="t in tiposIncidente"
                                        :key="t.id"
                                        :value="t.id.toString()"
                                    >
                                        {{ t.nombre }}
                                    </SelectItem>
                                </SelectContent>
                            </Select>
                            <InputError
                                :message="form.errors.tipo_incidente_id"
                            />
                        </div>

                        <div class="space-y-2">
                            <Label for="cla-nombre">Nombre *</Label>
                            <Input
                                id="cla-nombre"
                                v-model="form.nombre"
                                maxlength="200"
                                required
                            />
                            <InputError :message="form.errors.nombre" />
                        </div>

                        <div class="flex items-center gap-2">
                            <input
                                id="cla-activo"
                                v-model="form.activo"
                                type="checkbox"
                                class="h-4 w-4 rounded border"
                            />
                            <Label for="cla-activo">Activa</Label>
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
