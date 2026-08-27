# Keputusan Aktif

Dokumen ini menjadi indeks singkat ADR dan keputusan baseline yang berlaku.

| ADR | Keputusan | Status |
| --- | --- | --- |
| [ADR-001](decisions/ADR-001-DDD-LITE-MODULAR-MONOLITH-HEXAGONAL.md) | SakaSantri memakai DDD-lite Modular Monolith dengan Hexagonal Architecture | Accepted |

## Keputusan Baseline dari Dokumen Arsitektur

- SakaSantri adalah aplikasi non-SaaS untuk satu yayasan dengan banyak unit.
- Primary identifier table aplikasi memakai ULID.
- Frontend routing memakai Ziggy conventional named routes.
- Laravel Boost dan Laravel Wayfinder tidak digunakan.
- MySQL menjadi database utama.
- Spatie Permission menjadi RBAC dan Spatie Media Library menjadi adapter media.

Tambahkan ADR baru hanya untuk keputusan yang mahal atau sulit dibalik. Jangan
memindahkan keputusan dari project lain tanpa evaluasi ulang terhadap baseline
SakaSantri.
