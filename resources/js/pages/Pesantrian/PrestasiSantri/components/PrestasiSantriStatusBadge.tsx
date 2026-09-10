import { Badge } from '@/components/ui/badge';
import type { AchievementLevel, AchievementStatus } from '../types';
import {
    achievementLevelLabel,
    achievementStatusLabel,
} from './prestasiSantriDisplay';

export function PrestasiSantriStatusBadge({
    status,
}: {
    status: AchievementStatus;
}) {
    const tone = {
        draft: 'border-slate-300 bg-background text-foreground',
        submitted: 'border-amber-300 bg-background text-foreground',
        needs_revision:
            'border-orange-300 bg-background text-foreground',
        verified: 'border-emerald-300 bg-background text-foreground',
        void: 'border-rose-300 bg-background text-foreground',
    }[status];

    return (
        <Badge variant="outline" className={tone}>
            {achievementStatusLabel(status)}
        </Badge>
    );
}

export function PrestasiSantriLevelBadge({
    level,
}: {
    level: AchievementLevel;
}) {
    const tone = {
        internal: 'border-slate-300 bg-background text-foreground',
        district: 'border-cyan-300 bg-background text-foreground',
        city: 'border-blue-300 bg-background text-foreground',
        province: 'border-violet-300 bg-background text-foreground',
        national: 'border-amber-300 bg-background text-foreground',
        international:
            'border-emerald-300 bg-background text-foreground',
    }[level];

    return (
        <Badge variant="outline" className={tone}>
            {achievementLevelLabel(level)}
        </Badge>
    );
}
