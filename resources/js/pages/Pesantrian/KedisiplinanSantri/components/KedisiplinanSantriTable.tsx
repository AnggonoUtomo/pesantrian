import { Link } from '@inertiajs/react';
import { Button } from '@/components/ui/button';
import { routeOr } from '@/lib/route';
import type { StudentDisciplineCase } from '../types';
import {
    formatDateTime,
    lifecycleSummary,
    pointsLabel,
} from './kedisiplinanSantriDisplay';
import {
    KedisiplinanSantriSeverityBadge,
    KedisiplinanSantriStatusBadge,
} from './KedisiplinanSantriStatusBadge';

type Props = {
    cases: StudentDisciplineCase[];
    canManage: boolean;
    canReview: boolean;
    canResolve: boolean;
    onEdit: (disciplineCase: StudentDisciplineCase) => void;
    onSubmitCase: (disciplineCase: StudentDisciplineCase) => void;
    onReview: (disciplineCase: StudentDisciplineCase) => void;
    onAssignAction: (disciplineCase: StudentDisciplineCase) => void;
    onResolve: (disciplineCase: StudentDisciplineCase) => void;
    onVoid: (disciplineCase: StudentDisciplineCase) => void;
};

export function KedisiplinanSantriTable({
    cases,
    canManage,
    canReview,
    canResolve,
    onEdit,
    onSubmitCase,
    onReview,
    onAssignAction,
    onResolve,
    onVoid,
}: Props) {
    return (
        <div className="overflow-hidden rounded-xl border bg-background">
            <div className="hidden overflow-x-auto md:block">
                <table className="w-full min-w-[980px] text-left text-sm">
                    <thead className="bg-muted/60 text-xs uppercase tracking-wide text-foreground/60">
                        <tr>
                            <th className="px-4 py-3 font-medium">
                                Nomor kasus
                            </th>
                            <th className="px-4 py-3 font-medium">Santri</th>
                            <th className="px-4 py-3 font-medium">
                                Kategori
                            </th>
                            <th className="px-4 py-3 font-medium">
                                Kejadian
                            </th>
                            <th className="px-4 py-3 font-medium">Tingkat</th>
                            <th className="px-4 py-3 font-medium">Status</th>
                            <th className="px-4 py-3 font-medium">
                                Ringkasan
                            </th>
                            <th className="px-4 py-3 text-right font-medium">
                                Aksi
                            </th>
                        </tr>
                    </thead>
                    <tbody className="divide-y">
                        {cases.map((disciplineCase) => (
                            <tr key={disciplineCase.id} className="align-top">
                                <td className="px-4 py-3 font-medium">
                                    {disciplineCase.case_no}
                                </td>
                                <td className="px-4 py-3">
                                    <div className="font-medium">
                                        {disciplineCase.student_name}
                                    </div>
                                    <div className="text-xs text-foreground/60">
                                        {disciplineCase.student_no}
                                    </div>
                                </td>
                                <td className="px-4 py-3">
                                    <div className="font-medium">
                                        {disciplineCase.category.name}
                                    </div>
                                    <div className="text-xs text-foreground/60">
                                        {pointsLabel(disciplineCase.points)}
                                    </div>
                                </td>
                                <td className="px-4 py-3 text-xs text-foreground/70">
                                    <div>
                                        {formatDateTime(
                                            disciplineCase.occurred_at,
                                        )}
                                    </div>
                                    <div>
                                        {disciplineCase.location ??
                                            'Lokasi belum diisi'}
                                    </div>
                                </td>
                                <td className="px-4 py-3">
                                    <KedisiplinanSantriSeverityBadge
                                        severity={disciplineCase.severity}
                                    />
                                </td>
                                <td className="px-4 py-3">
                                    <KedisiplinanSantriStatusBadge
                                        status={disciplineCase.status}
                                    />
                                </td>
                                <td className="px-4 py-3 text-xs text-foreground/65">
                                    {lifecycleSummary(disciplineCase.summary)}
                                </td>
                                <td className="px-4 py-3 text-right">
                                    <Button asChild variant="secondary" size="sm">
                                        <Link
                                            href={disciplineCaseShowUrl(
                                                disciplineCase,
                                            )}
                                            prefetch
                                        >
                                            Lihat detail
                                        </Link>
                                    </Button>
                                    <CaseActions
                                        case={disciplineCase}
                                        canManage={canManage}
                                        canReview={canReview}
                                        canResolve={canResolve}
                                        onEdit={onEdit}
                                        onSubmitCase={onSubmitCase}
                                        onReview={onReview}
                                        onAssignAction={onAssignAction}
                                        onResolve={onResolve}
                                        onVoid={onVoid}
                                    />
                                </td>
                            </tr>
                        ))}
                    </tbody>
                </table>
            </div>
            <div className="divide-y md:hidden">
                {cases.map((disciplineCase) => (
                    <article
                        key={disciplineCase.id}
                        className="space-y-3 bg-background p-4"
                    >
                        <div className="flex items-start justify-between gap-3">
                            <div>
                                <h3 className="font-medium">
                                    {disciplineCase.student_name}
                                </h3>
                                <p className="text-xs text-foreground/60">
                                    {disciplineCase.case_no} -{' '}
                                    {disciplineCase.student_no}
                                </p>
                            </div>
                            <KedisiplinanSantriStatusBadge
                                status={disciplineCase.status}
                            />
                        </div>
                        <dl className="grid gap-2 text-sm text-foreground/70">
                            <CaseField
                                label="Kategori"
                                value={disciplineCase.category.name}
                            />
                            <CaseField
                                label="Tingkat"
                                value={pointsLabel(disciplineCase.points)}
                            />
                            <CaseField
                                label="Kejadian"
                                value={formatDateTime(
                                    disciplineCase.occurred_at,
                                )}
                            />
                            <CaseField
                                label="Lokasi"
                                value={
                                    disciplineCase.location ?? 'Belum diisi'
                                }
                            />
                        </dl>
                        <div className="flex items-center justify-between gap-3">
                            <p className="text-xs text-foreground/60">
                                {lifecycleSummary(disciplineCase.summary)}
                            </p>
                            <Button asChild variant="secondary" size="sm">
                                <Link
                                    href={disciplineCaseShowUrl(disciplineCase)}
                                    prefetch
                                >
                                    Lihat detail
                                </Link>
                            </Button>
                        </div>
                        <CaseActions
                            case={disciplineCase}
                            canManage={canManage}
                            canReview={canReview}
                            canResolve={canResolve}
                            onEdit={onEdit}
                            onSubmitCase={onSubmitCase}
                            onReview={onReview}
                            onAssignAction={onAssignAction}
                            onResolve={onResolve}
                            onVoid={onVoid}
                        />
                    </article>
                ))}
            </div>
        </div>
    );
}

function CaseField({ label, value }: { label: string; value: string }) {
    return (
        <div className="flex justify-between gap-3">
            <dt className="text-foreground/50">{label}</dt>
            <dd className="text-right font-medium">{value}</dd>
        </div>
    );
}

function CaseActions({
    case: disciplineCase,
    canManage,
    canReview,
    canResolve,
    onEdit,
    onSubmitCase,
    onReview,
    onAssignAction,
    onResolve,
    onVoid,
}: {
    case: StudentDisciplineCase;
    canManage: boolean;
    canReview: boolean;
    canResolve: boolean;
    onEdit: (disciplineCase: StudentDisciplineCase) => void;
    onSubmitCase: (disciplineCase: StudentDisciplineCase) => void;
    onReview: (disciplineCase: StudentDisciplineCase) => void;
    onAssignAction: (disciplineCase: StudentDisciplineCase) => void;
    onResolve: (disciplineCase: StudentDisciplineCase) => void;
    onVoid: (disciplineCase: StudentDisciplineCase) => void;
}) {
    const isFinal = disciplineCase.summary.is_final;

    return (
        <div className="mt-2 flex flex-wrap justify-end gap-2">
            {canManage && disciplineCase.status === 'draft' ? (
                <>
                    <Button
                        type="button"
                        variant="outline"
                        size="sm"
                        onClick={() => onEdit(disciplineCase)}
                    >
                        Edit
                    </Button>
                    <Button
                        type="button"
                        variant="outline"
                        size="sm"
                        onClick={() => onSubmitCase(disciplineCase)}
                    >
                        Submit
                    </Button>
                </>
            ) : null}
            {canReview && disciplineCase.status === 'submitted' ? (
                <Button
                    type="button"
                    variant="outline"
                    size="sm"
                    onClick={() => onReview(disciplineCase)}
                >
                    Review
                </Button>
            ) : null}
            {canReview && disciplineCase.status === 'in_review' ? (
                <Button
                    type="button"
                    variant="outline"
                    size="sm"
                    onClick={() => onAssignAction(disciplineCase)}
                >
                    Tindakan
                </Button>
            ) : null}
            {canResolve && disciplineCase.status === 'action_assigned' ? (
                <Button
                    type="button"
                    variant="outline"
                    size="sm"
                    onClick={() => onResolve(disciplineCase)}
                >
                    Selesai
                </Button>
            ) : null}
            {canResolve && !isFinal ? (
                <Button
                    type="button"
                    variant="destructive"
                    size="sm"
                    onClick={() => onVoid(disciplineCase)}
                >
                    Batalkan
                </Button>
            ) : null}
        </div>
    );
}

function disciplineCaseShowUrl(disciplineCase: StudentDisciplineCase): string {
    return String(
        routeOr(
            `/pesantrian/student-discipline-cases/${disciplineCase.id}`,
            'pesantrian.student-discipline-cases.show',
            disciplineCase.id,
        ),
    );
}
