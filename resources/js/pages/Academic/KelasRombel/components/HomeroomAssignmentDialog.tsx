import { useForm } from '@inertiajs/react';
import { UserRoundCheck } from 'lucide-react';
import { useEffect } from 'react';
import type { FormEvent } from 'react';
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
import type { ClassGroup, ReferenceOption } from '../types';
import {
    KelasRombelSelectField,
    KelasRombelTextField,
} from './KelasRombelFormFields';

type HomeroomPayload = {
    employee_id: string;
    assigned_on: string;
};

type Props = {
    open: boolean;
    classGroup: ClassGroup;
    employees: ReferenceOption[];
    onOpenChange: (open: boolean) => void;
};

export function HomeroomAssignmentDialog({
    open,
    classGroup,
    employees,
    onOpenChange,
}: Props) {
    const form = useForm<HomeroomPayload>({
        employee_id: '',
        assigned_on: today(),
    });

    useEffect(() => {
        if (open) {
            form.setData({
                employee_id: employees[0]?.id ?? '',
                assigned_on: today(),
            });
            form.clearErrors();
        }
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, [open, employees]);

    const submit = (event: FormEvent<HTMLFormElement>) => {
        event.preventDefault();

        form.post(
            String(
                routeOr(
                    `/academic/class-groups/${classGroup.id}/homerooms`,
                    'academic.class-groups.homerooms.store',
                    classGroup.id,
                ),
            ),
            {
                preserveScroll: true,
                onSuccess: () => onOpenChange(false),
            },
        );
    };

    return (
        <Dialog
            open={open}
            onOpenChange={(nextOpen) => !form.processing && onOpenChange(nextOpen)}
        >
            <DialogContent>
                <DialogHeader>
                    <div className="flex items-center gap-3">
                        <span className="dashboard-icon dashboard-accent--blue flex size-10 items-center justify-center rounded-lg">
                            <UserRoundCheck className="size-5" aria-hidden="true" />
                        </span>
                        <div>
                            <DialogTitle>Tetapkan wali kelas</DialogTitle>
                            <DialogDescription className="mt-1">
                                Satu rombel hanya memiliki satu wali kelas aktif.
                            </DialogDescription>
                        </div>
                    </div>
                </DialogHeader>
                <form onSubmit={submit} className="space-y-4">
                    <KelasRombelSelectField
                        id="homeroom-employee"
                        label="Pegawai / ustaz"
                        value={form.data.employee_id}
                        error={form.errors.employee_id}
                        options={employees}
                        placeholder="Pilih wali kelas"
                        onChange={(value) => form.setData('employee_id', value)}
                    />
                    <KelasRombelTextField
                        id="homeroom-assigned-on"
                        label="Tanggal mulai"
                        type="date"
                        value={form.data.assigned_on}
                        error={form.errors.assigned_on}
                        onChange={(value) => form.setData('assigned_on', value)}
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
                            {form.processing ? 'Menyimpan...' : 'Tetapkan wali kelas'}
                        </LoadingButton>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>
    );
}

function today(): string {
    return new Date().toISOString().slice(0, 10);
}
