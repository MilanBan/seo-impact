<script setup lang="ts">
withDefaults(
    defineProps<{
        compact?: boolean;
        background?: 'light' | 'dark';
    }>(),
    { compact: false, background: 'dark' },
);
</script>

<template>
    <svg
        class="brand"
        :class="{ 'brand--compact': compact }"
        :viewBox="compact ? '485 195 325 295' : '485 195 1210 295'"
        :width="compact ? 36 : 164"
        height="40"
        role="img"
        aria-label="AION"
        xmlns="http://www.w3.org/2000/svg"
    >
        <image
            :href="`/images/logo/aion/aion-za-${background === 'light' ? 'svetlu' : 'tamnu'}-pozadinu.png`"
            width="2172"
            height="724"
        />
    </svg>
</template>
