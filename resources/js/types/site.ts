export type Locale = 'en' | 'sr' | 'de';

export interface SiteCopy {
    language: string;
    nav: Record<string, string>;
    eyebrow: string;
    headline: string[];
    summary: string;
    primary_cta: string;
    secondary_cta: string;
    services_title: string;
    services_intro: string;
    results_title: string;
    contact_title: string;
    contact_intro: string;
    form: Record<string, string>;
}

export interface SeoData {
    title: string;
    description: string;
    canonical: string;
    alternates: Partial<Record<Locale, string>>;
    x_default: string;
}

export interface PublicPageProps {
    locale: Locale;
    copy: SiteCopy;
    routeKey: string;
    routes: Record<Locale, Record<string, string>>;
    seo: SeoData;
}

export interface Service {
    key: string;
    icon: string;
    title: string;
    summary: string;
}

export interface CaseStudy {
    client: string;
    industry: string;
    title: string;
    summary: string;
    metrics: Array<{ value: string; label: string }>;
}
