# 04. QR Payload And Revision Schema

## Context

Previous step expected: MVP structure from [01_MVP_FOUNDATION.md](/home/abuhafi/Project/kutubio/dev-guide/01_MVP_FOUNDATION.md:1), capture pipeline from [02_PHONE_CAPTURE_AND_ASYNC_INGESTION.md](/home/abuhafi/Project/kutubio/dev-guide/02_PHONE_CAPTURE_AND_ASYNC_INGESTION.md:1), and review flow from [03_REVIEW_METADATA_AND_PRINT_MVP.md](/home/abuhafi/Project/kutubio/dev-guide/03_REVIEW_METADATA_AND_PRINT_MVP.md:1) are accepted.

This step defines two things that must stay stable early: what QR codes mean, and how metadata history is stored. If these stay vague, capture, review, printing, and future syncing will all drift.

## Steps

1. Choose one QR responsibility for MVP.
   - Preferred: QR identifies `BookCopy`
   - Reason: labels live on physical items, not abstract books
   - Do not overload QR with full metadata payload
2. Keep QR payload compact and durable.
   - Include stable copy identifier
   - Include app namespace or prefix
   - Include version marker
   - Optionally include integrity signature later
3. Use app-generated payload format for MVP.
   - Example shape:
   ```text
   kutubio:copy:v1:{copy_public_id}
   ```
   - Better than raw JSON for small labels
   - Better than temporary URLs for long-term shelf use
4. Separate internal numeric ID from public QR ID.
   - Internal DB `id` can stay private
   - Public QR identifier should be stable and non-guessable enough for operations
   - Use UUID/ULID or other public token strategy
5. Reserve future expansion without breaking old labels.
   - `v1` for current parser
   - Future formats can use `v2`
   - Parser should reject unknown versions clearly
6. Define QR parse outcomes.
   - `valid_known_copy`
   - `valid_unknown_copy`
   - `invalid_format`
   - `unreadable`
   - `missing`
7. Treat QR read as one evidence source, not source of truth alone.
   - Keep captured image
   - Keep decoded text
   - Keep parse result
   - Keep parser version used
8. Create metadata revision model with append-only history.
   - One row per revision event
   - Never overwrite prior revision payload
   - Current approved metadata can be denormalized onto `books` for fast reads if needed
9. Minimum `metadata_revisions` fields for MVP:
   - `id`
   - `book_id`
   - `capture_session_id` nullable
   - `revision_type`
   - `source_stage`
   - `source_actor_type`
   - `source_actor_id` nullable
   - `confidence_score` nullable
   - `payload` JSON
   - `diff_from_previous` JSON nullable
   - `created_at`
10. Recommended revision type values:
   - `raw_capture`
   - `qr_parse`
   - `llm_draft`
   - `metadata_enrichment`
   - `human_reviewed`
   - `system_merge`
11. Recommended payload structure:
   - `title`
   - `authors`
   - `isbn`
   - `publisher`
   - `page_count`
   - `synopsis`
   - `category_code`
   - `qr_payload`
   - `notes`
   - `source_meta`
12. Tag every revision with enough provenance to debug later.
   - model or tool name
   - parser version
   - prompt version if LLM used
   - enrichment command used
   - reviewer identity if manual
13. Define approval rule clearly.
   - `Book` becomes trusted for operations only after `human_reviewed` or equivalent approved revision
   - Earlier revisions remain historical evidence

## Checks

### Automation

- Add tests for QR parser with:
  - valid known copy payload
  - valid unknown copy payload
  - bad prefix
  - bad version
  - malformed token
- Add tests proving revision inserts are append-only.
- Add tests proving approved metadata read model resolves from latest approved revision, not raw draft by accident.
- Add tests proving old QR versions stay parseable or fail with explicit reason.
- Add tests proving print jobs use approved `BookCopy` public QR identifier only.

### Manual

- Print sample QR at target physical size and test scan reliability on real phones.
- Confirm librarians do not need to understand payload internals to use system.
- Confirm revision timeline tells coherent story from raw capture to approved record.
- Confirm payload format still works if school later adds multiple library branches.
