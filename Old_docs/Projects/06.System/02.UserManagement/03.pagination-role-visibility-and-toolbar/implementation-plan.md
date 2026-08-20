# Implementation Plan: Pagination, Role Efektif, dan Toolbar User

## Preflight

| Item | Evidence |
| --- | --- |
| Source | `AGENTS.md`, `docs/AGENTS.md`, ADR-0004, filter increment 01, dan source UserManagement. |
| Existing contract | `UserListFilter`, `ListUsers`, `UserRepository::list()`, controller index, dan `UserTable`. |
| Dependency | Filter server-side selesai; AccessControl hanya menyediakan catalog/role public. |
| Rollback | Commit per task setelah test dan browser verification. |

## Urutan

### Task 01 — Pagination server-side

Tulis test request invalid dan kombinasi filter/page. Ubah DTO query, read
contract, adapter Eloquent, controller, type frontend, dan tabel. Verifikasi
focused test, lint, type check, build, module validation, dan browser.

### Task 02 — Role efektif

Dimulai setelah Task 01 lulus. Perluas read model secara additive, eager load
role, tampilkan badge dan detail, lalu verifikasi N+1/contract/browser.

### Task 03 — Toolbar dan shortcut

Dimulai setelah Task 02 lulus. Rapikan operasi filter/pagination, tambahkan
 shortcut yang aman, dan uji keyboard serta mobile.
