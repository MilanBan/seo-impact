# SEO Impact

Multilingual SEO/AI agency website, CMS, internal CRM and isolated client portal. The stack is Laravel 13, PHP 8.4, Vue 3, TypeScript, Inertia 3 SSR, Tailwind CSS 4, Filament 5, TresJS/Three.js and MySQL 8.4.

## Start from a clean checkout

Requirements: Docker Engine with Compose v2. No host PHP, Composer, Node or MySQL installation is required.

```bash
cp .env.example .env
docker run --rm -u "$(id -u):$(id -g)" -v "$PWD:/app" -w /app composer:2 composer install --ignore-platform-reqs
docker compose up -d --build
docker compose exec laravel.test php artisan key:generate
docker compose exec laravel.test npm ci
docker compose exec laravel.test php artisan migrate --seed
docker compose exec laravel.test npm run build:ssr
docker compose exec -d laravel.test php artisan inertia:start-ssr
docker compose exec -d laravel.test php artisan queue:work --sleep=3 --tries=3
```

Open the public website at [http://localhost:8000/en](http://localhost:8000/en), Filament at [http://localhost:8000/admin](http://localhost:8000/admin), and Mailpit at [http://localhost:8025](http://localhost:8025).

Local seed accounts (development only):

- Admin: `admin@seo-impact.test` / `password`
- Client: `client@seo-impact.test` / `password`

## Daily commands

```bash
docker compose up -d
docker compose exec laravel.test npm run dev
docker compose exec laravel.test php artisan queue:work
docker compose exec laravel.test ./vendor/bin/pest
docker compose exec laravel.test npm run types:check
docker compose exec laravel.test npm run build:ssr
docker compose down
```

The default queue is database-backed. Redis is intentionally not required for the MVP. Public registration is disabled; create a 72-hour client invitation from `/admin/invitations`.

## Product surfaces

- Localized public routes: `/en`, `/sr`, `/de`, including Services, AI Solutions, Case Studies, About, Contact and Blog.
- CMS/CRM: `/admin`, available only to `admin` and `staff`. User management is admin-only.
- Client portal: `/{locale}/portal`, tenant-filtered by organization with private authorized downloads.
- Health: `/up`; sitemap: `/sitemap.xml`; email preview: Mailpit on port 8025.

See [deployment notes](docs/deployment.md) for production processes, backups and storage requirements.
