<script setup lang="ts">
import { computed } from 'vue';
import { Badge } from '@/components/ui/badge';
import type { Criticidad } from '@/types/reportes';

const props = defineProps<{ value: Criticidad }>();

const variant = computed<'secondary' | 'outline' | 'destructive'>(() => {
    switch (props.value) {
        case 'LEVE':
            return 'secondary';
        case 'MODERADO':
            return 'outline';
        case 'CRITICO':
            return 'destructive';
    }
});

const label = computed(() => {
    switch (props.value) {
        case 'LEVE':
            return 'Leve';
        case 'MODERADO':
            return 'Moderado';
        case 'CRITICO':
            return 'Crítico';
    }
});

const extraClass = computed(() =>
    props.value === 'MODERADO'
        ? 'border-amber-500 text-amber-700 dark:text-amber-400'
        : '',
);
</script>

<template>
    <Badge :variant="variant" :class="extraClass">
        {{ label }}
    </Badge>
</template>
