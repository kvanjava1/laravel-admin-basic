# Scope And Decisions

## Objective

Build a real media management module to replace the current mock-backed UI for:

- `media.index`
- `media.create`
- `media.edit`
- `media.detail`

Current implemented scope now includes:

- create media
- store original output + generated variants
- list media in index
- show media detail
- edit metadata/category/tags/crops
- soft delete media with cleanup marker
- advanced filter by text, category, and tags

## Approved Decisions

- Keep the generated original output (`original.webp`).
- Store outputs as `webp`.
- Use one media record per uploaded source image.
- Store crop coordinates for each ratio.
- Use a per-media UUID subdirectory under the date path.
- `category_id` is optional.
- Use relational tags via `tags` and `media_tag`.
- Soft delete database record and mark file cleanup timestamp.
- Do not remove physical files on soft delete.
- Edit flow currently supports:
  - SEO metadata updates
  - category updates
  - tag updates
  - recropping the existing source image
- Optional source-image replacement remains future work.

## Output Sizes

- `16:9`
  - medium: `800x450`
  - big: `1600x900`
- `4:3`
  - medium: `800x600`
  - big: `1600x1200`

## Storage Layout

Base path:

- `media/YYYY/MM/DD/{uuid}/`

Files:

- `original.webp`
- `16x9-medium.webp`
- `16x9-big.webp`
- `4x3-medium.webp`
- `4x3-big.webp`

## SEO Fields

- `title`
- `alt_text`
- `caption`
- `description`

## Category Direction

- keep `category_id` nullable
- do not hardcode category-group IDs in frontend code
- category options should be resolved from the `Image` group through backend-driven option sources

## Tag Direction

- tags are backend-backed, not UI-only
- tags autocomplete should come from the `tags` table through an API endpoint
- tags should remain relational, not a JSON field on `media`
