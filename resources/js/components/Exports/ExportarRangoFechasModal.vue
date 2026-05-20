<script setup lang="ts">
import { Download } from 'lucide-vue-next';
import { reactive, watch } from 'vue';
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

type Props = {
    open: boolean;
    titulo: string;
    descripcion?: string;
    desde?: string | null;
    hasta?: string | null;
    exportUrlFn: (query: Record<string, string>) => string;
};

const props = withDefaults(defineProps<Props>(), {
    descripcion:
        'Elige el rango de fechas a incluir en el archivo Excel.',
    desde: null,
    hasta: null,
});

const emit = defineEmits<{
    'update:open': [value: boolean];
}>();

const state = reactive<{ desde: string; hasta: string }>({
    desde: props.desde ?? '',
    hasta: props.hasta ?? '',
});

watch(
    () => [props.open, props.desde, props.hasta],
    ([open]) => {
        if (open) {
            state.desde = props.desde ?? '';
            state.hasta = props.hasta ?? '';
        }
    },
);

const handleOpenChange = (next: boolean) => {
    emit('update:open', next);
};

const rangoInvalido = () =>
    !!(state.desde && state.hasta && state.desde > state.hasta);

const exportar = () => {
    if (rangoInvalido()) return;

    const query: Record<string, string> = {};
    if (state.desde) query.desde = state.desde;
    if (state.hasta) query.hasta = state.hasta;

    window.location.href = props.exportUrlFn(query);
    emit('update:open', false);
};
</script>

<template>
    <Dialog :open="props.open" @update:open="handleOpenChange">
        <DialogContent>
            <DialogHeader>
                <DialogTitle>{{ titulo }}</DialogTitle>
                <DialogDescription>
                    {{ descripcion }}
                </DialogDescription>
            </DialogHeader>

            <div class="grid gap-4 py-4 sm:grid-cols-2">
                <div class="space-y-2">
                    <Label for="export-desde">Desde</Label>
                    <Input
                        id="export-desde"
                        v-model="state.desde"
                        type="date"
                    />
                </div>
                <div class="space-y-2">
                    <Label for="export-hasta">Hasta</Label>
                    <Input
                        id="export-hasta"
                        v-model="state.hasta"
                        type="date"
                    />
                </div>
            </div>

            <p
                v-if="rangoInvalido()"
                class="text-sm text-red-600 dark:text-red-500"
            >
                La fecha «Hasta» debe ser igual o posterior a «Desde».
            </p>
            <p
                v-else-if="!state.desde && !state.hasta"
                class="text-sm text-muted-foreground"
            >
                Si dejas ambos campos vacíos se exportarán todos los
                registros.
            </p>

            <DialogFooter class="gap-2">
                <DialogClose as-child>
                    <Button variant="secondary"> Cancelar </Button>
                </DialogClose>
                <Button :disabled="rangoInvalido()" @click="exportar">
                    <Download class="mr-2 h-4 w-4" /> Exportar Excel
                </Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>
