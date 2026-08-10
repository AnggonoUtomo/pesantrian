import { categoryFromKey, settingCategories } from '../categories';
import type { SettingCategory, SystemSettingItem } from '../types';

type Props = {
    activeCategory: SettingCategory;
    settings: SystemSettingItem[];
    onCategoryChange: (category: SettingCategory) => void;
};

export function SystemSettingMenu({
    activeCategory,
    settings,
    onCategoryChange,
}: Props) {
    return (
        <aside className="dashboard-card dashboard-card--violet h-fit overflow-hidden rounded-2xl border xl:sticky xl:top-24">
            <div className="border-b border-border/70 p-4">
                <h2 className="font-semibold">Menu SystemSetting</h2>
                <p className="mt-1 text-xs text-foreground/65">
                    Pilih kelompok konfigurasi yang akan ditinjau.
                </p>
            </div>
            <nav className="space-y-2 p-3" aria-label="Kategori SystemSetting">
                {settingCategories.map((category) => {
                    const Icon = category.icon;
                    const active = activeCategory === category.key;
                    const count = settings.filter(
                        (setting) =>
                            categoryFromKey(setting.key) === category.key,
                    ).length;

                    return (
                        <button
                            key={category.key}
                            type="button"
                            onClick={() => onCategoryChange(category.key)}
                            aria-current={active ? 'page' : undefined}
                            className={`flex w-full items-start gap-3 rounded-xl border p-3 text-left transition-colors focus-visible:ring-2 focus-visible:ring-ring focus-visible:outline-none ${
                                active
                                    ? 'border-primary/40 bg-primary/10 text-primary'
                                    : 'border-transparent hover:bg-accent hover:text-accent-foreground'
                            }`}
                        >
                            <span
                                className={`dashboard-icon ${category.accent} flex size-9 shrink-0 items-center justify-center rounded-lg`}
                            >
                                <Icon className="size-4" aria-hidden="true" />
                            </span>
                            <span className="min-w-0 flex-1">
                                <span className="flex items-center justify-between gap-2">
                                    <span className="text-sm font-medium">
                                        {category.title}
                                    </span>
                                    <span className="text-xs opacity-70">
                                        {count}
                                    </span>
                                </span>
                                <span className="mt-0.5 block text-xs leading-relaxed text-foreground/75">
                                    {category.description}
                                </span>
                            </span>
                        </button>
                    );
                })}
            </nav>
        </aside>
    );
}
