export type Typography = 'system' | 'sans' | 'serif' | 'mono';

const typographyOptions: Typography[] = ['system', 'sans', 'serif', 'mono'];

export function initializeTypography(): void {
    if (typeof window === 'undefined') {
        return;
    }

    const stored = localStorage.getItem('typography');
    const fallback = document.documentElement.dataset.defaultTypography;
    const typography = typographyOptions.includes(stored as Typography)
        ? (stored as Typography)
        : typographyOptions.includes(fallback as Typography)
          ? (fallback as Typography)
          : 'system';

    document.documentElement.dataset.typography = typography;
}
