# Amani Hospital

Public consultation-booking website plus a staff back office.

- **Public site** (`/`, `/consultation`, `/privacy`): patients register and book a consultation; no patient accounts.
- **Staff panel** (`/admin`, Filament): admins, receptionists and doctors manage patients, appointments, doctors and departments. Doctors only see their own appointments and patients.

## Stack

Laravel 13 · PHP 8.3+ · Filament 5 · spatie/laravel-permission · Tailwind/Vite · MySQL (SQLite for tests, PostgreSQL on Render)

## Requirements

PHP 8.3+ with `intl`, `mbstring`, `zip`, `gd`, `bcmath`, `pdo_mysql` (or `pdo_pgsql`), Composer, Node 22, MySQL 8.

## Local setup

```bash
composer install
cp .env.example .env && php artisan key:generate
# create the database named in .env, then:
php artisan migrate
php artisan db:seed            # DEVELOPMENT ONLY – creates demo staff (see below)
npm ci && npm run dev          # or: npm run build
php artisan serve
```

Development-only demo logins (created by `db:seed`, password `password`):
`admin@hospital.test`, `receptionist@hospital.test`, `doctor@hospital.test`.
**Never run the full seeder in production.**

## Production

1. Set `APP_ENV=production`, `APP_DEBUG=false`, `APP_URL`, a fresh `APP_KEY`, DB and SMTP credentials (see `.env.example`).
2. `composer install --no-dev -o && npm ci && npm run build`
3. `php artisan migrate --force && php artisan db:seed --class=RoleSeeder --force && php artisan db:seed --class=PermissionSeeder --force`
4. Create the first admin with `php artisan tinker` (`User::create([...])->assignRole('admin')`).
5. `php artisan config:cache route:cache view:cache filament:optimize`

Guides: `DEPLOY-EC2.md` (Ubuntu + Nginx), `render.yaml` + `Dockerfile` (Render), `compose.yaml` (Docker). Health checks: `/up` (framework) and `/health` (also checks the database).

## Tests

```bash
php artisan test
```

Covers booking rules (double booking, phone normalisation, consent, no overwriting of medical data, rate limiting), role/policy authorisation, security headers, SEO endpoints and error pages.

## Architecture notes

- Authentication is Filament's `/admin/login`. `/login` redirects there; the old Breeze register/profile routes are disabled.
- Authorisation: Spatie roles (`admin`, `receptionist`, `doctor`) → policies. Doctors are additionally scoped in Filament queries and policies.
- Booking (`PatientController@store`) runs in one transaction with doctor rows locked, so two requests cannot take the same slot. Existing patients (matched on name + normalised phone) are only back-filled, never overwritten.
- Security headers + nonce-based CSP are applied by `App\Http\Middleware\SecurityHeaders` (the Filament panel is exempt from CSP only).
- Departments cannot be deleted (deactivate them instead); the DB restricts deleting departments/doctors that have dependants.
- Timezone `Africa/Dar_es_Salaam`; phone numbers stored as `+255…`. Change via `APP_TIMEZONE` / `HOSPITAL_COUNTRY_CODE`.

## Backups (must be configured on the server)

This repo contains no backup job. Production must have: a nightly `mysqldump` (or managed-DB snapshots) kept ≥ 14 days and copied off-site (e.g. S3); `storage/app` backed up if uploads are ever enabled; and a restore drill (`mysql hospital_system < dump.sql`) tested at least once.

## Before launch

- Have `resources/views/privacy.blade.php` reviewed by counsel (Tanzania PDPA 2022).
- Set real `HOSPITAL_PHONE`, `HOSPITAL_EMAIL`, `HOSPITAL_ADDRESS`.
