<script setup lang="ts">
import LogoMark from '@/components/brand/LogoMark.vue';
import type { PublicPageProps } from '@/types/site';
import { Head, Link } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

const props = defineProps<PublicPageProps>();
const menuOpen = ref(false);
const localeLabels = { en: 'EN', sr: 'SR', de: 'DE' };
const routeUrl = (key: string) =>
    `/${props.locale}${props.routes[props.locale][key] ? `/${props.routes[props.locale][key]}` : ''}`;
const navigation = computed(() => ['services', 'ai', 'cases', 'about', 'blog']);
</script>

<template>
    <div class="site-shell">
        <Head>
            <title>{{ seo.title }}</title>
            <meta name="description" :content="seo.description" />
            <link rel="canonical" :href="seo.canonical" />
            <link
                v-for="(href, code) in seo.alternates"
                :key="code"
                rel="alternate"
                :hreflang="code"
                :href="href"
            />
            <link rel="alternate" hreflang="x-default" :href="seo.x_default" />
            <meta property="og:title" :content="seo.title" />
            <meta property="og:description" :content="seo.description" />
            <meta property="og:url" :content="seo.canonical" />
            <meta property="og:type" content="website" />
            <component :is="'script'" type="application/ld+json">{{
                JSON.stringify({
                    '@context': 'https://schema.org',
                    '@type': 'Organization',
                    name: 'SEO Impact',
                    url: (seo.alternates.en ?? seo.x_default).replace(
                        /\/(services|ai-solutions|case-studies|about|contact|blog)$/,
                        '',
                    ),
                    email: 'hello@seoimpact.rs',
                    areaServed: ['RS', 'DE', 'EU'],
                })
            }}</component>
        </Head>

        <header class="site-header">
            <Link :href="routeUrl('home')" class="site-logo">
                <LogoMark wordmark="AiON" background="dark" />
            </Link>
            <nav class="desktop-nav" aria-label="Primary navigation">
                <Link
                    v-for="key in navigation"
                    :key="key"
                    :href="routeUrl(key)"
                    :class="{ active: routeKey === key }"
                    >{{ copy.nav[key] }}</Link
                >
            </nav>
            <div class="header-actions">
                <div class="language-switcher" aria-label="Language">
                    <a
                        v-for="(href, code) in seo.alternates"
                        :key="code"
                        :href="href"
                        :class="{ active: code === locale }"
                        :hreflang="code"
                        >{{ localeLabels[code] }}</a
                    >
                </div>
                <Link class="button button--small" :href="routeUrl('contact')"
                    >{{ copy.nav.contact }} <span>↗</span></Link
                >
                <button
                    class="menu-toggle"
                    type="button"
                    :aria-expanded="menuOpen"
                    aria-label="Toggle menu"
                    @click="menuOpen = !menuOpen"
                >
                    <span></span><span></span>
                </button>
            </div>
            <nav v-if="menuOpen" class="mobile-nav">
                <Link
                    v-for="key in navigation"
                    :key="key"
                    :href="routeUrl(key)"
                    @click="menuOpen = false"
                    >{{ copy.nav[key] }}</Link
                >
                <Link :href="routeUrl('contact')" @click="menuOpen = false">{{
                    copy.nav.contact
                }}</Link>
            </nav>
        </header>

        <main><slot /></main>

        <footer class="site-footer">
            <div>
                <LogoMark />
                <p>Search intelligence. AI leverage. Measurable growth.</p>
            </div>
            <div class="footer-links">
                <Link
                    v-for="key in navigation"
                    :key="key"
                    :href="routeUrl(key)"
                    >{{ copy.nav[key] }}</Link
                >
            </div>
            <p class="footer-meta">
                © {{ new Date().getFullYear() }} SEO Impact · Belgrade, Serbia
            </p>
        </footer>
    </div>
</template>
