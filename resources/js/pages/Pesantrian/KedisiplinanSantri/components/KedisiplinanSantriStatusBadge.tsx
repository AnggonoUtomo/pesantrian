import { Badge } from '@/components/ui/badge';
import type {
    StudentDisciplineSeverity,
    StudentDisciplineStatus,
} from '../types';
import {
    disciplineSeverityLabel,
    disciplineStatusLabel,
} from './kedisiplinanSantriDisplay';

export function KedisiplinanSantriStatusBadge({
    status,
}: {
    status: StudentDisciplineStatus;
}) {
    const tone = {
        draft: 'border-slate-300 bg-slate-50 text-slate-700 dark:bg-slate-950/30',
        submitted:
            'border-amber-300 bg-amber-50 text-amber-700 dark:bg-amber-950/30',
        in_review:
            'border-blue-300 bg-blue-50 text-blue-700 dark:bg-blue-950/30',
        action_assigned:
            'border-cyan-300 bg-cyan-50 text-cyan-700 dark:bg-cyan-950/30',
        resolved:
            'border-emerald-300 bg-emerald-50 text-emerald-700 dark:bg-emerald-950/30',
        void: 'border-rose-300 bg-rose-50 text-rose-700 dark:bg-rose-950/30',
    }[status];

    return (
        <Badge variant="outline" className={tone}>
            {disciplineStatusLabel(status)}
        </Badge>
    );
}

export function KedisiplinanSantriSeverityBadge({
    severity,
}: {
    severity: StudentDisciplineSeverity;
}) {
    const tone = {
        minor: 'border-emerald-300 bg-emerald-50 text-emerald-700 dark:bg-emerald-950/30',
        moderate:
            'border-amber-300 bg-amber-50 text-amber-700 dark:bg-amber-950/30',
        major: 'border-rose-300 bg-rose-50 text-rose-700 dark:bg-rose-950/30',
    }[severity];

    return (
        <Badge variant="outline" className={tone}>
            {disciplineSeverityLabel(severity)}
        </Badge>
    );
}
