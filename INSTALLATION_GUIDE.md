# Project Kutubio: Laravel Installation Guide

Bismillahirrahmanirrahim.

This document is source of truth for bootstrapping Kutubio with current Laravel stack, verified on `2026-04-23`.

## 1. Version Baseline

### Local tools available
- [x] **PHP**: 8.5.5
- [x] **Composer**: 2.9.7
- [x] **Laravel Installer**: 5.25.3
- [x] **Package Manager**: Bun 1.3.13

### Latest stack to target

| Component | Technology | Notes |
| :--- | :--- | :--- |
| **Backend** | Laravel 13.x | Current stable major. Released `2026-03-17`. |
| **Starter Kit** | Livewire 4 | Current Laravel 13 Livewire starter kit. |
| **Admin UI** | Filament v5 | Current stable Filament docs branch. |
| **AI Tooling** | Laravel Boost 2.x | Install as dev dependency in app. |
| **Queue** | Laravel Horizon | Latest compatible first-party queue dashboard. |
| **Database** | PostgreSQL | Main relational datastore. |
| **Frontend Build** | Bun + Vite + Tailwind CSS 4 | Matches current Laravel starter stack. |
| **Dev Environment** | Laravel Sail | Docker-first local development. |

## 2. Rules

- Use Docker container for development workflow.
- Do **not** run local dev server directly from terminal.
- Run app, queue, Composer, Artisan, Bun through Sail after project bootstrap.
- Use Sail `pgsql` service for PostgreSQL.

## 3. Installation Workflow

### Phase 1: Create application

Use Laravel installer to generate Laravel 13 app with Livewire starter kit and Bun.

```bash
laravel new . --database=pgsql --livewire --bun --no-interaction
```

### Phase 2: Start Sail

After app exists, move all project commands into Docker via Sail.

```bash
./vendor/bin/sail up -d
```

### Phase 3: Install Laravel Boost

Boost is now app package, not `laravel new --boost` flag requirement.

```bash
./vendor/bin/sail composer require laravel/boost --dev
./vendor/bin/sail artisan boost:install
```

### Phase 4: Install Filament v5 and Horizon

Run package installs inside Sail.

```bash
./vendor/bin/sail composer require filament/filament:"^5.0" -W
./vendor/bin/sail php artisan filament:install --panels

./vendor/bin/sail composer require laravel/horizon
./vendor/bin/sail php artisan horizon:install
```

## 4. Post-Install Verification

```bash
./vendor/bin/sail artisan about
./vendor/bin/sail artisan migrate
./vendor/bin/sail artisan make:filament-user
./vendor/bin/sail artisan horizon:status
```

Open Filament at `/admin` after creating first admin user.

## 5. Notes On Current Ecosystem

- Laravel 13 supports PHP `8.3` to `8.5`.
- Laravel 13 Livewire starter kit ships with **Livewire 4**, **Tailwind CSS 4**, and **Flux UI**.
- Filament 5 is current stable branch. Filament 4 docs marked previous version.
- Laravel Boost current public release line is `2.x`.
- `laravel/cloud-cli` not required for core app setup unless deploying to Laravel Cloud.

## 6. Next Steps

1. [ ] Bootstrap fresh Laravel 13 app.
2. [ ] Start Sail containers.
3. [ ] Install Laravel Boost in app.
4. [ ] Install Filament 5 and Horizon inside Sail.
5. [ ] Run migrations and create admin user.

---
*Updated for Laravel 13 / Filament 5*
