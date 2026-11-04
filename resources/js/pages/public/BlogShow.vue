<script setup lang="ts">
import PublicLayout from '@/layouts/PublicLayout.vue';
import type { PublicPageProps } from '@/types/site';

defineProps<
    PublicPageProps & {
        post: {
            title: string;
            excerpt: string;
            body: string;
            published_at: string;
            author: string;
        };
    }
>();
</script>

<template>
    <PublicLayout v-bind="$props"
        ><article class="article">
            <header>
                <p class="eyebrow">
                    <span></span>{{ post.published_at }} · {{ post.author }}
                </p>
                <h1>{{ post.title }}</h1>
                <p>{{ post.excerpt }}</p>
            </header>
            <div class="article-body" v-html="post.body"></div></article
    ></PublicLayout>
</template>
