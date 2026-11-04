<script setup lang="ts">
import HeroGlobeWorld from '@/components/hero/HeroGlobeWorld.vue';
import { TresCanvas } from '@tresjs/core';
</script>

<template>
    <TresCanvas
        :dpr="[1, 1.5]"
        alpha
        antialias
        power-preference="high-performance"
    >
        <HeroGlobeWorld />
    </TresCanvas>
</template>
