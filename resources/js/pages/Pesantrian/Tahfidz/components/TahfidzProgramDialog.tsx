import { useForm } from '@inertiajs/react';
import { BookOpen } from 'lucide-react';
import type { FormEvent } from 'react';
import { useMemo, useState } from 'react';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { LoadingButton } from '@/components/ui/loading-button';
import { SelectItem } from '@/components/ui/select';
import { routeOr } from '@/lib/route';
import type {
    TahfidzIndexPageProps,
    TahfidzProgramOption,
    TahfidzProgramPayload,
} from '../types';
import {
    nullable,
    TahfidzCancelButton,
    TahfidzFieldError,
    TahfidzSelectField,
    TahfidzTextareaField,
    TahfidzTextField,
} from './TahfidzFormFields';

type Props = {
    open: boolean;
    options: TahfidzIndexPageProps['options'];
    onOpenChange: (open: boolean) => void;
};

type ErrorKey = keyof TahfidzProgramPayload | 'payload';

export function TahfidzProgramDialog({ open, options, onOpenChange }: Props) {
    const [selectedProgramId, setSelectedProgramId] = useState('new');
    const selectedProgram = useMemo(
        () =>
            options.programs.find(
                (program) => program.id === selectedProgramId,
            ) ?? null,
        [options.programs, selectedProgramId],
    );
    const form = useForm<TahfidzProgramPayload>(
        programDefaults(selectedProgram),
    );
    const errors = form.errors as Partial<Record<ErrorKey, string>>;
    const isEdit = selectedProgram !== null;

    const chooseProgram = (programId: string) => {
        setSelectedProgramId(programId);
        const program =
            options.programs.find((option) => option.id === programId) ?? null;
        form.setData(programDefaults(program));
        form.clearErrors();
    };

    const submit = (event: FormEvent<HTMLFormElement>) => {
        event.preventDefault();

        const url =
            selectedProgram === null
                ? routeOr(
                      '/pesantrian/tahfidz/programs',
                      'pesantrian.tahfidz.programs.store',
                  )
                : routeOr(
                      `/pesantrian/tahfidz/programs/${selectedProgram.id}`,
                      'pesantrian.tahfidz.programs.update',
                      selectedProgram.id,
                  );
        const submitOptions = {
            preserveScroll: true,
            onSuccess: () => {
                setSelectedProgramId('new');
                form.reset();
                onOpenChange(false);
            },
        };

        if (selectedProgram === null) {
            form.post(url, submitOptions);

            return;
        }

        form.patch(url, submitOptions);
    };

    return (
        <Dialog
            open={open}
            onOpenChange={(nextOpen) =>
                !form.processing && onOpenChange(nextOpen)
            }
        >
            <DialogContent className="max-h-[90vh] overflow-y-auto">
                <DialogHeader>
                    <div className="flex items-center gap-3">
                        <span className="dashboard-icon dashboard-accent--green flex size-10 items-center justify-center rounded-lg">
                            <BookOpen className="size-5" aria-hidden="true" />
                        </span>
                        <div>
                            <DialogTitle>Program tahfidz</DialogTitle>
                            <DialogDescription className="mt-1">
                                Buat atau ubah program hafalan, misalnya
                                reguler, intensif, atau program asrama.
                            </DialogDescription>
                        </div>
                    </div>
                </DialogHeader>

                {errors.payload ? <TahfidzFieldError message={errors.payload} /> : null}

                <form className="space-y-4" onSubmit={submit}>
                    <TahfidzSelectField
                        id="tahfidz-program-existing"
                        label="Mode"
                        value={selectedProgramId}
                        onChange={chooseProgram}
                    >
                        <SelectItem value="new">Buat program baru</SelectItem>
                        {options.programs.map((program) => (
                            <SelectItem key={program.id} value={program.id}>
                                Edit {program.name} ({program.code})
                            </SelectItem>
                        ))}
                    </TahfidzSelectField>

                    <div className="grid gap-4 sm:grid-cols-2">
                        <TahfidzTextField
                            id="tahfidz-program-code"
                            label="Kode program"
                            value={form.data.code}
                            error={errors.code}
                            placeholder="Contoh: THF-REG"
                            required
                            onChange={(value) =>
                                form.setData('code', value.toUpperCase())
                            }
                        />
                        <TahfidzTextField
                            id="tahfidz-program-name"
                            label="Nama program"
                            value={form.data.name}
                            error={errors.name}
                            placeholder="Contoh: Tahfidz Reguler"
                            required
                            onChange={(value) => form.setData('name', value)}
                        />
                    </div>
                    <TahfidzTextareaField
                        id="tahfidz-program-description"
                        label="Deskripsi"
                        value={form.data.description ?? ''}
                        error={errors.description}
                        placeholder="Opsional"
                        onChange={(value) =>
                            form.setData('description', nullable(value))
                        }
                    />
                    <TahfidzSelectField
                        id="tahfidz-program-status"
                        label="Status program"
                        value={form.data.status}
                        error={errors.status}
                        onChange={(value) =>
                            form.setData(
                                'status',
                                value as TahfidzProgramPayload['status'],
                            )
                        }
                    >
                        <SelectItem value="active">Aktif</SelectItem>
                        <SelectItem value="inactive">Nonaktif</SelectItem>
                    </TahfidzSelectField>

                    <DialogFooter>
                        <TahfidzCancelButton
                            disabled={form.processing}
                            onClick={() => onOpenChange(false)}
                        />
                        <LoadingButton type="submit" loading={form.processing}>
                            {isEdit ? 'Simpan program' : 'Buat program'}
                        </LoadingButton>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>
    );
}

function programDefaults(
    program: TahfidzProgramOption | null,
): TahfidzProgramPayload {
    return {
        code: program?.code ?? '',
        name: program?.name ?? '',
        description: program?.description ?? null,
        status: (program?.status as TahfidzProgramPayload['status']) ?? 'active',
    };
}
