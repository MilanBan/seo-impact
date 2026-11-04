<script setup lang="ts">
import type { HTMLAttributes } from 'vue';
import LogoMark from '@/components/brand/LogoMark.vue';

defineProps<{
    className?: HTMLAttributes['class'];
    background?: 'light' | 'dark';
}>();
</script>

<template>
    <LogoMark compact :background="background" :class="className" />
</template>
