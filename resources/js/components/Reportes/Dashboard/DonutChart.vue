<script setup lang="ts">
import { Donut } from '@unovis/ts';
import { VisDonut, VisSingleContainer, VisTooltip } from '@unovis/vue';
import { computed } from 'vue';

type Slice = { label: string; value: number; color: string };

const props = withDefaults(
    defineProps<{
        data: Slice[];
        centralLabel?: string;
        height?: number;
        valueSuffix?: string;
    }>(),
    {
        valueSuffix: 'registros',
    },
);

const total = computed(() =>
    props.data.reduce((acc, s) => acc + s.value, 0),
);

const central = computed(() => props.centralLabel ?? String(total.value));

const colorAccessor = (_d: Slice, i: number): string =>
    props.data[i]?.color ?? 'var(--primary)';

const valueAccessor = (d: Slice): number => d.value;

const tooltipTrigger = {
    [Donut.selectors.segment]: (d: { data: Slice }): string => {
        const slice = d.data;
        const pct =
            total.value > 0
                ? Math.round((slice.value / total.value) * 100)
                : 0;
        return `<div class="rounded-md bg-popover px-2 py-1 text-popover-foreground shadow-md">
            <div class="font-medium">${slice.label}</div>
            <div class="text-xs text-muted-foreground">${slice.value} ${props.valueSuffix} (${pct}%)</div>
        </div>`;
    },
};
</script>

<template>
    <div class="space-y-3">
        <VisSingleContainer :data="data" :height="height ?? 240">
            <VisDonut
                :value="valueAccessor"
                :color="colorAccessor"
                :central-label="central"
                :arc-width="32"
                :show-empty-segments="false"
            />
            <VisTooltip :triggers="tooltipTrigger" />
        </VisSingleContainer>

        <ul
            class="grid grid-cols-1 gap-x-4 gap-y-1 sm:grid-cols-2"
        >
            <li
                v-for="(slice, idx) in data"
                :key="idx"
                class="flex items-center gap-2 text-sm"
            >
                <span
                    class="h-3 w-3 shrink-0 rounded-sm"
                    :style="{ backgroundColor: slice.color }"
                />
                <span class="min-w-0 flex-1 truncate" :title="slice.label">
                    {{ slice.label }}
                </span>
                <span class="text-muted-foreground tabular-nums">
                    {{ slice.value }}
                </span>
            </li>
        </ul>
    </div>
</template>
