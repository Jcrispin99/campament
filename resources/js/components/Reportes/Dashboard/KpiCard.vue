<script setup lang="ts">
import { computed } from 'vue';

type Tone = 'neutral' | 'red' | 'green' | 'amber';

const props = defineProps<{
    label: string;
    value: number;
    total?: number;
    tone?: Tone;
}>();

const toneClass = computed(() => {
    switch (props.tone ?? 'neutral') {
        case 'red':
            return 'border-red-200 bg-red-50 dark:border-red-900/40 dark:bg-red-950/30';
        case 'green':
            return 'border-emerald-200 bg-emerald-50 dark:border-emerald-900/40 dark:bg-emerald-950/30';
        case 'amber':
            return 'border-amber-200 bg-amber-50 dark:border-amber-900/40 dark:bg-amber-950/30';
        default:
            return 'border-border bg-card';
    }
});

const numberToneClass = computed(() => {
    switch (props.tone ?? 'neutral') {
        case 'red':
            return 'text-red-700 dark:text-red-300';
        case 'green':
            return 'text-emerald-700 dark:text-emerald-300';
        case 'amber':
            return 'text-amber-700 dark:text-amber-300';
        default:
            return 'text-foreground';
    }
});

const pct = computed(() => {
    if (!props.total || props.total === 0) return null;
    return Math.round((props.value / props.total) * 100);
});
</script>

<template>
    <div :class="['rounded-xl border p-4 sm:p-5', toneClass]">
        <p class="text-sm text-muted-foreground">{{ label }}</p>
        <div class="mt-2 flex items-baseline gap-2">
            <p
                :class="[
                    'text-3xl font-semibold tracking-tight tabular-nums',
                    numberToneClass,
                ]"
            >
                {{ value.toLocaleString('es-PE') }}
            </p>
            <p v-if="pct !== null" class="text-sm text-muted-foreground">
                ({{ pct }}%)
            </p>
        </div>
    </div>
</template>
