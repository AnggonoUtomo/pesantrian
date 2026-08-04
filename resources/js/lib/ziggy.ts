import type { route as ziggyRoute } from 'ziggy-js';

export type ZiggyConfig = NonNullable<Parameters<typeof ziggyRoute>[3]>;

let config: ZiggyConfig;

export function setZiggy(cfg: ZiggyConfig) {
    config = cfg;
}

export function getZiggy(): ZiggyConfig {
    if (!config) {
        throw new Error('Ziggy belum diinisialisasi.');
    }

    return config;
}
