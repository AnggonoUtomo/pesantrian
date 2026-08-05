import { useSyncExternalStore } from 'react';

export const themePalettes = [
    { value: 'urban', label: 'Urban', color: 'oklch(0.47 0.11 252)' },
    { value: 'slate', label: 'Slate', color: 'oklch(0.45 0.05 255)' },
    { value: 'gray', label: 'Gray', color: 'oklch(0.45 0.03 257)' },
    { value: 'zinc', label: 'Zinc', color: 'oklch(0.45 0.03 285)' },
    { value: 'neutral', label: 'Neutral', color: 'oklch(0.45 0 0)' },
    { value: 'stone', label: 'Stone', color: 'oklch(0.45 0.04 75)' },
    { value: 'graphite', label: 'Graphite', color: 'oklch(0.43 0.055 245)' },
    { value: 'mist', label: 'Mist', color: 'oklch(0.46 0.085 135)' },
    { value: 'harbor', label: 'Harbor', color: 'oklch(0.49 0.11 245)' },
    { value: 'quartz', label: 'Quartz', color: 'oklch(0.21 0.006 285)' },
    { value: 'aurora', label: 'Aurora', color: 'oklch(0.45 0.105 190)' },
    { value: 'saffron', label: 'Saffron', color: 'oklch(0.62 0.13 78)' },
    { value: 'ruby', label: 'Ruby', color: 'oklch(0.52 0.16 18)' },
    { value: 'forest', label: 'Forest', color: 'oklch(0.43 0.105 145)' },
    { value: 'ocean', label: 'Ocean', color: 'oklch(0.45 0.12 235)' },
    { value: 'plum', label: 'Plum', color: 'oklch(0.48 0.13 305)' },
    { value: 'copper', label: 'Copper', color: 'oklch(0.55 0.12 48)' },
] as const;

export type ThemePalette = (typeof themePalettes)[number]['value'];

const listeners = new Set<() => void>();
let currentPalette: ThemePalette = 'neutral';

const getStoredPalette = (): ThemePalette => {
    if (typeof window === 'undefined') {
        return 'neutral';
    }

    const stored = localStorage.getItem('theme-palette');

    return themePalettes.some((palette) => palette.value === stored)
        ? (stored as ThemePalette)
        : 'neutral';
};

const applyPalette = (palette: ThemePalette): void => {
    if (typeof document === 'undefined') {
        return;
    }

    document.documentElement.dataset.theme = palette;
};

const notify = (): void => listeners.forEach((listener) => listener());

export function initializeThemePalette(): void {
    if (typeof window === 'undefined') {
        return;
    }

    currentPalette = getStoredPalette();
    applyPalette(currentPalette);
}

export function useThemePalette() {
    const palette = useSyncExternalStore(
        (callback) => {
            listeners.add(callback);

            return () => listeners.delete(callback);
        },
        () => currentPalette,
        () => 'neutral' as ThemePalette,
    );

    const updatePalette = (nextPalette: ThemePalette): void => {
        currentPalette = nextPalette;
        localStorage.setItem('theme-palette', nextPalette);
        applyPalette(nextPalette);
        notify();
    };

    return { palette, updatePalette } as const;
}
