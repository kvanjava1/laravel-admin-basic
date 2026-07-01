# Architecture

## Layered Architecture

### Backend Flow

```
Route → FormRequest → Controller → Service → Repository → Model/DB
```

| Layer | Location | Responsibility |
|-------|----------|---------------|
| Routes | `routes/api.php`, `routes/web.php` | URL-to-controller mapping, middleware |
| Form Requests | `app/Http/Requests/AdminDashboard/` | Input validation, some authorization |
| Controllers | `app/Http/Controllers/Api/AdminDashboard/` | Accept input, delegate to services, return JSON |
| Services | `app/Services/` | Business rules, state transitions, cross-model orchestration |
| Repositories | `app/Repositories/` | Reusable queries, filtering, pagination, basic CRUD |
| Models | `app/Models/` | Eloquent entities, relationships, attribute accessors |
| Traits | `app/Traits/` | Shared behavior (`ApiResponse`, `HandlesImageUploads`) |
| Exceptions | `app/Exceptions/` | `ApiException` for business failures, `ApiExceptionHandler` for normalization |
| Resources | `app/Http/Resources/` | API response transformation (`MediaDetailResource`, `MediaListResource`) |
| Console | `app/Console/Commands/` | Artisan commands (`ReleaseExpiredBans`) |

### Frontend Flow

```
View → Composable → Service → useApi → Backend API
```

| Layer | Location | Responsibility |
|-------|----------|---------------|
| Views | `resources/js/admin-dashboard/views/` | Page-level Vue components |
| Composables | `resources/js/admin-dashboard/composables/` | Stateful page logic (`ref`, `computed`, `watch`) |
| Services | `resources/js/admin-dashboard/services/` | HTTP calls via `useApi()`, TypeScript interfaces |
| Components | `resources/js/admin-dashboard/components/` | Reusable UI (`ui/`), layout, domain-specific |
| Utils | `resources/js/admin-dashboard/utils/` | Shared utilities (`sweetalert.ts`) |
| Store | `resources/js/admin-dashboard/store/` | Empty (state managed via composables, not Vuex/Pinia) |
| Types | `resources/js/admin-dashboard/types/` | Empty (types co-located in service files) |

## Directory Map

```
laravel-admin-basic/
├── app/
│   ├── Console/Commands/         # Artisan commands (1: ReleaseExpiredBans)
│   ├── Exceptions/               # ApiException, ApiExceptionHandler
│   ├── Http/
│   │   ├── Controllers/Api/
│   │   │   ├── AuthController    # Login, logout, me
│   │   │   └── AdminDashboard/   # 8 controllers (Article, Category, Media, Permission, Profile, Role, Tag, UserBan, UserManagement)
│   │   ├── Requests/AdminDashboard/  # FormRequest classes grouped by module
│   │   └── Resources/            # MediaDetailResource, MediaListResource
│   ├── Models/                   # 9 models (Article, BanHistory, Category, CategoryGroup, Media, Role, Tag, User, UserStatus)
│   ├── Providers/                # Service providers
│   ├── Repositories/             # 6 repositories (Article, Category, Media, Role, Tag, User)
│   ├── Rules/                    # Custom validation rules
│   ├── Services/                 # 10 services (Article, Auth, Category, Media, Profile, RoleAndAccountProtection, Role, Tag, User, UserBan)
│   └── Traits/                   # ApiResponse, HandlesImageUploads
├── bootstrap/
│   └── app.php                   # Exception handler + Spatie permission middleware alias
├── config/
│   ├── media.php                 # Media disk, quality, variant sizes
│   ├── protection.php            # Protected roles and accounts
│   ├── sanctum.php               # Token auth configuration
│   └── permission.php            # Spatie permission configuration
├── database/
│   ├── database.sqlite           # SQLite database file
│   ├── migrations/               # 19 migration files
│   └── seeders/                  # 7 seeders (Admin, Article, CategoryGroup, Category, Database, RolesAndPermissions, UserStatus)
├── resources/
│   └── js/
│       ├── app.ts                # Vite entry point
│       └── admin-dashboard/      # Vue SPA root
│           ├── index.ts          # createApp + mount
│           ├── router.ts         # Vue Router with guards
│           ├── App.vue           # Root component (<router-view>)
│           ├── components/
│           │   ├── layout/       # AdminLayout, Header, Sidebar
│           │   ├── ui/           # Base* components (17 files)
│           │   ├── article/      # ArticleDetailsModal, ArticleFilterModal, ArticleStatusBadge, MediaLibraryModal
│           │   ├── media/        # MediaDetailsModal, MediaFilterModal, MediaUploadWorkflow
│           │   ├── role/         # Role-specific components
│           │   ├── user/         # User-specific components
│           │   └── dashboard/    # Dashboard components
│           ├── composables/      # 15 composables (useApi, useAuth, usePermission, useArticle*, useCategory*, useMedia*, useProfile*, useRole*, useUser*)
│           ├── services/         # 8 service files (article, auth, category, media, profile, role, tag, user)
│           ├── utils/            # sweetalert.ts
│           ├── store/            # Empty (unused)
│           └── types/            # Empty (types in service files)
├── routes/
│   ├── api.php                   # 29 API routes (all with permission middleware)
│   ├── web.php                   # SPA catch-all (/admin/{any?})
│   └── console.php               # inspire command only
├── tests/
│   └── Feature/
│       └── AdminDashboard/       # 8 test files (56 tests)
├── context/                      # This folder
│   ├── docs/                     # Project documentation
│   └── bin/                      # Docker shortcut scripts (php, composer, npm, container)
└──

## Configuration Strategy

### Custom Config Files

| File | Purpose |
|------|---------|
| `config/protection.php` | Lists protected roles (`Super Administrator`) and protected accounts (`admin@admin.com`) |
| `config/media.php` | Media disk (`public`), base directory (`media`), WEBP quality (`80`), variant sizes |

### Key Environment Variables

| Variable | Default | Purpose |
|----------|---------|---------|
| `DB_CONNECTION` | `sqlite` | Database driver |
| `QUEUE_CONNECTION` | `database` | Queue driver |
| `APP_URL` | `http://laravel-admin-basic.test` | Application URL for Docker/Nginx |
| `VITE_APP_NAME` | `${APP_NAME}` | Exposed to frontend |

## Data Flow Patterns

### API Response Envelope

All API responses use a consistent envelope via the `ApiResponse` trait:

```json
// Success
{ "success": true, "message": "...", "data": { ... } }

// Error
{ "success": false, "message": "...", "errors": { ... } }
```

Exception: `ArticleController` and `CategoryController` return raw `response()->json()` without the `ApiResponse` trait. This is an inconsistency in the codebase.

### Error Propagation

```
Service throws ApiException → ApiExceptionHandler catches in bootstrap/app.php → Normalized JSON response
```

The handler also normalizes:
- `ValidationException` → 422
- `AuthenticationException` → 401
- `AuthorizationException` → 403
- `ModelNotFoundException` → 404 with model name

### Authentication Flow

```
Login → Sanctum token created → Token stored in localStorage
Request → Axios interceptor adds Bearer header → Sanctum middleware validates
401 → Axios response interceptor clears localStorage and redirects to /admin/login
```

### Permission Flow

All API routes behind `auth:sanctum` also require a `permission:*` middleware (Spatie). Every authenticated route is tagged with its required permission slug:

```
Route::middleware('auth:sanctum')  // 401 if no token
  → Route::middleware('permission:view-users')  // 403 if user lacks permission
    → Controller checks RoleAndAccountProtectionService for protected entities
      → Frontend uses usePermission().can(perm) to hide/show UI elements
```

Permissions are returned in the login and me responses via `getAllPermissions()` (inherited via Spatie roles, not direct assignment).

### Image Processing Pipeline

```
Upload → Intervention Image reads file
       → Original encoded to WEBP → stored at media/YYYY/MM/DD/{uuid}/original.webp
       → For each ratio (16:9, 4:3) × size (medium, big):
           → Crop using submitted coordinates → Resize to configured dimensions → Encode WEBP → Store variant
       → All paths and sizes persisted to database
```

Variant dimensions (from `config/media.php`):

| Variant | Width | Height |
|---------|-------|--------|
| 16:9 Medium | 800 | 450 |
| 16:9 Big | 1600 | 900 |
| 4:3 Medium | 800 | 600 |
| 4:3 Big | 1600 | 1200 |
