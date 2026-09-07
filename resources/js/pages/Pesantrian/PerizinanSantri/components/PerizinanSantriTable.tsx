import { Link } from '@inertiajs/react';
import { Button } from '@/components/ui/button';
import { routeOr } from '@/lib/route';
import type { StudentPermit } from '../types';
import {
    formatDateTime,
    lifecycleSummary,
    permitTypeLabel,
} from './perizinanSantriDisplay';
import { PerizinanSantriStatusBadge } from './PerizinanSantriStatusBadge';

type Props = {
    permits: StudentPermit[];
    canManage: boolean;
    canApprove: boolean;
    canCheckout: boolean;
    canReturn: boolean;
    canArchive: boolean;
    onEdit: (permit: StudentPermit) => void;
    onSubmitPermit: (permit: StudentPermit) => void;
    onApprove: (permit: StudentPermit) => void;
    onReject: (permit: StudentPermit) => void;
    onCheckout: (permit: StudentPermit) => void;
    onReturn: (permit: StudentPermit) => void;
    onVoid: (permit: StudentPermit) => void;
};

export function PerizinanSantriTable({
    permits,
    canManage,
    canApprove,
    canCheckout,
    canReturn,
    canArchive,
    onEdit,
    onSubmitPermit,
    onApprove,
    onReject,
    onCheckout,
    onReturn,
    onVoid,
}: Props) {
    return (
        <div className="overflow-hidden rounded-xl border bg-background">
            <div className="hidden overflow-x-auto md:block">
                <table className="w-full min-w-[920px] text-left text-sm">
                    <thead className="bg-muted/60 text-xs uppercase tracking-wide text-foreground/60">
                        <tr>
                            <th className="px-4 py-3 font-medium">
                                Nomor izin
                            </th>
                            <th className="px-4 py-3 font-medium">Santri</th>
                            <th className="px-4 py-3 font-medium">
                                Jenis / tujuan
                            </th>
                            <th className="px-4 py-3 font-medium">Rentang</th>
                            <th className="px-4 py-3 font-medium">Status</th>
                            <th className="px-4 py-3 font-medium">Ringkasan</th>
                            <th className="px-4 py-3 text-right font-medium">
                                Aksi
                            </th>
                        </tr>
                    </thead>
                    <tbody className="divide-y">
                        {permits.map((permit) => (
                            <tr key={permit.id} className="align-top">
                                <td className="px-4 py-3 font-medium">
                                    {permit.permit_no}
                                </td>
                                <td className="px-4 py-3">
                                    <div className="font-medium">
                                        {permit.student_name}
                                    </div>
                                    <div className="text-xs text-foreground/60">
                                        {permit.student_no}
                                    </div>
                                </td>
                                <td className="px-4 py-3">
                                    <div className="font-medium">
                                        {permitTypeLabel(permit.permit_type)}
                                    </div>
                                    <div className="text-xs text-foreground/60">
                                        {permit.destination ??
                                            'Tujuan belum diisi'}
                                    </div>
                                </td>
                                <td className="px-4 py-3 text-xs text-foreground/70">
                                    <div>{formatDateTime(permit.starts_at)}</div>
                                    <div>{formatDateTime(permit.ends_at)}</div>
                                </td>
                                <td className="px-4 py-3">
                                    <PerizinanSantriStatusBadge
                                        status={permit.status}
                                    />
                                </td>
                                <td className="px-4 py-3 text-xs text-foreground/65">
                                    {lifecycleSummary(permit.summary)}
                                </td>
                                <td className="px-4 py-3 text-right">
                                    <Button asChild variant="secondary" size="sm">
                                        <Link
                                            href={permitShowUrl(permit)}
                                            prefetch
                                        >
                                            Lihat detail
                                        </Link>
                                    </Button>
                                    <PermitActions
                                        permit={permit}
                                        canManage={canManage}
                                        canApprove={canApprove}
                                        canCheckout={canCheckout}
                                        canReturn={canReturn}
                                        canArchive={canArchive}
                                        onEdit={onEdit}
                                        onSubmitPermit={onSubmitPermit}
                                        onApprove={onApprove}
                                        onReject={onReject}
                                        onCheckout={onCheckout}
                                        onReturn={onReturn}
                                        onVoid={onVoid}
                                    />
                                </td>
                            </tr>
                        ))}
                    </tbody>
                </table>
            </div>
            <div className="divide-y md:hidden">
                {permits.map((permit) => (
                    <article
                        key={permit.id}
                        className="space-y-3 bg-background p-4"
                    >
                        <div className="flex items-start justify-between gap-3">
                            <div>
                                <h3 className="font-medium">
                                    {permit.student_name}
                                </h3>
                                <p className="text-xs text-foreground/60">
                                    {permit.permit_no} · {permit.student_no}
                                </p>
                            </div>
                            <PerizinanSantriStatusBadge
                                status={permit.status}
                            />
                        </div>
                        <dl className="grid gap-2 text-sm text-foreground/70">
                            <PermitField
                                label="Jenis"
                                value={permitTypeLabel(permit.permit_type)}
                            />
                            <PermitField
                                label="Tujuan"
                                value={permit.destination ?? 'Belum diisi'}
                            />
                            <PermitField
                                label="Mulai"
                                value={formatDateTime(permit.starts_at)}
                            />
                            <PermitField
                                label="Batas kembali"
                                value={formatDateTime(permit.ends_at)}
                            />
                        </dl>
                        <div className="flex items-center justify-between gap-3">
                            <p className="text-xs text-foreground/60">
                                {lifecycleSummary(permit.summary)}
                            </p>
                            <Button asChild variant="secondary" size="sm">
                                <Link href={permitShowUrl(permit)} prefetch>
                                    Lihat detail
                                </Link>
                            </Button>
                        </div>
                        <PermitActions
                            permit={permit}
                            canManage={canManage}
                            canApprove={canApprove}
                            canCheckout={canCheckout}
                            canReturn={canReturn}
                            canArchive={canArchive}
                            onEdit={onEdit}
                            onSubmitPermit={onSubmitPermit}
                            onApprove={onApprove}
                            onReject={onReject}
                            onCheckout={onCheckout}
                            onReturn={onReturn}
                            onVoid={onVoid}
                        />
                    </article>
                ))}
            </div>
        </div>
    );
}

function PermitActions({
    permit,
    canManage,
    canApprove,
    canCheckout,
    canReturn,
    canArchive,
    onEdit,
    onSubmitPermit,
    onApprove,
    onReject,
    onCheckout,
    onReturn,
    onVoid,
}: Omit<Props, 'permits'> & { permit: StudentPermit }) {
    const isFinal =
        permit.status === 'rejected' ||
        permit.status === 'returned' ||
        permit.status === 'void';

    return (
        <div className="mt-2 flex flex-wrap justify-end gap-2">
            {canManage && permit.status === 'draft' ? (
                <>
                    <Button
                        type="button"
                        variant="outline"
                        size="sm"
                        onClick={() => onEdit(permit)}
                    >
                        Edit
                    </Button>
                    <Button
                        type="button"
                        variant="outline"
                        size="sm"
                        onClick={() => onSubmitPermit(permit)}
                    >
                        Submit
                    </Button>
                </>
            ) : null}
            {canApprove && permit.status === 'submitted' ? (
                <>
                    <Button
                        type="button"
                        variant="outline"
                        size="sm"
                        onClick={() => onApprove(permit)}
                    >
                        Approve
                    </Button>
                    <Button
                        type="button"
                        variant="outline"
                        size="sm"
                        onClick={() => onReject(permit)}
                    >
                        Reject
                    </Button>
                </>
            ) : null}
            {canCheckout && permit.status === 'approved' ? (
                <Button
                    type="button"
                    variant="outline"
                    size="sm"
                    onClick={() => onCheckout(permit)}
                >
                    Check-out
                </Button>
            ) : null}
            {canReturn && permit.status === 'checked_out' ? (
                <Button
                    type="button"
                    variant="outline"
                    size="sm"
                    onClick={() => onReturn(permit)}
                >
                    Return
                </Button>
            ) : null}
            {canArchive && !isFinal ? (
                <Button
                    type="button"
                    variant="outline"
                    size="sm"
                    onClick={() => onVoid(permit)}
                >
                    Void
                </Button>
            ) : null}
        </div>
    );
}

function PermitField({ label, value }: { label: string; value: string }) {
    return (
        <div className="flex justify-between gap-4">
            <dt className="text-foreground/55">{label}</dt>
            <dd className="text-right font-medium">{value}</dd>
        </div>
    );
}

function permitShowUrl(permit: StudentPermit): string {
    return String(
        routeOr(
            `/pesantrian/student-permits/${permit.id}`,
            'pesantrian.student-permits.show',
            permit.id,
        ),
    );
}
