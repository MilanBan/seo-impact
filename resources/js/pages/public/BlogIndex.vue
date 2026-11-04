<script setup lang="ts">
import PublicLayout from '@/layouts/PublicLayout.vue';
import type { PublicPageProps } from '@/types/site';
import { Link } from '@inertiajs/vue3';

const props = defineProps<
    PublicPageProps & {
        page: { title: string; headline: string; summary: string };
        posts: Array<{
            slug: string;
            title: string;
            excerpt: string;
            published_at: string;
            author: string;
        }>;
    }
>();
const blogBase = `/${props.locale}/${props.routes[props.locale].blog}`;
</script>

<template>
    <PublicLayout v-bind="props"
        ><section class="inner-hero">
            <div class="inner-hero__glow"></div>
            <p class="eyebrow"><span></span>{{ page.title }}</p>
            <h1>{{ page.headline }}</h1>
            <p>{{ page.summary }}</p>
        </section>
        <section class="section blog-grid">
            <article
                v-for="post in posts"
                :key="post.slug"
                class="glass-card post-card"
            >
                <div class="post-art"><span>↗</span></div>
                <small>{{ post.published_at }} · {{ post.author }}</small>
                <h2>{{ post.title }}</h2>
                <p>{{ post.excerpt }}</p>
                <Link :href="`${blogBase}/${post.slug}`"
                    >Read insight <span>↗</span></Link
                >
            </article>
        </section></PublicLayout
    >
</template>
