# 05. Filament Mobile Capture Page Spec

## Context

Previous step expected: capture workflow from [02_PHONE_CAPTURE_AND_ASYNC_INGESTION.md](/home/abuhafi/Project/kutubio/dev-guide/02_PHONE_CAPTURE_AND_ASYNC_INGESTION.md:1) and QR/revision contracts from [04_QR_PAYLOAD_AND_REVISION_SCHEMA.md](/home/abuhafi/Project/kutubio/dev-guide/04_QR_PAYLOAD_AND_REVISION_SCHEMA.md:1) are accepted.

This step turns abstract intake flow into concrete Filament implementation target. Goal is one focused mobile page that feels native enough on phone browser, while still staying inside Laravel + Filament + Livewire architecture.

## Steps

1. Build this as custom Filament page, not generic resource form.
   - Reason: camera lifecycle, mobile states, and staged capture flow are task-driven
   - Keep normal resources for review and CRUD work
2. Use one-page state machine for capture UX.
   - `idle`
   - `camera_requesting`
   - `camera_ready`
   - `front_detecting`
   - `front_captured`
   - `back_detecting`
   - `back_captured`
   - `preview_ready`
   - `submitting`
   - `submitted`
   - `error`
3. Keep page layout mobile-first.
   - Large camera viewport first
   - Status pill over preview
   - One primary action at bottom
   - Retake action always obvious
   - Minimal text during active capture
4. Use browser camera for acquisition, Livewire for session state.
   - Browser handles camera permission and frame capture
   - Client compresses image before upload if possible
   - Livewire stores uploaded blobs or temporary files
   - Server persists capture session
5. Rewire sample PoC logic into production-safe behavior.
   - Keep low-res motion/stability analysis
   - Default to rear camera
   - Auto-capture only after stable threshold
   - Manual shutter fallback always available
   - Stop camera stream when page leaves active capture
6. Separate UI stages clearly.
   - Stage A: capture front cover
   - Stage B: capture back/QR side
   - Stage C: preview and retake
   - Stage D: submit to pipeline
7. Add explicit QR scan feedback.
   - `QR found`
   - `QR not found yet`
   - `QR unreadable, retake suggested`
   - Do not silently continue if QR read is product-critical for that flow
8. Required page actions:
   - `Start Camera`
   - `Capture Now`
   - `Retake Front`
   - `Retake Back`
   - `Submit Book`
   - `Cancel Session`
9. Required visible operator feedback:
   - permission error
   - unstable camera / hold still
   - upload in progress
   - session saved
   - async processing started
10. Keep performance constraints explicit.
   - Do not send full-resolution originals if compressed version is enough for intake
   - Avoid continuous server round-trips during live preview
   - Save network for submit boundary, not per frame
11. Suggested implementation split:
   - Filament custom page class for orchestration
   - Blade view for mobile camera shell
   - small JS module for camera, stability, QR decode, compression
   - Livewire actions for temp upload and final submit
12. Suggested server-side submit contract:
   - validate two images exist
   - validate capture session not already submitted
   - persist raw assets
   - create `raw_capture` revision
   - dispatch detached sequential pipeline
   - return reviewable session reference
13. Keep design-system rules for this page narrow and repeatable.
   - tap targets at least thumb-friendly
   - high-contrast status labels
   - minimal secondary actions
   - no dense admin sidebar distractions during capture if avoidable

## Checks

### Automation

- Add Livewire or feature tests for page submit action success and validation failures.
- Add tests proving double-submit does not create duplicate sessions.
- Add browser-level test coverage later for mobile flow if project adopts Dusk or similar.
- Add tests proving session cannot submit with only one image.
- Add tests proving successful submit dispatches async pipeline and returns session reference.

### Manual

- Test on Android Chrome and iPhone Safari.
- Confirm camera permission flow recovers after denial.
- Confirm rear camera is selected by default where browser supports it.
- Confirm auto-capture does not feel slower than manual capture under normal light.
- Confirm retake path is obvious and fast.
- Confirm page remains usable with one hand and intermittent network.
