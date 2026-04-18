# Current Status

## Completed / Mostly Completed

### Phase 1. Domain And Contract Finalization

- completed
- locked decisions:
  - keep generated original output as `original.webp`
  - always store as `webp`
  - sizes:
    - `16:9`: `800x450`, `1600x900`
    - `4:3`: `800x600`, `1600x1200`
  - `category_id` optional
  - use relational tags via `tags` + `media_tag`
  - soft delete DB record and mark file cleanup with `file_cleanup_marked_at`
  - do not delete physical files on soft delete
  - edit supports metadata update and recrop
  - source-image replacement is still not implemented
  - SEO fields:
    - `title`
    - `alt_text`
    - `caption`
    - `description`

### Phase 2. Backend Foundation

- completed for current scope
- implemented:
  - media config file
  - media migration
  - variant size migration
  - tags and pivot migrations
  - `Media` model
  - `Tag` model
  - `MediaRepository`
  - `StoreMediaRequest`
  - `UpdateMediaRequest`
  - `MediaService`
  - `MediaController`
  - API routes:
    - `GET /api/media`
    - `POST /api/media`
    - `GET /api/media/{media}`
    - `PUT /api/media/{media}`
    - `DELETE /api/media/{media}`
- still open:
  - permission model beyond auth

### Phase 3. Image Processing And Storage

- completed for current flow
- implemented:
  - retain generated original output
  - generate:
    - `16x9-medium.webp`
    - `16x9-big.webp`
    - `4x3-medium.webp`
    - `4x3-big.webp`
  - store under `media/YYYY/MM/DD/{uuid}/...`
  - persist crop metadata and file paths
  - persist per-variant file sizes
  - server-side crop normalization
  - service-side config fallbacks for disk, quality, and sizes
- still open:
  - source-image replacement lifecycle
  - later manual cleanup command/policy using `file_cleanup_marked_at`

### Phase 4. Backend Verification

- mostly completed for MVP
- completed checks:
  - frontend build verification
  - PHP syntax checks in container
  - route verification in container
  - live debugging through Laravel logs
  - Pest feature tests for create, validation, list, filters, tag options, update, and delete marker
- fixed during verification:
  - missing table issue
  - negative crop coordinates from frontend
  - null quality config fallback issue
  - null size config fallback issue
  - out-of-bounds crop handling
  - real upload tag payload issue
  - DB-backed variant size issue
  - list vs detail API contract split
- still open:
  - authorization-specific tests
  - stronger delete/file-lifecycle verification beyond marker semantics
  - more exhaustive crop edge-case coverage

### Phase 5. Frontend Service And State Layer

- completed for current scope
- implemented:
  - `mediaService.ts`
  - `useMediaList.ts`
  - `useMediaForm.ts`
  - shared metadata options composable for categories and tags
  - tag suggestion service
  - edit support
  - delete support

### Phase 6. Frontend View Refactor

- completed for current scope
- implemented:
  - `MediaCreate.vue` uses live create flow
  - `MediaIndex.vue` uses live list flow
  - cropper integrated into create flow
  - `MediaEdit.vue` is backend-backed
  - delete flow is real
  - category and tags are integrated in create/edit/detail/index
  - advanced search supports category and tags

## Not Yet Completed

### Phase 7. Crop Accuracy Validation

- partially completed
- still needed:
  - verify crop results across several real uploads
  - add stronger regression coverage for extreme crop values

Rotation validation was removed from scope because rotation support was removed from the cropper.

### Phase 8. SEO And Publishing Readiness

- field support exists and is live in media CRUD
- broader publishing integration with other content modules is not done

### Phase 9. Hardening And Cleanup

- partially completed
- completed:
  - remove mock-backed media edit flow
  - split list vs detail API contracts
  - persist variant sizes
  - add explicit per-test cleanup instead of global `RefreshDatabase`
- still needed:
  - authorization hardening
  - file cleanup tooling for marked media
  - final doc/status sweep if media becomes a shared dependency for articles or other modules

## Next Recommended Step

1. Add authorization rules/tests for media actions.
2. Decide whether a manual cleanup command should consume `file_cleanup_marked_at`.
3. Add a few targeted crop edge-case tests.
