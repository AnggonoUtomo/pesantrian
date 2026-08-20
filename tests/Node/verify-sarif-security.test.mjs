import assert from 'node:assert/strict';
import test from 'node:test';
import { blockingFindings } from '../../tools/ci/verify-sarif-security.mjs';

function report(score, level = 'warning') {
    return {
        version: '2.1.0',
        runs: [
            {
                tool: {
                    driver: {
                        rules: [
                            {
                                id: 'fixture-rule',
                                properties: { 'security-severity': score },
                            },
                        ],
                    },
                },
                results: [{ ruleId: 'fixture-rule', level }],
            },
        ],
    };
}

test('menerima SARIF tanpa finding high atau critical', () => {
    assert.deepEqual(blockingFindings(report('6.9')), []);
});

test('menolak security severity high', () => {
    assert.deepEqual(blockingFindings(report('7.0')), [
        { ruleId: 'fixture-rule', level: 'warning', score: 7 },
    ]);
});

test('menolak result level error walau skor tidak tersedia', () => {
    assert.equal(blockingFindings(report(undefined, 'error')).length, 1);
});
