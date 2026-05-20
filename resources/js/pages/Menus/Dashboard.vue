<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { ArrowRight, Download } from 'lucide-vue-next';
import { computed, ref } from 'vue';
import ExportarRangoFechasModal from '@/components/Exports/ExportarRangoFechasModal.vue';
import BarChart from '@/components/Reportes/Dashboard/BarChart.vue';
import ChartCard from '@/components/Reportes/Dashboard/ChartCard.vue';
import DonutChart from '@/components/Reportes/Dashboard/DonutChart.vue';
import FilterBar from '@/components/Reportes/Dashboard/FilterBar.vue';
import KpiCard from '@/components/Reportes/Dashboard/KpiCard.vue';
import LineChart from '@/components/Reportes/Dashboard/LineChart.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { dashboard, exportMethod, index, show } from '@/routes/menus';

type Filtros = {
    desde: string | null;
    hasta: string | null;
};

type Kpis = {
    total: number;
    previsionPromedio: number;
    conformesAprox: number;
    inconformesAprox: number;
    sinPrevision: number;
};

type NombreYTotal = { nombre: string; total: number };
type TextoYTotal = { texto: string; total: number };
type RangoYTotal = { rango: string; total: number };

type CambioUrgente = {
    id: number;
    fecha: string;
    servicio: string | null;
    componente: string | null;
    programado: string;
    propuesta: string;
    motivo: string;
    diasPrevision: number;
    conformidad: string;
};

type Props = {
    filtros: Filtros;
    kpis: Kpis;
    porServicio: NombreYTotal[];
    porComponente: NombreYTotal[];
    porSemana: { semana: number; total: number }[];
    distribucionPrevision: RangoYTotal[];
    conformidadDistribucion: TextoYTotal[];
    topMotivos: TextoYTotal[];
    topAnalisis: TextoYTotal[];
    cambiosUrgentes: CambioUrgente[];
};

const props = defineProps<Props>();

defineOptions({
    layout: () => ({
        breadcrumbs: [
            { title: 'Cambios de menú', href: index() },
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

const servicioBars = computed(() =>
    props.porServicio.map((s, i) => ({
        label: s.nombre,
        value: s.total,
        color: palette[i % palette.length],
    })),
);

const componenteBars = computed(() =>
    props.porComponente.map((c, i) => ({
        label: c.nombre,
        value: c.total,
        color: palette[i % palette.length],
    })),
);

const previsionColors: Record<string, string> = {
    'Menos de 3 días': '#ef4444',
    '3-4 días': '#f59e0b',
    '5 o más días': '#10b981',
};

const previsionSlices = computed(() =>
    props.distribucionPrevision.map((r) => ({
        label: r.rango,
        value: r.total,
        color: previsionColors[r.rango] ?? '#6366f1',
    })),
);

const conformidadColor = (texto: string): string => {
    const v = texto.toLowerCase();
    if (v.includes('inconforme')) return '#ef4444';
    if (v.includes('conforme')) return '#10b981';
    return '#f59e0b';
};

const conformidadSlices = computed(() =>
    props.conformidadDistribucion.map((c) => ({
        label: c.texto,
        value: c.total,
        color: conformidadColor(c.texto),
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

const conformidadClass = (valor: string): string => {
    const v = valor.toLowerCase();
    if (v.includes('conforme') && !v.includes('inconforme'))
        return 'border-emerald-500 bg-emerald-50 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-400';
    if (v.includes('inconforme'))
        return 'border-red-500 bg-red-50 text-red-700 dark:bg-red-950 dark:text-red-400';
    return 'border-amber-500 bg-amber-50 text-amber-700 dark:bg-amber-950 dark:text-amber-400';
};

const previsionClass = (dias: number): string => {
    if (dias < 3) return 'text-red-700 dark:text-red-400';
    if (dias < 5) return 'text-amber-700 dark:text-amber-400';
    return 'text-emerald-700 dark:text-emerald-400';
};
</script>

<template>
    <Head title="Dashboard de cambios de menú" />

    <div class="flex h-full flex-1 flex-col gap-6 p-4">
        <div
            class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between"
        >
            <div>
                <h1 class="text-2xl font-semibold tracking-tight">
                    Dashboard de cambios de menú
                </h1>
                <p class="text-sm text-muted-foreground">
                    Indicadores de cambios de menú, previsión y conformidad.
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
        <div class="grid grid-cols-2 gap-3 lg:grid-cols-5">
            <KpiCard
                label="Total cambios"
                :value="kpis.total"
                tone="neutral"
            />
            <KpiCard
                label="Previsión promedio"
                :value="kpis.previsionPromedio"
                suffix=" días"
                tone="neutral"
            />
            <KpiCard
                label="Conformes (aprox)"
                :value="kpis.conformesAprox"
                :total="kpis.total"
                tone="green"
            />
            <KpiCard
                label="Inconformes (aprox)"
                :value="kpis.inconformesAprox"
                :total="kpis.total"
                tone="red"
            />
            <KpiCard
                label="Con poca previsión"
                :value="kpis.sinPrevision"
                :total="kpis.total"
                tone="amber"
            />
        </div>

        <!-- Charts principales -->
        <div class="grid gap-4 lg:grid-cols-2">
            <ChartCard
                title="Distribución de previsión"
                description="Cuántos días antes se solicitó el cambio"
                :empty="!tieneDatos"
            >
                <DonutChart :data="previsionSlices" value-suffix="cambios" />
            </ChartCard>

            <ChartCard
                title="Conformidad declarada"
                description="Valores únicos del campo «Conformidad»"
                :empty="!tieneDatos || conformidadSlices.length === 0"
            >
                <DonutChart :data="conformidadSlices" value-suffix="cambios" />
            </ChartCard>

            <ChartCard
                title="Cambios por servicio"
                description="Top 10 servicios con más cambios"
                :empty="!tieneDatos || servicioBars.length === 0"
            >
                <BarChart :data="servicioBars" :height="260" value-suffix="cambios" />
            </ChartCard>

            <ChartCard
                title="Cambios por componente"
                description="Top 10 componentes con más cambios"
                :empty="!tieneDatos || componenteBars.length === 0"
            >
                <BarChart :data="componenteBars" :height="260" value-suffix="cambios" />
            </ChartCard>
        </div>

        <!-- Tendencia semanal -->
        <ChartCard
            title="Tendencia semanal"
            description="Cantidad de cambios por semana del año"
            :empty="!tieneDatos || porSemana.length === 0"
        >
            <LineChart :data="porSemana" :height="260" value-suffix="cambios" />
        </ChartCard>

        <!-- Top motivos y análisis -->
        <div class="grid gap-4 lg:grid-cols-2">
            <ChartCard
                title="Motivos más frecuentes"
                description="Top 8 textos repetidos en el campo «Motivo»"
                :empty="!tieneDatos || topMotivos.length === 0"
            >
                <div class="space-y-2">
                    <div
                        v-for="(item, idx) in topMotivos"
                        :key="idx"
                        class="flex items-start justify-between gap-3 rounded-md border p-3"
                    >
                        <p class="flex-1 text-sm">{{ item.texto }}</p>
                        <Badge variant="secondary">{{ item.total }}</Badge>
                    </div>
                </div>
            </ChartCard>

            <ChartCard
                title="Análisis más frecuentes"
                description="Top 8 textos repetidos en el campo «Análisis»"
                :empty="!tieneDatos || topAnalisis.length === 0"
            >
                <div class="space-y-2">
                    <div
                        v-for="(item, idx) in topAnalisis"
                        :key="idx"
                        class="flex items-start justify-between gap-3 rounded-md border p-3"
                    >
                        <p class="flex-1 text-sm">{{ item.texto }}</p>
                        <Badge variant="secondary">{{ item.total }}</Badge>
                    </div>
                </div>
            </ChartCard>
        </div>

        <!-- Cambios urgentes (sin previsión) -->
        <ChartCard
            title="Cambios con poca previsión (< 3 días)"
            description="Solicitudes con muy poca anticipación, para análisis cualitativo"
            :empty="!tieneDatos || cambiosUrgentes.length === 0"
        >
            <div class="space-y-3">
                <article
                    v-for="c in cambiosUrgentes"
                    :key="c.id"
                    class="rounded-lg border p-4"
                >
                    <header
                        class="flex flex-col gap-2 sm:flex-row sm:flex-wrap sm:items-center sm:justify-between"
                    >
                        <div>
                            <p class="text-xs text-muted-foreground">
                                {{ formatFecha(c.fecha) }} ·
                                {{ c.servicio ?? '—' }} ·
                                {{ c.componente ?? '—' }}
                            </p>
                            <p
                                class="mt-1 flex flex-wrap items-center gap-1 text-sm font-medium"
                            >
                                <span class="line-through opacity-60">
                                    {{ c.programado }}
                                </span>
                                <ArrowRight class="h-3 w-3" />
                                <span>{{ c.propuesta }}</span>
                            </p>
                        </div>
                        <div class="flex flex-wrap items-center gap-2">
                            <Badge
                                variant="outline"
                                :class="conformidadClass(c.conformidad)"
                            >
                                {{ c.conformidad }}
                            </Badge>
                            <span
                                class="text-xs font-medium"
                                :class="previsionClass(c.diasPrevision)"
                            >
                                {{ c.diasPrevision }}
                                {{ c.diasPrevision === 1 ? 'día' : 'días' }}
                            </span>
                        </div>
                    </header>

                    <div class="mt-3 grid gap-3 text-sm">
                        <div>
                            <p class="text-xs uppercase text-muted-foreground">
                                Motivo
                            </p>
                            <p class="whitespace-pre-wrap">{{ c.motivo }}</p>
                        </div>
                    </div>

                    <div class="mt-3 flex justify-end">
                        <Link
                            :href="show(c.id).url"
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
            titulo="Exportar cambios de menú a Excel"
            :desde="filtros.desde"
            :hasta="filtros.hasta"
            :export-url-fn="(query) => exportMethod({ query }).url"
            @update:open="exportarOpen = $event"
        />
    </div>
</template>
