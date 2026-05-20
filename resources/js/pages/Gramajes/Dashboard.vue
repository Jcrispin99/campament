<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { Download } from 'lucide-vue-next';
import { computed, ref } from 'vue';
import ExportarRangoFechasModal from '@/components/Exports/ExportarRangoFechasModal.vue';
import EstatusBadge from '@/components/Gramajes/EstatusBadge.vue';
import BarChart from '@/components/Reportes/Dashboard/BarChart.vue';
import BarList from '@/components/Reportes/Dashboard/BarList.vue';
import ChartCard from '@/components/Reportes/Dashboard/ChartCard.vue';
import DonutChart from '@/components/Reportes/Dashboard/DonutChart.vue';
import FilterBar from '@/components/Reportes/Dashboard/FilterBar.vue';
import KpiCard from '@/components/Reportes/Dashboard/KpiCard.vue';
import LineChart from '@/components/Reportes/Dashboard/LineChart.vue';
import { Button } from '@/components/ui/button';
import {
    dashboard,
    exportMethod,
    index,
    show,
} from '@/routes/gramajes';

type Filtros = {
    desde: string | null;
    hasta: string | null;
};

type Kpis = {
    total: number;
    conformes: number;
    inconformes: number;
    variacionPromedio: number;
    totalMedidas: number;
};

type NombreYTotal = { nombre: string; total: number };

type ComponenteRow = NombreYTotal & {
    conformes: number;
    inconformes: number;
};

type DesviacionRow = {
    id: number;
    fecha: string;
    componente: string | null;
    variacion: number;
};

type InconformeRow = {
    id: number;
    fecha: string;
    comedor: string | null;
    servicio: string | null;
    componente: string | null;
    esperado: number;
    promedio: number;
    variacion: number;
    cantidad: number;
};

type Props = {
    filtros: Filtros;
    kpis: Kpis;
    porComedor: NombreYTotal[];
    porServicio: NombreYTotal[];
    porPlato: NombreYTotal[];
    porComponente: ComponenteRow[];
    porSemana: { semana: number; total: number }[];
    estatusDistribucion: { conformes: number; inconformes: number };
    topDesviaciones: DesviacionRow[];
    ultimasInconformes: InconformeRow[];
};

const props = defineProps<Props>();

defineOptions({
    layout: () => ({
        breadcrumbs: [
            { title: 'Gramajes', href: index() },
            { title: 'Dashboard', href: dashboard() },
        ],
    }),
});

const palette = [
    '#6366f1',
    '#0ea5e9',
    '#f59e0b',
    '#ec4899',
    '#14b8a6',
    '#8b5cf6',
    '#22c55e',
    '#f97316',
];

const comedorBars = computed(() =>
    props.porComedor.map((c, i) => ({
        label: c.nombre,
        value: c.total,
        color: palette[i % palette.length],
    })),
);

const servicioBars = computed(() =>
    props.porServicio.map((s, i) => ({
        label: s.nombre,
        value: s.total,
        color: palette[i % palette.length],
    })),
);

const platoSlices = computed(() =>
    props.porPlato.map((p, i) => ({
        label: p.nombre,
        value: p.total,
        color: palette[i % palette.length],
    })),
);

const estatusSlices = computed(() => [
    {
        label: 'Conformes',
        value: props.estatusDistribucion.conformes,
        color: '#10b981',
    },
    {
        label: 'Inconformes',
        value: props.estatusDistribucion.inconformes,
        color: '#ef4444',
    },
]);

const componenteItems = computed(() =>
    props.porComponente.map((c, i) => ({
        label: c.nombre,
        sublabel: `${c.conformes} conformes · ${c.inconformes} inconformes`,
        value: c.total,
        color: palette[i % palette.length],
    })),
);

const tieneDatos = computed(() => props.kpis.total > 0);
const exportarOpen = ref(false);

const formatFecha = (iso: string): string => {
    const d = new Date(iso);
    return d.toLocaleDateString('es-PE', {
        year: 'numeric',
        month: '2-digit',
        day: '2-digit',
    });
};

const desviacionClass = (variacion: number): string => {
    const distancia = Math.abs(variacion - 100);
    if (distancia <= 5) {
        return 'text-emerald-700 dark:text-emerald-400';
    }
    if (distancia <= 15) {
        return 'text-amber-700 dark:text-amber-400';
    }
    return 'text-red-700 dark:text-red-400';
};
</script>

<template>
    <Head title="Dashboard de gramajes" />

    <div class="flex h-full flex-1 flex-col gap-6 p-4">
        <div
            class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between"
        >
            <div>
                <h1 class="text-2xl font-semibold tracking-tight">
                    Dashboard de gramajes
                </h1>
                <p class="text-sm text-muted-foreground">
                    Indicadores de muestreo de peso por componente y servicio.
                </p>
            </div>
            <Button
                variant="outline"
                class="w-full sm:w-auto"
                @click="exportarOpen = true"
            >
                <Download class="mr-2 h-4 w-4" /> Exportar Excel
            </Button>
        </div>

        <FilterBar :value="filtros" :dashboard-url="dashboard().url" />

        <!-- KPIs -->
        <div class="grid grid-cols-2 gap-3 lg:grid-cols-4">
            <KpiCard
                label="Total registros"
                :value="kpis.total"
                tone="neutral"
            />
            <KpiCard
                label="Conformes"
                :value="kpis.conformes"
                :total="kpis.total"
                tone="green"
            />
            <KpiCard
                label="Inconformes"
                :value="kpis.inconformes"
                :total="kpis.total"
                tone="red"
            />
            <KpiCard
                label="Variación promedio"
                :value="kpis.variacionPromedio"
                suffix=" %"
                tone="neutral"
            />
        </div>

        <!-- Charts principales -->
        <div class="grid gap-4 lg:grid-cols-2">
            <ChartCard
                title="Estatus de gramajes"
                description="Distribución entre conforme e inconforme"
                :empty="!tieneDatos"
            >
                <DonutChart :data="estatusSlices" value-suffix="registros" />
            </ChartCard>

            <ChartCard
                title="Por plato"
                description="Distribución por categoría de plato"
                :empty="!tieneDatos || platoSlices.length === 0"
            >
                <DonutChart :data="platoSlices" value-suffix="registros" />
            </ChartCard>

            <ChartCard
                title="Por comedor"
                description="Cantidad de muestreos por comedor"
                :empty="!tieneDatos || comedorBars.length === 0"
            >
                <BarChart :data="comedorBars" :height="260" value-suffix="registros" />
            </ChartCard>

            <ChartCard
                title="Por servicio"
                description="Cantidad de muestreos por servicio"
                :empty="!tieneDatos || servicioBars.length === 0"
            >
                <BarChart :data="servicioBars" :height="260" value-suffix="registros" />
            </ChartCard>
        </div>

        <!-- Top componentes (full width) -->
        <ChartCard
            title="Top 10 componentes muestreados"
            description="Los componentes más frecuentes con su desglose de conformidad"
            :empty="!tieneDatos || componenteItems.length === 0"
        >
            <BarList :items="componenteItems" />
        </ChartCard>

        <!-- Tendencia semanal -->
        <ChartCard
            title="Tendencia semanal"
            description="Cantidad de registros por semana del año"
            :empty="!tieneDatos || porSemana.length === 0"
        >
            <LineChart :data="porSemana" :height="260" value-suffix="registros" />
        </ChartCard>

        <!-- Top desviaciones -->
        <ChartCard
            title="Top 10 mayores desviaciones"
            description="Registros con mayor distancia desde el 100% de variación (sea por exceso o defecto)"
            :empty="!tieneDatos || topDesviaciones.length === 0"
        >
            <div class="overflow-hidden rounded-md border">
                <table class="w-full text-sm">
                    <thead class="bg-muted/50 text-left">
                        <tr>
                            <th class="px-3 py-2 font-medium">Fecha</th>
                            <th class="px-3 py-2 font-medium">Componente</th>
                            <th class="px-3 py-2 text-right font-medium">
                                Variación
                            </th>
                            <th class="px-3 py-2 text-right font-medium">
                                Acción
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr
                            v-for="d in topDesviaciones"
                            :key="d.id"
                            class="border-t"
                        >
                            <td class="px-3 py-2">
                                {{ formatFecha(d.fecha) }}
                            </td>
                            <td class="px-3 py-2">
                                {{ d.componente ?? '—' }}
                            </td>
                            <td
                                class="px-3 py-2 text-right font-medium"
                                :class="desviacionClass(d.variacion)"
                            >
                                {{ d.variacion }} %
                            </td>
                            <td class="px-3 py-2 text-right">
                                <Link
                                    :href="show(d.id).url"
                                    class="text-xs text-primary hover:underline"
                                >
                                    Ver
                                </Link>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </ChartCard>

        <!-- Últimas inconformes -->
        <ChartCard
            title="Últimas inconformidades"
            description="Hasta 10 registros más recientes con estatus inconforme"
            :empty="!tieneDatos || ultimasInconformes.length === 0"
        >
            <div class="space-y-3">
                <article
                    v-for="g in ultimasInconformes"
                    :key="g.id"
                    class="rounded-lg border p-4"
                >
                    <header
                        class="flex flex-col gap-2 sm:flex-row sm:flex-wrap sm:items-center sm:justify-between"
                    >
                        <div>
                            <p class="text-xs text-muted-foreground">
                                {{ formatFecha(g.fecha) }} ·
                                {{ g.comedor ?? '—' }} ·
                                {{ g.servicio ?? '—' }}
                            </p>
                            <p class="mt-1 font-medium">
                                {{ g.componente ?? '—' }}
                            </p>
                        </div>
                        <EstatusBadge value="INCONFORME" />
                    </header>

                    <div class="mt-3 grid gap-3 text-sm sm:grid-cols-4">
                        <div>
                            <p class="text-xs uppercase text-muted-foreground">
                                Esperado
                            </p>
                            <p class="font-medium">{{ g.esperado }}</p>
                        </div>
                        <div>
                            <p class="text-xs uppercase text-muted-foreground">
                                Promedio
                            </p>
                            <p class="font-medium">{{ g.promedio }}</p>
                        </div>
                        <div>
                            <p class="text-xs uppercase text-muted-foreground">
                                Variación
                            </p>
                            <p
                                class="font-medium"
                                :class="desviacionClass(g.variacion)"
                            >
                                {{ g.variacion }} %
                            </p>
                        </div>
                        <div>
                            <p class="text-xs uppercase text-muted-foreground">
                                Muestras
                            </p>
                            <p class="font-medium">{{ g.cantidad }}</p>
                        </div>
                    </div>

                    <div class="mt-3 flex justify-end">
                        <Link
                            :href="show(g.id).url"
                            class="text-xs text-primary hover:underline"
                        >
                            Ver detalle →
                        </Link>
                    </div>
                </article>
            </div>
        </ChartCard>

        <ExportarRangoFechasModal
            :open="exportarOpen"
            titulo="Exportar gramajes a Excel"
            :desde="filtros.desde"
            :hasta="filtros.hasta"
            :export-url-fn="(query) => exportMethod({ query }).url"
            @update:open="exportarOpen = $event"
        />
    </div>
</template>
