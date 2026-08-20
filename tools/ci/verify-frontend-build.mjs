import { readFile } from 'node:fs/promises';

const manifestPath = new URL(
    '../../public/build/manifest.json',
    import.meta.url,
);
const manifest = JSON.parse(await readFile(manifestPath, 'utf8'));
const forbidden =
    /(?:^|[/_.-])(test|spec|vitest|testing-library|playwright|axe-core)(?:[/_.-]|$)/i;
const findings = [];

for (const [entry, value] of Object.entries(manifest)) {
    const serialized = JSON.stringify(value);

    if (forbidden.test(entry) || forbidden.test(serialized)) {
        findings.push(entry);
    }
}

if (findings.length > 0) {
    process.stderr.write(
        `Build frontend memuat dependency atau entry test: ${findings.join(', ')}\n`,
    );
    process.exit(1);
}

process.stdout.write('Build frontend bebas dari entry/dependency test.\n');
