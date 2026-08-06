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
import type { SettingValue, SystemSettingItem } from '../types';

type Props = {
    setting: SystemSettingItem;
    onClose: () => void;
};

type UpdateSettingForm = {
    value: SettingValue;
    reason: string;
};

export function EditSystemSettingDialog({ setting, onClose }: Props) {
    const form = useForm<UpdateSettingForm>({
        value: setting.value,
        reason: '',
    });

    const submit = (event: FormEvent<HTMLFormElement>) => {
        event.preventDefault();

        form.transform((data) => ({
            ...data,
            value: setting.nullable && data.value === '' ? null : data.value,
        }));
        form.patch(route('system.system-settings.update', setting.key), {
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
            <DialogContent className="sm:max-w-xl">
                <form onSubmit={submit} className="space-y-5">
                    <DialogHeader>
                        <div className="flex items-start gap-3 pr-8">
                            <span className="dashboard-icon dashboard-accent--violet flex size-10 shrink-0 items-center justify-center rounded-xl">
                                <Settings2
                                    className="size-5"
                                    aria-hidden="true"
                                />
                            </span>
                            <div>
                                <DialogTitle>Ubah SystemSetting</DialogTitle>
                                <DialogDescription className="mt-1 break-all">
                                    {setting.key}
                                </DialogDescription>
                            </div>
                        </div>
                    </DialogHeader>

                    <div className="dashboard-subcard space-y-4 rounded-xl border p-4">
                        <div className="space-y-2">
                            <label
                                htmlFor="system-setting-value"
                                className="text-sm font-medium"
                            >
                                Nilai
                            </label>
                            {setting.type === 'boolean' ? (
                                <select
                                    id="system-setting-value"
                                    value={String(form.data.value)}
                                    onChange={(event) =>
                                        form.setData(
                                            'value',
                                            event.target.value === 'true',
                                        )
                                    }
                                    className="h-9 w-full rounded-md border border-input bg-background px-3 text-sm focus-visible:ring-2 focus-visible:ring-ring focus-visible:outline-none"
                                    aria-invalid={Boolean(form.errors.value)}
                                >
                                    <option value="true">Aktif</option>
                                    <option value="false">Nonaktif</option>
                                </select>
                            ) : setting.type === 'enum' ? (
                                <select
                                    id="system-setting-value"
                                    value={String(form.data.value ?? '')}
                                    onChange={(event) =>
                                        form.setData(
                                            'value',
                                            event.target.value,
                                        )
                                    }
                                    className="h-9 w-full rounded-md border border-input bg-background px-3 text-sm focus-visible:ring-2 focus-visible:ring-ring focus-visible:outline-none"
                                    aria-invalid={Boolean(form.errors.value)}
                                >
                                    {setting.options.map((option) => (
                                        <option key={option} value={option}>
                                            {option}
                                        </option>
                                    ))}
                                </select>
                            ) : (
                                <Input
                                    id="system-setting-value"
                                    type={
                                        setting.type === 'integer'
                                            ? 'number'
                                            : 'text'
                                    }
                                    value={String(form.data.value ?? '')}
                                    min={setting.min ?? undefined}
                                    max={setting.max ?? undefined}
                                    onChange={(event) =>
                                        form.setData(
                                            'value',
                                            setting.type === 'integer'
                                                ? Number(event.target.value)
                                                : event.target.value,
                                        )
                                    }
                                    aria-invalid={Boolean(form.errors.value)}
                                />
                            )}
                            <InputError message={form.errors.value} />
                            <p className="text-xs text-foreground/60">
                                {setting.description}
                            </p>
                        </div>

                        <div className="space-y-2">
                            <label
                                htmlFor="system-setting-reason"
                                className="text-sm font-medium"
                            >
                                Alasan perubahan
                            </label>
                            <textarea
                                id="system-setting-reason"
                                value={form.data.reason}
                                onChange={(event) =>
                                    form.setData('reason', event.target.value)
                                }
                                maxLength={500}
                                rows={4}
                                required
                                placeholder="Jelaskan alasan operasional perubahan ini."
                                className="w-full resize-y rounded-md border border-input bg-background px-3 py-2 text-sm focus-visible:ring-2 focus-visible:ring-ring focus-visible:outline-none"
                                aria-invalid={Boolean(form.errors.reason)}
                            />
                            <InputError message={form.errors.reason} />
                        </div>
                    </div>

                    <DialogFooter>
                        <Button
                            type="button"
                            variant="outline"
                            onClick={onClose}
                            disabled={form.processing}
                        >
                            Batal
                        </Button>
                        <Button type="submit" disabled={form.processing}>
                            <Save aria-hidden="true" />
                            {form.processing
                                ? 'Menyimpan...'
                                : 'Simpan perubahan'}
                        </Button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>
    );
}
