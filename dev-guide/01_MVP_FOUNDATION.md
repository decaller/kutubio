# 01. MVP Foundation

## Context

Previous step expected: decisions from [00_FINDINGS_AND_DECISIONS.md](/home/abuhafi/Project/kutubio/dev-guide/00_FINDINGS_AND_DECISIONS.md:1) are accepted.

This step defines minimum backend and admin foundation needed before camera capture and AI intake. Goal is stable domain model, revision strategy, queue boundaries, and Filament admin structure that can absorb later workflow complexity without rewrite.

## Steps

1. Define core entities for MVP.
   - `Book`
   - `BookCopy`
   - `Category`
   - `MetadataRevision`
   - `CaptureSession`
2. Split `Book` and `BookCopy` cleanly.
   - `Book` stores canonical bibliographic data
   - `BookCopy` stores physical ownership and circulation identity
   - QR code should identify physical copy strategy explicitly before print rollout
3. Introduce metadata revision model early.
   - `raw_capture`
   - `llm_draft`
   - `human_reviewed`
   - Store source tag, actor, timestamp, confidence, payload snapshot
4. Keep category source flexible.
   - Start from [reference/tier2DDC.json](/home/abuhafi/Project/kutubio/dev-guide/reference/tier2DDC.json:1)
   - Import `number` into `categories.code`
   - Import `name` into `categories.label`
   - Import into `categories` table
   - Cache read paths aggressively
   - Allow display metadata changes from Filament later
5. Create status model for ingestion lifecycle.
   - `pending_capture`
   - `captured`
   - `processing`
   - `needs_review`
   - `approved`
   - `failed`
6. Define queue responsibilities up front.
   - External API calls must be queued
   - Metadata enrichment must be queued
   - Long-running print preparation must be queued if added later
7. Plan Filament admin around task-oriented pages, not generic CRUD only.
   - Review Queue
   - Capture Sessions
   - Books
   - Copies
   - Categories
   - Print Settings
8. Keep MVP authorization simple but real.
   - Admin/librarian access only
   - Policies on mutable records
   - Audit trail on approval actions
9. Keep student-data integration outside MVP core schema.
   - Do not couple core library tables directly to SurrealDB structure yet
   - Prefer explicit integration boundary such as sync/import or service layer
   - SurrealDB can be used when student lookup or clearance work starts
   - Core library writes should still not depend on SurrealDB table structure
10. Set design-system baseline for mobile-first admin flows.
   - Large tap targets
   - Minimal form density
   - Clear progress states
   - Strong camera-state feedback

## Checks

### Automation

- Add feature tests for core model creation and status transitions once models exist.
- Add tests proving a `Book` can own many `BookCopy` records without duplicating metadata.
- Add tests proving metadata revisions append history instead of overwriting prior payloads.
- Add tests proving categories load from database and remain cache-safe after update.

### Manual

- Review whether each planned model has one job only. If model exists only to compensate for unclear workflow, cut it.
- Verify Filament navigation groups match librarian mental model, not developer table names.
- Confirm no MVP feature depends on unfinished student-sync design.
- Confirm future SurrealDB integration can fail or lag without breaking intake, review, or printing flows.
