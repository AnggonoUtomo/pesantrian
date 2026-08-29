import { Badge } from '@/components/ui/badge';
import type { AcademicPeriodStatus } from '../types';

const labels: Record<AcademicPeriodStatus, string> = {
    draft: 'Draft',
    active: 'Aktif',
    closed: 'Ditutup',
};

export function AcademicPeriodStatusBadge({
    status,
}: {
    status: AcademicPeriodStatus;
}) {
    return (
        <Badge variant={status === 'closed' ? 'outline' : 'secondary'}>
            {labels[status]}
        </Badge>
    );
}
