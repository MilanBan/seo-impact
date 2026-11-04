<?php

use App\Models\PageTranslation;
use Inertia\Testing\AssertableInertia as Assert;

it('serves localized public routes with metadata', function (string $locale, string $path, string $component) {
    $this->seed();
    $this->get("/$locale/$path")->assertOk()->assertInertia(fn (Assert $page) => $page
        ->component($component)->where('locale', $locale)->has('seo.canonical')
        ->has('seo.alternates.en')->has('seo.alternates.sr')->has('seo.alternates.de'));
})->with([
    ['en', 'services', 'public/StandardPage'],
    ['sr', 'ai-resenja', 'public/StandardPage'],
    ['de', 'fallstudien', 'public/StandardPage'],
    ['en', 'blog', 'public/BlogIndex'],
]);

it('redirects the root to english', fn () => $this->get('/')->assertRedirect('/en'));

it('renders the english home page with SEO fallbacks', function () {
    $this->seed();
    $this->get('/en')->assertOk()->assertInertia(fn (Assert $page) => $page
        ->component('public/Home')
        ->where('seo.title', 'SEO Impact')
        ->where('seo.description', config('site.content.en.summary'))
        ->has('services', 5));
});

it('omits an unpublished page translation', function () {
    $this->seed();
    PageTranslation::query()->where('locale', 'de')->where('slug', 'leistungen')->update(['published' => false]);
    $this->get('/en/services')->assertOk()->assertInertia(fn (Assert $page) => $page
        ->missing('seo.alternates.de')
        ->has('seo.alternates.en')
        ->has('seo.x_default'));
    $this->get('/de/leistungen')->assertNotFound();
    $this->get('/sitemap.xml')->assertOk()->assertDontSee('/de/leistungen');
});
