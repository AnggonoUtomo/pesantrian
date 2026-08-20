# ADR-0001: Dynamic Module Runtime Bootstrap

## Status

`Accepted - 14 Agustus 2026.`

## Context

`ModuleRegistry` sudah membaca manifest dan mengisolasi beberapa manifest yang
tidak valid. Namun provider empat module masih didaftarkan langsung pada
`bootstrap/providers.php`. Akibatnya, field `status`, `dependencies`, dan
`provider` pada `module.json` belum mengontrol runtime Laravel.

Kondisi tersebut bertentangan dengan baseline bahwa module invalid atau
disabled harus diisolasi dan module valid lain tetap berjalan. Mengubah
composition root berisiko memutus HTTP, Artisan, route cache, config cache,
migration, dan seeder. Karena itu, perubahan wajib dilakukan setelah graph
validator dan bootstrap test tersedia.

## Decision

1. `ModuleRegistry` menjadi source canonical untuk discovery manifest dan graph
   dependency module. Discovery tetap read-only dan tidak mendaftarkan provider.
2. Graph validator memeriksa manifest, identity unik, source file, dependency
   yang hilang, self-dependency, cycle, status, class provider, serta urutan
   topological yang deterministik.
3. Module berstatus selain `enabled`, invalid, memiliki dependency invalid, atau
   bergantung pada module disabled tidak boleh masuk boot plan. Diagnostic harus
   menyebut kode serta owner module secara aman tanpa absolute path, exception
   mentah, secret, atau konfigurasi sensitif.
4. `bootstrap/providers.php` hanya menyimpan provider aplikasi/framework yang
   stabil. Satu composition provider memakai boot plan tervalidasi untuk
   mendaftarkan provider module sesuai urutan dependency.
5. Kegagalan register atau boot satu provider diisolasi. Module yang bergantung
   padanya tidak dilanjutkan, sedangkan peer valid yang tidak bergantung tetap
   dapat boot. Failure dicatat sebagai diagnostic terstruktur dan aman.
6. Tidak ada fallback diam-diam ke daftar provider module statis. Jika boot plan
   tidak dapat dibangun, hanya framework minimum dan module valid independen
   yang boleh berjalan.
7. `module:discover`, `module:validate`, `module:list`, `module:inspect`, dan
   runtime bootstrap wajib memakai hasil graph serta diagnostic canonical yang
   sama.
8. `config:cache` dan `route:cache` wajib lulus. Perubahan manifest/status
   module membutuhkan invalidasi dan pembuatan ulang cache runtime terkait.
9. Provider statis baru dihapus setelah test membuktikan module enabled,
   disabled, invalid, dependency hilang, cycle, provider gagal, dan valid peer.

## Urutan Runtime

```text
Framework provider stabil
  -> discovery manifest tanpa side effect
  -> validasi identity dan dependency graph
  -> boot plan topological
  -> register provider module valid/enabled
  -> boot provider module
  -> readiness dan diagnostic aman
```

## Failure Contract

- Diagnostic mempunyai kode stabil, module owner, fase `discovery`,
  `validation`, `register`, atau `boot`, serta pesan allowlist.
- Dependency yang gagal membuat dependent berstatus tidak dapat di-boot, bukan
  dipaksa berjalan dengan container binding yang tidak lengkap.
- Failure module tidak boleh memunculkan absolute path, stack trace, `.env`,
  credential, token, password, atau payload setting.
- HTTP dan Artisan harus tetap dapat menampilkan health/diagnostic framework
  minimum saat module business diisolasi.

## Acceptance Criteria

- Graph valid menghasilkan urutan deterministik
  `AccessControl -> UserManagement -> AuditLog -> SystemSetting`.
- Disabled/invalid module dan semua dependent yang tidak memenuhi dependency
  tidak terdaftar pada runtime.
- Provider yang melempar exception tidak menjatuhkan peer valid independen.
- Empat command module dan runtime bootstrap memberi diagnostic yang konsisten.
- HTTP, console, migration, seeder, `config:cache`, dan `route:cache` lulus pada
  graph production saat ini.
- Focused positive/negative bootstrap test dan full quality gate lulus sebelum
  provider statis dihapus.

## Alternatives Considered

### Mempertahankan provider statis

Ditolak karena manifest tidak menjadi runtime authority. Module disabled atau
invalid tetap dapat boot dan isolation hanya menjadi klaim dokumentasi.

### Mendaftarkan provider langsung saat discovery

Ditolak karena discovery harus read-only. Side effect sebelum seluruh graph
valid membuat failure sulit diisolasi dan hasil command tidak deterministik.

### Menghentikan seluruh aplikasi saat satu module gagal

Ditolak untuk baseline modular monolith ini karena module valid independen
wajib tetap berjalan dan diagnostic minimum harus tetap tersedia.

## Consequences

- Composition root menjadi lebih kompleks dan membutuhkan state diagnostic
  terstruktur.
- Dependency graph harus selesai divalidasi sebelum provider module pertama
  didaftarkan.
- Cache deployment perlu diinvalidasi saat manifest atau status module berubah.
- Test provider failure menjadi gate wajib agar isolation tidak hanya menguji
  hasil array discovery.

## Rollback

Jika dynamic bootstrap membuat framework minimum tidak dapat boot, kembalikan
composition provider dan `bootstrap/providers.php` pada perubahan increment
T04.2. Graph validator serta test discovery tetap dapat dipertahankan sebagai
capability read-only. Rollback tidak boleh menghapus ADR atau diagnostic
evidence; keputusan baru harus memakai ADR superseding.

## References

- [Implementation plan](../implementation-plan.md)
- [Task checklist](../tasks.md)
- [Module Registry](../../../../../06-FRAMEWORK/06.03-MODULE-REGISTRY.md)
- [Kernel Bootstrap](../../../../../07-KERNEL/07.01-BOOTSTRAP.md)
- [Registry Service](../../../../../07-KERNEL/07.06-REGISTRY-SERVICE.md)
- [ADR arsitektur modular monolith](../../../../../05-DECISIONS/ADR/05.03-ADR-0001-ARCHITECTURE.md)

## Revision History

| Versi | Tanggal | Perubahan |
| --- | --- | --- |
| 1.0 | 2026-08-14 | Menerima dynamic module runtime bootstrap dan failure isolation. |
