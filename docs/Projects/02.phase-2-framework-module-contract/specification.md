# Spesifikasi Phase 2 Framework dan Module Contract

## Kebutuhan

| ID | Kebutuhan | Prioritas | Kriteria Penerimaan |
|---|---|---|---|
| REQ-001 | Package reusable tersedia | Wajib | `packages/StarterKit` memiliki Composer metadata dan dapat dimuat Laravel |
| REQ-002 | Manifest module memiliki schema stabil | Wajib | `module.json` memiliki field wajib dan validasi tipe/status |
| REQ-003 | Runtime config terpisah dari manifest | Wajib | `module.php` menjadi sumber konfigurasi runtime, bukan manifest kedua |
| REQ-004 | Permission identity dimiliki module | Wajib | `permissions.php` memvalidasi key, description, module, sensitive |
| REQ-005 | Module registry dapat discovery | Wajib | Module valid ditemukan dari lokasi yang disepakati |
| REQ-006 | Module invalid diisolasi | Wajib | Module valid tetap dilaporkan saat module lain invalid |
| REQ-007 | Duplicate identity ditolak | Wajib | Duplicate name/path/namespace/provider/permission menghasilkan diagnostic |
| REQ-008 | Command memiliki output JSON | Wajib | `discover`, `validate`, dan `list` mendukung `--json` dan exit code |
| REQ-009 | Tidak ada file module ditimpa | Wajib | Phase 2 hanya discovery/validation; generator belum menulis module |

## Contract Manifest `module.json`

Field wajib:

```json
{
  "name": "ExampleModule",
  "namespace": "App\\Modules\\Example\\ExampleModule",
  "version": "1.0.0",
  "schema_version": 1,
  "status": "enabled",
  "domain": "Example",
  "path": "app/Modules/Example/ExampleModule",
  "provider": "App\\Modules\\Example\\ExampleModule\\ServiceProvider",
  "dependencies": [],
  "permission_source": "permissions.php",
  "config_source": "module.php"
}
```

## Contract Permission Identity

Setiap item `permissions.php` wajib memiliki `key`, `description`, `module`, dan
`sensitive`. Permission key harus unik secara global dan memakai dot notation.

## Batasan Keamanan

- Registry tidak memberi authorization kepada caller.
- Manifest dan permission metadata tidak boleh berisi secret.
- Diagnostic tidak menampilkan password, token, credential, atau sensitive payload.
- Cross-module concrete dependency ditolak atau dilaporkan sebagai invalid.

## Open Decision

Keputusan Phase 2 yang memengaruhi boundary dicatat pada ADR-0001.
