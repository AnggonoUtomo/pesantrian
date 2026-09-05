import { useForm } from '@inertiajs/react';
import {
    Archive,
    BedDouble,
    DoorOpen,
    PencilLine,
    RotateCcw,
    UserMinus,
    UserPlus,
    UserRoundCheck,
} from 'lucide-react';
import { useEffect } from 'react';
import type { FormEvent, ReactNode } from 'react';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { LoadingButton } from '@/components/ui/loading-button';
import { routeOr } from '@/lib/route';
import type { RouteParams } from '@/lib/route';
import type {
    ArchivePayload,
    Dormitory,
    DormitoryIndexPageProps,
    DormitoryMutationPayload,
    DormitoryRoom,
    DormitoryRoomPayload,
    DormitoryShowPageProps,
    EndPayload,
    StudentPlacementPayload,
    StudentRoomPlacement,
    StudentTransferPayload,
    SupervisorPayload,
} from '../types';
import { genderPolicyOptions } from './asramaDisplay';
import {
    AsramaFieldError,
    AsramaOption,
    AsramaSelectField,
    AsramaTextareaField,
    AsramaTextField,
    nullable,
    today,
} from './AsramaFormFields';

type DormitoryDialogProps = {
    open: boolean;
    dormitory: Dormitory | null;
    units: DormitoryIndexPageProps['options']['units'];
    onOpenChange: (open: boolean) => void;
};

export function DormitoryFormDialog({
    open,
    dormitory,
    units,
    onOpenChange,
}: DormitoryDialogProps) {
    const isEdit = dormitory !== null;
    const form = useForm<DormitoryMutationPayload>(
        dormitoryDefaults(dormitory, units),
    );
    const formErrors = form.errors as Partial<
        Record<keyof DormitoryMutationPayload | 'payload', string>
    >;

    useEffect(() => {
        if (open) {
            form.setData(dormitoryDefaults(dormitory, units));
            form.clearErrors();
        }
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, [open, dormitory?.id, units]);

    const submit = (event: FormEvent<HTMLFormElement>) => {
        event.preventDefault();

        const url =
            dormitory === null
                ? routeOr('/pesantrian/asrama', 'pesantrian.asrama.store')
                : routeOr(
                      `/pesantrian/asrama/${dormitory.id}`,
                      'pesantrian.asrama.update',
                      dormitory.id,
                  );

        const options = {
            preserveScroll: true,
            onSuccess: () => onOpenChange(false),
        };

        if (dormitory === null) {
            form.post(String(url), options);

            return;
        }

        form.patch(String(url), options);
    };

    return (
        <Dialog
            open={open}
            onOpenChange={(nextOpen) =>
                !form.processing && onOpenChange(nextOpen)
            }
        >
            <DialogContent className="max-h-[90vh] max-w-2xl overflow-y-auto">
                <DialogHeader>
                    <div className="flex items-center gap-3">
                        <span className="dashboard-icon dashboard-accent--blue flex size-10 items-center justify-center rounded-lg">
                            {isEdit ? (
                                <PencilLine
                                    className="size-5"
                                    aria-hidden="true"
                                />
                            ) : (
                                <BedDouble
                                    className="size-5"
                                    aria-hidden="true"
                                />
                            )}
                        </span>
                        <div>
                            <DialogTitle>
                                {isEdit ? 'Edit asrama' : 'Tambah asrama'}
                            </DialogTitle>
                            <DialogDescription className="mt-1">
                                Unit asrama harus berasal dari Unit Organisasi
                                bertipe asrama.
                            </DialogDescription>
                        </div>
                    </div>
                </DialogHeader>
                {formErrors.payload ? (
                    <AsramaFieldError message={formErrors.payload} />
                ) : null}
                <form onSubmit={submit} className="space-y-4">
                    <AsramaSelectField
                        id="dormitory-unit"
                        label="Unit asrama"
                        value={form.data.unit_id}
                        error={form.errors.unit_id}
                        onChange={(value) => form.setData('unit_id', value)}
                    >
                        {units.map((unit) => (
                            <AsramaOption key={unit.id} value={unit.id}>
                                {unit.name} ({unit.code})
                            </AsramaOption>
                        ))}
                    </AsramaSelectField>
                    <div className="grid gap-4 sm:grid-cols-[160px_1fr]">
                        <AsramaTextField
                            id="dormitory-code"
                            label="Kode"
                            value={form.data.code}
                            error={form.errors.code}
                            placeholder="ASR-PUTRA"
                            maxLength={40}
                            required
                            onChange={(value) =>
                                form.setData('code', value.toUpperCase())
                            }
                        />
                        <AsramaTextField
                            id="dormitory-name"
                            label="Nama"
                            value={form.data.name}
                            error={form.errors.name}
                            placeholder="Asrama Putra"
                            maxLength={180}
                            required
                            onChange={(value) => form.setData('name', value)}
                        />
                    </div>
                    <div className="grid gap-4 sm:grid-cols-2">
                        <AsramaSelectField
                            id="dormitory-gender-policy"
                            label="Tipe penghuni"
                            value={form.data.gender_policy}
                            error={form.errors.gender_policy}
                            onChange={(value) =>
                                form.setData(
                                    'gender_policy',
                                    value as DormitoryMutationPayload['gender_policy'],
                                )
                            }
                        >
                            {genderPolicyOptions.map(([value, label]) => (
                                <AsramaOption key={value} value={value}>
                                    {label}
                                </AsramaOption>
                            ))}
                        </AsramaSelectField>
                        <AsramaSelectField
                            id="dormitory-status"
                            label="Status asrama"
                            value={form.data.status}
                            error={form.errors.status}
                            onChange={(value) =>
                                form.setData(
                                    'status',
                                    value as DormitoryMutationPayload['status'],
                                )
                            }
                        >
                            <AsramaOption value="active">Aktif</AsramaOption>
                            <AsramaOption value="inactive">
                                Nonaktif
                            </AsramaOption>
                        </AsramaSelectField>
                    </div>
                    <AsramaTextareaField
                        id="dormitory-description"
                        label="Catatan"
                        value={form.data.description ?? ''}
                        error={form.errors.description}
                        placeholder="Opsional"
                        onChange={(value) =>
                            form.setData('description', nullable(value))
                        }
                    />
                    <DialogFooter>
                        <Button
                            type="button"
                            variant="outline"
                            disabled={form.processing}
                            onClick={() => onOpenChange(false)}
                        >
                            Batal
                        </Button>
                        <LoadingButton type="submit" loading={form.processing}>
                            {isEdit ? 'Simpan asrama' : 'Tambah asrama'}
                        </LoadingButton>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>
    );
}

type RoomDialogProps = {
    open: boolean;
    dormitory: Dormitory;
    room: DormitoryRoom | null;
    onOpenChange: (open: boolean) => void;
};

export function RoomFormDialog({
    open,
    dormitory,
    room,
    onOpenChange,
}: RoomDialogProps) {
    const isEdit = room !== null;
    const form = useForm<DormitoryRoomPayload>(roomDefaults(room));

    useEffect(() => {
        if (open) {
            form.setData(roomDefaults(room));
            form.clearErrors();
        }
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, [open, room?.id]);

    const submit = (event: FormEvent<HTMLFormElement>) => {
        event.preventDefault();

        form.transform((data) => ({
            ...data,
            capacity: Number(data.capacity),
        }));

        const url =
            room === null
                ? routeOr(
                      `/pesantrian/asrama/${dormitory.id}/rooms`,
                      'pesantrian.asrama.rooms.store',
                      routeParams({ dormitory: dormitory.id }),
                  )
                : routeOr(
                      `/pesantrian/asrama/${dormitory.id}/rooms/${room.id}`,
                      'pesantrian.asrama.rooms.update',
                      routeParams({ dormitory: dormitory.id, room: room.id }),
                  );

        const options = {
            preserveScroll: true,
            onSuccess: () => onOpenChange(false),
        };

        if (room === null) {
            form.post(String(url), options);

            return;
        }

        form.patch(String(url), options);
    };

    return (
        <Dialog
            open={open}
            onOpenChange={(nextOpen) =>
                !form.processing && onOpenChange(nextOpen)
            }
        >
            <DialogContent>
                <DialogHeader>
                    <div className="flex items-center gap-3">
                        <span className="dashboard-icon dashboard-accent--green flex size-10 items-center justify-center rounded-lg">
                            <DoorOpen className="size-5" aria-hidden="true" />
                        </span>
                        <div>
                            <DialogTitle>
                                {isEdit ? 'Edit kamar' : 'Tambah kamar'}
                            </DialogTitle>
                            <DialogDescription className="mt-1">
                                Kode kamar unik di dalam satu asrama.
                            </DialogDescription>
                        </div>
                    </div>
                </DialogHeader>
                <form onSubmit={submit} className="space-y-4">
                    <div className="grid gap-4 sm:grid-cols-[160px_1fr_120px]">
                        <AsramaTextField
                            id="room-code"
                            label="Kode"
                            value={form.data.code}
                            error={form.errors.code}
                            placeholder="A01"
                            maxLength={40}
                            required
                            onChange={(value) =>
                                form.setData('code', value.toUpperCase())
                            }
                        />
                        <AsramaTextField
                            id="room-name"
                            label="Nama"
                            value={form.data.name}
                            error={form.errors.name}
                            placeholder="Kamar A-01"
                            maxLength={120}
                            required
                            onChange={(value) => form.setData('name', value)}
                        />
                        <AsramaTextField
                            id="room-capacity"
                            label="Kapasitas"
                            type="number"
                            value={form.data.capacity}
                            error={form.errors.capacity}
                            required
                            onChange={(value) =>
                                form.setData('capacity', value)
                            }
                        />
                    </div>
                    <AsramaSelectField
                        id="room-status"
                        label="Status kamar"
                        value={form.data.status}
                        error={form.errors.status}
                        onChange={(value) =>
                            form.setData(
                                'status',
                                value as DormitoryRoomPayload['status'],
                            )
                        }
                    >
                        <AsramaOption value="active">Aktif</AsramaOption>
                        <AsramaOption value="inactive">Nonaktif</AsramaOption>
                    </AsramaSelectField>
                    <DialogFooter>
                        <Button
                            type="button"
                            variant="outline"
                            disabled={form.processing}
                            onClick={() => onOpenChange(false)}
                        >
                            Batal
                        </Button>
                        <LoadingButton type="submit" loading={form.processing}>
                            {isEdit ? 'Simpan kamar' : 'Tambah kamar'}
                        </LoadingButton>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>
    );
}

export function PlacementDialog({
    open,
    dormitory,
    students,
    onOpenChange,
}: {
    open: boolean;
    dormitory: Dormitory;
    students: DormitoryShowPageProps['options']['students'];
    onOpenChange: (open: boolean) => void;
}) {
    const rooms = activeRooms(dormitory);
    const form = useForm<StudentPlacementPayload>({
        student_id: '',
        dormitory_room_id: '',
        started_at: today(),
    });

    useEffect(() => {
        if (open) {
            form.setData({
                student_id: students[0]?.id ?? '',
                dormitory_room_id: rooms[0]?.id ?? '',
                started_at: today(),
            });
            form.clearErrors();
        }
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, [open, students, dormitory.id]);

    const submit = (event: FormEvent<HTMLFormElement>) => {
        event.preventDefault();
        form.post(
            String(
                routeOr(
                    `/pesantrian/asrama/${dormitory.id}/placements`,
                    'pesantrian.asrama.placements.store',
                    routeParams({ dormitory: dormitory.id }),
                ),
            ),
            {
                preserveScroll: true,
                onSuccess: () => onOpenChange(false),
            },
        );
    };

    return (
        <BasicFormDialog
            open={open}
            title="Tempatkan santri"
            description="Santri hanya boleh memiliki satu kamar aktif."
            icon={<UserPlus className="size-5" aria-hidden="true" />}
            processing={form.processing}
            submitLabel="Tempatkan santri"
            onOpenChange={onOpenChange}
            onSubmit={submit}
        >
            <AsramaSelectField
                id="placement-student"
                label="Santri"
                value={form.data.student_id}
                error={form.errors.student_id}
                onChange={(value) => form.setData('student_id', value)}
            >
                {students.map((student) => (
                    <AsramaOption key={student.id} value={student.id}>
                        {student.name} ({student.code})
                    </AsramaOption>
                ))}
            </AsramaSelectField>
            <AsramaSelectField
                id="placement-room"
                label="Kamar"
                value={form.data.dormitory_room_id}
                error={form.errors.dormitory_room_id}
                onChange={(value) => form.setData('dormitory_room_id', value)}
            >
                {rooms.map((room) => (
                    <AsramaOption key={room.id} value={room.id}>
                        {room.name} ({room.code})
                    </AsramaOption>
                ))}
            </AsramaSelectField>
            <AsramaTextField
                id="placement-started-at"
                label="Tanggal mulai"
                type="date"
                value={form.data.started_at}
                error={form.errors.started_at}
                onChange={(value) => form.setData('started_at', value)}
            />
        </BasicFormDialog>
    );
}

export function TransferPlacementDialog({
    open,
    dormitory,
    placement,
    onOpenChange,
}: {
    open: boolean;
    dormitory: Dormitory;
    placement: StudentRoomPlacement | null;
    onOpenChange: (open: boolean) => void;
}) {
    const rooms = activeRooms(dormitory).filter(
        (room) => room.id !== placement?.room_id,
    );
    const form = useForm<StudentTransferPayload>({
        target_room_id: '',
        started_at: today(),
        reason: '',
    });

    useEffect(() => {
        if (open) {
            form.setData({
                target_room_id: rooms[0]?.id ?? '',
                started_at: today(),
                reason: '',
            });
            form.clearErrors();
        }
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, [open, placement?.id, dormitory.id]);

    const submit = (event: FormEvent<HTMLFormElement>) => {
        event.preventDefault();

        if (placement === null) {
            return;
        }

        form.patch(
            String(
                routeOr(
                    `/pesantrian/asrama/${dormitory.id}/placements/${placement.id}/transfer`,
                    'pesantrian.asrama.placements.transfer',
                    routeParams({
                        dormitory: dormitory.id,
                        placement: placement.id,
                    }),
                ),
            ),
            {
                preserveScroll: true,
                onSuccess: () => onOpenChange(false),
            },
        );
    };

    return (
        <BasicFormDialog
            open={open}
            title="Pindah kamar"
            description="Placement lama ditutup dan placement baru dibuat sebagai riwayat."
            icon={<UserPlus className="size-5" aria-hidden="true" />}
            processing={form.processing}
            submitLabel="Pindah kamar"
            onOpenChange={onOpenChange}
            onSubmit={submit}
        >
            <AsramaSelectField
                id="transfer-room"
                label="Kamar tujuan"
                value={form.data.target_room_id}
                error={form.errors.target_room_id}
                onChange={(value) => form.setData('target_room_id', value)}
            >
                {rooms.map((room) => (
                    <AsramaOption key={room.id} value={room.id}>
                        {room.name} ({room.code})
                    </AsramaOption>
                ))}
            </AsramaSelectField>
            <AsramaTextField
                id="transfer-started-at"
                label="Tanggal pindah"
                type="date"
                value={form.data.started_at}
                error={form.errors.started_at}
                onChange={(value) => form.setData('started_at', value)}
            />
            <AsramaTextareaField
                id="transfer-reason"
                label="Alasan pindah"
                value={form.data.reason}
                error={form.errors.reason}
                required
                onChange={(value) => form.setData('reason', value)}
            />
        </BasicFormDialog>
    );
}

export function EndPlacementDialog({
    open,
    dormitory,
    placement,
    onOpenChange,
}: {
    open: boolean;
    dormitory: Dormitory;
    placement: StudentRoomPlacement | null;
    onOpenChange: (open: boolean) => void;
}) {
    return (
        <EndActionDialog
            open={open}
            title="Keluarkan santri"
            description="Placement aktif ditutup, riwayat tetap tersimpan."
            label="Keluarkan santri"
            url={String(
                placement
                    ? routeOr(
                          `/pesantrian/asrama/${dormitory.id}/placements/${placement.id}/remove`,
                          'pesantrian.asrama.placements.remove',
                          routeParams({
                              dormitory: dormitory.id,
                              placement: placement.id,
                          }),
                      )
                    : '#',
            )}
            onOpenChange={onOpenChange}
        />
    );
}

export function SupervisorDialog({
    open,
    dormitory,
    employees,
    onOpenChange,
}: {
    open: boolean;
    dormitory: Dormitory;
    employees: DormitoryShowPageProps['options']['employees'];
    onOpenChange: (open: boolean) => void;
}) {
    const rooms = activeRooms(dormitory);
    const form = useForm<SupervisorPayload>({
        employee_id: '',
        dormitory_room_id: null,
        role: 'musyrif',
        started_at: today(),
    });

    useEffect(() => {
        if (open) {
            form.setData({
                employee_id: employees[0]?.id ?? '',
                dormitory_room_id: null,
                role: 'musyrif',
                started_at: today(),
            });
            form.clearErrors();
        }
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, [open, employees, dormitory.id]);

    const submit = (event: FormEvent<HTMLFormElement>) => {
        event.preventDefault();
        form.post(
            String(
                routeOr(
                    `/pesantrian/asrama/${dormitory.id}/supervisors`,
                    'pesantrian.asrama.supervisors.store',
                    routeParams({ dormitory: dormitory.id }),
                ),
            ),
            {
                preserveScroll: true,
                onSuccess: () => onOpenChange(false),
            },
        );
    };

    return (
        <BasicFormDialog
            open={open}
            title="Tugaskan musyrif"
            description="Musyrif dapat ditugaskan pada level asrama atau kamar tertentu."
            icon={<UserRoundCheck className="size-5" aria-hidden="true" />}
            processing={form.processing}
            submitLabel="Tugaskan musyrif"
            onOpenChange={onOpenChange}
            onSubmit={submit}
        >
            <AsramaSelectField
                id="supervisor-employee"
                label="Pegawai / ustaz"
                value={form.data.employee_id}
                error={form.errors.employee_id}
                onChange={(value) => form.setData('employee_id', value)}
            >
                {employees.map((employee) => (
                    <AsramaOption key={employee.id} value={employee.id}>
                        {employee.name} ({employee.code})
                    </AsramaOption>
                ))}
            </AsramaSelectField>
            <AsramaSelectField
                id="supervisor-room"
                label="Scope tugas"
                value={form.data.dormitory_room_id ?? '__dormitory'}
                error={form.errors.dormitory_room_id}
                onChange={(value) =>
                    form.setData(
                        'dormitory_room_id',
                        value === '__dormitory' ? null : value,
                    )
                }
            >
                <AsramaOption value="__dormitory">Level asrama</AsramaOption>
                {rooms.map((room) => (
                    <AsramaOption key={room.id} value={room.id}>
                        Kamar {room.code}
                    </AsramaOption>
                ))}
            </AsramaSelectField>
            <AsramaSelectField
                id="supervisor-role"
                label="Peran"
                value={form.data.role}
                error={form.errors.role}
                onChange={(value) =>
                    form.setData('role', value as SupervisorPayload['role'])
                }
            >
                <AsramaOption value="musyrif">Musyrif</AsramaOption>
                <AsramaOption value="pembina">Pembina</AsramaOption>
            </AsramaSelectField>
            <AsramaTextField
                id="supervisor-started-at"
                label="Tanggal mulai"
                type="date"
                value={form.data.started_at}
                error={form.errors.started_at}
                onChange={(value) => form.setData('started_at', value)}
            />
        </BasicFormDialog>
    );
}

export function EndSupervisorDialog({
    open,
    dormitory,
    assignmentId,
    onOpenChange,
}: {
    open: boolean;
    dormitory: Dormitory;
    assignmentId: string | null;
    onOpenChange: (open: boolean) => void;
}) {
    return (
        <EndActionDialog
            open={open}
            title="Akhiri tugas musyrif"
            description="Riwayat penugasan tetap tersimpan setelah tugas diakhiri."
            label="Akhiri tugas musyrif"
            url={String(
                assignmentId
                    ? routeOr(
                          `/pesantrian/asrama/${dormitory.id}/supervisors/${assignmentId}/end`,
                          'pesantrian.asrama.supervisors.end',
                          routeParams({
                              dormitory: dormitory.id,
                              assignment: assignmentId,
                          }),
                      )
                    : '#',
            )}
            onOpenChange={onOpenChange}
        />
    );
}

export function ArchiveActionDialog({
    open,
    title,
    description,
    label,
    url,
    destructive = true,
    requireReason = true,
    onOpenChange,
}: {
    open: boolean;
    title: string;
    description: string;
    label: string;
    url: string;
    destructive?: boolean;
    requireReason?: boolean;
    onOpenChange: (open: boolean) => void;
}) {
    const form = useForm<ArchivePayload>({ reason: '' });

    useEffect(() => {
        if (open) {
            form.reset();
            form.clearErrors();
        }
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, [open, url]);

    const submit = (event: FormEvent<HTMLFormElement>) => {
        event.preventDefault();
        form.patch(url, {
            preserveScroll: true,
            onSuccess: () => onOpenChange(false),
        });
    };

    return (
        <BasicFormDialog
            open={open}
            title={title}
            description={description}
            icon={
                destructive ? (
                    <Archive className="size-5" aria-hidden="true" />
                ) : (
                    <RotateCcw className="size-5" aria-hidden="true" />
                )
            }
            processing={form.processing}
            submitLabel={label}
            submitVariant={destructive ? 'destructive' : 'default'}
            onOpenChange={onOpenChange}
            onSubmit={submit}
        >
            {requireReason ? (
                <AsramaTextareaField
                    id="archive-reason"
                    label="Alasan"
                    value={form.data.reason}
                    error={form.errors.reason}
                    required
                    onChange={(value) => form.setData('reason', value)}
                />
            ) : (
                <p className="rounded-xl border border-dashed p-4 text-sm text-foreground/65">
                    Data akan kembali aktif dan bisa dipakai dalam operasional.
                </p>
            )}
        </BasicFormDialog>
    );
}

function EndActionDialog({
    open,
    title,
    description,
    label,
    url,
    onOpenChange,
}: {
    open: boolean;
    title: string;
    description: string;
    label: string;
    url: string;
    onOpenChange: (open: boolean) => void;
}) {
    const form = useForm<EndPayload>({
        ended_at: today(),
        reason: '',
    });

    useEffect(() => {
        if (open) {
            form.setData({ ended_at: today(), reason: '' });
            form.clearErrors();
        }
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, [open, url]);

    const submit = (event: FormEvent<HTMLFormElement>) => {
        event.preventDefault();
        form.patch(url, {
            preserveScroll: true,
            onSuccess: () => onOpenChange(false),
        });
    };

    return (
        <BasicFormDialog
            open={open}
            title={title}
            description={description}
            icon={<UserMinus className="size-5" aria-hidden="true" />}
            processing={form.processing}
            submitLabel={label}
            submitVariant="destructive"
            onOpenChange={onOpenChange}
            onSubmit={submit}
        >
            <AsramaTextField
                id="end-date"
                label="Tanggal selesai"
                type="date"
                value={form.data.ended_at}
                error={form.errors.ended_at}
                onChange={(value) => form.setData('ended_at', value)}
            />
            <AsramaTextareaField
                id="end-reason"
                label="Alasan"
                value={form.data.reason}
                error={form.errors.reason}
                required
                onChange={(value) => form.setData('reason', value)}
            />
        </BasicFormDialog>
    );
}

function BasicFormDialog({
    open,
    title,
    description,
    icon,
    processing,
    submitLabel,
    submitVariant = 'default',
    children,
    onOpenChange,
    onSubmit,
}: {
    open: boolean;
    title: string;
    description: string;
    icon: ReactNode;
    processing: boolean;
    submitLabel: string;
    submitVariant?: 'default' | 'destructive';
    children: ReactNode;
    onOpenChange: (open: boolean) => void;
    onSubmit: (event: FormEvent<HTMLFormElement>) => void;
}) {
    return (
        <Dialog
            open={open}
            onOpenChange={(nextOpen) => !processing && onOpenChange(nextOpen)}
        >
            <DialogContent className="max-h-[90vh] overflow-y-auto">
                <DialogHeader>
                    <div className="flex items-center gap-3">
                        <span className="dashboard-icon dashboard-accent--blue flex size-10 items-center justify-center rounded-lg">
                            {icon}
                        </span>
                        <div>
                            <DialogTitle>{title}</DialogTitle>
                            <DialogDescription className="mt-1">
                                {description}
                            </DialogDescription>
                        </div>
                    </div>
                </DialogHeader>
                <form onSubmit={onSubmit} className="space-y-4">
                    {children}
                    <DialogFooter>
                        <Button
                            type="button"
                            variant="outline"
                            disabled={processing}
                            onClick={() => onOpenChange(false)}
                        >
                            Batal
                        </Button>
                        <LoadingButton
                            type="submit"
                            loading={processing}
                            variant={submitVariant}
                        >
                            {submitLabel}
                        </LoadingButton>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>
    );
}

function dormitoryDefaults(
    dormitory: Dormitory | null,
    units: DormitoryIndexPageProps['options']['units'],
): DormitoryMutationPayload {
    return {
        unit_id: dormitory?.unit.id ?? units[0]?.id ?? '',
        code: dormitory?.code ?? '',
        name: dormitory?.name ?? '',
        gender_policy: dormitory?.gender_policy ?? 'unspecified',
        description: dormitory?.description ?? null,
        status: dormitory?.status === 'inactive' ? 'inactive' : 'active',
    };
}

function roomDefaults(room: DormitoryRoom | null): DormitoryRoomPayload {
    return {
        code: room?.code ?? '',
        name: room?.name ?? '',
        capacity: String(room?.capacity ?? ''),
        status: room?.status === 'inactive' ? 'inactive' : 'active',
    };
}

function activeRooms(dormitory: Dormitory): DormitoryRoom[] {
    return (dormitory.rooms ?? []).filter(
        (room) => room.status === 'active' && room.archived_at === null,
    );
}

function routeParams(params: Record<string, string>): RouteParams {
    return params as RouteParams;
}
