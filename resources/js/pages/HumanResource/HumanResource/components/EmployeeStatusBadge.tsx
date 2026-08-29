import { Badge } from '@/components/ui/badge';
import type { EmployeeStatus } from '../types';
import { employeeStatusLabels } from './employeeDisplay';

type Props = {
    status: EmployeeStatus;
};

export function EmployeeStatusBadge({ status }: Props) {
    return (
        <Badge variant={status === 'active' ? 'default' : 'secondary'}>
            {employeeStatusLabels[status]}
        </Badge>
    );
}
