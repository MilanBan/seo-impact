<script setup lang="ts">
import LogoMark from '@/components/brand/LogoMark.vue';
</script>

<template>
    <div class="flex items-center">
        <LogoMark class="group-data-[collapsible=icon]:hidden" />
        <LogoMark
            compact
            class="hidden size-8 group-data-[collapsible=icon]:block"
        />
    </div>
</template>
