import { Badge } from '@/components/ui/badge';
import type { OrganizationUnitStatus } from '../types';
import { statusLabels } from './organizationUnitDisplay';

export function OrganizationUnitStatusBadge({
    status,
}: {
    status: OrganizationUnitStatus;
}) {
    return (
        <Badge variant={status === 'active' ? 'default' : 'secondary'}>
            {statusLabels[status]}
        </Badge>
    );
}
