import { route as ziggyRoute } from 'ziggy-js';
import { getZiggy } from './ziggy';

type ZiggyRoute = typeof ziggyRoute;

export type RouteName = Parameters<ZiggyRoute>[0];
export type RouteParams = Parameters<ZiggyRoute>[1];
export type RouteAbsolute = Parameters<ZiggyRoute>[2];
export type RouteUrl = ReturnType<ZiggyRoute>;

export function route(
    name: Exclude<RouteName, undefined>,
    params?: RouteParams,
    absolute?: RouteAbsolute,
): RouteUrl {
    return ziggyRoute(name, params, absolute, getZiggy());
}

export default route;

// import { route as ziggyRoute } from 'ziggy-js';

// import { getZiggy } from './ziggy';

// type RouteName = Parameters<typeof ziggyRoute>[0];
// type RouteParams = Parameters<typeof ziggyRoute>[1];
// type RouteAbsolute = Parameters<typeof ziggyRoute>[2];
// type RouteUrl = ReturnType<typeof ziggyRoute>;

// export default function route(
//     name: Exclude<RouteName, undefined>,
//     params?: RouteParams,
//     absolute?: RouteAbsolute,
// ): RouteUrl {
//     return ziggyRoute(name, params, absolute, getZiggy());
// }

// import { route as ziggyRoute } from 'ziggy-js';

// let ziggyConfig: any = null;

// export function setZiggy(config: any) {
//     ziggyConfig = config;
// }

// export default function route(name: string, params?: any, absolute?: boolean) {
//     return ziggyRoute(name, params, absolute, ziggyConfig);
// }
