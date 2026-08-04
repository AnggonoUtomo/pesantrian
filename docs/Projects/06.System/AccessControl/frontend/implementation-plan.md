# Implementation Plan Frontend AccessControl

## Urutan

1. Inventory route, layout, component UI, type, dan Ziggy yang sudah tersedia.
2. Buat contract props dan type ULID string.
3. Buat page `System/AccessControl/Index.tsx`.
4. Buat header, shortcut panel, role card, permission panel, summary, empty
   state, dan dialog.
5. Hubungkan action dengan route Ziggy dan Inertia form.
6. Tambahkan loading, error, success, protected role, dan read-only state.
7. Jalankan TypeScript check, build, browser test, dan accessibility scan.
8. Tinjau checklist dan dokumentasi sebelum module dinyatakan selesai.

## Adaptasi dari FrontendContoh

Nama komponen dari contoh dipertahankan sebagai pola karena mudah ditelusuri,
tetapi data disesuaikan:

| Contoh | Implementasi project |
|---|---|
| `RoleOption.id: number` | `RoleOption.id: string` ULID |
| permission `roles.manage` | `access_control.role.manage` |
| `auth.super` | `auth.superSystem` |
| URL literal access control | route Ziggy bernama |
| state lokal tanpa server error | Inertia form errors dan processing |

## Verification command

```bash
npm run types:check
npm run build
php artisan test
```

Browser verification menggunakan Chrome DevTools MCP dan accessibility check
yang tersedia pada project.

## Execution evidence terbaru

- `RoleController` memiliki middleware untuk view, dashboard, create, update,
  dan delete.
- `AddRoleDialog` dan `DeleteRoleDialog` memakai route Ziggy serta Inertia
  mutation. Field nama role memiliki label, validasi server, processing state,
  dan error alert.
- Role `SuperSystem` tidak dapat dihapus atau diubah permission-nya. UI hanya
  membantu visibility; middleware dan policy tetap memutuskan akses.
- Full test suite `php artisan test` lulus dengan 113 test dan 374 assertion.
- `npm run types:check`, `npm run lint:check`, `npm run build`, dan
  `git diff --check` lulus.
- Browser mobile 375x812, dialog tambah role, protected state, console, dan
  Lighthouse sudah ditinjau. Lighthouse mobile mendapat skor 100 untuk
  accessibility, best practices, SEO, dan agentic browsing.
- `vite.config.ts` memisahkan dependency vendor sehingga build tidak lagi
  menghasilkan warning chunk di atas 500 kB.
