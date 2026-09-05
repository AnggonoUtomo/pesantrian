import { Badge } from '@/components/ui/badge';
import type { DormitoryStatus } from '../types';
import { dormitoryStatusLabel } from './asramaDisplay';

type Props = {
    status: DormitoryStatus;
};

export function AsramaStatusBadge({ status }: Props) {
    const variant = status === 'active' ? 'default' : 'secondary';

    return <Badge variant={variant}>{dormitoryStatusLabel(status)}</Badge>;
}
