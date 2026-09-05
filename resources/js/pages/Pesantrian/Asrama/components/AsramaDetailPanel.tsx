import { Link } from '@inertiajs/react';
import {
    Archive,
    ArrowLeft,
    BedDouble,
    DoorOpen,
    PencilLine,
    Plus,
    RotateCcw,
    UserMinus,
    UserPlus,
    UserRoundCheck,
} from 'lucide-react';
import { useState } from 'react';
import { Button } from '@/components/ui/button';
import { routeOr } from '@/lib/route';
import type { RouteParams } from '@/lib/route';
import type {
    Dormitory,
    DormitoryRoom,
    DormitoryShowPageProps,
    DormitorySupervisor,
    StudentRoomPlacement,
} from '../types';
import { genderPolicyLabel, occupancyLabel } from './asramaDisplay';
import {
    ArchiveActionDialog,
    DormitoryFormDialog,
    EndPlacementDialog,
    EndSupervisorDialog,
    PlacementDialog,
    RoomFormDialog,
    SupervisorDialog,
    TransferPlacementDialog,
} from './AsramaMutationDialogs';
import { AsramaStatusBadge } from './AsramaStatusBadge';

type Props = {
    dormitory: Dormitory;
    options: DormitoryShowPageProps['options'];
    canManage: boolean;
    canPlacement: boolean;
    canSupervisor: boolean;
    canArchive: boolean;
};

export function AsramaDetailPanel({
    dormitory,
    options,
    canManage,
    canPlacement,
    canSupervisor,
    canArchive,
}: Props) {
    const rooms = dormitory.rooms ?? [];
    const placements = dormitory.placements ?? [];
    const supervisors = dormitory.supervisors ?? [];
    const [editDormitoryOpen, setEditDormitoryOpen] = useState(false);
    const [roomFormOpen, setRoomFormOpen] = useState(false);
    const [selectedRoom, setSelectedRoom] = useState<DormitoryRoom | null>(
        null,
    );
    const [placementOpen, setPlacementOpen] = useState(false);
    const [transferOpen, setTransferOpen] = useState(false);
    const [removePlacementOpen, setRemovePlacementOpen] = useState(false);
    const [selectedPlacement, setSelectedPlacement] =
        useState<StudentRoomPlacement | null>(null);
    const [supervisorOpen, setSupervisorOpen] = useState(false);
    const [endSupervisorOpen, setEndSupervisorOpen] = useState(false);
    const [selectedSupervisorId, setSelectedSupervisorId] = useState<
        string | null
    >(null);
    const [archiveDormitoryOpen, setArchiveDormitoryOpen] = useState(false);
    const [restoreDormitoryOpen, setRestoreDormitoryOpen] = useState(false);
    const [archiveRoomOpen, setArchiveRoomOpen] = useState(false);
    const [restoreRoomOpen, setRestoreRoomOpen] = useState(false);
    const isArchived = dormitory.archived_at !== null;

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
                        <div className="flex flex-wrap justify-end gap-2">
                            {canManage && !isArchived ? (
                                <Button
                                    type="button"
                                    size="sm"
                                    variant="outline"
                                    onClick={() => setEditDormitoryOpen(true)}
                                >
                                    <PencilLine
                                        className="size-4"
                                        aria-hidden="true"
                                    />
                                    Edit asrama
                                </Button>
                            ) : null}
                            {canManage && !isArchived ? (
                                <Button
                                    type="button"
                                    size="sm"
                                    variant="outline"
                                    onClick={() => {
                                        setSelectedRoom(null);
                                        setRoomFormOpen(true);
                                    }}
                                >
                                    <Plus
                                        className="size-4"
                                        aria-hidden="true"
                                    />
                                    Tambah kamar
                                </Button>
                            ) : null}
                            {canPlacement && !isArchived ? (
                                <Button
                                    type="button"
                                    size="sm"
                                    variant="outline"
                                    onClick={() => setPlacementOpen(true)}
                                >
                                    <UserPlus
                                        className="size-4"
                                        aria-hidden="true"
                                    />
                                    Tempatkan santri
                                </Button>
                            ) : null}
                            {canSupervisor && !isArchived ? (
                                <Button
                                    type="button"
                                    size="sm"
                                    variant="outline"
                                    onClick={() => setSupervisorOpen(true)}
                                >
                                    <UserRoundCheck
                                        className="size-4"
                                        aria-hidden="true"
                                    />
                                    Tugaskan musyrif
                                </Button>
                            ) : null}
                            {canArchive ? (
                                isArchived ? (
                                    <Button
                                        type="button"
                                        size="sm"
                                        onClick={() =>
                                            setRestoreDormitoryOpen(true)
                                        }
                                    >
                                        <RotateCcw
                                            className="size-4"
                                            aria-hidden="true"
                                        />
                                        Pulihkan asrama
                                    </Button>
                                ) : (
                                    <Button
                                        type="button"
                                        size="sm"
                                        variant="destructive"
                                        onClick={() =>
                                            setArchiveDormitoryOpen(true)
                                        }
                                    >
                                        <Archive
                                            className="size-4"
                                            aria-hidden="true"
                                        />
                                        Arsipkan asrama
                                    </Button>
                                )
                            ) : null}
                        </div>
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
                <RoomList
                    rooms={rooms}
                    canManage={canManage}
                    canArchive={canArchive}
                    onEdit={(room) => {
                        setSelectedRoom(room);
                        setRoomFormOpen(true);
                    }}
                    onArchive={(room) => {
                        setSelectedRoom(room);
                        setArchiveRoomOpen(true);
                    }}
                    onRestore={(room) => {
                        setSelectedRoom(room);
                        setRestoreRoomOpen(true);
                    }}
                />
                <PlacementList
                    placements={placements}
                    canPlacement={canPlacement}
                    onTransfer={(placement) => {
                        setSelectedPlacement(placement);
                        setTransferOpen(true);
                    }}
                    onRemove={(placement) => {
                        setSelectedPlacement(placement);
                        setRemovePlacementOpen(true);
                    }}
                />
            </section>
            <SupervisorList
                supervisors={supervisors}
                canSupervisor={canSupervisor}
                onEnd={(supervisor) => {
                    setSelectedSupervisorId(supervisor.id);
                    setEndSupervisorOpen(true);
                }}
            />

            <DormitoryFormDialog
                open={editDormitoryOpen}
                dormitory={dormitory}
                units={[dormitory.unit]}
                onOpenChange={setEditDormitoryOpen}
            />
            <RoomFormDialog
                open={roomFormOpen}
                dormitory={dormitory}
                room={selectedRoom}
                onOpenChange={setRoomFormOpen}
            />
            <PlacementDialog
                open={placementOpen}
                dormitory={dormitory}
                students={options.students}
                onOpenChange={setPlacementOpen}
            />
            <TransferPlacementDialog
                open={transferOpen}
                dormitory={dormitory}
                placement={selectedPlacement}
                onOpenChange={setTransferOpen}
            />
            <EndPlacementDialog
                open={removePlacementOpen}
                dormitory={dormitory}
                placement={selectedPlacement}
                onOpenChange={setRemovePlacementOpen}
            />
            <SupervisorDialog
                open={supervisorOpen}
                dormitory={dormitory}
                employees={options.employees}
                onOpenChange={setSupervisorOpen}
            />
            <EndSupervisorDialog
                open={endSupervisorOpen}
                dormitory={dormitory}
                assignmentId={selectedSupervisorId}
                onOpenChange={setEndSupervisorOpen}
            />
            <ArchiveActionDialog
                open={archiveDormitoryOpen}
                title="Arsipkan asrama"
                description="Asrama arsip tidak dipakai untuk operasional baru."
                label="Arsipkan asrama"
                url={String(
                    routeOr(
                        `/pesantrian/asrama/${dormitory.id}/archive`,
                        'pesantrian.asrama.archive',
                        routeParams({ dormitory: dormitory.id }),
                    ),
                )}
                onOpenChange={setArchiveDormitoryOpen}
            />
            <ArchiveActionDialog
                open={restoreDormitoryOpen}
                title="Pulihkan asrama"
                description="Asrama akan kembali aktif dan tampil pada daftar aktif."
                label="Pulihkan asrama"
                url={String(
                    routeOr(
                        `/pesantrian/asrama/${dormitory.id}/restore`,
                        'pesantrian.asrama.restore',
                        routeParams({ dormitory: dormitory.id }),
                    ),
                )}
                destructive={false}
                requireReason={false}
                onOpenChange={setRestoreDormitoryOpen}
            />
            <ArchiveActionDialog
                open={archiveRoomOpen}
                title="Arsipkan kamar"
                description="Kamar arsip tidak bisa dipakai untuk placement baru."
                label="Arsipkan kamar"
                url={String(
                    selectedRoom
                        ? routeOr(
                              `/pesantrian/asrama/${dormitory.id}/rooms/${selectedRoom.id}/archive`,
                              'pesantrian.asrama.rooms.archive',
                              routeParams({
                                  dormitory: dormitory.id,
                                  room: selectedRoom.id,
                              }),
                          )
                        : '#',
                )}
                onOpenChange={setArchiveRoomOpen}
            />
            <ArchiveActionDialog
                open={restoreRoomOpen}
                title="Pulihkan kamar"
                description="Kamar akan kembali bisa dipakai sesuai status aktif."
                label="Pulihkan kamar"
                url={String(
                    selectedRoom
                        ? routeOr(
                              `/pesantrian/asrama/${dormitory.id}/rooms/${selectedRoom.id}/restore`,
                              'pesantrian.asrama.rooms.restore',
                              routeParams({
                                  dormitory: dormitory.id,
                                  room: selectedRoom.id,
                              }),
                          )
                        : '#',
                )}
                destructive={false}
                requireReason={false}
                onOpenChange={setRestoreRoomOpen}
            />
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

function routeParams(params: Record<string, string>): RouteParams {
    return params as RouteParams;
}

function RoomList({
    rooms,
    canManage,
    canArchive,
    onEdit,
    onArchive,
    onRestore,
}: {
    rooms: DormitoryRoom[];
    canManage: boolean;
    canArchive: boolean;
    onEdit: (room: DormitoryRoom) => void;
    onArchive: (room: DormitoryRoom) => void;
    onRestore: (room: DormitoryRoom) => void;
}) {
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
                            <div className="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
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
                                <div className="flex flex-col gap-2 sm:items-end">
                                    <AsramaStatusBadge status={room.status} />
                                    <div className="flex flex-wrap justify-end gap-2">
                                        {canManage &&
                                        room.archived_at === null ? (
                                            <Button
                                                type="button"
                                                size="sm"
                                                variant="outline"
                                                onClick={() => onEdit(room)}
                                            >
                                                Edit
                                            </Button>
                                        ) : null}
                                        {canArchive ? (
                                            room.archived_at === null ? (
                                                <Button
                                                    type="button"
                                                    size="sm"
                                                    variant="destructive"
                                                    onClick={() =>
                                                        onArchive(room)
                                                    }
                                                >
                                                    Arsipkan
                                                </Button>
                                            ) : (
                                                <Button
                                                    type="button"
                                                    size="sm"
                                                    onClick={() =>
                                                        onRestore(room)
                                                    }
                                                >
                                                    Pulihkan
                                                </Button>
                                            )
                                        ) : null}
                                    </div>
                                </div>
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

function PlacementList({
    placements,
    canPlacement,
    onTransfer,
    onRemove,
}: {
    placements: StudentRoomPlacement[];
    canPlacement: boolean;
    onTransfer: (placement: StudentRoomPlacement) => void;
    onRemove: (placement: StudentRoomPlacement) => void;
}) {
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
                            {canPlacement && placement.status === 'active' ? (
                                <div className="mt-3 flex flex-wrap gap-2">
                                    <Button
                                        type="button"
                                        size="sm"
                                        variant="outline"
                                        onClick={() => onTransfer(placement)}
                                    >
                                        <UserPlus
                                            className="size-4"
                                            aria-hidden="true"
                                        />
                                        Pindah kamar
                                    </Button>
                                    <Button
                                        type="button"
                                        size="sm"
                                        variant="destructive"
                                        onClick={() => onRemove(placement)}
                                    >
                                        <UserMinus
                                            className="size-4"
                                            aria-hidden="true"
                                        />
                                        Keluarkan santri
                                    </Button>
                                </div>
                            ) : null}
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
    canSupervisor,
    onEnd,
}: {
    supervisors: DormitorySupervisor[];
    canSupervisor: boolean;
    onEnd: (supervisor: DormitorySupervisor) => void;
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
                            {canSupervisor && supervisor.status === 'active' ? (
                                <Button
                                    type="button"
                                    size="sm"
                                    variant="destructive"
                                    className="mt-3"
                                    onClick={() => onEnd(supervisor)}
                                >
                                    Akhiri tugas musyrif
                                </Button>
                            ) : null}
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
