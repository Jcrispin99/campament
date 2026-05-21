<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { ArrowLeft, Pencil } from 'lucide-vue-next';
import { computed } from 'vue';
import EstatusBadge from '@/components/Gramajes/EstatusBadge.vue';
import { Button } from '@/components/ui/button';
import { formatFechaLocal } from '@/lib/fecha';
import { edit, index, show } from '@/routes/gramajes';
import type { Gramaje } from '@/types/gramajes';

const props = defineProps<{ gramaje: Gramaje }>();

defineOptions({
    layout: (props: { gramaje: Gramaje }) => ({
        breadcrumbs: [
            { title: 'Gramajes', href: index() },
            {
                title: `Gramaje #${props.gramaje.id}`,
                href: show(props.gramaje.id),
            },
        ],
    }),
});

const formatFecha = (iso: string): string =>
    formatFechaLocal(iso, { year: 'numeric', month: 'long', day: 'numeric' });

const unidadAbreviada = computed<string>(() =>
    props.gramaje.componente?.unidad === 'UNIDADES' ? 'u' : 'g',
);
</script>

<template>
    <Head :title="`Gramaje #${props.gramaje.id}`" />

    <div class="flex h-full flex-1 flex-col gap-6 p-4">
        <div
            class="flex flex-col gap-3 sm:flex-row sm:flex-wrap sm:items-center sm:justify-between"
        >
            <div class="flex flex-wrap items-center gap-3">
                <Button as-child variant="outline" size="sm">
                    <Link :href="index().url">
                        <ArrowLeft class="mr-2 h-4 w-4" /> Volver
                    </Link>
                </Button>
                <h1 class="text-xl font-semibold tracking-tight sm:text-2xl">
                    Gramaje #{{ props.gramaje.id }}
                </h1>
                <EstatusBadge :value="props.gramaje.estatus" />
            </div>
            <Button as-child class="w-full sm:w-auto">
                <Link :href="edit(props.gramaje.id).url">
                    <Pencil class="mr-2 h-4 w-4" /> Editar
                </Link>
            </Button>
        </div>

        <!-- Resumen KPI -->
        <section
            class="grid gap-3 rounded-lg border p-6 sm:grid-cols-2 md:grid-cols-4"
        >
            <div>
                <p class="text-xs uppercase text-muted-foreground">
                    Esperado
                </p>
                <p class="text-2xl font-semibold">
                    {{ gramaje.gramaje_esperado }} {{ unidadAbreviada }}
                </p>
            </div>
            <div>
                <p class="text-xs uppercase text-muted-foreground">
                    Promedio servido
                </p>
                <p class="text-2xl font-semibold">
                    {{ gramaje.peso_promedio }} {{ unidadAbreviada }}
                </p>
            </div>
            <div>
                <p class="text-xs uppercase text-muted-foreground">
                    Variación
                </p>
                <p class="text-2xl font-semibold">
                    {{ gramaje.variacion_pct }} %
                </p>
            </div>
            <div>
                <p class="text-xs uppercase text-muted-foreground">
                    Cantidad muestreada
                </p>
                <p class="text-2xl font-semibold">
                    {{ gramaje.cantidad_muestreada }}
                </p>
            </div>
        </section>

        <!-- Datos -->
        <section class="grid gap-4 rounded-lg border p-6 md:grid-cols-2">
            <div>
                <p class="text-xs uppercase text-muted-foreground">
                    Fecha de muestreo
                </p>
                <p class="font-medium">{{ formatFecha(gramaje.fecha) }}</p>
            </div>
            <div>
                <p class="text-xs uppercase text-muted-foreground">Semana</p>
                <p class="font-medium">{{ gramaje.semana }}</p>
            </div>
            <div>
                <p class="text-xs uppercase text-muted-foreground">
                    Fecha de producción
                </p>
                <p class="font-medium">
                    {{ formatFecha(gramaje.fecha_produccion) }}
                </p>
            </div>
            <div>
                <p class="text-xs uppercase text-muted-foreground">
                    Reportado por
                </p>
                <p class="font-medium">
                    {{ gramaje.reportado_por?.name ?? '—' }}
                </p>
            </div>
            <div>
                <p class="text-xs uppercase text-muted-foreground">Comedor</p>
                <p class="font-medium">{{ gramaje.comedor?.nombre ?? '—' }}</p>
            </div>
            <div>
                <p class="text-xs uppercase text-muted-foreground">Servicio</p>
                <p class="font-medium">
                    {{ gramaje.servicio?.nombre ?? '—' }}
                </p>
            </div>
            <div>
                <p class="text-xs uppercase text-muted-foreground">Plato</p>
                <p class="font-medium">{{ gramaje.plato?.nombre ?? '—' }}</p>
            </div>
            <div>
                <p class="text-xs uppercase text-muted-foreground">
                    Componente
                </p>
                <p class="font-medium">
                    {{ gramaje.componente?.nombre ?? '—' }}
                </p>
            </div>
            <div v-if="gramaje.tipo_corte">
                <p class="text-xs uppercase text-muted-foreground">
                    Tipo de corte
                </p>
                <p class="font-medium">{{ gramaje.tipo_corte.nombre }}</p>
            </div>
        </section>

        <!-- Medidas -->
        <section class="space-y-3 rounded-lg border p-6">
            <h3 class="text-base font-semibold">
                Medidas individuales ({{ gramaje.medidas.length }})
            </h3>
            <div
                v-if="gramaje.medidas.length === 0"
                class="text-sm text-muted-foreground"
            >
                No hay medidas registradas.
            </div>
            <div
                v-else
                class="grid grid-cols-3 gap-2 sm:grid-cols-4 md:grid-cols-6 lg:grid-cols-8"
            >
                <div
                    v-for="(m, idx) in gramaje.medidas"
                    :key="m.id"
                    class="rounded-md border px-3 py-2 text-center"
                >
                    <p class="text-xs text-muted-foreground">
                        #{{ idx + 1 }}
                    </p>
                    <p class="font-medium">
                        {{ m.peso }} {{ unidadAbreviada }}
                    </p>
                </div>
            </div>
        </section>
    </div>
</template>
