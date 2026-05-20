<script setup lang="ts">
import { router, useForm } from '@inertiajs/vue3';
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
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { destroy, store, update } from '@/routes/catalogos/componentes';
import type {
    ComponenteItem,
    UnidadGramaje,
    UnidadOption,
} from '@/types/catalogos';

type Props = {
    items: ComponenteItem[];
    unidades: UnidadOption[];
};

const props = defineProps<Props>();

const dialogOpen = ref(false);
const editing = ref<ComponenteItem | null>(null);

const form = useForm<{
    nombre: string;
    gramaje_sugerido: number | null;
    unidad: UnidadGramaje;
    observacion: string;
    activo: boolean;
}>({
    nombre: '',
    gramaje_sugerido: null,
    unidad: 'GRAMOS',
    observacion: '',
    activo: true,
});

const openCreate = () => {
    editing.value = null;
    form.reset();
    form.clearErrors();
    form.unidad = 'GRAMOS';
    form.activo = true;
    dialogOpen.value = true;
};

const openEdit = (item: ComponenteItem) => {
    editing.value = item;
    form.clearErrors();
    form.nombre = item.nombre;
    form.gramaje_sugerido = Number(item.gramaje_sugerido);
    form.unidad = item.unidad;
    form.observacion = item.observacion ?? '';
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

const toggleActivo = (item: ComponenteItem) => {
    router.put(
        update(item.id).url,
        {
            nombre: item.nombre,
            gramaje_sugerido: Number(item.gramaje_sugerido),
            unidad: item.unidad,
            observacion: item.observacion,
            activo: !item.activo,
        },
        { preserveScroll: true },
    );
};

const eliminar = (item: ComponenteItem) => {
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

const abreviado = (u: UnidadGramaje): string => (u === 'UNIDADES' ? 'u' : 'g');
</script>

<template>
    <div class="flex h-full flex-1 flex-col gap-6 p-4">
        <div
            class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between"
        >
            <div>
                <h1 class="text-2xl font-semibold tracking-tight">
                    Componentes
                </h1>
                <p class="text-sm text-muted-foreground">
                    Insumos y preparaciones con su gramaje sugerido por defecto.
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
                            Gramaje sugerido
                        </th>
                        <th class="px-4 py-3 font-medium">Unidad</th>
                        <th class="px-4 py-3 font-medium">Observación</th>
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
                        <td class="px-4 py-3 text-right">
                            {{ item.gramaje_sugerido }}
                            {{ abreviado(item.unidad) }}
                        </td>
                        <td class="px-4 py-3 capitalize">
                            {{ item.unidad.toLowerCase() }}
                        </td>
                        <td
                            class="max-w-xs truncate px-4 py-3 text-muted-foreground"
                        >
                            {{ item.observacion ?? '—' }}
                        </td>
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
                            colspan="6"
                            class="px-4 py-12 text-center text-muted-foreground"
                        >
                            Aún no hay componentes registrados.
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
                                    ? 'Editar componente'
                                    : 'Nuevo componente'
                            }}
                        </DialogTitle>
                        <DialogDescription>
                            Datos del insumo y su gramaje sugerido. Este valor
                            pre-rellena el «gramaje esperado» al crear un
                            gramaje.
                        </DialogDescription>
                    </DialogHeader>

                    <div class="space-y-4 py-4">
                        <div class="space-y-2">
                            <Label for="comp-nombre">Nombre *</Label>
                            <Input
                                id="comp-nombre"
                                v-model="form.nombre"
                                maxlength="200"
                                required
                                autofocus
                            />
                            <InputError :message="form.errors.nombre" />
                        </div>

                        <div class="grid gap-4 sm:grid-cols-2">
                            <div class="space-y-2">
                                <Label for="comp-gramaje">
                                    Gramaje sugerido *
                                </Label>
                                <Input
                                    id="comp-gramaje"
                                    :model-value="form.gramaje_sugerido ?? ''"
                                    type="number"
                                    step="0.01"
                                    min="0.01"
                                    required
                                    @update:model-value="
                                        form.gramaje_sugerido =
                                            $event === '' || $event === null
                                                ? null
                                                : Number($event)
                                    "
                                />
                                <InputError
                                    :message="form.errors.gramaje_sugerido"
                                />
                            </div>
                            <div class="space-y-2">
                                <Label for="comp-unidad">Unidad *</Label>
                                <Select
                                    :model-value="form.unidad"
                                    @update:model-value="
                                        form.unidad = $event as UnidadGramaje
                                    "
                                >
                                    <SelectTrigger id="comp-unidad" class="w-full">
                                        <SelectValue />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem
                                            v-for="u in unidades"
                                            :key="u.value"
                                            :value="u.value"
                                        >
                                            {{ u.label }}
                                        </SelectItem>
                                    </SelectContent>
                                </Select>
                                <InputError :message="form.errors.unidad" />
                            </div>
                        </div>

                        <div class="space-y-2">
                            <Label for="comp-obs">Observación</Label>
                            <Input
                                id="comp-obs"
                                v-model="form.observacion"
                                maxlength="200"
                                placeholder="Opcional"
                            />
                            <InputError :message="form.errors.observacion" />
                        </div>

                        <div class="flex items-center gap-2">
                            <input
                                id="comp-activo"
                                v-model="form.activo"
                                type="checkbox"
                                class="h-4 w-4 rounded border"
                            />
                            <Label for="comp-activo">Activo</Label>
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
