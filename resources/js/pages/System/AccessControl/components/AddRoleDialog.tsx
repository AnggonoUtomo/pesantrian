import { Plus } from 'lucide-react';
import type { FormEvent } from 'react';
import { useState } from 'react';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
    DialogTrigger,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';

interface AddRoleDialogProps {
    canManage: boolean;
    isProcessing: boolean;
    onSubmit: (name: string) => void;
}

export function AddRoleDialog({
    canManage,
    isProcessing,
    onSubmit,
}: AddRoleDialogProps) {
    const [open, setOpen] = useState(false);
    const [name, setName] = useState('');

    const submit = (event: FormEvent<HTMLFormElement>) => {
        event.preventDefault();
        const trimmedName = name.trim();

        if (!trimmedName) {
            return;
        }

        onSubmit(trimmedName);
        setName('');
        setOpen(false);
    };

    return (
        <Dialog open={open} onOpenChange={setOpen}>
            <DialogTrigger asChild>
                <Button type="button" size="sm" disabled={!canManage}>
                    <Plus />
                    Tambah role
                </Button>
            </DialogTrigger>
            <DialogContent>
                <DialogHeader>
                    <DialogTitle>Tambah role</DialogTitle>
                    <DialogDescription>
                        Buat role baru. Permission dapat diatur setelah role
                        dibuat.
                    </DialogDescription>
                </DialogHeader>
                <form onSubmit={submit} className="space-y-4">
                    <div className="space-y-2">
                        <label htmlFor="new-role-name" className="text-sm font-medium">
                            Nama role
                        </label>
                        <Input
                            id="new-role-name"
                            value={name}
                            onChange={(event) => setName(event.target.value)}
                            placeholder="Contoh: ContentEditor"
                            autoFocus
                            disabled={isProcessing}
                        />
                    </div>
                    <DialogFooter>
                        <Button
                            type="button"
                            variant="outline"
                            onClick={() => setOpen(false)}
                        >
                            Batal
                        </Button>
                        <Button type="submit" disabled={isProcessing || !name.trim()}>
                            {isProcessing ? 'Menyimpan...' : 'Simpan role'}
                        </Button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>
    );
}
