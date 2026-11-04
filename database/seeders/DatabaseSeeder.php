<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\CaseStudy;
use App\Models\Category;
use App\Models\ClientOrganization;
use App\Models\FinancialRecord;
use App\Models\Page;
use App\Models\Post;
use App\Models\Project;
use App\Models\Service;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $admin = User::factory()->create([
            'name' => 'SEO Impact Admin',
            'email' => 'admin@seo-impact.test',
            'role' => UserRole::Admin,
        ]);

        $serviceContent = [
            'technical-seo' => [
                'icon' => 'search',
                'en' => ['Technical SEO', 'technical-seo', 'A fast, crawlable and measurable foundation for sustainable organic growth.'],
                'sr' => ['Tehnički SEO', 'tehnicki-seo', 'Brza, dostupna i merljiva osnova za održiv organski rast.'],
                'de' => ['Technisches SEO', 'technisches-seo', 'Eine schnelle, crawlbare und messbare Basis für nachhaltiges organisches Wachstum.'],
            ],
            'ai-search' => [
                'icon' => 'sparkles',
                'en' => ['AI Search Visibility', 'ai-search-visibility', 'Become a trusted source in ChatGPT, Gemini, Perplexity and the next generation of search.'],
                'sr' => ['Vidljivost u AI pretrazi', 'vidljivost-ai-pretraga', 'Postanite relevantan izvor u ChatGPT, Gemini, Perplexity i novoj generaciji pretrage.'],
                'de' => ['Sichtbarkeit in der KI-Suche', 'ki-sichtbarkeit', 'Werden Sie zur vertrauenswürdigen Quelle in ChatGPT, Gemini, Perplexity und der nächsten Suche.'],
            ],
            'content-systems' => [
                'icon' => 'layers',
                'en' => ['Content systems', 'content-systems', 'Turn customer demand and first-party expertise into content that compounds.'],
                'sr' => ['Sistemi sadržaja', 'sistemi-sadrzaja', 'Pretvorite potražnju i znanje svog tima u sadržaj koji dugoročno donosi rezultat.'],
                'de' => ['Content-Systeme', 'content-systeme', 'Verwandeln Sie Nachfrage und Expertise in Inhalte mit nachhaltiger Wirkung.'],
            ],
            'automation' => [
                'icon' => 'workflow',
                'en' => ['AI automation', 'ai-automation', 'Remove repetitive work with reliable workflows designed around your real processes.'],
                'sr' => ['AI automatizacija', 'ai-automatizacija', 'Uklonite ponavljajući rad pouzdanim tokovima zasnovanim na vašim procesima.'],
                'de' => ['KI-Automatisierung', 'ki-automatisierung', 'Eliminieren Sie Routinearbeit mit zuverlässigen Workflows für Ihre echten Prozesse.'],
            ],
            'analytics' => [
                'icon' => 'chart',
                'en' => ['Analytics & reporting', 'analytics-reporting', 'Connect visibility to pipeline and make the next decision with confidence.'],
                'sr' => ['Analitika i izveštavanje', 'analitika-izvestavanje', 'Povežite vidljivost sa prodajnim rezultatima i donosite sigurnije odluke.'],
                'de' => ['Analytics & Reporting', 'analytics-reporting', 'Verbinden Sie Sichtbarkeit mit Pipeline und treffen Sie bessere Entscheidungen.'],
            ],
        ];

        $sortOrder = 0;
        foreach ($serviceContent as $key => $data) {
            $service = Service::create(['key' => $key, 'icon' => $data['icon'], 'sort_order' => $sortOrder++]);
            foreach (['en', 'sr', 'de'] as $locale) {
                [$title, $slug, $summary] = $data[$locale];
                $service->translations()->create(compact('locale', 'title', 'slug', 'summary') + ['published' => true]);
            }
        }

        foreach (config('site.page_copy') as $key => $translations) {
            $page = Page::create(['key' => $key]);
            foreach ($translations as $locale => $copy) {
                $page->translations()->create([
                    'locale' => $locale,
                    'slug' => config("site.routes.$locale.$key"),
                    'title' => $copy['title'],
                    'headline' => $copy['headline'],
                    'summary' => $copy['summary'],
                    'published' => true,
                ]);
            }
        }

        $case = CaseStudy::create([
            'client_name' => 'Regional commerce brand', 'industry' => 'E-commerce',
            'metrics' => [['value' => '+328%', 'label' => 'organic growth'], ['value' => '4.8×', 'label' => 'qualified leads']],
        ]);
        foreach ([
            'en' => ['From fragmented visibility to a repeatable growth engine', 'repeatable-growth-engine', 'Technical cleanup, clearer demand mapping and a useful content system changed the growth curve.'],
            'sr' => ['Od fragmentisane vidljivosti do sistema rasta', 'sistem-organskog-rasta', 'Tehnička optimizacija, jasna mapa potražnje i koristan sadržaj promenili su krivu rasta.'],
            'de' => ['Von fragmentierter Sichtbarkeit zum Wachstumssystem', 'wiederholbares-wachstumssystem', 'Technische Optimierung, klare Nachfrage und ein hilfreiches Content-System veränderten die Wachstumskurve.'],
        ] as $locale => [$title, $slug, $summary]) {
            $case->translations()->create(compact('locale', 'title', 'slug', 'summary') + ['published' => true]);
        }

        $category = Category::create(['key' => 'growth']);
        foreach (['en' => ['Growth', 'growth'], 'sr' => ['Rast', 'rast'], 'de' => ['Wachstum', 'wachstum']] as $locale => [$name, $slug]) {
            $category->translations()->create(compact('locale', 'name', 'slug'));
        }

        $post = Post::create(['category_id' => $category->id, 'author_id' => $admin->id, 'published_at' => now()->subDay()]);
        foreach ([
            'en' => ['Search visibility after the AI shift', 'search-visibility-after-ai-shift', 'A practical framework for earning demand across classic and AI search.', '<p>Search is no longer a single results page. Strong brands build a reliable technical foundation, publish genuinely useful expertise, and make their entities easy for machines to understand.</p><h2>Start with evidence</h2><p>Measure where customers discover you today, then prioritize the few interventions that can change commercial outcomes.</p>'],
            'sr' => ['Vidljivost nakon AI promene pretrage', 'vidljivost-nakon-ai-promene', 'Praktičan okvir za rast kroz klasičnu i AI pretragu.', '<p>Pretraga više nije jedna stranica rezultata. Snažni brendovi grade tehničku osnovu, objavljuju korisno znanje i omogućavaju sistemima da jasno razumeju njihov identitet.</p><h2>Počnite od dokaza</h2><p>Izmerite gde vas korisnici danas pronalaze, zatim odredite nekoliko poteza koji menjaju poslovni rezultat.</p>'],
            'de' => ['Sichtbarkeit nach dem KI-Wandel der Suche', 'sichtbarkeit-nach-dem-ki-wandel', 'Ein praktischer Rahmen für klassische und KI-gestützte Suche.', '<p>Suche ist nicht mehr nur eine Ergebnisliste. Starke Marken schaffen eine verlässliche technische Basis, veröffentlichen nützliches Fachwissen und machen ihre Entitäten maschinenlesbar.</p><h2>Mit Evidenz beginnen</h2><p>Messen Sie heutige Kontaktpunkte und priorisieren Sie die Maßnahmen mit echtem Geschäftseinfluss.</p>'],
        ] as $locale => [$title, $slug, $excerpt, $body]) {
            $post->translations()->create(compact('locale', 'title', 'slug', 'excerpt', 'body') + ['published' => true]);
        }

        $organization = ClientOrganization::create(['name' => 'Demo Client', 'slug' => 'demo-client', 'email' => 'client@seo-impact.test', 'country' => 'RS']);
        User::factory()->create(['name' => 'Demo Client', 'email' => 'client@seo-impact.test', 'role' => UserRole::Client, 'client_organization_id' => $organization->id]);
        $project = Project::create(['client_organization_id' => $organization->id, 'name' => 'Organic Growth Programme', 'description' => 'Technical SEO, content and AI visibility programme.', 'status' => 'active', 'starts_at' => now(), 'due_at' => now()->addMonths(3)]);
        $project->tasks()->createMany([
            ['title' => 'Technical discovery', 'status' => 'done', 'priority' => 'high', 'client_visible' => true],
            ['title' => 'Search opportunity map', 'status' => 'in_progress', 'priority' => 'high', 'due_at' => now()->addWeek(), 'client_visible' => true],
            ['title' => 'Internal implementation review', 'status' => 'todo', 'priority' => 'normal', 'client_visible' => false],
        ]);
        FinancialRecord::create(['project_id' => $project->id, 'document_number' => 'INV-DEMO-001', 'currency' => 'EUR', 'total' => 2400, 'issued_at' => now(), 'due_at' => now()->addDays(14), 'status' => 'open']);
    }
}
