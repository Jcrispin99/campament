<script setup lang="ts">
import { computed } from 'vue';

type Item = {
    label: string;
    sublabel?: string;
    value: number;
    color?: string;
};

const props = defineProps<{
    items: Item[];
}>();

const max = computed(() =>
    Math.max(1, ...props.items.map((i) => i.value)),
);
</script>

<template>
    <ul class="space-y-3">
        <li
            v-for="(item, idx) in items"
            :key="idx"
            class="grid grid-cols-[minmax(0,1fr)_auto] gap-x-3 gap-y-1"
        >
            <div class="min-w-0">
                <p class="truncate text-sm font-medium" :title="item.label">
                    {{ item.label }}
                </p>
                <p
                    v-if="item.sublabel"
                    class="text-xs text-muted-foreground"
                >
                    {{ item.sublabel }}
                </p>
            </div>
            <p class="text-sm font-semibold tabular-nums">
                {{ item.value }}
            </p>
            <div
                class="col-span-2 h-2 overflow-hidden rounded-full bg-muted"
            >
                <div
                    class="h-full rounded-full transition-all"
                    :style="{
                        width: `${(item.value / max) * 100}%`,
                        backgroundColor: item.color ?? 'var(--primary)',
                    }"
                />
            </div>
        </li>
    </ul>
</template>
