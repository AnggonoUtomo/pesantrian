import { Link } from '@inertiajs/react';
import { Button } from '@/components/ui/button';
import { routeOr } from '@/lib/route';
import type { StudentAttendance } from '../types';
import {
    attendanceRatio,
    contextLabel,
    followUpCount,
} from './presensiSantriDisplay';
import { PresensiSantriStatusBadge } from './PresensiSantriStatusBadge';

type Props = {
    attendances: StudentAttendance[];
};

export function PresensiSantriTable({ attendances }: Props) {
    return (
        <div className="overflow-hidden rounded-xl border">
            <div className="hidden overflow-x-auto md:block">
                <table className="w-full text-left text-sm">
                    <thead className="bg-muted/50 text-xs text-foreground/65 uppercase">
                        <tr>
                            <th scope="col" className="px-4 py-3">
                                Kode sesi
                            </th>
                            <th scope="col" className="px-4 py-3">
                                Tanggal
                            </th>
                            <th scope="col" className="px-4 py-3">
                                Konteks
                            </th>
                            <th scope="col" className="px-4 py-3">
                                Ringkasan hadir
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
                        {attendances.map((attendance) => (
                            <tr key={attendance.id} className="bg-background">
                                <td className="px-4 py-3">
                                    <div className="font-medium">
                                        {attendance.session_code}
                                    </div>
                                    <div className="text-xs text-foreground/60">
                                        {attendance.session_name}
                                    </div>
                                </td>
                                <td className="px-4 py-3 text-foreground/70">
                                    {attendance.attendance_date}
                                </td>
                                <td className="px-4 py-3">
                                    <div className="text-foreground/70">
                                        {contextLabel(attendance.context_type)}
                                    </div>
                                    <div className="text-xs text-foreground/55">
                                        {attendance.context_name}
                                    </div>
                                </td>
                                <td className="px-4 py-3 text-foreground/70">
                                    {attendance.summary.present}/
                                    {attendance.summary.total} hadir (
                                    {attendanceRatio(attendance.summary)}) ·{' '}
                                    {followUpCount(attendance.summary)} tindak
                                    lanjut
                                </td>
                                <td className="px-4 py-3">
                                    <PresensiSantriStatusBadge
                                        status={attendance.status}
                                    />
                                </td>
                                <td className="px-4 py-3">
                                    <Button
                                        asChild
                                        variant="secondary"
                                        size="sm"
                                    >
                                        <Link
                                            href={attendanceShowUrl(attendance)}
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
                {attendances.map((attendance) => (
                    <article
                        key={attendance.id}
                        className="space-y-3 bg-background p-4"
                    >
                        <div className="flex items-start justify-between gap-3">
                            <div>
                                <h3 className="font-medium">
                                    {attendance.session_code} ·{' '}
                                    {attendance.session_name}
                                </h3>
                                <p className="text-xs text-foreground/60">
                                    {attendance.attendance_date} ·{' '}
                                    {contextLabel(attendance.context_type)} ·{' '}
                                    {attendance.context_name}
                                </p>
                            </div>
                            <PresensiSantriStatusBadge
                                status={attendance.status}
                            />
                        </div>
                        <dl className="grid gap-2 text-sm text-foreground/70">
                            <AttendanceField
                                label="Hadir"
                                value={`${attendance.summary.present}/${attendance.summary.total} (${attendanceRatio(attendance.summary)})`}
                            />
                            <AttendanceField
                                label="Tindak lanjut"
                                value={String(followUpCount(attendance.summary))}
                            />
                        </dl>
                        <Button asChild variant="secondary" size="sm">
                            <Link href={attendanceShowUrl(attendance)} prefetch>
                                Lihat detail
                            </Link>
                        </Button>
                    </article>
                ))}
            </div>
        </div>
    );
}

function AttendanceField({ label, value }: { label: string; value: string }) {
    return (
        <div className="flex justify-between gap-4">
            <dt className="text-foreground/55">{label}</dt>
            <dd className="text-right font-medium">{value}</dd>
        </div>
    );
}

function attendanceShowUrl(attendance: StudentAttendance): string {
    return String(
        routeOr(
            `/pesantrian/student-attendances/${attendance.id}`,
            'pesantrian.student-attendances.show',
            attendance.id,
        ),
    );
}
