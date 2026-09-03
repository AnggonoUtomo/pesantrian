import { useForm } from '@inertiajs/react';
import { UserPlus } from 'lucide-react';
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

type PlacementPayload = {
    student_id: string;
    joined_on: string;
};

type Props = {
    open: boolean;
    classGroup: ClassGroup;
    students: ReferenceOption[];
    onOpenChange: (open: boolean) => void;
};

export function StudentPlacementDialog({
    open,
    classGroup,
    students,
    onOpenChange,
}: Props) {
    const form = useForm<PlacementPayload>({
        student_id: '',
        joined_on: today(),
    });

    useEffect(() => {
        if (open) {
            form.setData({
                student_id: students[0]?.id ?? '',
                joined_on: today(),
            });
            form.clearErrors();
        }
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, [open, students]);

    const submit = (event: FormEvent<HTMLFormElement>) => {
        event.preventDefault();

        form.post(
            String(
                routeOr(
                    `/academic/class-groups/${classGroup.id}/students`,
                    'academic.class-groups.students.store',
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
                        <span className="dashboard-icon dashboard-accent--green flex size-10 items-center justify-center rounded-lg">
                            <UserPlus className="size-5" aria-hidden="true" />
                        </span>
                        <div>
                            <DialogTitle>Tempatkan santri</DialogTitle>
                            <DialogDescription className="mt-1">
                                Santri hanya bisa aktif pada satu rombel dalam
                                semester yang sama.
                            </DialogDescription>
                        </div>
                    </div>
                </DialogHeader>
                <form onSubmit={submit} className="space-y-4">
                    <KelasRombelSelectField
                        id="placement-student"
                        label="Santri"
                        value={form.data.student_id}
                        error={form.errors.student_id}
                        options={students}
                        placeholder="Pilih santri"
                        onChange={(value) => form.setData('student_id', value)}
                    />
                    <KelasRombelTextField
                        id="placement-joined-on"
                        label="Tanggal masuk"
                        type="date"
                        value={form.data.joined_on}
                        error={form.errors.joined_on}
                        onChange={(value) => form.setData('joined_on', value)}
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
                            {form.processing ? 'Menyimpan...' : 'Tempatkan santri'}
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
