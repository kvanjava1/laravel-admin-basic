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

Atau pake shortcut `context/bin/`:

```bash
# Semua perintah di atas setara dengan:
context/bin/php artisan serve
context/bin/composer install
context/bin/npm run dev

# Container management
context/bin/container --status
context/bin/container --start
context/bin/container --stop
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

All legacy Breeze tests have been removed. Current tests (56 tests across 8 files):

| File | Coverage Area |
|------|--------------|
| `tests/Feature/AdminDashboard/AuthTest.php` | Login, logout, me, banned/inactive rejection |
| `tests/Feature/AdminDashboard/UserManagementTest.php` | User CRUD, filters, banned status block |
| `tests/Feature/AdminDashboard/UserBanTest.php` | Ban, unban, activate, deactivate, audit trail |
| `tests/Feature/AdminDashboard/RoleTest.php` | Role CRUD, permission sync, protected role deletion |
| `tests/Feature/AdminDashboard/CategoryTest.php` | Category CRUD, tree, self-parenting block |
| `tests/Feature/AdminDashboard/MediaTest.php` | Media upload, list, filter, update, delete |
| `tests/Feature/AdminDashboard/ArticleTest.php` | Article CRUD, tags, auto published_at |
| `tests/Feature/AdminDashboard/ProfileTest.php` | Profile show/update, password change |

### Testing Notes

- All tests use `RefreshDatabase` trait with `:memory:` SQLite — **never** touches the real `database/database.sqlite`
- `tests/bootstrap.php` clears cached config before test run to ensure `:memory:` is used
- Run tests: `context/bin/php artisan test` or `context/bin/composer test`
- Some tests include negative assertions (403 for users without required permissions)

## Cache

**Driver:** `file` (set in `.env: CACHE_STORE=file`)

Cache is written to `storage/framework/cache/data/`. No Redis container needed.

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
2. **Unscheduled command**: `app:release-expired-bans` exists but has no automatic scheduling.
3. **No descendant-cycle protection**: Category hierarchy blocks self-parenting but does not prevent assigning a descendant as a parent (would create a cycle).
4. **Empty directories**: `store/` and `types/` directories exist in the frontend but are unused.
5. **Node version mismatch**: Container is named `node20.19.1` but reported `node -v` output is `v18.17.1`.
6. **Breeze dev dependency**: `laravel/breeze` remains in `composer.json` dev dependencies despite the project having fully migrated to Sanctum API auth.
7. **Ziggy dependency**: `tightenco/ziggy` is in both backend (composer) and frontend (vite alias), used only for `route()` in frontend services. The web routes file still imports `Inertia\Inertia` (unused).
8. **Media file cleanup**: Soft-deleted media marks `file_cleanup_marked_at` but there is no command or scheduled task to actually remove the orphaned files from disk.
