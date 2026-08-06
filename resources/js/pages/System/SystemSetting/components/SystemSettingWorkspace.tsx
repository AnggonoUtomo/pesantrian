import { Database, Pencil, SearchX, ShieldCheck } from 'lucide-react';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { settingCategories } from '../categories';
import type { SettingCategory, SystemSettingItem } from '../types';

type Props = {
    category: SettingCategory;
    settings: SystemSettingItem[];
    onEdit: (setting: SystemSettingItem) => void;
};

function displayValue(value: SystemSettingItem['value']): string {
    if (value === null) {
        return 'Belum diatur';
    }

    if (typeof value === 'boolean') {
        return value ? 'Aktif' : 'Nonaktif';
    }

    return String(value);
}

export function SystemSettingWorkspace({ category, settings, onEdit }: Props) {
    const definition =
        settingCategories.find((item) => item.key === category) ??
        settingCategories[0];
    const Icon = definition.icon;

    return (
        <section
            className={`dashboard-card ${definition.cardTone} overflow-hidden rounded-2xl border`}
        >
            <div className="flex flex-col gap-3 border-b border-border/70 p-4 sm:flex-row sm:items-center sm:justify-between sm:p-5">
                <div className="flex items-start gap-3">
                    <span
                        className={`dashboard-icon ${definition.accent} flex size-11 shrink-0 items-center justify-center rounded-xl`}
                    >
                        <Icon className="size-5" aria-hidden="true" />
                    </span>
                    <div>
                        <h2 className="font-semibold">
                            {definition.title} settings
                        </h2>
                        <p className="mt-1 text-sm text-foreground/65">
                            {definition.description}
                        </p>
                    </div>
                </div>
                <Badge
                    variant="outline"
                    className="dashboard-badge dashboard-badge--blue"
                >
                    {settings.length} setting
                </Badge>
            </div>

            {settings.length === 0 ? (
                <div className="dashboard-subcard m-4 rounded-xl border border-dashed px-6 py-12 text-center sm:m-5">
                    <SearchX className="mx-auto size-9 text-foreground/45" />
                    <h3 className="mt-3 font-medium">
                        Setting tidak ditemukan
                    </h3>
                    <p className="mt-1 text-sm text-foreground/60">
                        Ubah kata pencarian atau pilih kategori lain.
                    </p>
                </div>
            ) : (
                <div className="grid gap-3 p-4 sm:p-5">
                    {settings.map((setting) => (
                        <article
                            key={setting.key}
                            className="dashboard-subcard rounded-xl border p-4"
                        >
                            <div className="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                                <div className="min-w-0">
                                    <div className="flex flex-wrap items-center gap-2">
                                        <h3 className="text-sm font-semibold break-all">
                                            {setting.key}
                                        </h3>
                                        <Badge
                                            variant="outline"
                                            className={
                                                setting.source === 'database'
                                                    ? 'dashboard-badge dashboard-badge--emerald'
                                                    : 'dashboard-badge dashboard-badge--amber'
                                            }
                                        >
                                            {setting.source === 'database' ? (
                                                <Database aria-hidden="true" />
                                            ) : (
                                                <ShieldCheck aria-hidden="true" />
                                            )}
                                            {setting.source === 'database'
                                                ? 'Database'
                                                : 'Default aman'}
                                        </Badge>
                                    </div>
                                    <p className="mt-1 text-sm text-foreground/65">
                                        {setting.description}
                                    </p>
                                    <p className="mt-2 font-mono text-sm font-medium break-all text-primary">
                                        {displayValue(setting.value)}
                                    </p>
                                </div>
                                <Button
                                    type="button"
                                    variant="outline"
                                    className="shrink-0"
                                    onClick={() => onEdit(setting)}
                                >
                                    <Pencil aria-hidden="true" />
                                    Ubah
                                </Button>
                            </div>
                        </article>
                    ))}
                </div>
            )}
        </section>
    );
}
