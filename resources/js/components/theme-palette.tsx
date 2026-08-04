import { Check } from 'lucide-react';
import { themePalettes, useThemePalette } from '@/hooks/use-theme-palette';
import { cn } from '@/lib/utils';

export default function ThemePalette() {
    const { palette, updatePalette } = useThemePalette();

    return (
        <div className="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
            {themePalettes.map((item) => {
                const isSelected = palette === item.value;

                return (
                    <button
                        key={item.value}
                        type="button"
                        onClick={() => updatePalette(item.value)}
                        aria-pressed={isSelected}
                        className={cn(
                            'flex items-center gap-3 rounded-lg border p-3 text-left transition hover:border-primary/60 hover:bg-accent/50',
                            isSelected &&
                                'border-primary bg-accent/50 ring-2 ring-primary/20',
                        )}
                        data-test={`theme-palette-${item.value}`}
                    >
                        <span
                            className="flex size-9 shrink-0 items-center justify-center rounded-full border border-black/10 shadow-sm dark:border-white/10"
                            style={{ backgroundColor: item.color }}
                        >
                            {isSelected ? (
                                <Check className="size-4 text-white" />
                            ) : null}
                        </span>
                        <span className="min-w-0">
                            <span className="block text-sm font-medium">
                                {item.label}
                            </span>
                            <span className="block text-xs text-muted-foreground">
                                Palet tema
                            </span>
                        </span>
                    </button>
                );
            })}
        </div>
    );
}
