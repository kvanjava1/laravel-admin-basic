# Backend Plan

## Target Architecture

Follow the project pattern:

`Route -> FormRequest -> Controller -> Service -> Repository -> Model`

## Implemented / Planned Components

### Data Layer

- `media` migration
- `App\Models\Media`

Suggested fields already planned/implemented:

- `uuid`
- `category_id`
- `created_by`
- `title`
- `slug`
- `alt_text`
- `caption`
- `description`
- `original_path`
- `ratio_16_9_medium_path`
- `ratio_16_9_big_path`
- `ratio_4_3_medium_path`
- `ratio_4_3_big_path`
- `original_mime_type`
- `output_mime_type`
- `original_size`
- crop coordinate fields for both ratios
- soft deletes
- timestamps

### Configuration

- `config/media.php`
- current service also includes fallback defaults because config availability was inconsistent during runtime verification

### Request Validation

Implemented:

- `StoreMediaRequest`
- `UpdateMediaRequest`

### Repository

Implemented:

- `MediaRepository`

Current scope:

- create
- paginate
- update
- delete

### Service

Implemented:

- `MediaService`

Current responsibilities:

- paginate list
- create media record
- retain generated original output
- generate 4 variants
- normalize crop geometry against image bounds
- build date + UUID storage path
- update flow
- delete flow
- tag synchronization
- file cleanup marking

Still planned:

- replace-image flow
- manual cleanup tooling that consumes `file_cleanup_marked_at`

### Controller

Implemented:

- `MediaController`

Current endpoints:

- `index`
- `store`
- `show`
- `update`
- `destroy`

### Routes

Implemented:

- `GET /api/media`
- `POST /api/media`
- `GET /api/media/{media}`
- `PUT/PATCH /api/media/{media}`
- `DELETE /api/media/{media}`

## Backend Gaps

- no media-specific authorization model yet
- no source-image replacement flow yet
- no manual cleanup command/process for `file_cleanup_marked_at` yet
- crop edge-case coverage is still incomplete
