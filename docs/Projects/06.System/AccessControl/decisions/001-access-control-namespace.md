# ADR-001: Namespace AccessControl pada Domain System

## Status

`Diusulkan`.

## Konteks

Project menggunakan struktur module `app/Modules/{Domain}/{Module}`. Module
authorization baseline perlu berada pada domain yang jelas agar module lain
seperti `UserManagement` dapat dikelompokkan tanpa mencampur ownership.

## Keputusan yang diusulkan

`AccessControl` ditempatkan di bawah domain `System` dengan identitas:

```text
Path: app/Modules/System/AccessControl
Namespace: App\Modules\System\AccessControl
Dokumentasi: docs/Projects/06.System/AccessControl
```

Dokumentasi module lain di bawah domain yang sama akan mengikuti pola:

```text
docs/Projects/06.System/UserManagement
app/Modules/System/UserManagement
```

## Alasan

- Struktur dokumentasi mengikuti struktur module runtime.
- `System` menjadi kelompok domain, sedangkan `AccessControl` tetap menjadi
  owner capability authorization.
- Namespace mudah ditelusuri saat review, debugging, dan rollback.
- Module lain tetap memiliki boundary dan dokumentasi sendiri.

## Dampak

- Generator dipanggil dengan `--domain=System`.
- Semua class module memakai namespace `App\Modules\System\AccessControl`.
- Permission identity dimiliki oleh `AccessControl`, bukan oleh folder `System`.
- Perubahan namespace setelah coding membutuhkan migration namespace dan ADR
  baru.

## Open Decision

- Persetujuan user diperlukan sebelum module runtime dibuat.
