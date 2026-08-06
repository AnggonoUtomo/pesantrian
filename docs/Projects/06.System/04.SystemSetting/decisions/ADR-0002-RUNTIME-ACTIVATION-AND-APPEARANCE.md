# ADR-0002: Aktivasi Runtime, Cache, dan Appearance

## Status

`Accepted` pada 6 Agustus 2026.

## Context

Requirement menyatakan perubahan setting langsung aktif dan tetap aman saat
storage/cache bermasalah. Cache nilai lintas request dapat mengembalikan nilai
lama jika invalidation gagal, terutama ketika deployment nanti memakai banyak
worker atau Redis.

Workspace saat ini menyimpan appearance dan palette pribadi melalui cookie serta
localStorage. Mengganti behavior tersebut dengan setting global akan menghapus
pilihan user dan mencampur SystemSetting dengan pengaturan pribadi starter kit.

Upload logo/favicon juga belum mempunyai owner media dan package Media Library
belum terpasang.

## Decision

- Database menjadi sumber kebenaran nilai runtime.
- Increment awal hanya memakai memoization per request. Nilai tidak disimpan
  pada cache lintas request.
- Perubahan dianggap aktif pada response mutation dan seluruh request
  berikutnya karena reader kembali membaca source of truth.
- Jika storage gagal atau record invalid, reader memakai default registry dan
  menghasilkan diagnostic aman tanpa nilai sensitif.
- Persistent cache baru dapat ditambahkan melalui increment terpisah yang
  memiliki versioned invalidation, multi-worker test, failure test, dan rollback.
- `branding.appearance_default` serta `branding.palette_default` menjadi default
  global saat user belum memiliki pilihan pribadi.
- Cookie/localStorage user tetap menjadi override. Halaman
  `/settings/appearance` tidak dipindahkan ke SystemSetting.
- Logo dan favicon increment awal berupa path aset lokal yang tervalidasi.
  Protocol eksternal, traversal path, data URL, dan upload file ditolak.
- Upload/media management menunggu persetujuan package dan ADR terpisah.

## Alternatives Considered

### Cache-aside lintas request sejak awal

Ditolak karena invalidation failure dapat membuat nilai lama tetap aktif dan
melanggar kontrak aktivasi langsung.

### Redis sebagai source of truth

Ditolak karena runtime lokal memakai database driver dan kehilangan Redis tidak
boleh menghilangkan konfigurasi utama.

### Setting global selalu mengalahkan pilihan user

Ditolak karena SystemSetting bukan pengganti appearance pribadi starter kit.

### Menambah media package sekarang

Ditolak pada tahap dokumentasi karena dependency baru memerlukan persetujuan
eksplisit, threat model upload, storage policy, dan test file.

## Consequences

### Positif

- Tidak ada stale cache lintas worker pada baseline pertama.
- Safe default tetap tersedia ketika database belum siap atau bermasalah.
- Preference user tidak hilang.
- Tidak ada dependency media dan attack surface upload sebelum dibutuhkan.

### Batasan

- Request pertama untuk setiap key melakukan query database.
- Belum ada optimasi cache lintas request untuk traffic besar.
- Perubahan default appearance tidak memaksa user yang sudah memiliki pilihan.
- Logo/favicon harus sudah tersedia sebagai aset lokal sebelum path dipilih.

## Verification

- Reader test untuk database value, request memoization, invalid record, dan
  storage failure.
- Test perubahan terlihat pada request berikutnya.
- Browser test default global tanpa preference dan user override dengan
  cookie/localStorage.
- Path validation test untuk traversal, URL eksternal, dan data URL.
- Performance measurement sebelum cache lintas request dipertimbangkan.

## Keputusan User

Disetujui pada 6 Agustus 2026. Teknik visual dapat mengacu pada
`FrontendContoh/system-settings`, tetapi fitur referensi tidak menjadi scope.

## Revision History

| Versi | Tanggal | Perubahan |
| --- | --- | --- |
| 1.0 | 2026-08-06 | Mengusulkan source of truth, cache baseline, preference, dan asset path |
| 1.1 | 2026-08-06 | Menerima keputusan runtime dan batas adaptasi UI |
