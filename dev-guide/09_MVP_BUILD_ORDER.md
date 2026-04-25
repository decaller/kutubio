# 09. MVP Build Order

## Context

Previous step expected: all prior architecture docs in [00_FINDINGS_AND_DECISIONS.md](/home/abuhafi/Project/kutubio/dev-guide/00_FINDINGS_AND_DECISIONS.md:1) through [08_REVIEW_QUEUE_FILAMENT_SPEC.md](/home/abuhafi/Project/kutubio/dev-guide/08_REVIEW_QUEUE_FILAMENT_SPEC.md:1) exist and are directionally stable.

This step turns architecture into execution order. Goal is smallest implementation sequence that produces usable value early, avoids dependency traps, and exposes workflow risk fast.

## Steps

1. Phase 1: schema and seed backbone.
   - create migrations
   - create models
   - import initial category data from [reference/tier2DDC.json](/home/abuhafi/Project/kutubio/dev-guide/reference/tier2DDC.json:1)
   - preserve DDC `number` as a string category code
   - add factories where needed
   - add baseline tests for relations and uniqueness
2. Phase 2: capture session persistence without AI.
   - create `CaptureSession` flow
   - upload front and back images
   - persist raw assets
   - create `raw_capture` revision
   - show pending session in admin
3. Phase 3: mobile capture page.
   - custom Filament page
   - rear camera default
   - auto-capture concept
   - manual fallback
   - retake flow
   - submit flow
4. Phase 4: async orchestration skeleton.
   - starter job
   - validation job
   - terminal failure handling
   - status transitions
   - Horizon queue separation
5. Phase 5: QR parse stage.
   - decode from capture
   - save parser result
   - show QR evidence in review queue
   - do not block whole app on advanced AI yet
6. Phase 6: review queue MVP.
   - triage list
   - detail page
   - manual approval path
   - duplicate suggestion shell
7. Phase 7: LLM extraction.
   - vision extraction job
   - prompt versioning
   - structured output validation
   - provenance tagging
8. Phase 8: metadata enrichment.
   - Calibre integration
   - merge policy
   - enrichment provenance
9. Phase 9: print MVP.
   - QR payload generation
   - label HTML generation
   - Gotenberg rendering
   - print profile settings
10. Phase 10: hardening and operator polish.
   - retries
   - performance tuning
   - duplicate handling improvement
   - better manual tools
   - analytics and queue observability
11. Phase 11: student-data integration using live SurrealDB.
   - connect to SurrealDB through explicit service boundary
   - use JWT signin flow
   - read only needed student / clearance data
   - isolate timeout, retry, and auth refresh behavior
   - avoid coupling intake flow to external availability
12. Keep MVP gate strict after each phase.
   - do not add next automation layer before previous manual fallback works
   - review queue must always stay usable even if AI stages fail
13. Suggested first implementation milestone.
   - librarian captures 2 images on phone
   - session saved
   - review queue shows session
   - reviewer manually creates approved book and copy
   - QR label prints
   - this milestone already proves core loop

## Checks

### Automation

- Keep tests green per phase before starting next phase.
- Add targeted tests for each phase instead of waiting for full-suite coverage late.
- Track migration, queue, and review actions with feature tests first.
- Add end-to-end smoke coverage once core loop exists.

### Manual

- Validate each phase with real phone and real librarian workflow assumptions.
- Stop and adjust if one phase introduces too much hidden complexity before moving on.
- Do not let AI integration start before manual review loop is already operational.
- Treat first working end-to-end manual flow as release gate for continuing automation.
- Treat external SurrealDB work as separate phase after core library loop is stable.
