<script setup lang="ts">
import type { Locale } from '@/types/site';
import { computed, onBeforeUnmount, onMounted, ref } from 'vue';

const props = defineProps<{ locale: Locale }>();
const video = ref<HTMLVideoElement | null>(null);
const reducedMotion = ref(true);
const videoReady = ref(false);
const videoFailed = ref(false);
const paused = ref(false);
let motionQuery: MediaQueryList | undefined;
const labels = {
    en: { pause: 'Pause animation', play: 'Play animation' },
    sr: { pause: 'Pauziraj animaciju', play: 'Pokreni animaciju' },
    de: { pause: 'Animation pausieren', play: 'Animation abspielen' },
};
const toggleLabel = computed(
    () => labels[props.locale][paused.value ? 'play' : 'pause'],
);

function updateMotionPreference() {
    reducedMotion.value = motionQuery?.matches ?? true;
    if (reducedMotion.value) {
        videoReady.value = false;
    }
}

async function togglePlayback() {
    if (!video.value) return;
    if (video.value.paused) {
        try {
            await video.value.play();
        } catch {
            videoFailed.value = true;
            videoReady.value = false;
        }
    } else {
        video.value.pause();
    }
}

onMounted(() => {
    motionQuery = window.matchMedia('(prefers-reduced-motion: reduce)');
    updateMotionPreference();
    motionQuery.addEventListener('change', updateMotionPreference);
});
onBeforeUnmount(() => {
    motionQuery?.removeEventListener('change', updateMotionPreference);
});
</script>

<template>
    <div>
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
                />
            </picture>

            <video
                v-if="!reducedMotion && !videoFailed"
                ref="video"
                class="hero-video"
                :class="{ 'hero-video--ready': videoReady }"
                src="/videos/brand/hero-globe-loop-0.5x.mp4"
                width="1280"
                height="720"
                autoplay
                muted
                loop
                playsinline
                preload="metadata"
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

            <template v-if="!videoReady">
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
            v-if="videoReady && !reducedMotion"
            type="button"
            class="hero-video-toggle"
            @click="togglePlayback"
        >
            <span aria-hidden="true">{{ paused ? '▶' : 'Ⅱ' }}</span>
            {{ toggleLabel }}
        </button>
    </div>
</template>
