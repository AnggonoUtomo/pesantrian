# {Nama Module}

Source module mengikuti `docs/ARCHITECTURE.md` dan
`docs/FOLDER-STRUCTURE.md`.

## Identitas

- Namespace: `{Namespace}`
- Module: `{Module}`
- Source: `app/Modules/{Namespace}/{Module}/`
- Frontend: `resources/js/pages/{Namespace}/{Module}/`
- Status: `{Planned|Active|Deprecated|Disabled}`

## Tujuan

{Tanggung jawab utama module sebagai bounded capability.}

## Boundary

- Memiliki: {data dan behavior yang dimiliki module}.
- Tidak memiliki: {hal di luar tanggung jawab module}.

## Public Boundary

- Contract/DTO/event/action/query/route publik: {daftar yang memang diekspos}.
- Dependency lintas module: {dependency nyata dan alasan penggunaannya}.

## Data dan Identifier

- Table milik module: {nama table}.
- Primary identifier: ULID untuk table aplikasi.
- Business identifier: {nomor bisnis seperti student_no atau invoice_no bila ada}.

## Permission dan Audit

- Permission identity: `{module.permission}`.
- Policy/resource rule: {aturan authorization backend}.
- Audit/event: {event atau audit entry yang dibuat}.

## Operasi

- Migration/seeder/factory: {path atau catatan penting}.
- Queue/event/listener: {runtime concern bila ada}.
- Konfigurasi: {module.php, setting, atau env/config yang relevan}.

## Verifikasi Utama

```bash
{command validasi module atau focused test}
```
