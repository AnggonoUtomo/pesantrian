import type { route as ziggyRoute } from 'ziggy-js';

export type ZiggyConfig = NonNullable<Parameters<typeof ziggyRoute>[3]>;

let config: ZiggyConfig;

export function setZiggy(cfg: ZiggyConfig) {
    config = cfg;
}

export function getZiggy(): ZiggyConfig {
    if (!config && typeof document !== 'undefined') {
        const pageJson = document.querySelector<HTMLScriptElement>(
            'script[data-page="app"]',
        )?.textContent;

        if (pageJson) {
            try {
                const page = JSON.parse(pageJson) as {
                    props?: { ziggy?: ZiggyConfig };
                };

                if (page.props?.ziggy) {
                    config = page.props.ziggy;
                }
            } catch {
                // Inertia akan mengirim ulang props melalui withApp.
            }
        }
    }

    if (!config) {
        throw new Error('Ziggy belum diinisialisasi.');
    }

    return config;
}
