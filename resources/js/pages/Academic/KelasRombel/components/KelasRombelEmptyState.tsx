import { School } from 'lucide-react';

export function KelasRombelEmptyState() {
    return (
        <div
            role="status"
            className="rounded-xl border border-dashed p-8 text-center"
        >
            <School
                className="mx-auto size-10 text-foreground/40"
                aria-hidden="true"
            />
            <h3 className="mt-3 font-medium">Belum ada rombel yang cocok</h3>
            <p className="mx-auto mt-1 max-w-md text-sm text-foreground/65">
                Ubah filter pencarian atau pastikan data demo Kelas / Rombel /
                Kurikulum sudah dibuat melalui seeder.
            </p>
        </div>
    );
}
