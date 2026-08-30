import { Badge } from '@/components/ui/badge';
import type { RegistrationFeeStatus } from '../types';
import { registrationFeeStatusLabels } from './admissionDisplay';

export function RegistrationFeeStatusBadge({
    status,
}: {
    status: RegistrationFeeStatus;
}) {
    const variant = status === 'verified' ? 'default' : 'secondary';

    return <Badge variant={variant}>{registrationFeeStatusLabels[status]}</Badge>;
}
