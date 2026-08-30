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

export function hasRoute(name: string): boolean {
    try {
        return Object.prototype.hasOwnProperty.call(getZiggy().routes, name);
    } catch {
        return false;
    }
}

export function routeOr(
    fallback: string,
    name: Exclude<RouteName, undefined>,
    params?: RouteParams,
    absolute?: RouteAbsolute,
): RouteUrl | string {
    if (!hasRoute(String(name))) {
        return fallback;
    }

    return route(name, params, absolute);
}

export default route;
