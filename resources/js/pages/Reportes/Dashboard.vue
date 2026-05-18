<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { Download } from 'lucide-vue-next';
import { computed, ref } from 'vue';
import BarChart from '@/components/Reportes/Dashboard/BarChart.vue';
import BarList from '@/components/Reportes/Dashboard/BarList.vue';
import ChartCard from '@/components/Reportes/Dashboard/ChartCard.vue';
import DonutChart from '@/components/Reportes/Dashboard/DonutChart.vue';
import ExportarReportesModal from '@/components/Reportes/Dashboard/ExportarReportesModal.vue';
import FilterBar from '@/components/Reportes/Dashboard/FilterBar.vue';
import KpiCard from '@/components/Reportes/Dashboard/KpiCard.vue';
import LineChart from '@/components/Reportes/Dashboard/LineChart.vue';
import { Button } from '@/components/ui/button';
import { dashboard, index } from '@/routes/reportes';

type Filtros = {
    desde: string | null;
    hasta: string | null;
};

type Kpis = {
    total: number;
    criticos: number;
    corregidos: number;
    planAccion: number;
};

type NombreYTotal = { nombre: string; total: number };
type ClasificacionRow = NombreYTotal & { tipo: string };
type CriticidadRow = { nivel: 'LEVE' | 'MODERADO' | 'CRITICO'; total: number };

type Props = {
    filtros: Filtros;
    kpis: Kpis;
    porTipo: NombreYTotal[];
    porClasificacion: ClasificacionRow[];
    porCausa: NombreYTotal[];
    porCriticidad: CriticidadRow[];
    corregidos: { corregidos: number; pendientes: number };
    porSemana: { semana: number; total: number }[];
};

const props = defineProps<Props>();

defineOptions({
    layout: () => ({
        breadcrumbs: [
            { title: 'Reportes', href: index() },
            { title: 'Dashboard', href: dashboard() },
        ],
    }),
});

// Paleta consistente: colores semánticos para tipos conocidos y rotación para el resto.
const tipoColor: Record<string, string> = {
    Inocuidad: '#3b82f6',
    Servicio: '#10b981',
    Seguridad: '#ef4444',
};
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
const colorPorTipo = (nombre: string, idx: number): string =>
    tipoColor[nombre] ?? palette[idx % palette.length];

const criticidadColor: Record<CriticidadRow['nivel'], string> = {
    LEVE: '#94a3b8',
    MODERADO: '#f59e0b',
    CRITICO: '#ef4444',
};
const criticidadLabel: Record<CriticidadRow['nivel'], string> = {
    LEVE: 'Leve',
    MODERADO: 'Moderado',
    CRITICO: 'Crítico',
};

const tipoBars = computed(() =>
    props.porTipo.map((t, i) => ({
        label: t.nombre,
        value: t.total,
        color: colorPorTipo(t.nombre, i),
    })),
);

const criticidadBars = computed(() =>
    props.porCriticidad.map((c) => ({
        label: criticidadLabel[c.nivel],
        value: c.total,
        color: criticidadColor[c.nivel],
    })),
);

const causaSlices = computed(() =>
    props.porCausa.map((c, i) => ({
        label: c.nombre,
        value: c.total,
        color: palette[i % palette.length],
    })),
);

const corregidoSlices = computed(() => [
    {
        label: 'Corregidos',
        value: props.corregidos.corregidos,
        color: '#10b981',
    },
    {
        label: 'Pendientes',
        value: props.corregidos.pendientes,
        color: '#f59e0b',
    },
]);

const clasificacionItems = computed(() =>
    props.porClasificacion.map((c, i) => ({
        label: c.nombre,
        sublabel: c.tipo,
        value: c.total,
        color: colorPorTipo(c.tipo, i),
    })),
);

const tieneDatos = computed(() => props.kpis.total > 0);

const exportarOpen = ref(false);
</script>

<template>
    <Head title="Dashboard de reportes" />

    <div class="flex h-full flex-1 flex-col gap-6 p-4">
        <div
            class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between"
        >
            <div>
                <h1 class="text-2xl font-semibold tracking-tight">
                    Dashboard de reportes
                </h1>
                <p class="text-sm text-muted-foreground">
                    Indicadores y análisis de inspecciones según el rango
                    filtrado.
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

        <FilterBar :value="filtros" />

        <!-- KPIs -->
        <div class="grid grid-cols-2 gap-3 lg:grid-cols-4">
            <KpiCard
                label="Total de reportes"
                :value="kpis.total"
                tone="neutral"
            />
            <KpiCard
                label="Críticos"
                :value="kpis.criticos"
                :total="kpis.total"
                tone="red"
            />
            <KpiCard
                label="Se corrigieron"
                :value="kpis.corregidos"
                :total="kpis.total"
                tone="green"
            />
            <KpiCard
                label="Requieren plan de acción"
                :value="kpis.planAccion"
                :total="kpis.total"
                tone="amber"
            />
        </div>

        <!-- Charts principales -->
        <div class="grid gap-4 lg:grid-cols-2">
            <ChartCard
                title="Reportes por tipo de incidente"
                description="Cantidad de hallazgos según categoría principal"
                :empty="!tieneDatos || tipoBars.length === 0"
            >
                <BarChart :data="tipoBars" :height="260" />
            </ChartCard>

            <ChartCard
                title="Análisis de causa"
                description="Distribución de la causa raíz identificada"
                :empty="!tieneDatos || causaSlices.length === 0"
            >
                <DonutChart :data="causaSlices" />
            </ChartCard>

            <ChartCard
                title="Estado de corrección"
                description="Reportes que ya fueron atendidos versus pendientes"
                :empty="!tieneDatos"
            >
                <DonutChart :data="corregidoSlices" />
            </ChartCard>

            <ChartCard
                title="Distribución por criticidad"
                description="Severidad de los hallazgos registrados"
                :empty="!tieneDatos || criticidadBars.length === 0"
            >
                <BarChart :data="criticidadBars" :height="260" />
            </ChartCard>
        </div>

        <!-- Top clasificaciones (full width) -->
        <ChartCard
            title="Top 10 clasificaciones"
            description="Las observaciones específicas más frecuentes, coloreadas por tipo de incidente"
            :empty="!tieneDatos || clasificacionItems.length === 0"
        >
            <BarList :items="clasificacionItems" />
        </ChartCard>

        <!-- Tendencia semanal (full width) -->
        <ChartCard
            title="Tendencia semanal"
            description="Cantidad de reportes registrados por semana del año"
            :empty="!tieneDatos || porSemana.length === 0"
        >
            <LineChart :data="porSemana" :height="260" />
        </ChartCard>

        <ExportarReportesModal
            :open="exportarOpen"
            :desde="filtros.desde"
            :hasta="filtros.hasta"
            @update:open="exportarOpen = $event"
        />
    </div>
</template>
