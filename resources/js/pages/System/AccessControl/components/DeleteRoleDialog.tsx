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
import type { AccessControlRole } from '../types';

interface DeleteRoleDialogProps {
    role: AccessControlRole | null;
    canManage: boolean;
    isProcessing: boolean;
    onSubmit: (role: AccessControlRole) => void;
}

export function DeleteRoleDialog({
    role,
    canManage,
    isProcessing,
    onSubmit,
}: DeleteRoleDialogProps) {
    const [open, setOpen] = useState(false);

    if (!role || role.is_protected) {
        return null;
    }

    const submit = () => {
        onSubmit(role);
    };

    return (
        <Dialog open={open} onOpenChange={setOpen}>
            <DialogTrigger asChild>
                <Button
                    type="button"
                    variant="outline"
                    size="sm"
                    className="border-destructive text-destructive hover:bg-destructive/10 hover:text-destructive"
                    disabled={!canManage}
                >
                    Hapus role
                </Button>
            </DialogTrigger>
            <DialogContent>
                <DialogHeader>
                    <DialogTitle>Hapus role {role.name}?</DialogTitle>
                    <DialogDescription>
                        Role dan hubungan permission-nya akan dihapus. Tindakan
                        ini tidak dapat dibatalkan.
                    </DialogDescription>
                </DialogHeader>
                <DialogFooter>
                    <Button
                        type="button"
                        variant="outline"
                        onClick={() => setOpen(false)}
                    >
                        Batal
                    </Button>
                    <Button
                        type="button"
                        variant="destructive"
                        onClick={submit}
                        disabled={isProcessing}
                    >
                        {isProcessing ? 'Menghapus...' : 'Hapus role'}
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    );
}
