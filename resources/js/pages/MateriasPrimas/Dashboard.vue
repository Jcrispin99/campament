<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { Download } from 'lucide-vue-next';
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
import {
    dashboard,
    exportMethod,
    index,
} from '@/routes/materias-primas';

type Filtros = {
    desde: string | null;
    hasta: string | null;
};

type Kpis = {
    total: number;
    conformes: number;
    conNc: number;
    conformidadMp: number;
    conformidadDocumentacion: number;
    conformidadVehiculo: number;
};

type NombreYTotal = { nombre: string; total: number };
type TextoYTotal = { texto: string; total: number };

type ConformidadParcial = { conformes: number; noConformes: number };

type Props = {
    filtros: Filtros;
    kpis: Kpis;
    porTipoProducto: NombreYTotal[];
    porProveedor: NombreYTotal[];
    porOrigen: NombreYTotal[];
    conformidadDistribucion: {
        mp: ConformidadParcial;
        documentacion: ConformidadParcial;
        vehiculo: ConformidadParcial;
    };
    porSemana: { semana: number; total: number }[];
    topAcciones: TextoYTotal[];
    topProductosAfectados: TextoYTotal[];
    ultimasNc: {
        id: number;
        fecha: string;
        tipoProducto: string | null;
        proveedor: string | null;
        origen: string | null;
        causa: string;
        accion: string;
        productos: string;
        ncEn: string[];
    }[];
};

const props = defineProps<Props>();

defineOptions({
    layout: () => ({
        breadcrumbs: [
            { title: 'Materia prima', href: index() },
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

const tipoProductoBars = computed(() =>
    props.porTipoProducto.map((t, i) => ({
        label: t.nombre,
        value: t.total,
        color: palette[i % palette.length],
    })),
);

const proveedorBars = computed(() =>
    props.porProveedor.map((p, i) => ({
        label: p.nombre,
        value: p.total,
        color: palette[i % palette.length],
    })),
);

const origenSlices = computed(() =>
    props.porOrigen.map((o, i) => ({
        label: o.nombre,
        value: o.total,
        color: palette[i % palette.length],
    })),
);

const conformidadMpSlices = computed(() => [
    {
        label: 'Conforme',
        value: props.conformidadDistribucion.mp.conformes,
        color: '#10b981',
    },
    {
        label: 'No Conforme',
        value: props.conformidadDistribucion.mp.noConformes,
        color: '#ef4444',
    },
]);

const conformidadDocSlices = computed(() => [
    {
        label: 'Conforme',
        value: props.conformidadDistribucion.documentacion.conformes,
        color: '#10b981',
    },
    {
        label: 'No Conforme',
        value: props.conformidadDistribucion.documentacion.noConformes,
        color: '#ef4444',
    },
]);

const conformidadVehSlices = computed(() => [
    {
        label: 'Conforme',
        value: props.conformidadDistribucion.vehiculo.conformes,
        color: '#10b981',
    },
    {
        label: 'No Conforme',
        value: props.conformidadDistribucion.vehiculo.noConformes,
        color: '#ef4444',
    },
]);

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
</script>

<template>
    <Head title="Dashboard de materia prima" />

    <div class="flex h-full flex-1 flex-col gap-6 p-4">
        <div
            class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between"
        >
            <div>
                <h1 class="text-2xl font-semibold tracking-tight">
                    Dashboard de materia prima
                </h1>
                <p class="text-sm text-muted-foreground">
                    Indicadores de recepciones y conformidad según el rango
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
                label="Total recepciones"
                :value="kpis.total"
                tone="neutral"
            />
            <KpiCard
                label="Todo conforme"
                :value="kpis.conformes"
                :total="kpis.total"
                tone="green"
            />
            <KpiCard
                label="Con no conformidad"
                :value="kpis.conNc"
                :total="kpis.total"
                tone="red"
            />
            <KpiCard
                label="MP conforme"
                :value="kpis.conformidadMp"
                :total="kpis.total"
                tone="green"
            />
        </div>

        <!-- Conformidades por categoría -->
        <div class="grid gap-4 md:grid-cols-3">
            <ChartCard
                title="Conformidad de MP"
                description="Producto entrante"
                :empty="!tieneDatos"
            >
                <DonutChart :data="conformidadMpSlices" value-suffix="recepciones" />
            </ChartCard>

            <ChartCard
                title="Conformidad de documentación"
                description="Documentos del lote"
                :empty="!tieneDatos"
            >
                <DonutChart :data="conformidadDocSlices" value-suffix="recepciones" />
            </ChartCard>

            <ChartCard
                title="Conformidad de vehículo"
                description="Unidad de transporte"
                :empty="!tieneDatos"
            >
                <DonutChart :data="conformidadVehSlices" value-suffix="recepciones" />
            </ChartCard>
        </div>

        <!-- Charts principales -->
        <div class="grid gap-4 lg:grid-cols-2">
            <ChartCard
                title="Recepciones por tipo de producto"
                description="Cantidad de recepciones según categoría"
                :empty="!tieneDatos || tipoProductoBars.length === 0"
            >
                <BarChart :data="tipoProductoBars" :height="260" value-suffix="recepciones" />
            </ChartCard>

            <ChartCard
                title="Por proveedor"
                description="Volumen recibido por cada proveedor"
                :empty="!tieneDatos || proveedorBars.length === 0"
            >
                <BarChart :data="proveedorBars" :height="260" value-suffix="recepciones" />
            </ChartCard>

            <ChartCard
                title="Por origen"
                description="Distribución de la procedencia"
                :empty="!tieneDatos || origenSlices.length === 0"
            >
                <DonutChart :data="origenSlices" value-suffix="recepciones" />
            </ChartCard>

            <ChartCard
                title="Tendencia semanal"
                description="Recepciones registradas por semana"
                :empty="!tieneDatos || porSemana.length === 0"
            >
                <LineChart :data="porSemana" :height="260" value-suffix="recepciones" />
            </ChartCard>
        </div>

        <!-- Acciones realizadas y productos afectados más frecuentes -->
        <div class="grid gap-4 lg:grid-cols-2">
            <ChartCard
                title="Acciones realizadas más frecuentes"
                description="Top 8 textos repetidos en el campo «Acción realizada»"
                :empty="!tieneDatos || topAcciones.length === 0"
            >
                <div class="space-y-2">
                    <div
                        v-for="(item, idx) in topAcciones"
                        :key="idx"
                        class="flex items-start justify-between gap-3 rounded-md border p-3"
                    >
                        <p class="flex-1 text-sm">{{ item.texto }}</p>
                        <Badge variant="secondary">
                            {{ item.total }}
                        </Badge>
                    </div>
                </div>
            </ChartCard>

            <ChartCard
                title="Productos afectados más frecuentes"
                description="Top 8 textos repetidos en el campo «Productos afectados»"
                :empty="!tieneDatos || topProductosAfectados.length === 0"
            >
                <div class="space-y-2">
                    <div
                        v-for="(item, idx) in topProductosAfectados"
                        :key="idx"
                        class="flex items-start justify-between gap-3 rounded-md border p-3"
                    >
                        <p class="flex-1 text-sm">{{ item.texto }}</p>
                        <Badge variant="secondary">
                            {{ item.total }}
                        </Badge>
                    </div>
                </div>
            </ChartCard>
        </div>

        <!-- Últimas no conformidades -->
        <ChartCard
            title="Últimas no conformidades"
            description="Hasta 10 recepciones más recientes con al menos una NC, para análisis cualitativo"
            :empty="!tieneDatos || ultimasNc.length === 0"
        >
            <div class="space-y-3">
                <article
                    v-for="nc in ultimasNc"
                    :key="nc.id"
                    class="rounded-lg border p-4"
                >
                    <header
                        class="flex flex-col gap-2 sm:flex-row sm:flex-wrap sm:items-center sm:justify-between"
                    >
                        <div>
                            <p class="text-xs text-muted-foreground">
                                {{ formatFecha(nc.fecha) }} ·
                                {{ nc.tipoProducto ?? '—' }} ·
                                {{ nc.proveedor ?? '—' }} ·
                                {{ nc.origen ?? '—' }}
                            </p>
                            <p class="text-xs uppercase text-muted-foreground mt-1">
                                NC en:
                            </p>
                        </div>
                        <div class="flex flex-wrap gap-1">
                            <Badge
                                v-for="cat in nc.ncEn"
                                :key="cat"
                                variant="outline"
                                class="border-red-500 bg-red-50 text-red-700 dark:bg-red-950 dark:text-red-400"
                            >
                                {{ cat }}
                            </Badge>
                        </div>
                    </header>

                    <div class="mt-3 grid gap-3 text-sm md:grid-cols-3">
                        <div>
                            <p class="text-xs uppercase text-muted-foreground">
                                Causa / Observación
                            </p>
                            <p class="whitespace-pre-wrap">{{ nc.causa }}</p>
                        </div>
                        <div>
                            <p class="text-xs uppercase text-muted-foreground">
                                Productos afectados
                            </p>
                            <p class="whitespace-pre-wrap">
                                {{ nc.productos }}
                            </p>
                        </div>
                        <div>
                            <p class="text-xs uppercase text-muted-foreground">
                                Acción realizada
                            </p>
                            <p class="whitespace-pre-wrap">{{ nc.accion }}</p>
                        </div>
                    </div>
                </article>
            </div>
        </ChartCard>

        <ExportarRangoFechasModal
            :open="exportarOpen"
            titulo="Exportar materia prima a Excel"
            :desde="filtros.desde"
            :hasta="filtros.hasta"
            :export-url-fn="(query) => exportMethod({ query }).url"
            @update:open="exportarOpen = $event"
        />
    </div>
</template>
