<?php

namespace App\Http\Controllers;

use App\Models\CaseStudy;
use App\Models\CaseStudyTranslation;
use App\Models\Page;
use App\Models\PageTranslation;
use App\Models\Post;
use App\Models\PostTranslation;
use App\Models\Service;
use App\Models\ServiceTranslation;
use Illuminate\Http\Response as HttpResponse;
use Illuminate\Support\Facades\Schema;
use Inertia\Inertia;
use Inertia\Response;

class SiteController extends Controller
{
    public function home(string $locale): Response
    {
        return Inertia::render('public/Home', $this->baseProps($locale, 'home') + ['services' => $this->services($locale), 'caseStudies' => $this->caseStudies($locale)]);
    }

    public function page(string $locale, string $page): Response
    {
        $copy = config("site.page_copy.$page.$locale");
        if (Schema::hasTable('pages')) {
            $record = Page::query()->where('key', $page)
                ->whereHas('translations', fn ($query) => $query->where('locale', $locale)->where('published', true))
                ->with(['translations' => fn ($query) => $query->where('locale', $locale)])
                ->firstOrFail();
            /** @var PageTranslation|null $translation */
            $translation = $record->translations->first();
            abort_unless($translation !== null, 404);
            $copy = ['title' => $translation->title, 'headline' => $translation->headline, 'summary' => $translation->summary];
        }
        abort_unless($copy, 404);

        return Inertia::render('public/StandardPage', $this->baseProps($locale, $page) + ['page' => $copy, 'services' => $this->services($locale), 'caseStudies' => $this->caseStudies($locale)]);
    }

    public function blog(string $locale): Response
    {
        $posts = Post::query()->whereNotNull('published_at')->where('published_at', '<=', now())
            ->whereHas('translations', fn ($query) => $query->where('locale', $locale)->where('published', true))
            ->with(['translations' => fn ($query) => $query->where('locale', $locale), 'author:id,name'])
            ->latest('published_at')->get()->map(fn (Post $post) => [
                'slug' => $post->translations->first()?->slug,
                'title' => $post->translations->first()?->title,
                'excerpt' => $post->translations->first()?->excerpt,
                'published_at' => $post->published_at?->toDateString(),
                'author' => $post->author?->name,
            ])->values();

        return Inertia::render('public/BlogIndex', $this->baseProps($locale, 'blog') + ['page' => config("site.page_copy.blog.$locale"), 'posts' => $posts]);
    }

    public function post(string $locale, string $slug): Response
    {
        $translation = PostTranslation::query()->where('locale', $locale)->where('slug', $slug)->where('published', true)
            ->with(['post.author:id,name', 'post.translations'])->firstOrFail();
        $alternatePaths = [];
        foreach ($translation->post->translations as $postTranslation) {
            if (! $postTranslation->published) {
                continue;
            }
            $blogPath = config("site.routes.{$postTranslation->locale}.blog");
            if (is_string($blogPath)) {
                $alternatePaths[$postTranslation->locale] = "/{$postTranslation->locale}/{$blogPath}/{$postTranslation->slug}";
            }
        }

        return Inertia::render('public/BlogShow', $this->baseProps($locale, 'blog', $translation->seo_title ?: $translation->title, $translation->seo_description ?: $translation->excerpt, $alternatePaths) + [
            'post' => ['title' => $translation->title, 'excerpt' => $translation->excerpt, 'body' => $translation->body, 'published_at' => $translation->post->published_at?->toDateString(), 'author' => $translation->post->author?->name],
        ]);
    }

    public function sitemap(): HttpResponse
    {
        $urls = [];
        foreach (config('site.routes') as $locale => $routes) {
            foreach ($routes as $key => $slug) {
                if (Schema::hasTable('pages') && ! in_array($key, ['home', 'blog'], true)) {
                    $published = Page::query()->where('key', $key)
                        ->whereHas('translations', fn ($query) => $query->where('locale', $locale)->where('published', true))->exists();
                    if (! $published) {
                        continue;
                    }
                }
                $urls[] = url('/'.$locale.($slug ? '/'.$slug : ''));
            }
        }

        if (Schema::hasTable('post_translations')) {
            PostTranslation::query()->where('published', true)
                ->whereHas('post', fn ($query) => $query->whereNotNull('published_at')->where('published_at', '<=', now()))
                ->get(['locale', 'slug'])
                ->each(function (PostTranslation $translation) use (&$urls): void {
                    $blog = config("site.routes.{$translation->locale}.blog");
                    $urls[] = url("/{$translation->locale}/{$blog}/{$translation->slug}");
                });
        }

        return response()->view('sitemap', ['urls' => array_unique($urls)])->header('Content-Type', 'application/xml');
    }

    /**
     * @param  array<string, string>|null  $alternatePaths
     * @return array<string, mixed>
     */
    private function baseProps(string $locale, string $routeKey, ?string $title = null, ?string $description = null, ?array $alternatePaths = null): array
    {
        $copy = config("site.content.$locale");
        $pageCopy = config("site.page_copy.$routeKey.$locale");
        $routeMap = config('site.routes');
        abort_unless(is_array($copy) && is_array($routeMap), 500);
        $pageCopy = is_array($pageCopy) ? $pageCopy : [];

        $alternates = [];
        foreach ($routeMap as $code => $routes) {
            if (! is_string($code) || ! is_array($routes)) {
                continue;
            }
            if ($alternatePaths !== null) {
                if (isset($alternatePaths[$code])) {
                    $alternates[$code] = url($alternatePaths[$code]);
                }

                continue;
            }
            if (Schema::hasTable('pages') && ! in_array($routeKey, ['home', 'blog'], true)) {
                $hasTranslation = Page::query()->where('key', $routeKey)
                    ->whereHas('translations', fn ($query) => $query->where('locale', $code)->where('published', true))
                    ->exists();
                if (! $hasTranslation) {
                    continue;
                }
            }
            $slug = $routes[$routeKey] ?? null;
            if (is_string($slug)) {
                $alternates[$code] = url('/'.$code.($slug !== '' ? '/'.$slug : ''));
            }
        }

        return ['locale' => $locale, 'copy' => $copy, 'routeKey' => $routeKey, 'routes' => $routeMap, 'seo' => [
            'title' => $title ?: ($pageCopy['title'] ?? 'SEO Impact'),
            'description' => $description ?: ($pageCopy['summary'] ?? $copy['summary']),
            'canonical' => url()->current(),
            'alternates' => $alternates,
            'x_default' => $alternates['en'] ?? url()->current(),
        ]];
    }

    /** @return list<array{key: string, icon: string, title: string, summary: string}> */
    private function services(string $locale): array
    {
        if (! Schema::hasTable('services')) {
            return [];
        }

        return array_values(Service::query()->where('active', true)->with(['translations' => fn ($query) => $query->where('locale', $locale)->where('published', true)])
            ->orderBy('sort_order')->get()->filter(fn (Service $service) => $service->translations->isNotEmpty())
            ->map(function (Service $service): array {
                /** @var ServiceTranslation $translation */
                $translation = $service->translations->first();

                return ['key' => $service->key, 'icon' => $service->icon, 'title' => $translation->title, 'summary' => $translation->summary];
            })->all());
    }

    /** @return list<array{client: string, industry: string|null, title: string, summary: string, metrics: array<string, mixed>|null}> */
    private function caseStudies(string $locale): array
    {
        if (! Schema::hasTable('case_studies')) {
            return [];
        }

        return array_values(CaseStudy::query()->with(['translations' => fn ($query) => $query->where('locale', $locale)->where('published', true)])
            ->orderBy('sort_order')->get()->filter(fn (CaseStudy $case) => $case->translations->isNotEmpty())
            ->map(function (CaseStudy $case): array {
                /** @var CaseStudyTranslation $translation */
                $translation = $case->translations->first();

                return ['client' => $case->client_name, 'industry' => $case->industry, 'title' => $translation->title, 'summary' => $translation->summary, 'metrics' => $case->metrics];
            })->all());
    }
}
