export function EmployeeEmptyState() {
    return (
        <div
            role="status"
            className="rounded-xl border border-dashed p-8 text-center"
        >
            <h3 className="font-medium">Belum ada employee yang cocok</h3>
            <p className="mt-2 text-sm text-foreground/65">
                Coba ubah pencarian atau filter status/type untuk menemukan data
                SDM yang dibutuhkan.
            </p>
        </div>
    );
}
