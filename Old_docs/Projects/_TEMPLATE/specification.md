# Specification: {Nama Project}

## Objective

{Jelaskan tujuan utama project dengan kalimat singkat.}

## Parent boundary dan ownership

- Parent boundary: `{System atau domain bisnis}`.
- Code path: `app/Modules/{Domain}/{Module}`.
- Namespace: `App\\Modules\\{Domain}\\{Module}`.
- Owner data, permission, contract, dan capability: `{jelaskan}`.

## Scope saat ini

- {Kemampuan yang termasuk dalam pekerjaan.}
- {Aturan atau batasan utama.}

## Non-scope

- {Hal yang sengaja tidak dikerjakan.}

## Existing capability contract

{Tuliskan capability dari starter kit atau module existing yang digunakan.
Jangan mengulang capability yang sudah tersedia.}

## Data contract

{Jelaskan model, field, identifier, event, atau payload yang menjadi contract.}

## Route/API design

```txt
{METHOD} {path}
```

Permissions: `{permission.key}`.

## Acceptance criteria

- {Perilaku berhasil yang harus terpenuhi.}
- {Perilaku ditolak atau failure case yang harus terpenuhi.}
- {Aturan authorization, audit, atau security yang harus terpenuhi.}

## Commands dan test plan

```bash
{command test atau verifikasi}
```

## Generator contract

- Prompt: `{prompt generator yang disetujui}`.
- Dry-run command: `{command --dry-run --json}`.
- Expected dry-run: `MODULE_PREVIEWED`, planned structure terlihat, filesystem
  tidak berubah.
- Actual command: `{command --force --yes --json}`.
- Expected actual output: `MODULE_CREATED`, path, namespace, manifest, provider,
  permission source, dan structure valid.

## Boundaries

- Always: {aturan yang selalu berlaku}.
- Ask first: {perubahan yang membutuhkan persetujuan}.
- Never: {hal yang dilarang}.
