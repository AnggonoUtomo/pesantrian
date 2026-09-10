import { useForm } from '@inertiajs/react';
import { FolderPlus, PencilLine } from 'lucide-react';
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
import type {
    StudentAchievementCategory,
    StudentAchievementCategoryPayload,
} from '../types';
import {
    nullable,
    PrestasiFieldError,
    PrestasiTextareaField,
    PrestasiTextField,
} from './PrestasiSantriFormFields';

type Props = {
    open: boolean;
    category: StudentAchievementCategory | null;
    onOpenChange: (open: boolean) => void;
};

type ErrorKey = keyof StudentAchievementCategoryPayload | 'status';

export function PrestasiSantriCategoryDialog({
    open,
    category,
    onOpenChange,
}: Props) {
    const form = useForm<StudentAchievementCategoryPayload>({
        code: category?.code ?? '',
        name: category?.name ?? '',
        description: category?.description ?? null,
    });
    const errors = form.errors as Partial<Record<ErrorKey, string>>;
    const isEdit = category !== null;

    const submit = (event: FormEvent<HTMLFormElement>) => {
        event.preventDefault();

        form.transform((data) => ({
            ...data,
            code: data.code.trim().toUpperCase(),
            name: data.name.trim(),
            description: nullable(data.description ?? ''),
        }));

        const options = {
            preserveScroll: true,
            onSuccess: () => {
                if (!isEdit) {
                    form.reset();
                }

                onOpenChange(false);
            },
        };

        if (category === null) {
            form.post(
                routeOr(
                    '/pesantrian/prestasi-santri/categories',
                    'pesantrian.prestasi-santri.categories.store',
                ),
                options,
            );

            return;
        }

        form.patch(
            routeOr(
                `/pesantrian/prestasi-santri/categories/${category.id}`,
                'pesantrian.prestasi-santri.categories.update',
                category.id,
            ),
            options,
        );
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
                        <span className="dashboard-icon dashboard-accent--yellow flex size-10 items-center justify-center rounded-lg">
                            {isEdit ? (
                                <PencilLine
                                    className="size-5"
                                    aria-hidden="true"
                                />
                            ) : (
                                <FolderPlus
                                    className="size-5"
                                    aria-hidden="true"
                                />
                            )}
                        </span>
                        <div>
                            <DialogTitle>
                                {isEdit
                                    ? 'Edit kategori prestasi'
                                    : 'Buat kategori prestasi'}
                            </DialogTitle>
                            <DialogDescription>
                                Kategori membantu operator mengelompokkan
                                prestasi santri saat pencatatan.
                            </DialogDescription>
                        </div>
                    </div>
                </DialogHeader>
                <form className="space-y-4" onSubmit={submit}>
                    {errors.status ? (
                        <PrestasiFieldError message={errors.status} />
                    ) : null}
                    <PrestasiTextField
                        id="student-achievement-category-code"
                        label="Kode kategori"
                        value={form.data.code}
                        error={errors.code}
                        placeholder="Contoh: AKADEMIK"
                        required
                        onChange={(value) => form.setData('code', value)}
                    />
                    <PrestasiTextField
                        id="student-achievement-category-name"
                        label="Nama kategori"
                        value={form.data.name}
                        error={errors.name}
                        placeholder="Contoh: Akademik"
                        required
                        onChange={(value) => form.setData('name', value)}
                    />
                    <PrestasiTextareaField
                        id="student-achievement-category-description"
                        label="Deskripsi"
                        value={form.data.description ?? ''}
                        error={errors.description}
                        placeholder="Opsional, contoh: prestasi lomba dan capaian akademik"
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
                        <LoadingButton loading={form.processing}>
                            {isEdit
                                ? 'Simpan kategori'
                                : 'Buat kategori'}
                        </LoadingButton>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>
    );
}
