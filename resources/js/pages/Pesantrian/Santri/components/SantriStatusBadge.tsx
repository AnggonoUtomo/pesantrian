import { Badge } from '@/components/ui/badge';
import type { StudentStatus } from '../types';
import { studentStatusLabels } from './santriDisplay';

type Props = {
    status: StudentStatus;
};

const variants: Record<StudentStatus, string> = {
    active: 'border-emerald-500/30 bg-emerald-500/10 text-emerald-700 dark:text-emerald-300',
    inactive: 'border-slate-500/30 bg-slate-500/10 text-slate-700 dark:text-slate-300',
    transferred: 'border-amber-500/30 bg-amber-500/10 text-amber-700 dark:text-amber-300',
    graduated: 'border-blue-500/30 bg-blue-500/10 text-blue-700 dark:text-blue-300',
};

export function SantriStatusBadge({ status }: Props) {
    return (
        <Badge variant="outline" className={variants[status]}>
            {studentStatusLabels[status]}
        </Badge>
    );
}
