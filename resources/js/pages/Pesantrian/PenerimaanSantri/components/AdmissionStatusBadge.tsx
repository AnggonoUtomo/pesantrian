import { Badge } from '@/components/ui/badge';
import type { AdmissionStatus } from '../types';
import { admissionStatusLabels } from './admissionDisplay';

const variants: Record<AdmissionStatus, 'default' | 'secondary' | 'outline'> = {
    draft: 'secondary',
    submitted: 'outline',
    verified: 'default',
    accepted: 'default',
    rejected: 'secondary',
    cancelled: 'secondary',
};

export function AdmissionStatusBadge({ status }: { status: AdmissionStatus }) {
    return <Badge variant={variants[status]}>{admissionStatusLabels[status]}</Badge>;
}
