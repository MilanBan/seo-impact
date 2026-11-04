<script setup lang="ts">
import LogoMark from '@/components/brand/LogoMark.vue';
import HeroGlobe from '@/components/hero/HeroGlobe.vue';
import PublicLayout from '@/layouts/PublicLayout.vue';
import type { CaseStudy, PublicPageProps, Service } from '@/types/site';
import { Link } from '@inertiajs/vue3';

const props = defineProps<
    PublicPageProps & { services: Service[]; caseStudies: CaseStudy[] }
>();
const routeUrl = (key: string) =>
    `/${props.locale}${props.routes[props.locale][key] ? `/${props.routes[props.locale][key]}` : ''}`;
const stats = [
    {
        value: '328%',
        label:
            props.locale === 'de'
                ? 'Ø organisches Wachstum'
                : props.locale === 'sr'
                  ? 'prosečan organski rast'
                  : 'avg. organic growth',
    },
    {
        value: '4.8×',
        label:
            props.locale === 'de'
                ? 'mehr qualifizierte Leads'
                : props.locale === 'sr'
                  ? 'više kvalifikovanih upita'
                  : 'more qualified leads',
    },
    {
        value: '92%',
        label:
            props.locale === 'de'
                ? 'weniger Routinearbeit'
                : props.locale === 'sr'
                  ? 'manje rutinskog rada'
                  : 'less repetitive work',
    },
    {
        value: '12+',
        label:
            props.locale === 'de'
                ? 'Branchen'
                : props.locale === 'sr'
                  ? 'industrija'
                  : 'industries',
    },
];
</script>

<template>
    <PublicLayout v-bind="props">
        <section class="hero-section">
            <HeroGlobe />
            <div class="hero-grid"></div>
            <div class="hero-copy">
                <p class="eyebrow"><span></span>{{ copy.eyebrow }}</p>
                <h1>
                    <span>{{ copy.headline[0] }}</span
                    ><em>{{ copy.headline[1] }}</em>
                </h1>
                <p class="hero-summary">{{ copy.summary }}</p>
                <div class="hero-actions">
                    <Link class="button" :href="routeUrl('contact')"
                        >{{ copy.primary_cta }} <span>↗</span></Link
                    >
                    <Link class="text-link" :href="routeUrl('cases')"
                        ><i>▶</i>{{ copy.secondary_cta }}</Link
                    >
                </div>
                <div class="proof-row">
                    <span>◇ Transparent process</span
                    ><span>⌁ Measurable impact</span><span>◎ Senior team</span>
                </div>
            </div>
            <div class="hero-card-rig" aria-hidden="true">
                <div
                    class="floating-card-stage floating-card-stage--visibility"
                >
                    <div class="floating-card">
                        <small>AI SEARCH VISIBILITY</small>
                        <div class="visibility-sources">
                            <span>✦ ChatGPT</span>
                            <span>✧ Perplexity</span>
                            <span>◈ Bing AI</span>
                        </div>
                        <p>Your brand everywhere people ask.</p>
                    </div>
                </div>
                <div class="floating-card-stage floating-card-stage--growth">
                    <div class="floating-card">
                        <small>SEO GROWTH</small>
                        <svg class="growth-chart" viewBox="0 0 130 42">
                            <defs>
                                <linearGradient
                                    id="growth-fill"
                                    x1="0"
                                    y1="0"
                                    x2="0"
                                    y2="1"
                                >
                                    <stop
                                        offset="0"
                                        stop-color="#4b9cff"
                                        stop-opacity="0.4"
                                    />
                                    <stop
                                        offset="1"
                                        stop-color="#4b9cff"
                                        stop-opacity="0"
                                    />
                                </linearGradient>
                            </defs>
                            <path
                                class="growth-area"
                                d="M2 39 L22 34 L39 35 L56 27 L75 29 L95 17 L112 20 L128 5 L128 42 L2 42 Z"
                            />
                            <path
                                class="growth-line"
                                d="M2 39 L22 34 L39 35 L56 27 L75 29 L95 17 L112 20 L128 5"
                            />
                        </svg>
                        <b class="metric">+328%</b>
                        <span>organic traffic</span>
                    </div>
                </div>
                <div
                    class="floating-card-stage floating-card-stage--automations"
                >
                    <div class="floating-card">
                        <small>AI AUTOMATIONS</small>
                        <div class="automation-flow">
                            <i>⌘</i><em></em><i>✦</i><em></em><i>↗</i>
                        </div>
                        <span>Less manual work.</span>
                        <span>More growth.</span>
                    </div>
                </div>
                <div class="floating-card-stage floating-card-stage--software">
                    <div class="floating-card">
                        <small>CUSTOM AI SOFTWARE</small>
                        <div class="software-card-body">
                            <svg viewBox="0 0 42 42">
                                <path d="M21 3 36 12 21 21 6 12 21 3Z" />
                                <path d="M6 12v18l15 9V21L6 12Z" />
                                <path d="m36 12-15 9v18l15-9V12Z" />
                            </svg>
                            <b>Built for<br />your business.</b>
                        </div>
                    </div>
                </div>
            </div>
            <div class="hero-stats">
                <div v-for="stat in stats" :key="stat.value">
                    <strong>{{ stat.value }}</strong
                    ><span>{{ stat.label }}</span>
                </div>
            </div>
        </section>

        <section class="section services-section">
            <div class="section-heading">
                <p class="eyebrow"><span></span>SEO · AI · AUTOMATION</p>
                <h2>{{ copy.services_title }}</h2>
                <p>{{ copy.services_intro }}</p>
            </div>
            <div class="service-grid">
                <article
                    v-for="(service, index) in services"
                    :key="service.key"
                    class="glass-card service-card"
                >
                    <span class="service-number">0{{ index + 1 }}</span>
                    <div class="service-icon">✦</div>
                    <h3>{{ service.title }}</h3>
                    <p>{{ service.summary }}</p>
                    <Link
                        :href="
                            routeUrl(
                                service.key === 'ai-search' ||
                                    service.key === 'automation'
                                    ? 'ai'
                                    : 'services',
                            )
                        "
                        >Explore <span>↗</span></Link
                    >
                </article>
            </div>
        </section>

        <section class="section results-section">
            <div class="results-orbit">
                <span></span><i></i><b><LogoMark /></b>
            </div>
            <div class="results-content">
                <p class="eyebrow"><span></span>REAL GROWTH. REAL PEOPLE.</p>
                <h2>{{ copy.results_title }}</h2>
                <div class="result-list">
                    <div>
                        <strong>01</strong>
                        <p>Evidence before assumptions</p>
                    </div>
                    <div>
                        <strong>02</strong>
                        <p>One accountable senior team</p>
                    </div>
                    <div>
                        <strong>03</strong>
                        <p>Systems your business can own</p>
                    </div>
                </div>
            </div>
        </section>

        <section v-if="caseStudies.length" class="section cases-section">
            <div class="section-heading">
                <p class="eyebrow"><span></span>SELECTED WORK</p>
                <h2>{{ caseStudies[0].title }}</h2>
                <p>{{ caseStudies[0].summary }}</p>
            </div>
            <article class="case-panel">
                <div>
                    <small>{{ caseStudies[0].industry }}</small>
                    <h3>{{ caseStudies[0].client }}</h3>
                </div>
                <div
                    v-for="metric in caseStudies[0].metrics"
                    :key="metric.value"
                >
                    <strong>{{ metric.value }}</strong
                    ><span>{{ metric.label }}</span>
                </div>
                <Link class="circle-link" :href="routeUrl('cases')">↗</Link>
            </article>
        </section>

        <section class="section cta-section">
            <div>
                <p class="eyebrow"><span></span>NEXT MOVE</p>
                <h2>{{ copy.contact_title }}</h2>
                <p>{{ copy.contact_intro }}</p>
            </div>
            <Link class="button" :href="routeUrl('contact')"
                >{{ copy.primary_cta }} <span>↗</span></Link
            >
        </section>
    </PublicLayout>
</template>
