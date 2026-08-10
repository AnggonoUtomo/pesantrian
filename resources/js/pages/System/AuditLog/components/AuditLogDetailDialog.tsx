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
                                {record.moduleLabel}
                            </Badge>
                            <Badge className="dashboard-badge dashboard-badge--emerald">
                                {record.actionLabel}
                            </Badge>
                        </div>
                        <dl className="dashboard-subcard grid gap-3 rounded-xl border p-4 sm:grid-cols-2">
                            {[
                                ['Actor', record.actorName ?? 'System'],
                                ['Subject', record.subjectLabel],
                                [
                                    'Waktu',
                                    new Intl.DateTimeFormat('id-ID', {
                                        dateStyle: 'medium',
                                        timeStyle: 'medium',
                                    }).format(new Date(record.createdAt)),
                                ],
                                ['Alasan', record.reason ?? '-'],
                                ...(record.securityContext?.browser
                                    ? [
                                          [
                                              'Browser',
                                              record.securityContext.browser,
                                          ],
                                      ]
                                    : []),
                                ...(record.securityContext?.ipAddress
                                    ? [
                                          [
                                              'Alamat IP',
                                              record.securityContext.ipAddress,
                                          ],
                                      ]
                                    : []),
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
                        {record.settingChange ? (
                            <section className="dashboard-subcard space-y-3 rounded-xl border p-4">
                                <h3 className="font-semibold">
                                    Perubahan pengaturan
                                </h3>
                                <dl className="grid gap-3 sm:grid-cols-2">
                                    {[
                                        [
                                            'Kategori',
                                            record.settingChange.category,
                                        ],
                                        [
                                            'Pengaturan',
                                            record.settingChange.setting,
                                        ],
                                        [
                                            'Nilai sebelumnya',
                                            record.settingChange.beforeValue,
                                        ],
                                        [
                                            'Nilai setelahnya',
                                            record.settingChange.afterValue,
                                        ],
                                    ].map(([label, value]) => (
                                        <div key={label} className="min-w-0">
                                            <dt className="text-xs text-foreground/60">
                                                {label}
                                            </dt>
                                            <dd className="mt-1 font-medium break-words">
                                                {value}
                                            </dd>
                                        </div>
                                    ))}
                                </dl>
                            </section>
                        ) : null}
                    </div>
                ) : null}
            </DialogContent>
        </Dialog>
    );
}
