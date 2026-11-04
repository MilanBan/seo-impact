<script setup lang="ts">
withDefaults(
    defineProps<{
        compact?: boolean;
        background?: 'light' | 'dark';
        wordmark?: 'AION' | 'AiON';
    }>(),
    { compact: false, background: 'dark', wordmark: 'AION' },
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
        :aria-label="wordmark"
        xmlns="http://www.w3.org/2000/svg"
    >
        <image
            :href="
                !compact && wordmark === 'AiON'
                    ? `/images/logo/aion/transparentne-verzije/aiON-reference-za-${background === 'light' ? 'svetlu' : 'tamnu'}-pozadinu.png`
                    : `/images/logo/aion/aion-za-${background === 'light' ? 'svetlu' : 'tamnu'}-pozadinu.png`
            "
            width="2172"
            height="724"
        />
    </svg>
</template>
