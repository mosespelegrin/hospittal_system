# Amani Hospital Management System

A Laravel + Filament application for running a small hospital's front desk and back office: patients can request a consultation from the public website, and staff manage departments, doctors, patients, and appointments from an admin panel.

## What it does

- **Public consultation booking** — visitors pick a department (and optionally a doctor), request a date/time, and describe their complaint. The app auto-assigns an available doctor when none is chosen, avoids double-booking a doctor's slot, and reuses an existing patient record if the same name/phone has booked before.
- **Admin panel** (`/admin`, built with [Filament](https://filamentphp.com)) — manage departments, doctors, patients, and appointments; a dashboard shows today's appointment count, pending appointments, and active patient/doctor totals.
- **Role-based access**, via [spatie/laravel-permission](https://spatie.be/docs/laravel-permission):
  - **admin** — full access to everything.
  - **receptionist** — can manage patients and appointments day-to-day; can view (but not edit) doctors and departments; cannot delete anything.
  - **doctor** — a read-only portal scoped to their own appointments and the patients tied to them, nothing else.
- **Booking notifications** — patients get an email confirmation (if they gave one) and staff get notified of new bookings, via a Laravel notification currently wired to the mail channel.
- **CSV export** of the patient list, and soft deletes (with restore/force-delete) on patients, doctors, and appointments so records aren't lost to an accidental click.

## Tech stack

- PHP 8.3+, Laravel 13
- [Filament 5](https://filamentphp.com) for the admin panel
- [spatie/laravel-permission](https://spatie.be/docs/laravel-permission) for roles & permissions
- MySQL, Tailwind CSS + Alpine.js (via Breeze) for the public pages, Vite for asset bundling

## Getting started

**Requirements:** PHP 8.3+, Composer, Node.js, and a MySQL database.

```bash
composer install
cp .env.example .env
php artisan key:generate
```

Edit `.env` and point `DB_*` at a MySQL database (`DB_DATABASE=hospital_system` by default), then:

```bash
php artisan migrate
php artisan db:seed
npm install
npm run build   # or `npm run dev` while developing
php artisan serve
```

Visit `http://localhost:8000` for the public site, or `http://localhost:8000/admin` for the admin panel.

### Seeded accounts

`php artisan db:seed` creates one login per role, all with the password `password`:

| Role | Email |
|---|---|
| admin | `admin@hospital.test` |
| receptionist | `receptionist@hospital.test` |
| doctor | `doctor@hospital.test` (linked to the first seeded doctor) |

**Change or remove these before deploying anywhere real.**

## Testing

```bash
php artisan test
```

Feature tests cover the public booking flow (happy path, no-doctor-available handling, double-booking rejection, duplicate-patient reuse, the signed success URL) and role/policy authorization (panel access gating, and what each role can and can't do).

## Notes for deployment

- Set `APP_DEBUG=false`, `APP_ENV=production`, and a correct `APP_URL` (including `https://`) before going live — the booking success page uses signed URLs, which are generated against `APP_URL`.
- `MAIL_MAILER` defaults to `log` (emails are written to `storage/logs/laravel.log` instead of being sent). Point it at a real mail provider to have booking confirmations actually delivered.
- SMS/WhatsApp notifications aren't wired in yet — the app runs fine without them; it's a planned addition once a provider is chosen.
- No background queue worker is required; notifications are sent synchronously.
