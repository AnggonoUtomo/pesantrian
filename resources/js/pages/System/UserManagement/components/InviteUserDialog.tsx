import { useForm } from '@inertiajs/react';
import { MailPlus } from 'lucide-react';
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
import { LoadingButton } from '@/components/ui/loading-button';
import route from '@/lib/route';

export function InviteUserDialog({
    open,
    onOpenChange,
}: {
    open: boolean;
    onOpenChange: (open: boolean) => void;
}) {
    const form = useForm({ name: '', email: '' });

    return (
        <Dialog
            open={open}
            onOpenChange={(next) => !form.processing && onOpenChange(next)}
        >
            <DialogContent>
                <DialogHeader>
                    <DialogTitle className="flex items-center gap-2">
                        <MailPlus className="size-5" />
                        Kirim undangan user
                    </DialogTitle>
                    <DialogDescription>
                        User menerima email untuk menetapkan password. Link
                        hanya dapat digunakan sekali.
                    </DialogDescription>
                </DialogHeader>
                <form
                    className="space-y-4"
                    onSubmit={(event) => {
                        event.preventDefault();
                        form.post(route('system.users.invitations.store'), {
                            preserveScroll: true,
                            onSuccess: () => onOpenChange(false),
                        });
                    }}
                >
                    <label className="space-y-2 text-sm font-medium">
                        Nama
                        <Input
                            value={form.data.name}
                            onChange={(event) =>
                                form.setData('name', event.target.value)
                            }
                            required
                        />
                    </label>
                    <label className="space-y-2 text-sm font-medium">
                        Email
                        <Input
                            type="email"
                            value={form.data.email}
                            onChange={(event) =>
                                form.setData('email', event.target.value)
                            }
                            required
                        />
                    </label>
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
                            {form.processing ? 'Mengirim...' : 'Kirim undangan'}
                        </LoadingButton>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>
    );
}
