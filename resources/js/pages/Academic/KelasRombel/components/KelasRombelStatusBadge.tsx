import { Badge } from '@/components/ui/badge';
import type { ClassGroupStatus } from '../types';
import { classGroupStatusLabels } from './kelasRombelDisplay';

type Props = {
    status: ClassGroupStatus;
};

const variants: Record<ClassGroupStatus, string> = {
    draft: 'border-slate-500/30 bg-slate-500/10 text-slate-700 dark:text-slate-300',
    active: 'border-emerald-500/30 bg-emerald-500/10 text-emerald-700 dark:text-emerald-300',
    closed: 'border-amber-500/30 bg-amber-500/10 text-amber-700 dark:text-amber-300',
    archived: 'border-blue-500/30 bg-blue-500/10 text-blue-700 dark:text-blue-300',
};

export function KelasRombelStatusBadge({ status }: Props) {
    return (
        <Badge variant="outline" className={variants[status]}>
            {classGroupStatusLabels[status]}
        </Badge>
    );
}
