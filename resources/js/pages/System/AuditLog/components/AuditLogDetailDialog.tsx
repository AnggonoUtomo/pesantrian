import { Badge } from '@/components/ui/badge';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import type { AuditLogRecord } from '../types';

type Props = {
    record: AuditLogRecord | null;
    onOpenChange: (open: boolean) => void;
};

export function AuditLogDetailDialog({ record, onOpenChange }: Props) {
    return (
        <Dialog open={record !== null} onOpenChange={onOpenChange}>
            <DialogContent className="max-h-[85vh] overflow-y-auto sm:max-w-2xl">
                <DialogHeader>
                    <DialogTitle>Detail audit log</DialogTitle>
                    <DialogDescription>
                        Record ini hanya dapat dibaca dan tidak dapat diubah.
                    </DialogDescription>
                </DialogHeader>
                {record ? (
                    <div className="space-y-4 text-sm">
                        <div className="flex flex-wrap gap-2">
                            <Badge className="dashboard-badge dashboard-badge--blue">
                                {record.module}
                            </Badge>
                            <Badge className="dashboard-badge dashboard-badge--emerald">
                                {record.action}
                            </Badge>
                        </div>
                        <dl className="dashboard-subcard grid gap-3 rounded-xl border p-4 sm:grid-cols-2">
                            {[
                                ['Actor', record.actorName ?? 'System'],
                                ['Actor ID', record.actorId ?? '-'],
                                ['Subject', record.subjectType],
                                ['Subject ID', record.subjectId ?? '-'],
                                ['Event ID', record.eventId],
                                ['Correlation ID', record.correlationId],
                                [
                                    'Waktu',
                                    new Intl.DateTimeFormat('id-ID', {
                                        dateStyle: 'medium',
                                        timeStyle: 'medium',
                                    }).format(new Date(record.createdAt)),
                                ],
                                ['Alasan', record.reason ?? '-'],
                            ].map(([label, value]) => (
                                <div key={label} className="min-w-0">
                                    <dt className="text-xs text-foreground/60">
                                        {label}
                                    </dt>
                                    <dd className="mt-1 font-medium break-all">
                                        {value}
                                    </dd>
                                </div>
                            ))}
                        </dl>
                        <div>
                            <h3 className="font-medium">Metadata terfilter</h3>
                            <pre className="dashboard-subcard mt-2 overflow-x-auto rounded-xl border p-4 text-xs whitespace-pre-wrap">
                                {JSON.stringify(record.metadata, null, 2)}
                            </pre>
                        </div>
                    </div>
                ) : null}
            </DialogContent>
        </Dialog>
    );
}
