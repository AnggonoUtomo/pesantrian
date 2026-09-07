import { Badge } from '@/components/ui/badge';
import type { TahfidzSubmissionStatus } from '../types';
import { tahfidzStatusLabel } from './tahfidzDisplay';

type Props = {
    status: TahfidzSubmissionStatus;
};

export function TahfidzStatusBadge({ status }: Props) {
    const variant =
        status === 'accepted'
            ? 'default'
            : status === 'void'
              ? 'destructive'
              : 'secondary';

    return <Badge variant={variant}>{tahfidzStatusLabel(status)}</Badge>;
}
