<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import { RotateCcw } from 'lucide-vue-next';
import { reactive, watch } from 'vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { dashboard } from '@/routes/reportes';

type Filtros = {
    desde: string | null;
    hasta: string | null;
};

const props = defineProps<{ value: Filtros }>();

const state = reactive<Filtros>({
    desde: props.value.desde,
    hasta: props.value.hasta,
});

watch(
    () => props.value,
    (nuevo) => {
        state.desde = nuevo.desde;
        state.hasta = nuevo.hasta;
    },
);

const aplicar = () => {
    const data: Record<string, string> = {};
    if (state.desde) data.desde = state.desde;
    if (state.hasta) data.hasta = state.hasta;
    router.get(dashboard().url, data, {
        preserveState: false,
        preserveScroll: true,
    });
};

const limpiar = () => {
    router.get(
        dashboard().url,
        {},
        { preserveState: false, preserveScroll: true },
    );
};

const setRango = (dias: number) => {
    const hoy = new Date();
    const desde = new Date();
    desde.setDate(desde.getDate() - dias);
    state.desde = desde.toISOString().slice(0, 10);
    state.hasta = hoy.toISOString().slice(0, 10);
    aplicar();
};

const setSemanaActual = () => {
    const hoy = new Date();
    const dia = hoy.getDay(); // 0=Dom, 1=Lun, ... 6=Sab
    const offsetALunes = dia === 0 ? -6 : 1 - dia;
    const lunes = new Date(hoy);
    lunes.setDate(hoy.getDate() + offsetALunes);
    const domingo = new Date(lunes);
    domingo.setDate(lunes.getDate() + 6);
    state.desde = lunes.toISOString().slice(0, 10);
    state.hasta = domingo.toISOString().slice(0, 10);
    aplicar();
};

const setMesActual = () => {
    const hoy = new Date();
    const inicio = new Date(hoy.getFullYear(), hoy.getMonth(), 1);
    const fin = new Date(hoy.getFullYear(), hoy.getMonth() + 1, 0);
    state.desde = inicio.toISOString().slice(0, 10);
    state.hasta = fin.toISOString().slice(0, 10);
    aplicar();
};
</script>

<template>
    <section class="space-y-3 rounded-xl border bg-card p-4 sm:p-5">
        <div class="flex flex-wrap gap-2">
            <Button
                type="button"
                variant="outline"
                size="sm"
                @click="setSemanaActual"
            >
                Semana actual
            </Button>
            <Button
                type="button"
                variant="outline"
                size="sm"
                @click="setMesActual"
            >
                Mes actual
            </Button>
            <Button
                type="button"
                variant="outline"
                size="sm"
                @click="setRango(30)"
            >
                Últimos 30 días
            </Button>
            <Button
                type="button"
                variant="outline"
                size="sm"
                @click="setRango(90)"
            >
                Últimos 90 días
            </Button>
        </div>

        <div
            class="grid gap-3 sm:grid-cols-[1fr_1fr_auto_auto] sm:items-end"
        >
            <div class="space-y-2">
                <Label for="desde">Desde</Label>
                <Input
                    id="desde"
                    type="date"
                    :model-value="state.desde ?? ''"
                    @update:model-value="state.desde = String($event ?? '') || null"
                />
            </div>
            <div class="space-y-2">
                <Label for="hasta">Hasta</Label>
                <Input
                    id="hasta"
                    type="date"
                    :model-value="state.hasta ?? ''"
                    @update:model-value="state.hasta = String($event ?? '') || null"
                />
            </div>
            <Button @click="aplicar" class="w-full sm:w-auto">
                Aplicar
            </Button>
            <Button
                variant="outline"
                @click="limpiar"
                class="w-full sm:w-auto"
            >
                <RotateCcw class="mr-2 h-4 w-4" /> Limpiar
            </Button>
        </div>
    </section>
</template>
