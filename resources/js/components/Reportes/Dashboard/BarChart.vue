<script setup lang="ts">
import {
    VisAxis,
    VisGroupedBar,
    VisTooltip,
    VisXYContainer,
} from '@unovis/vue';

type Bar = { label: string; value: number; color: string };

const props = defineProps<{
    data: Bar[];
    height?: number;
    yLabel?: string;
}>();

const xAccessor = (_d: Bar, i: number): number => i;
const yAccessor = (d: Bar): number => d.value;
const colorAccessor = (_d: Bar, i: number): string =>
    props.data[i]?.color ?? 'var(--primary)';

const xTickFormat = (_v: unknown, i: number): string =>
    props.data[i]?.label ?? '';

const tooltipTrigger = {
    'g.vis-grouped-bar-group rect': (d: Bar) =>
        `<div class="rounded-md bg-popover px-2 py-1 text-popover-foreground shadow-md">
            <div class="font-medium">${d.label}</div>
            <div class="text-xs text-muted-foreground">${d.value} reportes</div>
        </div>`,
};
</script>

<template>
    <VisXYContainer :data="data" :height="height ?? 280" :margin="{ top: 12, right: 12, bottom: 32, left: 36 }">
        <VisGroupedBar
            :x="xAccessor"
            :y="yAccessor"
            :color="colorAccessor"
            :bar-padding="0.3"
            :rounded-corners="6"
        />
        <VisAxis
            type="x"
            :num-ticks="data.length"
            :tick-format="xTickFormat"
        />
        <VisAxis type="y" :label="yLabel ?? 'Cantidad'" :tick-format="(v: number) => Math.round(v).toString()" />
        <VisTooltip :triggers="tooltipTrigger" />
    </VisXYContainer>
</template>
