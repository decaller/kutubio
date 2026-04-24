# 08. Review Queue Filament Spec

## Context

Previous step expected: review responsibilities from [03_REVIEW_METADATA_AND_PRINT_MVP.md](/home/abuhafi/Project/kutubio/dev-guide/03_REVIEW_METADATA_AND_PRINT_MVP.md:1), revision rules from [04_QR_PAYLOAD_AND_REVISION_SCHEMA.md](/home/abuhafi/Project/kutubio/dev-guide/04_QR_PAYLOAD_AND_REVISION_SCHEMA.md:1), and model plan from [06_DOMAIN_MODEL_AND_TABLE_PLAN.md](/home/abuhafi/Project/kutubio/dev-guide/06_DOMAIN_MODEL_AND_TABLE_PLAN.md:1) are accepted.

This step defines core librarian review workspace inside Filament. Goal is one practical page where operator can inspect evidence, trust provenance, fix weak draft fields, and approve item without bouncing across multiple admin screens.

## Steps

1. Build review queue as task-first Filament page or resource flow.
   - Default landing area for draft intake
   - Prioritize unresolved records
   - Avoid generic CRUD-heavy layout
2. Main review list must surface only high-signal columns.
   - capture session reference
   - thumbnail status
   - draft title
   - QR parse status
   - confidence indicator
   - pipeline failure status
   - submitted time
3. Support queue filters that match real librarian triage.
   - `needs_review`
   - `failed`
   - `low_confidence`
   - `qr_missing`
   - `possible_duplicate`
   - `recently_submitted`
4. Review detail should stay single-task and evidence-first.
   - front image visible
   - back image visible
   - decoded QR payload visible
   - pipeline notes visible
   - extracted fields editable
5. Make provenance visible next to editable fields.
   - source badge per field:
     - `raw`
     - `qr`
     - `llm`
     - `enrichment`
     - `manual`
   - confidence score visible where meaningful
   - conflicting values called out, not hidden
6. Keep edit form short and operational.
   - title
   - authors display
   - isbn13
   - publisher
   - page count
   - synopsis
   - category
   - copy status or copy creation intent if relevant
7. Add duplicate-resolution affordance inside same review flow.
   - show likely matches
   - allow operator to:
     - merge into existing `Book`
     - keep separate
     - defer
8. Required operator actions:
   - `Approve Draft`
   - `Save Edits`
   - `Mark Needs Retake`
   - `Merge Into Existing Book`
   - `Retry Processing`
   - `Reject Session`
9. Approval action must do explicit writes only.
   - append `human_reviewed` revision
   - link approved revision to canonical `Book`
   - create or confirm `BookCopy` if physical copy should exist
   - mark session resolved
10. Failed items must remain actionable.
   - operator can still manually create approved metadata from evidence
   - operator can still trigger controlled retry where safe
11. Keep UI density lower on mobile, richer on desktop.
   - desktop can show split evidence/editor view
   - mobile can stack evidence above form
   - do not assume reviewer always on desktop
12. Add audit-friendly side panel or infolist.
   - revision timeline
   - source tools used
   - processing timestamps
   - acting user
13. Design language should reduce uncertainty fast.
   - strong status color semantics
   - obvious primary action
   - avoid decorative noise
   - show progress of queue workload

## Checks

### Automation

- Add tests for review queue filters and status segmentation.
- Add tests proving approval action appends `human_reviewed` revision.
- Add tests proving merge action attaches approved data to existing `Book` safely.
- Add tests proving failed sessions remain visible and actionable.
- Add tests proving unapproved drafts cannot bypass review and print directly.

### Manual

- Confirm reviewer can resolve one normal draft without leaving review screen.
- Confirm provenance badges are understandable without training jargon.
- Confirm low-confidence and failure items stand out immediately in queue.
- Confirm mobile stacked layout still workable if librarian reviews from phone.
