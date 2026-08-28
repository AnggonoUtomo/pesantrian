# Frontend Module Path Decision

## Status

Accepted untuk pekerjaan baru mulai setelah keputusan ini.

## Konteks

Baseline SakaSantri menetapkan frontend canonical di
`resources/js/modules/<module>/`, dengan isi per module:

```text
resources/js/modules/<module>/
|-- pages/
|-- components/
|-- hooks/
|-- types/
`-- schemas/
```

Source aplikasi yang sudah berjalan belum sepenuhnya mengikuti baseline ini.
Path aktif saat keputusan ini dibuat:

| Capability | Current Inertia component path | Target canonical path |
| --- | --- | --- |
| System dashboard | `resources/js/pages/System/Dashboard.tsx` | `resources/js/modules/access-control/pages/Dashboard.tsx` atau `resources/js/modules/console/pages/Dashboard.tsx` saat consolidation diputuskan |
| AccessControl | `resources/js/pages/System/AccessControl/pages/Index.tsx` | `resources/js/modules/access-control/pages/Index.tsx` |
| UserManagement | `resources/js/pages/System/UserManagement/pages/Index.tsx` dan `Show.tsx` | `resources/js/modules/access-control/pages/users/Index.tsx` dan `Show.tsx` bila digabung ke AccessControl |
| SystemSetting | `resources/js/pages/System/SystemSetting/pages/Index.tsx` | `resources/js/modules/system-setting/pages/Index.tsx` |
| AuditLog | `resources/js/pages/System/AuditLog/pages/Index.tsx` | `resources/js/modules/audit-trail/pages/Index.tsx` |
| Organization | `resources/js/pages/Organization/Organization/pages/Index.tsx` | `resources/js/modules/organization/pages/Index.tsx` |
| AcademicPeriod | belum ada UI web | `resources/js/modules/academic-period/pages/Index.tsx` saat UI dibuat |
| Settings profile/security/appearance | `resources/js/pages/settings/*` | tetap di `resources/js/pages/settings/*` sampai ada keputusan product namespace khusus |
| Auth pages | `resources/js/pages/auth/*` | tetap di `resources/js/pages/auth/*` karena bukan UI module domain |
| Welcome/errors | `resources/js/pages/welcome.tsx`, `resources/js/pages/errors/*` | tetap di `resources/js/pages/*` karena shell/global page |

## Keputusan

1. UI module baru memakai `resources/js/modules/<module>/` sebagai canonical
   path.
2. UI existing di `resources/js/pages/System/*` dan
   `resources/js/pages/Organization/Organization/*` tetap menjadi bridge sampai
   ada work item migrasi frontend per module.
3. Untuk module yang sudah memiliki UI di `resources/js/pages/*`, perubahan
   runtime Inertia tidak boleh dilakukan bersamaan dengan perubahan business
   behavior. Migration path UI harus menjadi increment tersendiri.
4. Komponen business-specific ditempatkan di folder module terkait:
   `resources/js/modules/<module>/components/` untuk UI canonical baru, atau
   bridge existing `resources/js/pages/<Namespace>/<Module>/components/` sampai
   module tersebut dipindah.
5. `resources/js/components/ui` tetap khusus shadcn/ui, sedangkan
   `resources/js/components/shared` hanya untuk komponen yang benar-benar lintas
   module.

## Compatibility Plan Ziggy dan Inertia

Ziggy route name dan Inertia component name adalah kontrak publik frontend.
Karena itu, migration UI wajib mengikuti aturan berikut:

- Route name tidak diganti hanya karena file frontend dipindah.
- URL tidak diganti tanpa compatibility plan dan regression test.
- Permission key backend tetap menjadi authority dan tidak diganti hanya karena
  namespace frontend berubah.
- Jika Inertia component name berubah, controller harus menyediakan bridge
  sementara atau migration dilakukan dalam satu work item terverifikasi dengan
  route smoke dan build frontend.
- Sidebar memakai namespace product untuk grouping menu, tetapi `href` tetap
  berasal dari route name yang sudah ada.
- Import TypeScript internal boleh dipindah bertahap setelah entry page baru
  tersedia dan build lulus.

Contoh compatibility aman:

```text
Route name tetap: organization.units.index
URL tetap: /organization/units
Inertia lama: Organization/Organization/pages/Index
Inertia target: modules/organization/pages/Index
```

Sebelum controller mengganti render target ke path canonical, harus ada
verifikasi minimal:

```bash
npm run types:check
npm run build
php artisan route:list --path=organization/units --no-ansi
```

Jika UI yang dipindah memiliki interaksi penting, tambahkan focused browser
smoke untuk memastikan halaman terbuka, form utama bisa dirender, dan tidak ada
error console kritis.

## Implikasi untuk Increment Berikutnya

- UI baru `Academic/AcademicPeriod` harus dibuat langsung di
  `resources/js/modules/academic-period/`.
- UI `Organization/Organization` boleh tetap di bridge existing sampai ada
  work item khusus `organization-frontend-path-migration`.
- Migration `System -> Console` tetap terpisah dari keputusan ini karena rename
  backend/source module memiliki risiko route, permission, seeder, audit, dan
  test yang lebih luas.
