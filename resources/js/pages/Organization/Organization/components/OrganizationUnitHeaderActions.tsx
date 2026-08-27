import { Network, Plus } from 'lucide-react';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';

type Props = {
    total: number;
    canManage: boolean;
    onCreate: () => void;
};

export function OrganizationUnitHeaderActions({
    total,
    canManage,
    onCreate,
}: Props) {
    return (
        <div className="flex flex-wrap items-center gap-2">
            <Badge variant="secondary" className="gap-2 rounded-full">
                <Network className="size-3.5" aria-hidden="true" />
                {total} unit
            </Badge>
            {canManage ? (
                <Button type="button" onClick={onCreate}>
                    <Plus className="size-4" />
                    Tambah unit
                </Button>
            ) : null}
        </div>
    );
}
