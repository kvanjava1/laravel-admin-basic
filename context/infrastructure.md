# Infrastructure

## Docker Runtime

This project runs inside Docker containers. Do not use `php artisan serve` or direct host commands for development.

### Containers

| Container | Name | Project Path Inside Container |
|-----------|------|-------------------------------|
| Nginx | `nginx_php_dev` | `/var/www/php/php8.2/laravel-admin-basic` |
| PHP | `php8.2` | `/var/www/php/php8.2/laravel-admin-basic` |
| Node | `node20.19.1` | `/var/www/node20.19.1/php8.2/laravel-admin-basic` |

### Browser Access

```
http://laravel-admin-basic.test
```

### Running Commands

```bash
# PHP / Composer / Artisan
docker exec php8.2 php artisan migrate
docker exec php8.2 composer install

# Node / NPM
docker exec node20.19.1 npm install
docker exec node20.19.1 npm run dev
```

### Vite Dev Server

Vite is configured to bind to `0.0.0.0:5176` with HMR on `localhost:5176` (see `vite.config.js`).

## Build & Dev Scripts

### Composer Scripts

| Script | Command | Purpose |
|--------|---------|---------|
| `setup` | install + env + key + migrate + npm build | Full project setup |
| `dev` | concurrently: serve + queue + pail + vite | Start all dev processes |
| `test` | config:clear + artisan test | Run Pest test suite |

### NPM Scripts

| Script | Command | Purpose |
|--------|---------|---------|
| `dev` | `vite` | Start Vite dev server |
| `build` | `vue-tsc && vite build` | Type-check + production build |

## Testing

### Framework

- **Pest 3** with `pestphp/pest-plugin-laravel`
- Test files at `tests/`

### Test Files

| File | Coverage Area |
|------|--------------|
| `tests/Feature/AdminDashboard/ArticleManagementTest.php` | Article CRUD |
| `tests/Feature/AdminDashboard/MediaManagementTest.php` | Media upload, edit, delete |
| `tests/Feature/AdminDashboard/UserProtectionTest.php` | Protected account/role enforcement |
| `tests/Feature/Auth/AuthenticationTest.php` | Legacy Breeze-style auth tests (may not match current API) |
| `tests/Feature/Auth/EmailVerificationTest.php` | Legacy (not active in current architecture) |
| `tests/Feature/Auth/Password*.php` | Legacy password tests |
| `tests/Feature/Auth/RegistrationTest.php` | Legacy registration tests |
| `tests/Feature/ProfileTest.php` | Legacy profile test |
| `tests/Feature/ExampleTest.php` | Default Laravel example |
| `tests/Unit/ExampleTest.php` | Default Laravel example |

### Testing Notes

- `AdminDashboard/` tests are the **active, current** tests matching the SPA/API architecture
- `Auth/` and root-level `Feature/` tests are **legacy** from the original Breeze scaffolding — they target web routes that no longer exist
- Media tests use **explicit cleanup** instead of `RefreshDatabase` to avoid touching the real SQLite file when config cache is stale
- Run tests: `docker exec php8.2 php artisan test` or `docker exec php8.2 composer test`

## Scheduled Tasks

### Commands

| Command | Signature | Purpose |
|---------|-----------|---------|
| `ReleaseExpiredBans` | `app:release-expired-bans` | Auto-restore users with expired temporary bans |

### Scheduler Status

The `app:release-expired-bans` command exists but is **not registered in the Laravel scheduler**. The `routes/console.php` file contains only the default `inspire` command. To automate expired ban release, it must be:
- Registered in `routes/console.php` or `bootstrap/app.php` via `->withSchedule()`, or
- Called directly via system cron: `docker exec php8.2 php artisan app:release-expired-bans`

## Storage Layout

### Public Disk Structure

```
storage/app/public/
├── media/
│   └── YYYY/MM/DD/{uuid}/
│       ├── original.webp
│       ├── 16x9-medium.webp
│       ├── 16x9-big.webp
│       ├── 4x3-medium.webp
│       └── 4x3-big.webp
└── profile_pictures/
    └── YYYY/MM/DD/
        └── {random20chars}.webp
```

The `public` disk requires `php artisan storage:link` to be accessible via web.

## Known Technical Debt

1. **Response format inconsistency**: `ArticleController` and `CategoryController` do not use the `ApiResponse` trait, producing a different JSON envelope than other controllers.
2. **Legacy test files**: 7 test files under `tests/Feature/Auth/` and `tests/Feature/ProfileTest.php` target old Breeze-style web routes and will fail against the current SPA/API architecture.
3. **Unscheduled command**: `app:release-expired-bans` exists but has no automatic scheduling.
4. **No permission middleware**: Seeded permissions (`view-users`, `create-users`, etc.) are not enforced at the route or controller level. Only `auth:sanctum` is applied.
5. **No descendant-cycle protection**: Category hierarchy blocks self-parenting but does not prevent assigning a descendant as a parent (would create a cycle).
6. **Empty directories**: `store/` and `types/` directories exist in the frontend but are unused.
7. **Node version mismatch**: Container is named `node20.19.1` but reported `node -v` output is `v18.17.1`.
8. **Breeze dev dependency**: `laravel/breeze` remains in `composer.json` dev dependencies despite the project having fully migrated to Sanctum API auth.
9. **Ziggy dependency**: `tightenco/ziggy` is in both backend (composer) and frontend (vite alias), used only for `route()` in frontend services. The web routes file still imports `Inertia\Inertia` (unused).
10. **Media file cleanup**: Soft-deleted media marks `file_cleanup_marked_at` but there is no command or scheduled task to actually remove the orphaned files from disk.
