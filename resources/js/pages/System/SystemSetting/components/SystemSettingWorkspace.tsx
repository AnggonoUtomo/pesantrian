import { Database, Pencil, SearchX, ShieldCheck } from 'lucide-react';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { guidanceForSetting, settingCategories } from '../categories';
import type { SettingCategory, SystemSettingItem } from '../types';

type Props = {
    category: SettingCategory;
    settings: SystemSettingItem[];
    onEditCategory: () => void;
};

function displayValue(setting: SystemSettingItem): string {
    if (setting.sensitive) {
        return setting.has_value ? 'Rahasia terisi' : 'Rahasia belum diatur';
    }

    if (setting.value === null) {
        return 'Belum diatur';
    }

    if (typeof setting.value === 'boolean') {
        return setting.value ? 'Aktif' : 'Nonaktif';
    }

    return String(setting.value);
}

export function SystemSettingWorkspace({
    category,
    settings,
    onEditCategory,
}: Props) {
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
                        <p className="mt-2 text-sm text-foreground/75">
                            <span className="font-medium">Untuk apa:</span>{' '}
                            {definition.operatorGuide}
                        </p>
                    </div>
                </div>
                <div className="flex items-center gap-2">
                    <Badge
                        variant="outline"
                        className="dashboard-badge dashboard-badge--blue"
                    >
                        {settings.length} setting
                    </Badge>
                    <Button
                        type="button"
                        variant="outline"
                        onClick={onEditCategory}
                        disabled={settings.length === 0}
                    >
                        <Pencil aria-hidden="true" />
                        Ubah kategori
                    </Button>
                </div>
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
                    {settings.map((setting) => {
                        const guidance = guidanceForSetting(setting.key);

                        return (
                            <article
                                key={setting.key}
                                className="dashboard-subcard rounded-xl border p-4"
                            >
                                <div className="min-w-0">
                                    <div className="flex flex-wrap items-center gap-2">
                                        <h3 className="text-sm font-semibold">
                                            {guidance.title}
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
                                    <p className="mt-1 font-mono text-xs break-all text-foreground/55">
                                        {setting.key}
                                    </p>
                                    <p className="mt-2 text-sm text-foreground/75">
                                        {guidance.purpose}
                                    </p>
                                    <p className="mt-2 text-sm text-foreground/65">
                                        <span className="font-medium">
                                            Cara mengisi:
                                        </span>{' '}
                                        {guidance.inputHint}
                                    </p>
                                    {guidance.caution ? (
                                        <p className="mt-2 text-sm text-amber-700 dark:text-amber-300">
                                            <span className="font-medium">
                                                Perhatikan:
                                            </span>{' '}
                                            {guidance.caution}
                                        </p>
                                    ) : null}
                                    <p className="mt-3 text-sm font-medium break-all text-primary">
                                        <span className="text-foreground/65">
                                            Nilai saat ini:
                                        </span>{' '}
                                        {displayValue(setting)}
                                    </p>
                                </div>
                            </article>
                        );
                    })}
                </div>
            )}
        </section>
    );
}
