# ADR-0003: Minimasi Context Keamanan Audit

## Status

`Accepted` pada 10 Agustus 2026.

## Context

Operator membutuhkan context saat menelusuri login dan perubahan keamanan
penting, terutama browser serta alamat IP. Menyimpan user-agent mentah, device
fingerprint, session, token, atau context untuk seluruh aktivitas akan
meningkatkan risiko privasi dan memperbesar payload audit tanpa manfaat yang
sepadan.

AuditLog sudah memiliki redaction dan payload Inertia minimum, tetapi belum
memiliki aturan khusus untuk memastikan browser/IP hanya ada pada aktivitas
yang memang sensitif.

## Decision

- AuditLog mencatat login sukses, logout, reset password sukses, dan verifikasi
  email melalui event autentikasi Laravel yang berjalan synchronous.
- Browser disimpan sebagai ringkasan tanpa versi, misalnya `Chrome di Windows`;
  user-agent mentah tidak disimpan.
- IP tervalidasi dari `Request::ip()` disimpan sebagai evidence internal audit.
- Payload Inertia hanya menerima IP yang dimasking; metadata mentah tetap tidak
  diteruskan ke UI.
- Context browser/IP hanya ditambahkan oleh server untuk action autentikasi dan
  keamanan yang di-allowlist, termasuk impersonation, perubahan role, hak
  akses role, dan status pengguna.
- Metadata browser/IP yang dikirim publisher diabaikan untuk action yang tidak
  masuk allowlist.
- Login gagal, geolokasi, serta device fingerprint tidak dicatat pada increment
  ini karena membutuhkan kebijakan privasi, retensi, dan mitigasi abuse
  tersendiri.

## Alternatives Considered

### Menyimpan user-agent mentah untuk seluruh audit

Ditolak karena menambah fingerprinting dan data tidak perlu, sedangkan operator
hanya membutuhkan nama browser dan sistem operasi.

### Menampilkan IP lengkap pada halaman Audit Log

Ditolak karena actor dengan scope audit miliknya sendiri dan SuperSystem dapat
membuka halaman yang sama. UI hanya memerlukan petunjuk investigasi; evidence
lengkap tetap berada pada persistence/API internal yang terotorisasi.

### Mencatat login gagal pada increment ini

Ditolak karena actor belum tentu teridentifikasi dan data input login dapat
memperbesar risiko enumerasi atau spam audit. Capability ini memerlukan rate
limit, policy visibility, dan ADR lanjutan.

## Consequences

### Positif

- operator mendapat context yang mudah dipahami untuk peristiwa keamanan;
- metadata teknis dan PII di UI tetap minimum;
- publisher tidak dapat memalsukan context browser/IP untuk audit bisnis;
- scope event dapat diuji dengan positive dan negative test.

### Batasan

- akurasi IP bergantung pada konfigurasi trusted proxy Laravel;
- IP lengkap tetap merupakan data sensitif pada storage internal;
- investigasi login gagal belum tersedia.

## Verification

- `AuditLogAuthenticationTest` membuktikan login sukses menyimpan browser
  ringkas serta IP dan audit bisnis biasa tidak menyimpan keduanya.
- `AuditLogPresentationTest` membuktikan payload Inertia memakai label operator
  serta IP tersamarkan tanpa metadata mentah.
- browser verification memastikan dialog detail hanya menampilkan browser dan
  IP tersamarkan untuk record autentikasi.

## Revision History

| Versi | Tanggal    | Perubahan                                                         |
| ----- | ---------- | ----------------------------------------------------------------- |
| 1.0   | 2026-08-10 | Menerima minimasi browser/IP untuk audit autentikasi dan keamanan |
