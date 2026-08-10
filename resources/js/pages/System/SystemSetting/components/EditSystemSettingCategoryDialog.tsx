import { useForm } from '@inertiajs/react';
import { Save, Settings2 } from 'lucide-react';
import type { FormEvent } from 'react';
import InputError from '@/components/input-error';
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
import route from '@/lib/route';
import { guidanceForSetting, settingCategories } from '../categories';
import type {
    SettingCategory,
    SettingValue,
    SystemSettingItem,
} from '../types';

type Props = {
    category: SettingCategory;
    settings: SystemSettingItem[];
    onClose: () => void;
};

type CategoryUpdate = {
    key: string;
    value: SettingValue;
};

type UpdateCategoryForm = {
    updates: CategoryUpdate[];
    reason: string;
};

function valueChanged(
    setting: SystemSettingItem,
    value: SettingValue,
): boolean {
    if (setting.sensitive) {
        return typeof value === 'string' && value.trim() !== '';
    }

    return JSON.stringify(value) !== JSON.stringify(setting.value);
}

export function EditSystemSettingCategoryDialog({
    category,
    settings,
    onClose,
}: Props) {
    const definition = settingCategories.find((item) => item.key === category);
    const form = useForm<UpdateCategoryForm>({
        updates: settings.map((setting) => ({
            key: setting.key,
            value: setting.sensitive ? '' : setting.value,
        })),
        reason: '',
    });
    const changedCount = form.data.updates.filter((update, index) =>
        valueChanged(settings[index], update.value),
    ).length;

    const setValue = (index: number, value: SettingValue) => {
        form.setData(
            'updates',
            form.data.updates.map((update, updateIndex) =>
                updateIndex === index ? { ...update, value } : update,
            ),
        );
    };

    const submit = (event: FormEvent<HTMLFormElement>) => {
        event.preventDefault();

        form.transform((data) => ({
            ...data,
            updates: data.updates.flatMap((update, index) => {
                const setting = settings[index];

                if (!setting || !valueChanged(setting, update.value)) {
                    return [];
                }

                return [
                    {
                        ...update,
                        value:
                            setting.nullable && update.value === ''
                                ? null
                                : update.value,
                    },
                ];
            }),
        }));
        form.patch(route('system.system-settings.category.update', category), {
            preserveScroll: true,
            onSuccess: onClose,
        });
    };

    return (
        <Dialog
            open
            onOpenChange={(open) => {
                if (!open && !form.processing) {
                    onClose();
                }
            }}
        >
            <DialogContent className="sm:max-w-3xl">
                <form
                    onSubmit={submit}
                    className="flex max-h-[calc(100vh-8rem)] flex-col"
                >
                    <DialogHeader className="shrink-0">
                        <div className="flex items-start gap-3 pr-8">
                            <span className="dashboard-icon dashboard-accent--violet flex size-10 shrink-0 items-center justify-center rounded-xl">
                                <Settings2
                                    className="size-5"
                                    aria-hidden="true"
                                />
                            </span>
                            <div>
                                <DialogTitle>
                                    Ubah kategori {definition?.title}
                                </DialogTitle>
                                <DialogDescription className="mt-1">
                                    {definition?.operatorGuide} Ubah beberapa
                                    nilai yang diperlukan, lalu beri satu alasan
                                    untuk seluruh perubahan kategori ini.
                                </DialogDescription>
                            </div>
                        </div>
                    </DialogHeader>

                    <div className="mt-5 space-y-4 overflow-y-auto pr-1">
                        {settings.map((setting, index) => {
                            const update = form.data.updates[index];
                            const error = form.errors[`updates.${index}.value`];
                            const inputId = `system-setting-${index}`;
                            const guidance = guidanceForSetting(setting.key);

                            return (
                                <section
                                    key={setting.key}
                                    className="dashboard-subcard space-y-3 rounded-xl border p-4"
                                >
                                    <div>
                                        <label
                                            htmlFor={inputId}
                                            className="text-sm font-semibold"
                                        >
                                            {guidance.title}
                                        </label>
                                        <p className="mt-1 font-mono text-xs break-all text-foreground/55">
                                            {setting.key}
                                        </p>
                                        <p className="mt-2 text-sm text-foreground/75">
                                            {guidance.purpose}
                                        </p>
                                        <p className="mt-2 text-sm text-foreground/65">
                                            <span className="font-medium">
                                                Cara mengisi:
                                            </span>{' '}
                                            {guidance.inputHint}
                                        </p>
                                        {guidance.caution ? (
                                            <p className="mt-2 text-sm text-amber-700 dark:text-amber-300">
                                                <span className="font-medium">
                                                    Perhatikan:
                                                </span>{' '}
                                                {guidance.caution}
                                            </p>
                                        ) : null}
                                    </div>

                                    {setting.type === 'boolean' ? (
                                        <select
                                            id={inputId}
                                            value={String(update.value)}
                                            onChange={(event) =>
                                                setValue(
                                                    index,
                                                    event.target.value ===
                                                        'true',
                                                )
                                            }
                                            className="h-9 w-full rounded-md border border-input bg-background px-3 text-sm focus-visible:ring-2 focus-visible:ring-ring focus-visible:outline-none"
                                            aria-invalid={Boolean(error)}
                                        >
                                            <option value="true">Aktif</option>
                                            <option value="false">
                                                Nonaktif
                                            </option>
                                        </select>
                                    ) : setting.type === 'enum' ? (
                                        <select
                                            id={inputId}
                                            value={String(update.value ?? '')}
                                            onChange={(event) =>
                                                setValue(
                                                    index,
                                                    event.target.value,
                                                )
                                            }
                                            className="h-9 w-full rounded-md border border-input bg-background px-3 text-sm focus-visible:ring-2 focus-visible:ring-ring focus-visible:outline-none"
                                            aria-invalid={Boolean(error)}
                                        >
                                            {setting.options.map((option) => (
                                                <option
                                                    key={option}
                                                    value={option}
                                                >
                                                    {option}
                                                </option>
                                            ))}
                                        </select>
                                    ) : setting.type === 'integer_list' ? (
                                        <Input
                                            id={inputId}
                                            value={
                                                Array.isArray(update.value)
                                                    ? update.value.join(', ')
                                                    : ''
                                            }
                                            onChange={(event) =>
                                                setValue(
                                                    index,
                                                    event.target.value
                                                        .split(',')
                                                        .map((item) =>
                                                            item.trim(),
                                                        )
                                                        .filter(Boolean)
                                                        .map(Number),
                                                )
                                            }
                                            aria-invalid={Boolean(error)}
                                            aria-describedby={`${inputId}-help`}
                                        />
                                    ) : (
                                        <Input
                                            id={inputId}
                                            type={
                                                setting.type === 'integer'
                                                    ? 'number'
                                                    : setting.type === 'secret'
                                                      ? 'password'
                                                      : 'text'
                                            }
                                            value={String(update.value ?? '')}
                                            min={setting.min ?? undefined}
                                            max={setting.max ?? undefined}
                                            placeholder={
                                                setting.sensitive
                                                    ? 'Kosongkan untuk mempertahankan nilai tersimpan'
                                                    : undefined
                                            }
                                            onChange={(event) =>
                                                setValue(
                                                    index,
                                                    event.target.value,
                                                )
                                            }
                                            aria-invalid={Boolean(error)}
                                        />
                                    )}
                                    <InputError message={error} />
                                    {setting.type === 'integer_list' ? (
                                        <p
                                            id={`${inputId}-help`}
                                            className="text-xs text-foreground/60"
                                        >
                                            Pisahkan setiap jumlah dengan koma,
                                            misalnya 10, 25, 50, 100.
                                        </p>
                                    ) : null}
                                    {setting.sensitive ? (
                                        <p className="text-xs text-foreground/60">
                                            Nilai tersimpan tidak ditampilkan.
                                            Isi hanya jika ingin menggantinya.
                                        </p>
                                    ) : null}
                                </section>
                            );
                        })}

                        <section className="dashboard-subcard space-y-2 rounded-xl border p-4">
                            <label
                                htmlFor="system-setting-category-reason"
                                className="text-sm font-semibold"
                            >
                                Alasan perubahan kategori
                            </label>
                            <textarea
                                id="system-setting-category-reason"
                                value={form.data.reason}
                                onChange={(event) =>
                                    form.setData('reason', event.target.value)
                                }
                                maxLength={500}
                                rows={4}
                                required
                                placeholder="Jelaskan alasan operasional untuk seluruh perubahan di kategori ini."
                                className="w-full resize-y rounded-md border border-input bg-background px-3 py-2 text-sm focus-visible:ring-2 focus-visible:ring-ring focus-visible:outline-none"
                                aria-invalid={Boolean(form.errors.reason)}
                            />
                            <InputError
                                message={
                                    form.errors.reason ?? form.errors.updates
                                }
                            />
                        </section>
                    </div>

                    <DialogFooter className="mt-5 shrink-0">
                        <Button
                            type="button"
                            variant="outline"
                            onClick={onClose}
                            disabled={form.processing}
                        >
                            Batal
                        </Button>
                        <Button
                            type="submit"
                            disabled={form.processing || changedCount === 0}
                        >
                            <Save aria-hidden="true" />
                            {form.processing
                                ? 'Menyimpan...'
                                : `Simpan ${changedCount} perubahan`}
                        </Button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>
    );
}
