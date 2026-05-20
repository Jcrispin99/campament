<script setup lang="ts">
import { computed } from 'vue';
import { Badge } from '@/components/ui/badge';
import type { EstatusGramaje } from '@/types/gramajes';

const props = defineProps<{ value: EstatusGramaje }>();

const label = computed(() =>
    props.value === 'CONFORME' ? 'Conforme' : 'Inconforme',
);

const extraClass = computed(() =>
    props.value === 'CONFORME'
        ? 'border-emerald-500 bg-emerald-50 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-400'
        : 'border-red-500 bg-red-50 text-red-700 dark:bg-red-950 dark:text-red-400',
);
</script>

<template>
    <Badge variant="outline" :class="extraClass">
        {{ label }}
    </Badge>
</template>
