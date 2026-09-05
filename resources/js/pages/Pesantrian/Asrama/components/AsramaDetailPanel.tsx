import { Link } from '@inertiajs/react';
import { ArrowLeft, BedDouble, DoorOpen, UserRoundCheck } from 'lucide-react';
import { Button } from '@/components/ui/button';
import { routeOr } from '@/lib/route';
import type {
    Dormitory,
    DormitoryRoom,
    DormitorySupervisor,
    StudentRoomPlacement,
} from '../types';
import { genderPolicyLabel, occupancyLabel } from './asramaDisplay';
import { AsramaStatusBadge } from './AsramaStatusBadge';

type Props = {
    dormitory: Dormitory;
    canManage: boolean;
    canPlacement: boolean;
    canSupervisor: boolean;
    canArchive: boolean;
};

export function AsramaDetailPanel({
    dormitory,
    canManage,
    canPlacement,
    canSupervisor,
    canArchive,
}: Props) {
    const rooms = dormitory.rooms ?? [];
    const placements = dormitory.placements ?? [];
    const supervisors = dormitory.supervisors ?? [];

    return (
        <div className="space-y-5">
            <Button asChild variant="outline" size="sm">
                <Link
                    href={routeOr(
                        '/pesantrian/asrama',
                        'pesantrian.asrama.index',
                    )}
                    prefetch
                >
                    <ArrowLeft className="size-4" aria-hidden="true" />
                    Kembali ke daftar
                </Link>
            </Button>

            <section className="dashboard-card dashboard-card--blue rounded-2xl border p-4 sm:p-5">
                <div className="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                    <div>
                        <p className="text-sm text-foreground/65">
                            {dormitory.code}
                        </p>
                        <h2 className="text-xl font-semibold">
                            {dormitory.name}
                        </h2>
                        <p className="mt-1 text-sm text-foreground/65">
                            {dormitory.unit.name} ·{' '}
                            {genderPolicyLabel(dormitory.gender_policy)}
                        </p>
                    </div>
                    <div className="flex flex-col gap-2 sm:items-end">
                        <AsramaStatusBadge status={dormitory.status} />
                        <p className="text-xs text-foreground/60">
                            Mutation kamar, penempatan, musyrif, dan arsip akan
                            dibuka pada increment berikutnya.
                        </p>
                    </div>
                </div>

                <dl className="mt-5 grid gap-3 text-sm md:grid-cols-2 xl:grid-cols-4">
                    <DetailField
                        label="Kapasitas"
                        value={String(dormitory.capacity)}
                    />
                    <DetailField
                        label="Hunian"
                        value={occupancyLabel(
                            dormitory.occupied_count,
                            dormitory.capacity,
                        )}
                    />
                    <DetailField
                        label="Sisa kapasitas"
                        value={String(dormitory.available_capacity)}
                    />
                    <DetailField
                        label="Permission mutation"
                        value={permissionSummary(
                            canManage,
                            canPlacement,
                            canSupervisor,
                            canArchive,
                        )}
                    />
                </dl>

                {dormitory.description ? (
                    <p className="mt-4 rounded-xl border border-dashed p-3 text-sm text-foreground/65">
                        {dormitory.description}
                    </p>
                ) : null}
            </section>

            <section className="grid gap-5 xl:grid-cols-[1.2fr_1fr]">
                <RoomList rooms={rooms} />
                <PlacementList placements={placements} />
            </section>
            <SupervisorList supervisors={supervisors} />
        </div>
    );
}

function DetailField({ label, value }: { label: string; value: string }) {
    return (
        <div className="dashboard-subcard rounded-xl border p-3">
            <dt className="text-xs font-medium text-foreground/55">{label}</dt>
            <dd className="mt-1 font-medium">{value}</dd>
        </div>
    );
}

function RoomList({ rooms }: { rooms: DormitoryRoom[] }) {
    return (
        <section className="dashboard-card rounded-2xl border p-4 sm:p-5">
            <div className="flex items-center justify-between gap-3">
                <div>
                    <h2 className="font-semibold">Daftar kamar</h2>
                    <p className="text-sm text-foreground/65">
                        Kamar yang tersedia pada asrama ini.
                    </p>
                </div>
                <DoorOpen className="size-5 text-primary" aria-hidden="true" />
            </div>

            <div className="mt-4 space-y-3">
                {rooms.length > 0 ? (
                    rooms.map((room) => (
                        <article
                            key={room.id}
                            className="dashboard-subcard rounded-xl border p-3"
                        >
                            <div className="flex items-start justify-between gap-3">
                                <div>
                                    <h3 className="font-medium">
                                        {room.code} · {room.name}
                                    </h3>
                                    <p className="text-xs text-foreground/60">
                                        Hunian{' '}
                                        {occupancyLabel(
                                            room.occupied_count,
                                            room.capacity,
                                        )}{' '}
                                        · sisa {room.available_capacity}
                                    </p>
                                </div>
                                <AsramaStatusBadge status={room.status} />
                            </div>
                        </article>
                    ))
                ) : (
                    <p className="rounded-xl border border-dashed p-4 text-sm text-foreground/65">
                        Belum ada kamar pada asrama ini.
                    </p>
                )}
            </div>
        </section>
    );
}

function PlacementList({ placements }: { placements: StudentRoomPlacement[] }) {
    return (
        <section className="dashboard-card rounded-2xl border p-4 sm:p-5">
            <div className="flex items-center justify-between gap-3">
                <div>
                    <h2 className="font-semibold">Penempatan santri</h2>
                    <p className="text-sm text-foreground/65">
                        Santri yang aktif menempati kamar asrama.
                    </p>
                </div>
                <BedDouble className="size-5 text-primary" aria-hidden="true" />
            </div>

            <div className="mt-4 space-y-3">
                {placements.length > 0 ? (
                    placements.map((placement) => (
                        <article
                            key={placement.id}
                            className="dashboard-subcard rounded-xl border p-3"
                        >
                            <div className="flex items-start justify-between gap-3">
                                <div>
                                    <h3 className="font-medium">
                                        {placement.student_name ??
                                            'Nama belum tersedia'}
                                    </h3>
                                    <p className="text-xs text-foreground/60">
                                        {placement.student_no} · kamar{' '}
                                        {placement.room_code ?? 'belum terbaca'}
                                    </p>
                                </div>
                                <span className="text-xs font-medium text-foreground/65">
                                    {placement.status}
                                </span>
                            </div>
                            <p className="mt-2 text-xs text-foreground/60">
                                Sejak {placement.started_at}
                            </p>
                        </article>
                    ))
                ) : (
                    <p className="rounded-xl border border-dashed p-4 text-sm text-foreground/65">
                        Belum ada santri aktif pada asrama ini.
                    </p>
                )}
            </div>
        </section>
    );
}

function SupervisorList({
    supervisors,
}: {
    supervisors: DormitorySupervisor[];
}) {
    return (
        <section className="dashboard-card rounded-2xl border p-4 sm:p-5">
            <div className="flex items-center justify-between gap-3">
                <div>
                    <h2 className="font-semibold">Musyrif / pembina</h2>
                    <p className="text-sm text-foreground/65">
                        Pengasuh yang ditugaskan pada asrama atau kamar.
                    </p>
                </div>
                <UserRoundCheck
                    className="size-5 text-primary"
                    aria-hidden="true"
                />
            </div>

            <div className="mt-4 grid gap-3 md:grid-cols-2">
                {supervisors.length > 0 ? (
                    supervisors.map((supervisor) => (
                        <article
                            key={supervisor.id}
                            className="dashboard-subcard rounded-xl border p-3"
                        >
                            <h3 className="font-medium">
                                {supervisor.employee_name}
                            </h3>
                            <p className="text-xs text-foreground/60">
                                {supervisor.role} ·{' '}
                                {supervisor.room_code
                                    ? `kamar ${supervisor.room_code}`
                                    : 'level asrama'}
                            </p>
                            <p className="mt-2 text-xs text-foreground/60">
                                Sejak {supervisor.started_at} ·{' '}
                                {supervisor.status}
                            </p>
                        </article>
                    ))
                ) : (
                    <p className="rounded-xl border border-dashed p-4 text-sm text-foreground/65 md:col-span-2">
                        Belum ada musyrif atau pembina pada asrama ini.
                    </p>
                )}
            </div>
        </section>
    );
}

function permissionSummary(
    canManage: boolean,
    canPlacement: boolean,
    canSupervisor: boolean,
    canArchive: boolean,
): string {
    const labels = [
        canManage ? 'kelola' : null,
        canPlacement ? 'penempatan' : null,
        canSupervisor ? 'musyrif' : null,
        canArchive ? 'arsip' : null,
    ].filter(Boolean);

    return labels.length > 0 ? labels.join(', ') : 'lihat saja';
}
