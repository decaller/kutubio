# 07. Queue Job Map

## Context

Previous step expected: detached pipeline rules from [02_PHONE_CAPTURE_AND_ASYNC_INGESTION.md](/home/abuhafi/Project/kutubio/dev-guide/02_PHONE_CAPTURE_AND_ASYNC_INGESTION.md:1), revision contract from [04_QR_PAYLOAD_AND_REVISION_SCHEMA.md](/home/abuhafi/Project/kutubio/dev-guide/04_QR_PAYLOAD_AND_REVISION_SCHEMA.md:1), and schema plan from [06_DOMAIN_MODEL_AND_TABLE_PLAN.md](/home/abuhafi/Project/kutubio/dev-guide/06_DOMAIN_MODEL_AND_TABLE_PLAN.md:1) are accepted.

This step defines concrete Horizon workload boundaries. Goal is clear async ownership, retry behavior, failure states, and idempotent handoffs. Pipeline must stay detached from web request, independent per book, and sequential per pipeline.

## Steps

1. Use one pipeline per submitted `CaptureSession`.
   - Submission request persists session
   - Submission request dispatches first job only
   - Each job triggers next stage on success
2. Suggested MVP job chain:
   - `StartCaptureSessionProcessingJob`
   - `ValidateCaptureAssetsJob`
   - `DecodeQrFromCaptureJob`
   - `ExtractBookDataWithVisionJob`
   - `EnrichBookMetadataJob`
   - `PersistDraftMetadataJob`
   - `MarkCaptureSessionReadyForReviewJob`
3. Keep first job very small.
   - Lock session for processing
   - Set `processing_started_at`
   - Move status to `processing`
   - Dispatch next job
4. Validate before expensive work.
   - front image exists
   - back image exists
   - session not already completed
   - session not already failed terminally
   - image metadata sane enough to continue
5. Decode QR as dedicated stage.
   - Save decode result even if empty or invalid
   - Append revision or source log if needed
   - Do not crash whole chain for unreadable QR if flow can continue with vision fallback
6. Run vision extraction as separate external-call job.
   - Use explicit timeout
   - Use retry with backoff
   - Record model name and prompt version in source metadata
   - Prefer queue middleware or rate limiting for provider calls
7. Run metadata enrichment as separate job.
   - Use Calibre CLI or other approved local enrichment source
   - Record command version or enrichment source in metadata
   - Merge with prior stage, do not overwrite blindly
8. Persist draft as explicit write stage.
   - Create or update draft `Book`
   - Create append-only revisions
   - Resolve duplicate candidates if simple automatic rule safe
   - Leave ambiguous duplicates for manual review
9. End with review-ready stage.
   - Set `CaptureSession` status to `needs_review`
   - Set `processing_finished_at`
   - Persist actionable failure summary if degraded
10. Add terminal failure path.
   - Every job should implement `failed()`
   - On terminal failure:
     - keep source assets
     - set session `failed`
     - record stage and reason
     - keep item visible in operator queue
11. Make jobs idempotent.
   - Re-running same job should not create duplicate books, copies, or revisions accidentally
   - Check session status and prior artifacts before writing
12. Use uniqueness where duplicate dispatch risk exists.
   - Session processing starter should be unique per `capture_session_id`
   - Avoid unique locks on later jobs if they depend on controlled trigger path only
13. Suggested queue segmentation for Horizon:
   - `default` for lightweight orchestration
   - `ai` for vision calls
   - `metadata` for enrichment
   - `print` later for rendering tasks
14. Set retry rules intentionally.
   - network/API failures: retry with exponential backoff
   - malformed user capture: fail fast
   - unrecoverable parser errors: fail fast with actionable reason
15. Record observability data from start.
   - session ID
   - stage name
   - attempt count
   - duration
   - external provider or command used
16. Do not use batches unless workflow becomes true fan-out/fan-in later.
   - Current pipeline is sequential
   - Chain-like handoff is easier to reason about for MVP

## Checks

### Automation

- Add tests proving submit dispatches only first job.
- Add tests proving each job triggers next expected job on success.
- Add tests proving duplicate dispatch of starter job does not process same session twice.
- Add tests proving failed stage marks session with terminal reason and preserves source artifacts.
- Add tests proving rerun of persistence job does not duplicate revisions or copies unexpectedly.
- Add tests proving external-call jobs have explicit timeout and retry policy.

### Manual

- Review queue should show enough failure detail that operator knows whether to retake, retry, or manually edit.
- Confirm Horizon queues are separated enough to stop AI congestion from blocking light orchestration.
- Confirm team can trace one session across all jobs from logs without guesswork.
- Confirm no job silently mutates approved data during draft processing.
