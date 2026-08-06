import { Database, Layers3, ShieldCheck } from 'lucide-react';
import type { SystemSettingItem } from '../types';

type Props = {
    settings: SystemSettingItem[];
};

export function SystemSettingSummary({ settings }: Props) {
    const databaseCount = settings.filter(
        (setting) => setting.source === 'database',
    ).length;
    const defaultCount = settings.length - databaseCount;
    const cards = [
        {
            label: 'Definition aktif',
            value: settings.length,
            detail: 'Key typed dalam registry',
            icon: Layers3,
            tone: 'dashboard-card--blue',
            accent: 'dashboard-accent--blue',
        },
        {
            label: 'Nilai database',
            value: databaseCount,
            detail: 'Override tersimpan dan valid',
            icon: Database,
            tone: 'dashboard-card--emerald',
            accent: 'dashboard-accent--emerald',
        },
        {
            label: 'Default aman',
            value: defaultCount,
            detail: 'Fallback registry saat dibutuhkan',
            icon: ShieldCheck,
            tone: 'dashboard-card--amber',
            accent: 'dashboard-accent--amber',
        },
    ];

    return (
        <div className="grid gap-4 sm:grid-cols-3">
            {cards.map((card) => {
                const Icon = card.icon;

                return (
                    <section
                        key={card.label}
                        className={`dashboard-card ${card.tone} rounded-2xl border p-4`}
                    >
                        <div className="flex items-start justify-between gap-3">
                            <div>
                                <p className="text-sm text-foreground/65">
                                    {card.label}
                                </p>
                                <p className="mt-1 text-2xl font-semibold">
                                    {card.value}
                                </p>
                            </div>
                            <span
                                className={`dashboard-icon ${card.accent} flex size-10 items-center justify-center rounded-xl`}
                            >
                                <Icon className="size-5" aria-hidden="true" />
                            </span>
                        </div>
                        <p className="mt-3 text-xs text-foreground/60">
                            {card.detail}
                        </p>
                    </section>
                );
            })}
        </div>
    );
}
