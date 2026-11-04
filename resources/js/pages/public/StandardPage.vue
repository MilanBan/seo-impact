<script setup lang="ts">
import LeadForm from '@/components/public/LeadForm.vue';
import PublicLayout from '@/layouts/PublicLayout.vue';
import type { CaseStudy, PublicPageProps, Service } from '@/types/site';
import { Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps<
    PublicPageProps & {
        page: { title: string; headline: string; summary: string };
        services: Service[];
        caseStudies: CaseStudy[];
    }
>();
const contactUrl = `/${props.locale}/${props.routes[props.locale].contact}`;
const flash = computed(
    () => (usePage().props.flash as { success?: string } | undefined)?.success,
);
</script>

<template>
    <PublicLayout v-bind="props">
        <section class="inner-hero">
            <div class="inner-hero__glow"></div>
            <p class="eyebrow"><span></span>{{ page.title }}</p>
            <h1>{{ page.headline }}</h1>
            <p>{{ page.summary }}</p>
        </section>

        <section
            v-if="routeKey === 'services' || routeKey === 'ai'"
            class="section content-section"
        >
            <div class="service-grid">
                <article
                    v-for="(service, index) in services.filter(
                        (item) =>
                            routeKey === 'services' ||
                            ['ai-search', 'automation'].includes(item.key),
                    )"
                    :key="service.key"
                    class="glass-card service-card"
                >
                    <span class="service-number">0{{ index + 1 }}</span>
                    <div class="service-icon">✦</div>
                    <h2>{{ service.title }}</h2>
                    <p>{{ service.summary }}</p>
                </article>
            </div>
        </section>
        <section
            v-else-if="routeKey === 'cases'"
            class="section content-section"
        >
            <article
                v-for="caseStudy in caseStudies"
                :key="caseStudy.title"
                class="case-panel case-panel--large"
            >
                <div>
                    <small>{{ caseStudy.industry }}</small>
                    <h2>{{ caseStudy.title }}</h2>
                    <p>{{ caseStudy.summary }}</p>
                </div>
                <div v-for="metric in caseStudy.metrics" :key="metric.value">
                    <strong>{{ metric.value }}</strong
                    ><span>{{ metric.label }}</span>
                </div>
            </article>
        </section>
        <section v-else-if="routeKey === 'about'" class="section split-story">
            <div class="story-visual">
                <span>SEO</span><span>AI</span><span>IMPACT</span>
            </div>
            <div>
                <p class="eyebrow"><span></span>HOW WE WORK</p>
                <h2>Clarity before complexity.</h2>
                <p>
                    We work close to your real business constraints: what
                    customers need, what teams can implement and what evidence
                    says will move growth. Strategy and execution stay connected
                    from first diagnosis to final measurement.
                </p>
                <ul>
                    <li>Senior specialists stay involved.</li>
                    <li>Commercial outcomes guide priorities.</li>
                    <li>Knowledge remains inside your team.</li>
                </ul>
            </div>
        </section>
        <section
            v-else-if="routeKey === 'contact'"
            class="section contact-section"
        >
            <div>
                <p class="eyebrow"><span></span>START A CONVERSATION</p>
                <h2>{{ copy.contact_title }}</h2>
                <p>{{ copy.contact_intro }}</p>
                <a href="mailto:hello@seoimpact.rs">hello@seoimpact.rs</a>
            </div>
            <div>
                <div v-if="flash" class="form-success">{{ flash }}</div>
                <LeadForm :locale="locale" :action="contactUrl" :copy="copy" />
            </div>
        </section>
        <section v-else class="section content-section">
            <div class="section-heading">
                <h2>{{ page.headline }}</h2>
                <p>{{ page.summary }}</p>
                <Link class="button" :href="contactUrl"
                    >{{ copy.primary_cta }} <span>↗</span></Link
                >
            </div>
        </section>
    </PublicLayout>
</template>
