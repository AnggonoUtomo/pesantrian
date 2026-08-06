import { Activity, Braces, Palette, ShieldCheck, Wrench } from 'lucide-react';
import type { LucideIcon } from 'lucide-react';
import type { SettingCategory } from './types';

export type SettingCategoryDefinition = {
    key: SettingCategory;
    title: string;
    description: string;
    icon: LucideIcon;
    accent: string;
    cardTone: string;
};

export const settingCategories: SettingCategoryDefinition[] = [
    {
        key: 'api',
        title: 'API',
        description: 'Rate limit dan retensi idempotency.',
        icon: Braces,
        accent: 'dashboard-accent--blue',
        cardTone: 'dashboard-card--blue',
    },
    {
        key: 'security',
        title: 'Security',
        description: 'Idle dan batas umur session.',
        icon: ShieldCheck,
        accent: 'dashboard-accent--rose',
        cardTone: 'dashboard-card--rose',
    },
    {
        key: 'branding',
        title: 'Branding',
        description: 'Nama, aset lokal, dan tampilan default.',
        icon: Palette,
        accent: 'dashboard-accent--violet',
        cardTone: 'dashboard-card--violet',
    },
    {
        key: 'monitoring',
        title: 'Monitoring',
        description: 'Capability monitoring eksternal.',
        icon: Activity,
        accent: 'dashboard-accent--emerald',
        cardTone: 'dashboard-card--emerald',
    },
    {
        key: 'operations',
        title: 'Operations',
        description: 'Target pemulihan RTO dan RPO.',
        icon: Wrench,
        accent: 'dashboard-accent--amber',
        cardTone: 'dashboard-card--amber',
    },
];

export function categoryFromKey(key: string): SettingCategory {
    const category = key.split('.')[0];

    return settingCategories.some((item) => item.key === category)
        ? (category as SettingCategory)
        : 'operations';
}
