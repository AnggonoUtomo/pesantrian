export function TahfidzAccessDenied() {
    return (
        <section className="dashboard-card dashboard-card--amber rounded-2xl border p-6">
            <p className="text-sm font-medium text-foreground/70">
                Akses Tahfidz / Hafalan dibatasi
            </p>
            <h2 className="mt-2 text-xl font-semibold">
                Anda belum memiliki izin untuk melihat data Tahfidz / Hafalan.
            </h2>
            <p className="mt-2 text-sm text-foreground/65">
                Hubungi admin sistem jika tugas Anda membutuhkan akses ke
                program, target, setoran, atau review hafalan santri.
            </p>
        </section>
    );
}
