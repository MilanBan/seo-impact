<template>
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

        <div class="hero-atmosphere"></div>
        <svg
            class="hero-light-trails"
            viewBox="0 0 1672 941"
            preserveAspectRatio="xMidYMid slice"
        >
            <defs>
                <linearGradient id="trail-blue" x1="0" y1="0" x2="1" y2="0">
                    <stop offset="0" stop-color="#3293ff" stop-opacity="0" />
                    <stop
                        offset="0.5"
                        stop-color="#82d5ff"
                        stop-opacity="0.55"
                    />
                    <stop offset="1" stop-color="#397cff" stop-opacity="0" />
                </linearGradient>
            </defs>
            <path d="M820 650 C 1010 505, 1280 475, 1570 565" />
            <path d="M920 770 C 1120 650, 1380 640, 1640 715" />
        </svg>
        <div class="hero-data-streams"></div>
        <div class="hero-beam"></div>
    </div>
</template>
