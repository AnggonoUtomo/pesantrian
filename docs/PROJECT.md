# Project SakaSantri

## Status

Draft baseline aktif untuk pengembangan awal.

## Masalah

Pesantren membutuhkan satu platform operasional yang menyatukan administrasi
yayasan, unit pendidikan, santri, wali, SDM, akademik, asrama, keuangan,
dokumen, komunikasi, pelaporan, dan audit tanpa memecah sistem menjadi banyak
aplikasi kecil.

## Tujuan

SakaSantri menjadi **Pesantren Operations & Management Platform** untuk satu
yayasan dengan banyak unit. Aplikasi harus dapat berkembang bertahap,
mempertahankan ownership data per module, dan menjaga jejak historis aktivitas
operasional.

## Pengguna

- Pengurus yayasan dan pimpinan pesantren.
- Admin unit pendidikan atau asrama.
- Guru, ustadz, musyrif, dan staff operasional.
- Staff keuangan dan administrasi.
- Wali atau actor eksternal hanya jika capability terkait sudah diputuskan.

## Scope Aktif

- Foundation Laravel 13, Fortify, Inertia React, TypeScript, Tailwind, Ziggy,
  shadcn/ui, Framer Motion, Spatie Permission, Spatie Media Library.
- Module loader, module generator, custom module stubs, Shared Kernel,
  convention event, CQRS pragmatis, dan testing convention.
- Release awal: AccessControl, Organization, SystemSetting, AcademicPeriod,
  HumanResource, Student, Guardian, Dormitory, Academic, StudentFinance,
  Document, Announcement, Notification, Reporting, AuditTrail.

## Di Luar Scope Awal

- SaaS multi-tenant.
- Microservices.
- Laravel Boost dan Laravel Wayfinder.
- Payroll, procurement, inventory, asset, POS/koperasi, laundry, klinik,
  perpustakaan, donasi/wakaf, payment gateway, public API penuh, BI kompleks,
  dan AI assistant.

Capability tersebut dapat menjadi phase ekspansi setelah foundation dan data
operasional cukup matang.

## Stack dan Constraint

- Primary database: MySQL.
- Primary identifier table aplikasi: ULID.
- Routing frontend: Ziggy dengan Laravel named routes.
- Auth: Laravel Starter Kit + Fortify.
- RBAC: Spatie Permission.
- Media/attachment: Spatie Media Library sebagai adapter.
- Backend adalah security authority; frontend permission hanya untuk UX.
- Unit pendidikan/asrama adalah data organisasi, bukan alasan membuat module
  Laravel baru.
- Data santri, wali, pegawai, dan keuangan diperlakukan sebagai data sensitif.

## Kriteria Keberhasilan

- Capability release awal dapat dibangun incremental tanpa direct mutation
  lintas module.
- Setiap module memiliki ownership data, permission, route, migration, dan
  ServiceProvider yang jelas.
- Table aplikasi memakai ULID primary identifier dan foreign ULID kompatibel.
- Perubahan penting memiliki test/verifikasi yang proporsional serta dokumentasi
  contract atau keputusan yang terdampak.
- Audit trail, authorization, dan historical traceability menjadi bagian dari
  desain module, bukan tambahan belakangan.

## Pertanyaan Terbuka

- Detail UX, role, permission, dan workflow tiap module diputuskan saat module
  specification dibuat.
- API publik belum menjadi scope awal kecuali ada keputusan produk terpisah.
