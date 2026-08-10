import {
    Activity,
    Braces,
    Clock3,
    KeyRound,
    List,
    Mail,
    Palette,
    Wrench,
} from 'lucide-react';
import type { LucideIcon } from 'lucide-react';
import type { SettingCategory } from './types';

export type SettingCategoryDefinition = {
    key: SettingCategory;
    title: string;
    description: string;
    operatorGuide: string;
    icon: LucideIcon;
    accent: string;
    cardTone: string;
};

export type SettingGuidance = {
    title: string;
    purpose: string;
    inputHint: string;
    caution?: string;
};

export const settingCategories: SettingCategoryDefinition[] = [
    {
        key: 'api',
        title: 'API',
        description: 'Rate limit dan retensi idempotency.',
        operatorGuide:
            'Mengatur perlindungan API agar request berulang tidak membebani atau menjalankan operasi yang sama dua kali.',
        icon: Braces,
        accent: 'dashboard-accent--blue',
        cardTone: 'dashboard-card--blue',
    },
    {
        key: 'password',
        title: 'Password',
        description: 'Panjang dan kompleksitas password.',
        operatorGuide:
            'Menentukan syarat password untuk pendaftaran, ganti password, dan reset password berikutnya.',
        icon: KeyRound,
        accent: 'dashboard-accent--rose',
        cardTone: 'dashboard-card--rose',
    },
    {
        key: 'session',
        title: 'Session',
        description: 'Batas idle dan umur session.',
        operatorGuide:
            'Menentukan kapan pengguna harus login kembali demi menjaga keamanan akun yang sedang terbuka.',
        icon: Clock3,
        accent: 'dashboard-accent--amber',
        cardTone: 'dashboard-card--amber',
    },
    {
        key: 'mail',
        title: 'Email',
        description: 'Koneksi SMTP dan identitas pengirim.',
        operatorGuide:
            'Menentukan cara aplikasi mengirim email undangan, verifikasi, dan notifikasi kepada pengguna.',
        icon: Mail,
        accent: 'dashboard-accent--cyan',
        cardTone: 'dashboard-card--cyan',
    },
    {
        key: 'pagination',
        title: 'Pagination',
        description: 'Pilihan dan nilai awal jumlah data per halaman.',
        operatorGuide:
            'Menentukan jumlah data yang nyaman ditampilkan pada setiap halaman daftar.',
        icon: List,
        accent: 'dashboard-accent--cyan',
        cardTone: 'dashboard-card--cyan',
    },
    {
        key: 'branding',
        title: 'Branding',
        description: 'Nama, aset lokal, dan tampilan default.',
        operatorGuide:
            'Menentukan identitas dan tampilan awal aplikasi untuk pengguna yang belum memilih preferensi pribadi.',
        icon: Palette,
        accent: 'dashboard-accent--violet',
        cardTone: 'dashboard-card--violet',
    },
    {
        key: 'monitoring',
        title: 'Monitoring',
        description: 'Capability monitoring eksternal.',
        operatorGuide:
            'Mengaktifkan atau menonaktifkan capability integrasi monitoring yang sudah tersedia pada lingkungan aplikasi.',
        icon: Activity,
        accent: 'dashboard-accent--emerald',
        cardTone: 'dashboard-card--emerald',
    },
    {
        key: 'operations',
        title: 'Operations',
        description: 'Target pemulihan RTO dan RPO.',
        operatorGuide:
            'Mencatat target pemulihan layanan dan toleransi kehilangan data sebagai acuan operasional saat insiden.',
        icon: Wrench,
        accent: 'dashboard-accent--amber',
        cardTone: 'dashboard-card--amber',
    },
];

export const settingGuidance: Record<string, SettingGuidance> = {
    'api.rate_limit.per_minute': {
        title: 'Batas request API per menit',
        purpose:
            'Membatasi berapa kali satu pengguna atau client dapat memanggil endpoint API dalam satu menit.',
        inputHint:
            'Masukkan jumlah request, misalnya 60 berarti maksimal 60 request per menit.',
        caution:
            'Nilai terlalu kecil dapat membuat pengguna menerima penolakan sementara saat bekerja cepat.',
    },
    'api.idempotency.retention_hours': {
        title: 'Masa simpan hasil request API yang sama',
        purpose:
            'Mencegah operasi API dijalankan dua kali ketika client mengirim ulang request dengan idempotency key yang sama.',
        inputHint:
            'Masukkan jumlah jam, misalnya 24 berarti hasil request yang sama dipakai kembali selama 24 jam.',
        caution:
            'Ini bukan masa simpan backup atau riwayat data; hanya perlindungan request API yang diulang.',
    },
    'security.password.min_length': {
        title: 'Panjang minimum password',
        purpose:
            'Menentukan jumlah karakter paling sedikit yang wajib ada pada password baru.',
        inputHint:
            'Masukkan jumlah karakter, misalnya 12 untuk mewajibkan password minimal 12 karakter.',
    },
    'security.password.require_mixed_case': {
        title: 'Wajib huruf besar dan kecil',
        purpose:
            'Menentukan apakah password baru harus memadukan huruf kapital dan huruf kecil.',
        inputHint:
            'Pilih Aktif untuk memperkuat password dengan dua jenis huruf.',
    },
    'security.password.require_numbers': {
        title: 'Wajib angka',
        purpose:
            'Menentukan apakah password baru harus mengandung setidaknya satu angka.',
        inputHint: 'Pilih Aktif untuk mewajibkan angka pada password.',
    },
    'security.password.require_symbols': {
        title: 'Wajib simbol',
        purpose:
            'Menentukan apakah password baru harus mengandung simbol seperti !, @, atau #.',
        inputHint: 'Pilih Aktif bila kebijakan organisasi memerlukan simbol.',
    },
    'security.session.idle_minutes': {
        title: 'Batas tidak aktif sebelum logout',
        purpose:
            'Mengeluarkan pengguna yang tidak melakukan aktivitas untuk menjaga akun pada perangkat yang ditinggalkan.',
        inputHint:
            'Masukkan menit, misalnya 30 berarti logout setelah 30 menit tanpa aktivitas.',
    },
    'security.session.absolute_hours': {
        title: 'Batas maksimum durasi login',
        purpose:
            'Meminta pengguna login kembali setelah waktu tertentu walaupun mereka masih aktif.',
        inputHint:
            'Masukkan jam, misalnya 12 berarti login harus diulang setelah paling lama 12 jam.',
        caution: 'Nilai ini harus lebih lama daripada batas tidak aktif.',
    },
    'mail.mailer': {
        title: 'Metode pengiriman email',
        purpose:
            'Menentukan apakah aplikasi mengirim email melalui server SMTP atau hanya mencatatnya ke log.',
        inputHint:
            'Pilih SMTP untuk pengiriman nyata. Pilih log hanya untuk pengembangan atau pemeriksaan teknis.',
    },
    'mail.host': {
        title: 'Alamat server email',
        purpose:
            'Menentukan nama host atau alamat server SMTP yang menerima email dari aplikasi.',
        inputHint:
            'Masukkan host SMTP, misalnya smtp.example.com atau alamat MailHog pada lingkungan lokal.',
    },
    'mail.port': {
        title: 'Port server email',
        purpose: 'Menentukan pintu koneksi pada server SMTP.',
        inputHint:
            'Gunakan port yang diberikan penyedia email, misalnya 1025 untuk MailHog lokal.',
    },
    'mail.username': {
        title: 'Username SMTP',
        purpose: 'Digunakan untuk autentikasi jika server email memerlukannya.',
        inputHint:
            'Isi hanya jika penyedia SMTP memberi username. Kosongkan untuk mempertahankan nilai yang ada.',
    },
    'mail.password': {
        title: 'Password SMTP',
        purpose:
            'Digunakan bersama username untuk autentikasi ke server email.',
        inputHint:
            'Isi hanya saat ingin mengganti password SMTP. Nilai yang tersimpan tidak ditampilkan.',
    },
    'mail.from_address': {
        title: 'Alamat pengirim email',
        purpose:
            'Menentukan alamat email yang terlihat sebagai pengirim oleh penerima.',
        inputHint:
            'Masukkan alamat email yang valid, misalnya noreply@example.com.',
    },
    'mail.from_name': {
        title: 'Nama pengirim email',
        purpose:
            'Menentukan nama yang terlihat bersama alamat pengirim pada inbox penerima.',
        inputHint:
            'Masukkan nama organisasi atau aplikasi, misalnya Starter13.',
    },
    'pagination.per_page_options': {
        title: 'Pilihan jumlah data per halaman',
        purpose:
            'Menentukan pilihan ukuran halaman yang dapat dipilih pengguna pada daftar data.',
        inputHint: 'Pisahkan angka dengan koma, misalnya 10, 25, 50, 100.',
    },
    'pagination.default_per_page': {
        title: 'Jumlah data awal per halaman',
        purpose:
            'Menentukan jumlah data yang langsung tampil saat pengguna belum memilih ukuran halaman sendiri.',
        inputHint:
            'Pilih angka yang juga tersedia pada pilihan jumlah data per halaman.',
    },
    'branding.app_name': {
        title: 'Nama aplikasi',
        purpose:
            'Menentukan nama aplikasi yang digunakan pada identitas global dan komunikasi sistem.',
        inputHint: 'Masukkan nama singkat yang mudah dikenali pengguna.',
    },
    'branding.logo_path': {
        title: 'Lokasi logo aplikasi',
        purpose: 'Menentukan path aset lokal logo default aplikasi.',
        inputHint:
            'Masukkan path lokal yang diawali /, misalnya /images/logo.svg. Kosongkan bila tidak memakai logo khusus.',
    },
    'branding.favicon_path': {
        title: 'Lokasi ikon browser',
        purpose: 'Menentukan ikon kecil yang muncul pada tab browser.',
        inputHint:
            'Masukkan path aset lokal yang diawali /, misalnya /favicon.ico.',
    },
    'branding.palette_default': {
        title: 'Palet warna awal',
        purpose:
            'Menentukan nuansa warna awal bagi pengguna yang belum memilih palet pribadi.',
        inputHint: 'Pilih palet yang paling sesuai dengan identitas aplikasi.',
    },
    'branding.typography_default': {
        title: 'Jenis huruf awal',
        purpose:
            'Menentukan keluarga huruf awal bagi pengguna yang belum memilih preferensi pribadi.',
        inputHint:
            'Pilih system untuk mengikuti perangkat pengguna, atau pilih gaya huruf yang tersedia.',
    },
    'branding.appearance_default': {
        title: 'Mode warna awal',
        purpose:
            'Menentukan mode terang, gelap, atau mengikuti perangkat untuk pengguna baru.',
        inputHint:
            'Pilih system agar aplikasi mengikuti pengaturan perangkat pengguna.',
    },
    'monitoring.external_enabled': {
        title: 'Aktifkan monitoring eksternal',
        purpose:
            'Mengizinkan capability monitoring eksternal yang telah dikonfigurasi untuk berjalan.',
        inputHint:
            'Pilih Aktif hanya jika integrasi monitoring pada lingkungan ini sudah siap digunakan.',
    },
    'operations.rto_hours': {
        title: 'Target waktu pemulihan (RTO)',
        purpose:
            'Menentukan target maksimal waktu layanan kembali tersedia setelah insiden.',
        inputHint:
            'Masukkan jam, misalnya 4 berarti target layanan pulih dalam 4 jam.',
        caution:
            'RTO adalah target operasional, bukan proses pemulihan otomatis.',
    },
    'operations.rpo_hours': {
        title: 'Toleransi kehilangan data (RPO)',
        purpose:
            'Menentukan seberapa jauh data boleh tertinggal ketika layanan dipulihkan setelah insiden.',
        inputHint:
            'Masukkan jam, misalnya 24 berarti toleransi kehilangan data maksimal 24 jam.',
        caution: 'RPO adalah target operasional, bukan jadwal backup otomatis.',
    },
};

export function guidanceForSetting(key: string): SettingGuidance {
    return (
        settingGuidance[key] ?? {
            title: key,
            purpose: 'Mengatur perilaku runtime aplikasi untuk key ini.',
            inputHint: 'Masukkan nilai sesuai deskripsi teknis yang tersedia.',
        }
    );
}

export function categoryFromKey(key: string): SettingCategory {
    if (key.startsWith('security.password.')) {
        return 'password';
    }

    if (key.startsWith('security.session.')) {
        return 'session';
    }

    const category = key.split('.')[0];

    return settingCategories.some((item) => item.key === category)
        ? (category as SettingCategory)
        : 'operations';
}
