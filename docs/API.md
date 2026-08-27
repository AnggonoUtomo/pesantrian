# API Publik

## Konvensi

- Base path: `[base path API]`.
- Authentication: `[mekanisme]`.
- Authorization: backend policy/capability milik module.
- Rate limit: `[limiter atau tidak ada]`.
- Idempotency mutation: `[aturan atau tidak ada]`.
- Format error: `[contract error]`.

## Endpoint

| Method | Endpoint  | Tujuan     | Authorization         |
| ------ | --------- | ---------- | --------------------- |
| GET    | `/contoh` | `[Tujuan]` | `[Policy/permission]` |

Perubahan endpoint wajib memperbarui tabel dan focused contract test yang
relevan. Hapus endpoint contoh ketika API pertama didokumentasikan.
