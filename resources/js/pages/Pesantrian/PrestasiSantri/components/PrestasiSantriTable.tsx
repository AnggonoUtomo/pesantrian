import { Link } from '@inertiajs/react';
import { Eye } from 'lucide-react';
import { Button } from '@/components/ui/button';
import { routeOr } from '@/lib/route';
import type { StudentAchievement } from '../types';
import {
    achievementTypeLabel,
    formatDate,
    lifecycleSummary,
} from './prestasiSantriDisplay';
import {
    PrestasiSantriLevelBadge,
    PrestasiSantriStatusBadge,
} from './PrestasiSantriStatusBadge';

type Props = {
    achievements: StudentAchievement[];
    canRecord: boolean;
    canVerify: boolean;
    canArchive: boolean;
    onEdit: (achievement: StudentAchievement) => void;
    onSubmitAchievement: (achievement: StudentAchievement) => void;
    onVerify: (achievement: StudentAchievement) => void;
    onRequestRevision: (achievement: StudentAchievement) => void;
    onVoid: (achievement: StudentAchievement) => void;
};

export function PrestasiSantriTable({
    achievements,
    canRecord,
    canVerify,
    canArchive,
    onEdit,
    onSubmitAchievement,
    onVerify,
    onRequestRevision,
    onVoid,
}: Props) {
    return (
        <div className="space-y-3">
            <div className="hidden overflow-hidden rounded-xl border lg:block">
                <table className="w-full text-left text-sm">
                    <thead className="bg-muted/60 text-xs uppercase tracking-wide text-foreground/60">
                        <tr>
                            <th className="px-4 py-3">Nomor prestasi</th>
                            <th className="px-4 py-3">Santri</th>
                            <th className="px-4 py-3">Kategori</th>
                            <th className="px-4 py-3">Kegiatan</th>
                            <th className="px-4 py-3">Tingkat</th>
                            <th className="px-4 py-3">Status</th>
                            <th className="px-4 py-3">Ringkasan</th>
                            <th className="px-4 py-3 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody className="divide-y">
                        {achievements.map((achievement) => (
                            <tr key={achievement.id} className="align-top">
                                <td className="px-4 py-3 font-medium">
                                    {achievement.achievement_no}
                                    <p className="mt-1 text-xs font-normal text-foreground/55">
                                        {formatDate(achievement.achieved_on)}
                                    </p>
                                </td>
                                <td className="px-4 py-3">
                                    {achievement.student_name}
                                    <p className="text-xs text-foreground/55">
                                        {achievement.student_no}
                                    </p>
                                </td>
                                <td className="px-4 py-3">
                                    {achievement.category.name}
                                </td>
                                <td className="px-4 py-3">
                                    <p className="font-medium">
                                        {achievement.title}
                                    </p>
                                    <p className="text-xs text-foreground/55">
                                        {achievementTypeLabel(
                                            achievement.achievement_type,
                                        )}{' '}
                                        · {achievement.result}
                                    </p>
                                </td>
                                <td className="px-4 py-3">
                                    <PrestasiSantriLevelBadge
                                        level={achievement.level}
                                    />
                                </td>
                                <td className="px-4 py-3">
                                    <PrestasiSantriStatusBadge
                                        status={achievement.status}
                                    />
                                </td>
                                <td className="px-4 py-3 text-foreground/65">
                                    {lifecycleSummary(achievement.summary)}
                                </td>
                                <td className="px-4 py-3 text-right">
                                    <Button asChild variant="outline" size="sm">
                                        <Link
                                            href={routeOr(
                                                `/pesantrian/prestasi-santri/${achievement.id}`,
                                                'pesantrian.prestasi-santri.show',
                                                achievement.id,
                                            )}
                                            className="gap-2"
                                        >
                                            <Eye
                                                className="size-4"
                                                aria-hidden="true"
                                            />
                                            Lihat detail
                                        </Link>
                                    </Button>
                                    <AchievementActions
                                        achievement={achievement}
                                        canRecord={canRecord}
                                        canVerify={canVerify}
                                        canArchive={canArchive}
                                        onEdit={onEdit}
                                        onSubmitAchievement={
                                            onSubmitAchievement
                                        }
                                        onVerify={onVerify}
                                        onRequestRevision={onRequestRevision}
                                        onVoid={onVoid}
                                    />
                                </td>
                            </tr>
                        ))}
                    </tbody>
                </table>
            </div>

            <div className="grid gap-3 lg:hidden">
                {achievements.map((achievement) => (
                    <article
                        key={achievement.id}
                        className="rounded-xl border bg-background p-4"
                    >
                        <div className="flex items-start justify-between gap-3">
                            <div>
                                <p className="text-xs text-foreground/55">
                                    {achievement.achievement_no}
                                </p>
                                <h3 className="mt-1 font-semibold">
                                    {achievement.title}
                                </h3>
                                <p className="text-sm text-foreground/65">
                                    {achievement.student_name} ·{' '}
                                    {achievement.student_no}
                                </p>
                            </div>
                            <PrestasiSantriStatusBadge
                                status={achievement.status}
                            />
                        </div>
                        <div className="mt-3 flex flex-wrap gap-2">
                            <PrestasiSantriLevelBadge
                                level={achievement.level}
                            />
                            <span className="rounded-full bg-muted px-2.5 py-1 text-xs text-foreground/65">
                                {achievement.category.name}
                            </span>
                            <span className="rounded-full bg-muted px-2.5 py-1 text-xs text-foreground/65">
                                {formatDate(achievement.achieved_on)}
                            </span>
                        </div>
                        <p className="mt-3 text-sm text-foreground/65">
                            {lifecycleSummary(achievement.summary)}
                        </p>
                        <Button
                            asChild
                            variant="outline"
                            size="sm"
                            className="mt-4"
                        >
                            <Link
                                href={routeOr(
                                    `/pesantrian/prestasi-santri/${achievement.id}`,
                                    'pesantrian.prestasi-santri.show',
                                    achievement.id,
                                )}
                            >
                                Lihat detail
                            </Link>
                        </Button>
                        <AchievementActions
                            achievement={achievement}
                            canRecord={canRecord}
                            canVerify={canVerify}
                            canArchive={canArchive}
                            onEdit={onEdit}
                            onSubmitAchievement={onSubmitAchievement}
                            onVerify={onVerify}
                            onRequestRevision={onRequestRevision}
                            onVoid={onVoid}
                        />
                    </article>
                ))}
            </div>
        </div>
    );
}

function AchievementActions({
    achievement,
    canRecord,
    canVerify,
    canArchive,
    onEdit,
    onSubmitAchievement,
    onVerify,
    onRequestRevision,
    onVoid,
}: {
    achievement: StudentAchievement;
    canRecord: boolean;
    canVerify: boolean;
    canArchive: boolean;
    onEdit: (achievement: StudentAchievement) => void;
    onSubmitAchievement: (achievement: StudentAchievement) => void;
    onVerify: (achievement: StudentAchievement) => void;
    onRequestRevision: (achievement: StudentAchievement) => void;
    onVoid: (achievement: StudentAchievement) => void;
}) {
    const isFinal = achievement.summary.is_final;

    return (
        <div className="mt-2 flex flex-wrap justify-end gap-2">
            {canRecord &&
            (achievement.status === 'draft' ||
                achievement.status === 'needs_revision') ? (
                <Button
                    type="button"
                    variant="outline"
                    size="sm"
                    onClick={() => onEdit(achievement)}
                >
                    Edit
                </Button>
            ) : null}
            {canRecord &&
            (achievement.status === 'draft' ||
                achievement.status === 'needs_revision') ? (
                <Button
                    type="button"
                    variant="outline"
                    size="sm"
                    onClick={() => onSubmitAchievement(achievement)}
                >
                    Submit
                </Button>
            ) : null}
            {canVerify && achievement.status === 'submitted' ? (
                <>
                    <Button
                        type="button"
                        variant="outline"
                        size="sm"
                        onClick={() => onVerify(achievement)}
                    >
                        Verifikasi
                    </Button>
                    <Button
                        type="button"
                        variant="outline"
                        size="sm"
                        onClick={() => onRequestRevision(achievement)}
                    >
                        Revisi
                    </Button>
                </>
            ) : null}
            {canArchive && !isFinal ? (
                <Button
                    type="button"
                    variant="destructive"
                    size="sm"
                    onClick={() => onVoid(achievement)}
                >
                    Batalkan
                </Button>
            ) : null}
        </div>
    );
}
