<script setup lang="ts">
import {
    VisAxis,
    VisCrosshair,
    VisLine,
    VisScatter,
    VisTooltip,
    VisXYContainer,
} from '@unovis/vue';

type Point = { semana: number; total: number };

const props = withDefaults(
    defineProps<{
        data: Point[];
        height?: number;
        valueSuffix?: string;
    }>(),
    {
        valueSuffix: 'registros',
    },
);

const xAccessor = (d: Point): number => d.semana;
const yAccessor = (d: Point): number => d.total;

const tooltipTrigger = {
    '.vis-scatter circle': (d: Point) =>
        `<div class="rounded-md bg-popover px-2 py-1 text-popover-foreground shadow-md">
            <div class="font-medium">Semana ${d.semana}</div>
            <div class="text-xs text-muted-foreground">${d.total} ${props.valueSuffix}</div>
        </div>`,
};
</script>

<template>
    <VisXYContainer :data="data" :height="height ?? 280" :margin="{ top: 12, right: 12, bottom: 32, left: 36 }">
        <VisLine
            :x="xAccessor"
            :y="yAccessor"
            color="var(--primary)"
            :line-width="2"
        />
        <VisScatter
            :x="xAccessor"
            :y="yAccessor"
            color="var(--primary)"
            :size="6"
        />
        <VisAxis type="x" label="Semana" :tick-format="(v: number) => Math.round(v).toString()" />
        <VisAxis type="y" :label="props.valueSuffix.charAt(0).toUpperCase() + props.valueSuffix.slice(1)" :tick-format="(v: number) => Math.round(v).toString()" />
        <VisCrosshair />
        <VisTooltip :triggers="tooltipTrigger" />
    </VisXYContainer>
</template>
