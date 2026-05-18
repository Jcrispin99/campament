<script setup lang="ts">
defineProps<{
    title: string;
    description?: string;
    empty?: boolean;
}>();
</script>

<template>
    <section class="rounded-xl border bg-card p-4 sm:p-5">
        <header class="mb-4">
            <h3 class="text-base font-semibold">{{ title }}</h3>
            <p v-if="description" class="text-xs text-muted-foreground">
                {{ description }}
            </p>
        </header>
        <div
            v-if="empty"
            class="flex h-[280px] items-center justify-center text-sm text-muted-foreground"
        >
            Sin datos para los filtros seleccionados.
        </div>
        <slot v-else />
    </section>
</template>
