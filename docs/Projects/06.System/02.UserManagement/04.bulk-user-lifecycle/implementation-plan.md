# Implementation Plan: Bulk Lifecycle User

## Preflight

- Authoritative source: ADR lifecycle user, specification UserManagement, dan baseline mutation frontend `docs/AGENTS.md`.
- Existing code: action single lifecycle, repository, controller, route, audit publisher, dan tabel UserManagement.
- Dependency: `user.delete` serta `user.force.delete` sudah tersedia.
- Rollback trace: increment `04`; tanpa schema atau permission baru.

## Urutan

1. Tambahkan request, result typed, dan Application Action preflight atomik.
2. Tambahkan route bulk sebelum `/{user}`, controller tipis, toast, serta audit.
3. Tambahkan selection, toolbar, dan dialog pada tabel.
4. Jalankan test, lint, type check, validasi module, browser test, dan perbarui evidence.
