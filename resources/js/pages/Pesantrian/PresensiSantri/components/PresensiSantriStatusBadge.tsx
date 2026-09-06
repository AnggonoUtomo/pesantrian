import { Badge } from '@/components/ui/badge';
import type {
    StudentAttendanceEntryStatus,
    StudentAttendanceStatus,
} from '../types';
import { entryStatusLabel, sessionStatusLabel } from './presensiSantriDisplay';

type Props = {
    status: StudentAttendanceStatus | StudentAttendanceEntryStatus;
    type?: 'session' | 'entry';
};

export function PresensiSantriStatusBadge({ status, type = 'session' }: Props) {
    const variant =
        status === 'submitted' || status === 'present'
            ? 'default'
            : status === 'void' || status === 'absent'
              ? 'destructive'
              : 'secondary';
    const label =
        type === 'entry'
            ? entryStatusLabel(status as StudentAttendanceEntryStatus)
            : sessionStatusLabel(status as StudentAttendanceStatus);

    return <Badge variant={variant}>{label}</Badge>;
}
