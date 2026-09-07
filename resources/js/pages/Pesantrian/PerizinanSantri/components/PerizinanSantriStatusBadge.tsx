import { Badge } from '@/components/ui/badge';
import type { StudentPermitStatus } from '../types';
import { permitStatusLabel } from './perizinanSantriDisplay';

type Props = {
    status: StudentPermitStatus;
};

export function PerizinanSantriStatusBadge({ status }: Props) {
    const variant =
        status === 'approved' || status === 'checked_out'
            ? 'default'
            : status === 'rejected' || status === 'void'
              ? 'destructive'
              : 'secondary';

    return <Badge variant={variant}>{permitStatusLabel(status)}</Badge>;
}
