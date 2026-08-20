# Alur Frontend AuditLog

## 1. Membuka Halaman

Menu sidebar dan command palette hanya menampilkan Audit Log jika authorization
context memiliki permission `audit_log.view` atau actor adalah `SuperSystem`.

```text
user memilih Audit Log
    -> Ziggy route system.audit-logs.index
    -> backend memeriksa auth, middleware, policy, dan query scope
    -> Inertia mengirim auditLogs, filters, auth, dan errors
    -> Index.tsx memakai SystemDashboardLayout
```

Visibility frontend membantu UX, tetapi tidak menggantikan authorization
backend.

## 2. Struktur Halaman

`Index.tsx` menyusun halaman berikut:

```text
SystemDashboardLayout
    -> header Audit Log dan badge append-only
    -> AuditLogSummaryCards
    -> workspace audit
       -> informasi shortcut
       -> AuditLogFilterBar
       -> state error filter
       -> AuditLogTable
    -> AuditLogDetailDialog
```

Komponen memakai baseline `dashboard-card`, `dashboard-subcard`,
`dashboard-badge`, dan token pada `resources/css/app.css`. Light/dark serta
palette aktif tidak dibuat dengan warna surface hardcode baru.

## 3. Filter dan Pagination

`AuditLogFilterBar` menyimpan input pencarian, module, action, tanggal mulai,
dan tanggal selesai. Submit serta reset memakai Inertia router dan route Ziggy
`system.audit-logs.index`.

```text
isi filter
    -> submit
    -> loading aktif
    -> router.get(route Ziggy, filter)
    -> backend memvalidasi AuditLogFilterRequest
    -> query mengembalikan halaman sesuai scope actor
    -> Inertia memperbarui props
    -> loading selesai
```

Pagination mempertahankan filter URL saat pindah halaman. Error validasi
ditampilkan sebagai pesan dengan `role="alert"`.

## 4. Tabel, Kartu Mobile, dan Empty State

Pada desktop, record ditampilkan sebagai tabel. Pada layar kecil, record
ditampilkan sebagai kartu agar tidak bergantung pada horizontal scroll.

Setiap record menampilkan waktu, actor, action, module, subject, dan correlation
ID. Tombol `Lihat` membuka detail pada dialog. Jika hasil kosong, halaman
menampilkan empty state dan saran untuk mengubah filter.

## 5. Dialog Detail

`AuditLogDetailDialog` menerima record yang dipilih dari state page. Dialog
menampilkan identity record dan metadata yang sudah disaring backend.

Frontend tidak mencoba membuka metadata asli atau data sensitif. Dialog juga
menjelaskan bahwa record hanya dapat dibaca dan tidak dapat diubah.

## 6. Shortcut Keyboard

- `/` memindahkan fokus ke kolom pencarian selama user tidak sedang mengetik;
- `Esc` menutup dialog detail yang sedang terbuka.

Informasi shortcut ditampilkan di atas workspace agar fitur dapat ditemukan
tanpa membaca dokumentasi.

## 7. State Akses dan Error

State yang tersedia:

- loading saat filter atau pagination berjalan;
- empty state saat hasil tidak ada;
- error state saat filter tidak valid;
- unauthorized state jika props frontend tidak memiliki akses;
- backend `403` tetap menjadi perlindungan utama untuk request langsung.

## 8. Ziggy dan TypeScript

Route yang digunakan frontend:

- `system.audit-logs.index`;
- `system.audit-logs.show`.

Route tersebut harus ada pada daftar Ziggy. Contract props dan record berada di
`resources/js/pages/System/AuditLog/types.ts`, sehingga page dan komponen tidak
menggunakan object tanpa bentuk yang jelas.

## Verification Frontend

```bash
npm run lint:check
npm run format:check
npm run types:check
npm run build
```

Browser test memeriksa halaman list, filter, detail, shortcut, empty/error
state, light/dark, desktop/mobile, console, dan accessibility dasar.
