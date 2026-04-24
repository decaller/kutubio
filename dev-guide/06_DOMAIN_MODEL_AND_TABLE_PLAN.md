# 06. Domain Model And Table Plan

## Context

Previous step expected: entity direction from [01_MVP_FOUNDATION.md](/home/abuhafi/Project/kutubio/dev-guide/01_MVP_FOUNDATION.md:1), revision contract from [04_QR_PAYLOAD_AND_REVISION_SCHEMA.md](/home/abuhafi/Project/kutubio/dev-guide/04_QR_PAYLOAD_AND_REVISION_SCHEMA.md:1), and mobile capture contract from [05_FILAMENT_MOBILE_CAPTURE_PAGE_SPEC.md](/home/abuhafi/Project/kutubio/dev-guide/05_FILAMENT_MOBILE_CAPTURE_PAGE_SPEC.md:1) are accepted.

This step converts architecture into Laravel model and migration targets. Goal is stable schema for MVP with room for later circulation, SIS sync, and bulk intake features without immediate redesign.

## Steps

1. Create canonical domain models for MVP.
   - `Book`
   - `BookCopy`
   - `Category`
   - `CaptureSession`
   - `MetadataRevision`
   - `PrintProfile`
2. Keep `Book` focused on approved bibliographic truth.
   - Suggested columns:
     - `id`
     - `public_id`
     - `title`
     - `subtitle` nullable
     - `isbn13` nullable
     - `publisher` nullable
     - `page_count` nullable
     - `synopsis` nullable
     - `category_id` nullable
     - `approved_metadata_revision_id` nullable
     - timestamps
3. Avoid stuffing multi-value authors into one brittle string long-term.
   - MVP options:
     - short-term `authors_display` text on `books`
     - later normalize to `authors` + pivot
   - If speed matters, start with `authors_display` and record full array inside revision payload
4. Keep `BookCopy` as physical operational record.
   - Suggested columns:
     - `id`
     - `public_id`
     - `book_id`
     - `tracking_code` nullable if later needed
     - `qr_payload`
     - `status`
     - `location_note` nullable
     - `acquired_at` nullable
     - timestamps
5. Define `BookCopy` status for MVP minimally.
   - `draft`
   - `available`
   - `processing`
   - `lost`
   - `archived`
6. Keep `Category` database-driven and cacheable.
   - Suggested columns:
     - `id`
     - `code`
     - `label`
     - `short_label` nullable
     - `color` nullable
     - `sort_order` nullable
     - `source_version` nullable
     - timestamps
   - Index `code` unique
7. Use `CaptureSession` as intake work envelope.
   - Suggested columns:
     - `id`
     - `public_id`
     - `submitted_by` nullable
     - `status`
     - `front_image_path`
     - `back_image_path`
     - `front_image_meta` JSON nullable
     - `back_image_meta` JSON nullable
     - `decoded_qr_payload` nullable
     - `qr_parse_status` nullable
     - `failure_reason` nullable
     - `submitted_at` nullable
     - `processing_started_at` nullable
     - `processing_finished_at` nullable
     - timestamps
8. Use `MetadataRevision` as append-only evidence log.
   - Suggested columns:
     - `id`
     - `book_id` nullable
     - `capture_session_id` nullable
     - `revision_type`
     - `source_stage`
     - `source_actor_type` nullable
     - `source_actor_id` nullable
     - `confidence_score` nullable
     - `payload` JSON
     - `diff_from_previous` JSON nullable
     - `source_meta` JSON nullable
     - timestamps
9. Keep `PrintProfile` simple but real.
   - Suggested columns:
     - `id`
     - `name`
     - `page_width_mm`
     - `page_height_mm`
     - `grid_columns`
     - `grid_rows`
     - `offset_x_mm`
     - `offset_y_mm`
     - `slot_width_mm`
     - `slot_height_mm`
     - `is_default`
     - timestamps
10. Add indexes where MVP will actually query.
   - `books.public_id` unique
   - `books.isbn13` index
   - `book_copies.public_id` unique
   - `book_copies.qr_payload` unique
   - `categories.code` unique
   - `capture_sessions.public_id` unique
   - `capture_sessions.status` index
   - `metadata_revisions.book_id, created_at` composite index
   - `metadata_revisions.capture_session_id, created_at` composite index
11. Keep model relationships explicit.
   - `Book` hasMany `BookCopy`
   - `Book` belongsTo `Category`
   - `Book` hasMany `MetadataRevision`
   - `Book` belongsTo `approvedMetadataRevision`
   - `CaptureSession` hasMany `MetadataRevision`
   - `MetadataRevision` belongsTo `Book`
   - `MetadataRevision` belongsTo `CaptureSession`
12. Use casts aggressively for correctness.
   - JSON columns cast to arrays
   - date/time columns cast to datetimes
   - confidence score cast numerically
13. Keep destructive edits out of history tables.
   - No updating old `MetadataRevision` payload rows
   - No repurposing `CaptureSession` rows for new uploads
14. Favor forward-safe schema choices.
   - `public_id` everywhere user-facing
   - nullable fields where pipeline fills progressively
   - avoid premature polymorphic complexity unless needed

## Checks

### Automation

- Add migration tests or feature tests proving schema constraints exist for unique public IDs and QR payloads.
- Add tests for relationship integrity:
  - one `Book` to many `BookCopy`
  - one `CaptureSession` to many `MetadataRevision`
- Add tests proving approved metadata revision can be resolved efficiently from `Book`.
- Add tests proving duplicate QR payload insertion fails fast.
- Add tests proving category lookup by `code` remains unique and cache-safe.

### Manual

- Review every table and ask: is this real domain object or only temporary convenience.
- Confirm no table duplicates same truth in two mutable places without explicit reason.
- Confirm schema still works if one capture session fails before a `Book` exists.
- Confirm print profile design matches actual sticker paper constraints before coding UI.
