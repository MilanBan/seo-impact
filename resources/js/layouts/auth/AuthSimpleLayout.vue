<script setup lang="ts">
import LogoMark from '@/components/brand/LogoMark.vue';
import { home } from '@/routes/site/en';
import { Link } from '@inertiajs/vue3';
import { ArrowLeft, ShieldCheck } from '@lucide/vue';

defineProps<{
    title?: string;
    description?: string;
}>();
</script>

<template>
    <div
        class="relative flex min-h-svh items-center justify-center overflow-hidden bg-[#09090b] px-5 py-8 text-white [color-scheme:dark] sm:px-8"
    >
        <main class="relative w-full max-w-lg">
            <div
                class="flex flex-col gap-7 rounded-xl border border-white/15 bg-[#18181b] px-6 py-8 shadow-[0_1px_2px_rgba(0,0,0,0.45)] sm:px-12 sm:py-12"
            >
                <div class="flex flex-col items-center gap-3 text-center">
                    <Link
                        :href="home()"
                        class="rounded-md focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-blue-600"
                        aria-label="AION home"
                    >
                        <LogoMark />
                    </Link>
                    <div>
                        <p
                            class="mb-1.5 text-xs font-semibold tracking-[0.12em] text-blue-400 uppercase"
                        >
                            Client portal
                        </p>
                        <h1
                            class="text-2xl font-semibold tracking-[-0.025em] text-white"
                        >
                            {{ title }}
                        </h1>
                        <p class="mt-1.5 text-sm leading-5 text-zinc-300">
                            {{ description }}
                        </p>
                    </div>
                </div>

                <slot />

                <div
                    class="flex flex-col gap-3 border-t border-white/15 pt-5 text-xs sm:flex-row sm:items-center sm:justify-between"
                >
                    <Link
                        :href="home()"
                        class="inline-flex min-h-10 items-center justify-center gap-1.5 rounded-md font-medium text-zinc-300 transition-colors duration-200 hover:text-blue-300 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-500 sm:justify-start"
                    >
                        <ArrowLeft class="size-3.5" aria-hidden="true" />
                        Back to website
                    </Link>
                    <span
                        class="inline-flex min-h-10 items-center justify-center gap-1.5 font-medium text-zinc-300 sm:justify-end"
                    >
                        <ShieldCheck
                            class="size-4 text-blue-400"
                            aria-hidden="true"
                        />
                        Secure session
                    </span>
                </div>
            </div>
        </main>
    </div>
</template>
