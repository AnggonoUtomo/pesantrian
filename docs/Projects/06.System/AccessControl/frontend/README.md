# Frontend AccessControl

Dokumentasi ini menjadi acuan untuk membuat UI AccessControl pada starter kit.
Pola visual diadaptasi dari `FrontendContoh/access-control`.

## Tujuan

Pengguna yang berwenang dapat melihat role, memilih role, melihat permission
berdasarkan module, dan mengubah permission role sesuai hak aksesnya.

## Dokumen

1. [Specification](specification.md)
2. [Implementation plan](implementation-plan.md)
3. [Tasks](tasks.md)

## Struktur frontend

```text
resources/js/pages/System/AccessControl/
|-- components/
|   |-- access-control-header.tsx
|   |-- access-control-shortcut-panel.tsx
|   |-- add-role-dialog.tsx
|   |-- delete-role-dialog.tsx
|   |-- empty-role-workspace.tsx
|   |-- permission-module-panel.tsx
|   |-- role-control-card.tsx
|   |-- role-permission-workspace.tsx
|   `-- summary-box.tsx
|-- pages/
|   `-- Index.tsx
|-- schemas.ts
|-- types.ts
`-- index.ts
```

## Status

Dokumentasi selesai. Coding frontend dan browser verification belum dimulai.
