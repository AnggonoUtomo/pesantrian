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

Frontend, mutation role, browser verification, dan accessibility check selesai.
Baseline visual dashboard memakai background bersih dengan accent semantic dan
glow ringan pada icon, badge, progress, serta garis card. Backend tetap menjadi
security authority untuk seluruh mutation. OPEN RISK styling, preload font, dan
format check global sudah ditutup. Baseline ini juga berlaku untuk halaman
AccessControl dan module System berikutnya.
Card module dan subcard memakai surface bone white pada light serta charcoal
netral pada dark, seperti pola Dashboard Shell 01. Main card memakai radius
besar dan subcard radius sedang. Warna theme tetap dipakai pada icon, badge,
grafik, progress, garis card, dan state interaksi.
