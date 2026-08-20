import { readFileSync } from 'node:fs';
import { pathToFileURL } from 'node:url';

const blockingScore = 7;

export function blockingFindings(sarif) {
    const findings = [];

    for (const run of sarif.runs ?? []) {
        const rules = new Map(
            (run.tool?.driver?.rules ?? []).map((rule) => [rule.id, rule]),
        );

        for (const result of run.results ?? []) {
            const rule = rules.get(result.ruleId);
            const rawScore =
                result.properties?.['security-severity'] ??
                rule?.properties?.['security-severity'];
            const score = Number.parseFloat(String(rawScore ?? ''));
            const level = result.level ?? 'warning';

            if (
                (Number.isFinite(score) && score >= blockingScore) ||
                level === 'error'
            ) {
                findings.push({
                    ruleId: result.ruleId ?? 'unknown',
                    level,
                    score: Number.isFinite(score) ? score : null,
                });
            }
        }
    }

    return findings;
}

function main(paths) {
    if (paths.length === 0) {
        throw new Error('Minimal satu file SARIF wajib diberikan.');
    }

    const findings = paths.flatMap((path) => {
        const sarif = JSON.parse(readFileSync(path, 'utf8'));

        return blockingFindings(sarif);
    });

    if (findings.length > 0) {
        const summary = findings
            .map(
                (finding) =>
                    `${finding.ruleId} (${finding.score ?? finding.level})`,
            )
            .join(', ');

        throw new Error(`SARIF memiliki finding high/critical: ${summary}`);
    }

    process.stdout.write(
        `SARIF gate lulus untuk ${paths.length} report; tidak ada finding high/critical.\n`,
    );
}

if (
    process.argv[1] &&
    import.meta.url === pathToFileURL(process.argv[1]).href
) {
    try {
        main(process.argv.slice(2));
    } catch (error) {
        process.stderr.write(`${error.message}\n`);
        process.exitCode = 1;
    }
}
