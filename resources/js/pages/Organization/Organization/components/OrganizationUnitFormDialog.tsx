import { useForm } from '@inertiajs/react';
import { Building2, PencilLine } from 'lucide-react';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { LoadingButton } from '@/components/ui/loading-button';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import route from '@/lib/route';
import type {
    OrganizationUnit,
    OrganizationUnitStatus,
    OrganizationUnitType,
} from '../types';

const typeLabels: Record<OrganizationUnitType, string> = {
    foundation: 'Yayasan',
    pesantren: 'Pesantren',
    education_unit: 'Unit pendidikan',
    operational_unit: 'Unit operasional',
    dormitory: 'Asrama',
    other: 'Lainnya',
};

const statusLabels: Record<OrganizationUnitStatus, string> = {
    active: 'Aktif',
    inactive: 'Nonaktif',
};

const typeOptions = Object.entries(typeLabels) as [
    OrganizationUnitType,
    string,
][];

const statusOptions = Object.entries(statusLabels) as [
    OrganizationUnitStatus,
    string,
][];

type OrganizationUnitFormData = {
    code: string;
    name: string;
    type: OrganizationUnitType;
    status: OrganizationUnitStatus;
    location_name: string;
};

type Props = {
    open: boolean;
    unit: OrganizationUnit | null;
    onOpenChange: (open: boolean) => void;
};

export function OrganizationUnitFormDialog({
    open,
    unit,
    onOpenChange,
}: Props) {
    const isEdit = unit !== null;
    const form = useForm<OrganizationUnitFormData>({
        code: unit?.code ?? '',
        name: unit?.name ?? '',
        type: unit?.type ?? 'operational_unit',
        status: unit?.status ?? 'active',
        location_name: unit?.location_name ?? '',
    });

    const submit = (event: React.FormEvent<HTMLFormElement>) => {
        event.preventDefault();

        if (isEdit && unit) {
            form.put(route('organization.units.update', unit.id), {
                preserveScroll: true,
                onSuccess: () => onOpenChange(false),
            });

            return;
        }

        form.post(route('organization.units.store'), {
            preserveScroll: true,
            onSuccess: () => onOpenChange(false),
        });
    };

    return (
        <Dialog
            open={open}
            onOpenChange={(nextOpen) =>
                !form.processing && onOpenChange(nextOpen)
            }
        >
            <DialogContent className="max-h-[90vh] max-w-xl overflow-y-auto">
                <DialogHeader>
                    <div className="flex items-center gap-3">
                        <span className="dashboard-icon dashboard-accent--blue flex size-10 items-center justify-center rounded-lg">
                            {isEdit ? (
                                <PencilLine className="size-5" />
                            ) : (
                                <Building2 className="size-5" />
                            )}
                        </span>
                        <div>
                            <DialogTitle>
                                {isEdit
                                    ? 'Edit unit organisasi'
                                    : 'Tambah unit organisasi'}
                            </DialogTitle>
                            <DialogDescription className="mt-1">
                                {isEdit
                                    ? 'Perbarui identitas dan status unit yang dipilih.'
                                    : 'Buat data unit organisasi baru dalam struktur yayasan.'}
                            </DialogDescription>
                        </div>
                    </div>
                </DialogHeader>

                <form onSubmit={submit} className="space-y-4">
                    <div className="grid gap-4 sm:grid-cols-[160px_1fr]">
                        <div className="space-y-2">
                            <Label htmlFor="organization-unit-code">
                                Kode
                            </Label>
                            <Input
                                id="organization-unit-code"
                                value={form.data.code}
                                onChange={(event) =>
                                    form.setData(
                                        'code',
                                        event.target.value.toUpperCase(),
                                    )
                                }
                                placeholder="PST"
                                maxLength={40}
                                required
                            />
                            {form.errors.code ? (
                                <p
                                    className="text-xs text-destructive"
                                    role="alert"
                                >
                                    {form.errors.code}
                                </p>
                            ) : null}
                        </div>
                        <div className="space-y-2">
                            <Label htmlFor="organization-unit-name">
                                Nama unit
                            </Label>
                            <Input
                                id="organization-unit-name"
                                value={form.data.name}
                                onChange={(event) =>
                                    form.setData('name', event.target.value)
                                }
                                placeholder="Pesantren Saka Utama"
                                minLength={2}
                                maxLength={180}
                                required
                            />
                            {form.errors.name ? (
                                <p
                                    className="text-xs text-destructive"
                                    role="alert"
                                >
                                    {form.errors.name}
                                </p>
                            ) : null}
                        </div>
                    </div>

                    <div className="grid gap-4 sm:grid-cols-2">
                        <div className="space-y-2">
                            <Label htmlFor="organization-unit-type">
                                Jenis unit
                            </Label>
                            <Select
                                value={form.data.type}
                                onValueChange={(value) =>
                                    form.setData(
                                        'type',
                                        value as OrganizationUnitType,
                                    )
                                }
                            >
                                <SelectTrigger id="organization-unit-type">
                                    <SelectValue placeholder="Pilih jenis" />
                                </SelectTrigger>
                                <SelectContent>
                                    {typeOptions.map(([value, label]) => (
                                        <SelectItem key={value} value={value}>
                                            {label}
                                        </SelectItem>
                                    ))}
                                </SelectContent>
                            </Select>
                            {form.errors.type ? (
                                <p
                                    className="text-xs text-destructive"
                                    role="alert"
                                >
                                    {form.errors.type}
                                </p>
                            ) : null}
                        </div>
                        <div className="space-y-2">
                            <Label htmlFor="organization-unit-status">
                                Status
                            </Label>
                            <Select
                                value={form.data.status}
                                onValueChange={(value) =>
                                    form.setData(
                                        'status',
                                        value as OrganizationUnitStatus,
                                    )
                                }
                            >
                                <SelectTrigger id="organization-unit-status">
                                    <SelectValue placeholder="Pilih status" />
                                </SelectTrigger>
                                <SelectContent>
                                    {statusOptions.map(([value, label]) => (
                                        <SelectItem key={value} value={value}>
                                            {label}
                                        </SelectItem>
                                    ))}
                                </SelectContent>
                            </Select>
                            {form.errors.status ? (
                                <p
                                    className="text-xs text-destructive"
                                    role="alert"
                                >
                                    {form.errors.status}
                                </p>
                            ) : null}
                        </div>
                    </div>

                    <div className="space-y-2">
                        <Label htmlFor="organization-unit-location">
                            Lokasi
                        </Label>
                        <Input
                            id="organization-unit-location"
                            value={form.data.location_name}
                            onChange={(event) =>
                                form.setData(
                                    'location_name',
                                    event.target.value,
                                )
                            }
                            placeholder="Kampus utama"
                            maxLength={180}
                        />
                        {form.errors.location_name ? (
                            <p
                                className="text-xs text-destructive"
                                role="alert"
                            >
                                {form.errors.location_name}
                            </p>
                        ) : null}
                    </div>

                    <DialogFooter>
                        <Button
                            type="button"
                            variant="outline"
                            onClick={() => onOpenChange(false)}
                            disabled={form.processing}
                        >
                            Batal
                        </Button>
                        <LoadingButton type="submit" loading={form.processing}>
                            {form.processing
                                ? 'Menyimpan...'
                                : isEdit
                                  ? 'Simpan perubahan'
                                  : 'Tambah unit'}
                        </LoadingButton>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>
    );
}
