# Production deployment

Build the `app` target from the root Dockerfile and run it behind an HTTPS reverse proxy that forwards PHP requests to port 9000. Build the `ssr` target from the same commit for Inertia SSR.

Run these long-lived processes as separate containers or managed processes:

```text
web/fpm:   php-fpm -F
ssr:       php artisan inertia:start-ssr
queue:     php artisan queue:work --sleep=3 --tries=3 --timeout=120
scheduler: php artisan schedule:work
```

Before switching traffic, run `php artisan migrate --force`, `php artisan config:cache`, `php artisan route:cache` and `php artisan view:cache`. Set `APP_KEY`, `APP_URL`, production MySQL credentials, SMTP credentials, `MAIL_LEADS_TO`, and an S3-compatible private disk if local persistent storage is unavailable.

## Backups

- Create an encrypted daily MySQL dump and retain at least 30 daily and 12 monthly copies.
- Version or snapshot the private client-file bucket independently of the database.
- Test restoration quarterly in an isolated environment.
- Never expose `storage/app/private`. Files must pass through the authorized Laravel route.

## Release checks

```bash
php artisan migrate:status
./vendor/bin/pest
npm run types:check
npm run build:ssr
php artisan route:list
curl --fail https://example.com/up
```

Queue and SSR processes should be monitored and restarted automatically. Configure HTTPS, secure cookies, trusted proxies, centralized logs and error reporting at the hosting layer.
