# Rivocare Backoffice

Internal CRM for placing employees of partner companies (clients) with psychotherapists.
Built with Laravel 13 and Filament 5.

## What's in it

| Area | What it does |
|---|---|
| **Pipeline** | Kanban board of all cases: New → Onboarding call → Matching → Proposed to client → First session scheduled → Decision pending → Closed |
| **Cases** | One client, one placement process. Tasks, therapist candidates, timeline, documents and billing per case |
| **Tasks** | Every task across cases, companies and therapists. Status Open / In progress / Waiting / Done, and who has to act (us, client, therapist, company) |
| **Companies, Clients, Therapists** | The CRM records. Clients have no accounts; contact happens by email and phone |
| **Billing** (admin) | What companies owe us and what we owe therapists |
| **Stage checklists** (admin) | The tasks created automatically when a case enters a stage |
| **Team, Audit log** (admin) | Users and roles, and who viewed or changed what |

### Language

The UI is German (`APP_LOCALE=de`). Labels are written in German in the code. Labels Filament generates from attribute names (`first_name` → "First name") are translated in `lang/de.json`. Date, number and currency formats are set in `app/Providers/AppServiceProvider.php`.

### Case workflow

The workflow lives in `app/Services/PlacementWorkflow.php`, triggered from `app/Observers/PlacementObserver.php`.

- **Entering a stage** writes a timeline entry and creates that stage's checklist from the stage checklists. A checklist isn't duplicated while its tasks are still open.
- **Decision pending** bills the company for the first session (company's price) and records what we owe the therapist (therapist's fee). A rematch with another therapist bills again.
- **Closed with "Introduction started"** bills the company's success fee once.
- On the board, a case can only move to "First session scheduled" or "Decision pending" once a therapist is assigned. Closing always goes through **Change stage**, so a close reason is recorded.

### Privacy by design

- **No clinical or topic-related data is stored.** Keep that off-system and link it by the case reference (e.g. `C-2026-0004`).
- Client email, phone and notes and all timeline text are encrypted in the database (`encrypted` casts).
- Encrypted values never reach the audit log (`app/Models/Concerns/AuditsChanges.php`).
- Documents are stored on the private disk and only streamed to signed-in users.
- Opening a client or case is logged as a "viewed" event.

## Local setup

Requirements: PHP 8.3+ with `intl`, `bcmath`, `sqlite` and Composer.

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
php artisan serve
```

Open http://localhost:8000. Local sign-ins are created by `database/seeders/DatabaseSeeder.php` (an admin and a team member), along with fictional demo data.

For local development only, `MFA_REQUIRED=false` in `.env` skips the 2FA setup.

Run the tests:

```bash
php artisan test
```

## Production checklist

- [ ] Host in the EU (e.g. Hetzner) and sign a data processing agreement (AVV) with every provider, including email.
- [ ] Use MySQL/MariaDB or PostgreSQL instead of SQLite.
- [ ] `APP_ENV=production`, `APP_DEBUG=false`, `MFA_REQUIRED=true`, HTTPS only, `SESSION_SECURE_COOKIE=true`.
- [ ] **Back up `APP_KEY` safely and separately.** Without it, encrypted client data can't be decrypted.
- [ ] Encrypted, tested database and `storage/app/private` backups.
- [ ] Configure mail (for password resets).
- [ ] Create the first admin with `php artisan backoffice:create-admin`; others are added under **Admin → Team**.
- [ ] Run `php artisan migrate --force` and `php artisan db:seed --class=TaskTemplateSeeder --force` on deploy.
- [ ] Define retention periods for closed cases and have them reviewed by a data protection officer.
