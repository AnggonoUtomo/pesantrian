import { Link } from '@inertiajs/react';
import { Button } from '@/components/ui/button';
import { routeOr } from '@/lib/route';
import type { TahfidzSubmission } from '../types';
import {
    memorizationRange,
    tahfidzTypeLabel,
    targetRange,
} from './tahfidzDisplay';
import { TahfidzStatusBadge } from './TahfidzStatusBadge';

type Props = {
    submissions: TahfidzSubmission[];
};

export function TahfidzTable({ submissions }: Props) {
    return (
        <div className="overflow-hidden rounded-xl border">
            <div className="hidden overflow-x-auto md:block">
                <table className="w-full text-left text-sm">
                    <thead className="bg-muted/50 text-xs text-foreground/65 uppercase">
                        <tr>
                            <th scope="col" className="px-4 py-3">
                                Santri
                            </th>
                            <th scope="col" className="px-4 py-3">
                                Program
                            </th>
                            <th scope="col" className="px-4 py-3">
                                Setoran
                            </th>
                            <th scope="col" className="px-4 py-3">
                                Pembimbing
                            </th>
                            <th scope="col" className="px-4 py-3">
                                Status
                            </th>
                            <th scope="col" className="px-4 py-3">
                                Aksi
                            </th>
                        </tr>
                    </thead>
                    <tbody className="divide-y">
                        {submissions.map((submission) => (
                            <tr key={submission.id} className="bg-background">
                                <td className="px-4 py-3">
                                    <div className="font-medium">
                                        {submission.student_name}
                                    </div>
                                    <div className="text-xs text-foreground/60">
                                        NIS {submission.student_no}
                                    </div>
                                </td>
                                <td className="px-4 py-3">
                                    <div className="text-foreground/70">
                                        {submission.program.name}
                                    </div>
                                    <div className="text-xs text-foreground/55">
                                        {submission.program.code}
                                    </div>
                                </td>
                                <td className="px-4 py-3">
                                    <div className="text-foreground/70">
                                        {tahfidzTypeLabel(submission.type)} ·{' '}
                                        {submission.submission_date}
                                    </div>
                                    <div className="text-xs text-foreground/55">
                                        {memorizationRange(submission)}
                                    </div>
                                </td>
                                <td className="px-4 py-3 text-foreground/70">
                                    {submission.supervisor_name ?? 'Belum diisi'}
                                </td>
                                <td className="px-4 py-3">
                                    <TahfidzStatusBadge
                                        status={submission.status}
                                    />
                                </td>
                                <td className="px-4 py-3">
                                    <Button
                                        asChild
                                        variant="secondary"
                                        size="sm"
                                    >
                                        <Link
                                            href={submissionShowUrl(submission)}
                                            prefetch
                                        >
                                            Lihat detail
                                        </Link>
                                    </Button>
                                </td>
                            </tr>
                        ))}
                    </tbody>
                </table>
            </div>
            <div className="divide-y md:hidden">
                {submissions.map((submission) => (
                    <article
                        key={submission.id}
                        className="space-y-3 bg-background p-4"
                    >
                        <div className="flex items-start justify-between gap-3">
                            <div>
                                <h3 className="font-medium">
                                    {submission.student_name}
                                </h3>
                                <p className="text-xs text-foreground/60">
                                    NIS {submission.student_no} ·{' '}
                                    {submission.program.name}
                                </p>
                            </div>
                            <TahfidzStatusBadge status={submission.status} />
                        </div>
                        <dl className="grid gap-2 text-sm text-foreground/70">
                            <TahfidzField
                                label="Setoran"
                                value={`${tahfidzTypeLabel(submission.type)} · ${memorizationRange(submission)}`}
                            />
                            <TahfidzField
                                label="Target"
                                value={targetRange(submission)}
                            />
                            <TahfidzField
                                label="Pembimbing"
                                value={submission.supervisor_name ?? 'Belum diisi'}
                            />
                        </dl>
                        <Button asChild variant="secondary" size="sm">
                            <Link href={submissionShowUrl(submission)} prefetch>
                                Lihat detail
                            </Link>
                        </Button>
                    </article>
                ))}
            </div>
        </div>
    );
}

function TahfidzField({ label, value }: { label: string; value: string }) {
    return (
        <div className="flex justify-between gap-4">
            <dt className="text-foreground/55">{label}</dt>
            <dd className="text-right font-medium">{value}</dd>
        </div>
    );
}

function submissionShowUrl(submission: TahfidzSubmission): string {
    return String(
        routeOr(
            `/pesantrian/tahfidz/${submission.id}`,
            'pesantrian.tahfidz.show',
            submission.id,
        ),
    );
}
