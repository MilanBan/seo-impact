<script setup lang="ts">
import type { Locale } from '@/types/site';
import {
    computed,
    nextTick,
    onBeforeUnmount,
    onMounted,
    ref,
    watch,
} from 'vue';

const props = defineProps<{ locale: Locale }>();
const root = ref<HTMLElement | null>(null);
const video = ref<HTMLVideoElement | null>(null);
const reducedMotion = ref(true);
const mobileViewport = ref(true);
const dataSaver = ref(false);
const inViewport = ref(true);
const documentVisible = ref(true);
const videoReady = ref(false);
const videoFailed = ref(false);
const paused = ref(false);
const userPaused = ref(false);
let motionQuery: MediaQueryList | undefined;
let mobileQuery: MediaQueryList | undefined;
let visibilityObserver: IntersectionObserver | undefined;
let connection: NetworkInformationWithSaveData | undefined;

type NetworkInformationWithSaveData = EventTarget & {
    saveData?: boolean;
};
const labels = {
    en: { pause: 'Pause animation', play: 'Play animation' },
    sr: { pause: 'Pauziraj animaciju', play: 'Pokreni animaciju' },
    de: { pause: 'Animation pausieren', play: 'Animation abspielen' },
};
const toggleLabel = computed(
    () => labels[props.locale][paused.value ? 'play' : 'pause'],
);
const videoAllowed = computed(
    () =>
        !reducedMotion.value &&
        !mobileViewport.value &&
        !dataSaver.value &&
        !videoFailed.value,
);
const shouldPlay = computed(
    () =>
        videoAllowed.value &&
        inViewport.value &&
        documentVisible.value &&
        !userPaused.value,
);
const showLoadingEffects = computed(
    () => videoAllowed.value && !videoReady.value,
);

function updateMediaPreferences() {
    reducedMotion.value = motionQuery?.matches ?? true;
    mobileViewport.value = mobileQuery?.matches ?? true;
    dataSaver.value = connection?.saveData ?? false;

    if (!videoAllowed.value) {
        videoReady.value = false;
    }
}

function updateDocumentVisibility() {
    documentVisible.value = document.visibilityState === 'visible';
}

function togglePlayback() {
    userPaused.value = !userPaused.value;
}

onMounted(() => {
    motionQuery = window.matchMedia('(prefers-reduced-motion: reduce)');
    mobileQuery = window.matchMedia('(max-width: 760px)');
    connection = (
        navigator as Navigator & {
            connection?: NetworkInformationWithSaveData;
        }
    ).connection;

    updateMediaPreferences();
    updateDocumentVisibility();
    motionQuery.addEventListener('change', updateMediaPreferences);
    mobileQuery.addEventListener('change', updateMediaPreferences);
    connection?.addEventListener('change', updateMediaPreferences);
    document.addEventListener('visibilitychange', updateDocumentVisibility);

    visibilityObserver = new IntersectionObserver(
        ([entry]) => {
            inViewport.value = entry?.isIntersecting ?? true;
        },
        { threshold: 0.08 },
    );
    if (root.value) visibilityObserver.observe(root.value);
});
onBeforeUnmount(() => {
    motionQuery?.removeEventListener('change', updateMediaPreferences);
    mobileQuery?.removeEventListener('change', updateMediaPreferences);
    connection?.removeEventListener('change', updateMediaPreferences);
    document.removeEventListener('visibilitychange', updateDocumentVisibility);
    visibilityObserver?.disconnect();
});

watch(
    [video, shouldPlay],
    async ([element, play]) => {
        await nextTick();
        if (!element) return;

        if (!play) {
            element.pause();
            return;
        }

        try {
            await element.play();
        } catch {
            videoFailed.value = true;
            videoReady.value = false;
        }
    },
    { flush: 'post' },
);
</script>

<template>
    <div ref="root" class="hero-media">
        <div class="hero-visual" aria-hidden="true">
            <picture class="hero-poster">
                <source
                    srcset="/images/brand/hero-globe-v3.avif"
                    type="image/avif"
                />
                <source
                    srcset="/images/brand/hero-globe-v3.webp"
                    type="image/webp"
                />
                <img
                    src="/images/brand/hero-globe-v3.png"
                    width="1672"
                    height="941"
                    alt=""
                    fetchpriority="high"
                    decoding="sync"
                />
            </picture>

            <video
                v-if="videoAllowed"
                ref="video"
                class="hero-video"
                :class="{ 'hero-video--ready': videoReady }"
                src="/videos/brand/hero-globe-loop-optimized-900p.mp4"
                width="1600"
                height="900"
                muted
                loop
                playsinline
                preload="none"
                tabindex="-1"
                @playing="
                    videoReady = true;
                    paused = false;
                "
                @pause="paused = true"
                @error="
                    videoFailed = true;
                    videoReady = false;
                "
            ></video>

            <template v-if="showLoadingEffects">
                <div class="hero-atmosphere"></div>
                <svg
                    class="hero-light-trails"
                    viewBox="0 0 1672 941"
                    preserveAspectRatio="xMidYMid slice"
                >
                    <defs>
                        <linearGradient
                            id="trail-blue"
                            x1="0"
                            y1="0"
                            x2="1"
                            y2="0"
                        >
                            <stop
                                offset="0"
                                stop-color="#3293ff"
                                stop-opacity="0"
                            />
                            <stop
                                offset="0.5"
                                stop-color="#82d5ff"
                                stop-opacity="0.55"
                            />
                            <stop
                                offset="1"
                                stop-color="#397cff"
                                stop-opacity="0"
                            />
                        </linearGradient>
                    </defs>
                    <path d="M820 650 C 1010 505, 1280 475, 1570 565" />
                    <path d="M920 770 C 1120 650, 1380 640, 1640 715" />
                </svg>
                <div class="hero-data-streams"></div>
                <div class="hero-beam"></div>
            </template>
        </div>
        <button
            v-if="videoReady && videoAllowed"
            type="button"
            class="hero-video-toggle"
            @click="togglePlayback"
        >
            <span aria-hidden="true">{{ paused ? '▶' : 'Ⅱ' }}</span>
            {{ toggleLabel }}
        </button>
    </div>
</template>
