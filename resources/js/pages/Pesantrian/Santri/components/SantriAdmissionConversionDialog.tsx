import { useForm } from '@inertiajs/react';
import { GraduationCap } from 'lucide-react';
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
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { LoadingButton } from '@/components/ui/loading-button';
import { routeOr } from '@/lib/route';

type Props = {
    open: boolean;
    onOpenChange: (open: boolean) => void;
};

type ConversionPayload = {
    admission_id: string;
};

export function SantriAdmissionConversionDialog({
    open,
    onOpenChange,
}: Props) {
    const form = useForm<ConversionPayload>({
        admission_id: '',
    });
    const conversionErrors = form.errors as Partial<
        Record<keyof ConversionPayload | 'admission', string>
    >;

    const submit = (event: FormEvent<HTMLFormElement>) => {
        event.preventDefault();

        form.post(
            routeOr(
                '/pesantrian/students/from-admission',
                'pesantrian.students.from-admission',
            ),
            {
                preserveScroll: true,
                onSuccess: () => {
                    form.reset();
                    onOpenChange(false);
                },
            },
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
                        <span className="dashboard-icon dashboard-accent--teal flex size-10 items-center justify-center rounded-lg">
                            <GraduationCap
                                className="size-5"
                                aria-hidden="true"
                            />
                        </span>
                        <div>
                            <DialogTitle>Konversi dari PPDB</DialogTitle>
                            <DialogDescription className="mt-1">
                                Masukkan ID pendaftaran yang sudah accepted dan
                                eligible. Sistem akan membuat NIS otomatis.
                            </DialogDescription>
                        </div>
                    </div>
                </DialogHeader>

                <form onSubmit={submit} className="space-y-4">
                    <div className="space-y-2">
                        <Label htmlFor="admission-id">
                            ID pendaftaran accepted
                        </Label>
                        <Input
                            id="admission-id"
                            value={form.data.admission_id}
                            placeholder="01..."
                            aria-invalid={
                                conversionErrors.admission_id ||
                                conversionErrors.admission
                                    ? true
                                    : undefined
                            }
                            onChange={(event) =>
                                form.setData(
                                    'admission_id',
                                    event.target.value.trim(),
                                )
                            }
                        />
                        <p className="text-xs text-foreground/60">
                            Ambil ID dari halaman PPDB accepted. Jika
                            pendaftaran belum eligible atau sudah dikonversi,
                            sistem akan menolak dengan pesan validasi.
                        </p>
                        {conversionErrors.admission_id ? (
                            <FieldError
                                message={conversionErrors.admission_id}
                            />
                        ) : null}
                        {conversionErrors.admission ? (
                            <FieldError message={conversionErrors.admission} />
                        ) : null}
                    </div>

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
                            Konversi menjadi santri
                        </LoadingButton>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>
    );
}

function FieldError({ message }: { message: string }) {
    return (
        <p className="text-xs text-destructive" role="alert">
            {message}
        </p>
    );
}
