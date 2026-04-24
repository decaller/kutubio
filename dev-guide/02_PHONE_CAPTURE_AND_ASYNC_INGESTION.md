# 02. Phone Capture And Async Ingestion

## Context

Previous step expected: core entities, statuses, and revision strategy from [01_MVP_FOUNDATION.md](/home/abuhafi/Project/kutubio/dev-guide/01_MVP_FOUNDATION.md:1) are in place.

This step implements core librarian workflow: capture book data from phone browser, store raw evidence, and hand work off to detached async processing. This is main MVP loop. If this flow feels slow or fragile, rest of system will suffer.

## Steps

1. Build phone-first capture page in Filament or Livewire-backed custom page.
   - Open from mobile browser
   - Default to rear camera
   - Full-screen or near-full-screen capture UI
   - Minimal distractions
2. Rewire sample camera concept into app architecture.
   - Keep local stability detection concept
   - Add manual shutter fallback
   - Add retake flow
   - Add permission denied recovery
   - Add upload progress and failure retry
3. Make capture flow explicit and short.
   - Step 1: capture front cover
   - Step 2: capture back cover or QR-side image
   - Step 3: preview both images
   - Step 4: submit session
4. Add QR-only scan behavior.
   - Detect QR from live stream or captured image
   - If QR found, save decoded payload with capture evidence
   - If QR not found, allow fallback retake and manual continuation rules
5. Store raw capture before any AI work.
   - Original images
   - Decoded QR payload if found
   - Device/browser metadata if useful
   - Capture timestamps
   - Initial `raw_capture` metadata revision
6. Dispatch one detached async pipeline per submitted session.
   - Web request returns immediately
   - Each book session processes independently
   - Steps remain sequential inside one pipeline
7. Use sequential pipeline stages for MVP.
   - Stage A: validate capture assets and parse QR
   - Stage B: run vision extraction if still needed
   - Stage C: run metadata enrichment
   - Stage D: persist normalized `llm_draft` revision and mark `needs_review`
8. Add guardrails before LLM.
   - Blur check
   - Exposure or glare check
   - File presence check
   - Duplicate session protection
9. Treat failures as first-class outcomes.
   - Partial failure must not lose source images
   - Session should become actionable in review queue
   - Operators must know whether failure was QR, image quality, LLM, or enrichment
10. Keep wording exact in implementation docs and code comments.
   - Detached async pipeline
   - Independent per book
   - Sequential per pipeline
   - Fan-out/fan-in only if later intentionally added

## Checks

### Automation

- Add tests for capture session creation with two uploaded images.
- Add tests that queue dispatch happens after submit and request returns without running AI inline.
- Add tests that one submitted session creates one pipeline, not duplicates.
- Add tests for each stage status transition:
  - `captured` -> `processing`
  - `processing` -> `needs_review`
  - `processing` -> `failed`
- Add tests for QR parse success and QR parse miss.
- Add tests for duplicate-submission protection.

### Manual

- Test on real phone browser, not desktop emulation only.
- Confirm rear camera opens by default on supported devices.
- Confirm user can complete one book in under practical desk-time target.
- Confirm glare, shaky hand, and poor connection still leave recoverable session.
- Confirm capture UI remains legible one-handed and under bright room light.
