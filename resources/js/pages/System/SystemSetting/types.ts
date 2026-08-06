import type { Auth } from '@/types';

export type SettingValue = number | boolean | string | null;

export type SystemSettingItem = {
    key: string;
    type: 'integer' | 'boolean' | 'string' | 'enum' | 'path';
    value: SettingValue;
    default_value: SettingValue;
    description: string;
    source: 'database' | 'default';
    updated_at: string | null;
    min: number | null;
    max: number | null;
    options: string[];
    nullable: boolean;
};

export type SettingCategory =
    'api' | 'security' | 'branding' | 'monitoring' | 'operations';

export type SystemSettingPageProps = {
    auth: Auth;
    settings: SystemSettingItem[];
    errors?: Record<string, string>;
};
