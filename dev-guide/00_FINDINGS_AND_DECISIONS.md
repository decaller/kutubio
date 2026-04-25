# 00. Findings And Decisions

## Context

Previous step: none. This is baseline document for current direction.

This project is now defined as a phone-browser-first smart school library system built inside current Laravel monolith already installed in this repo. This file records decisions already made, constraints discovered during analysis, and non-negotiable product assumptions before MVP build starts.

## Steps

1. Lock stack to current install, not older draft:
   - Laravel `13`
   - Filament `5`
   - Livewire `4`
   - Flux `2`
   - Horizon `5`
   - PHP `^8.3`
2. Keep system as Laravel monolith.
   - No separate Python service
   - No microservice split for MVP
   - AI calls happen from PHP
3. Make app fully usable from phone browser.
   - Primary intake flow must run inside mobile web UI
   - Camera access must use browser APIs
   - UI must be designed for one-hand use and short task cycles
4. Replace old scanner/barcode direction.
   - Use camera
   - Use QR code only
   - Do not plan around USB scanners or Code128 barcodes
5. Treat sample camera code as proof of interaction pattern, not production code.
   - Low-cost stability detection is useful
   - Rear camera must be default
   - Need retake, cancel, permission recovery, and upload state
   - Need Livewire or Filament-compatible state wiring
6. Define ingestion pipeline precisely.
   - Detached async pipeline
   - Independent per book
   - Sequential per pipeline
   - Do not call it branching unless real fan-out and fan-in are implemented
7. Version metadata at every major checkpoint.
   - Before LLM
   - After LLM
   - After manual HITL review
   - Every revision should be tagged, attributable, and reviewable
8. Keep student syncing out of MVP detail for now.
   - Only note future integration point
   - Avoid locking design too early
9. Student-data integration source is now partially known.
   - External SurrealDB already exists
   - SurrealDB instance is already up and usable by app
   - Connection pattern is HTTP/JWT-based, not local relational join
   - Current loaded graph uses SurrealDBV2 reference database `school_graph_v1`
   - Current loaded tables include school and student-related operational data
   - Library module should treat this as external system-of-record input, not primary app database
10. Start DDC as imported data, not hardcoded enum.
   - Initial source is [reference/tier2DDC.json](/home/abuhafi/Project/kutubio/dev-guide/reference/tier2DDC.json:1)
   - Use the two-digit/tens DDC tier as the first category seed
   - Treat `number` as the category code and `name` as the display label
   - Later load into categories table
   - Cache heavily
11. Preserve pragmatic print stack.
   - Gotenberg stays good fit for label rendering
   - Print layout should stay raw HTML/CSS in physical units

## Checks

### Automation

- Confirm installed versions from [composer.json](/home/abuhafi/Project/kutubio/composer.json:1) match this document.
- Keep future architecture docs aligned with these same package majors.
- Keep SurrealDB connection specifics in [reference/surrealDB-connection.md](/home/abuhafi/Project/kutubio/dev-guide/reference/surrealDB-connection.md:1), with detailed V2 schema and query docs under [reference/SurrealDBV2/README.md](/home/abuhafi/Project/kutubio/dev-guide/reference/SurrealDBV2/README.md:1).

### Manual

- Reject new feature ideas that assume desktop-first workflow unless explicitly intentional.
- Reject new docs that reintroduce barcode scanners, Code128, or Python sidecar services without deliberate architecture change.
- Confirm team agrees QR payload strategy still needs explicit spec before implementation.
- Confirm future student sync work treats SurrealDB as external dependency with network/auth/retry concerns.
