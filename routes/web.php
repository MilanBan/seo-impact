<?php

use App\Enums\UserRole;
use App\Http\Controllers\InvitationController;
use App\Http\Controllers\LeadController;
use App\Http\Controllers\PortalController;
use App\Http\Controllers\SiteController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/en', 302)->name('home');
Route::get('/invitation/{token}', [InvitationController::class, 'show'])->middleware('throttle:10,1')->name('invitation.show');
Route::post('/invitation/{token}', [InvitationController::class, 'accept'])->middleware('throttle:5,1')->name('invitation.accept');
Route::get('/sitemap.xml', [SiteController::class, 'sitemap'])->name('sitemap');
Route::get('/robots.txt', fn () => response("User-agent: *\nAllow: /\nDisallow: /admin\nDisallow: /en/portal\nDisallow: /sr/portal\nDisallow: /de/portal\nSitemap: ".url('/sitemap.xml')."\n", 200, ['Content-Type' => 'text/plain']));

foreach (config('site.routes') as $locale => $paths) {
    Route::prefix($locale)->middleware('locale')->group(function () use ($locale, $paths) {
        Route::get('/', [SiteController::class, 'home'])->defaults('locale', $locale)->name("site.$locale.home");
        Route::get($paths['services'], fn () => app(SiteController::class)->page($locale, 'services'))->defaults('locale', $locale)->name("site.$locale.services");
        Route::get($paths['ai'], fn () => app(SiteController::class)->page($locale, 'ai'))->defaults('locale', $locale)->name("site.$locale.ai");
        Route::get($paths['cases'], fn () => app(SiteController::class)->page($locale, 'cases'))->defaults('locale', $locale)->name("site.$locale.cases");
        Route::get($paths['about'], fn () => app(SiteController::class)->page($locale, 'about'))->defaults('locale', $locale)->name("site.$locale.about");
        Route::get($paths['contact'], fn () => app(SiteController::class)->page($locale, 'contact'))->defaults('locale', $locale)->name("site.$locale.contact");
        Route::post($paths['contact'], [LeadController::class, 'store'])->defaults('locale', $locale)->middleware('throttle:5,1')->name("site.$locale.contact.store");
        Route::get($paths['blog'], [SiteController::class, 'blog'])->defaults('locale', $locale)->name("site.$locale.blog");
        Route::get($paths['blog'].'/{slug}', [SiteController::class, 'post'])->defaults('locale', $locale)->name("site.$locale.post");

        Route::middleware(['auth', 'verified'])->prefix('portal')->group(function () use ($locale) {
            Route::get('/', [PortalController::class, 'index'])->defaults('locale', $locale)->name("portal.$locale");
            Route::post('/files', [PortalController::class, 'storeFile'])->defaults('locale', $locale)->name("portal.$locale.files.store");
            Route::get('/files/{clientFile}', [PortalController::class, 'download'])->defaults('locale', $locale)->name("portal.$locale.files.download");
        });
    });
}

Route::get('/dashboard', function () {
    $user = request()->user();

    return $user?->role === UserRole::Client
        ? redirect()->route('portal.'.($user->preferred_locale ?: 'en'))
        : redirect('/admin');
})->middleware(['auth', 'verified'])->name('dashboard');

require __DIR__.'/settings.php';
