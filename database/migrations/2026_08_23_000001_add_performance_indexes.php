<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Indeks yang hilang sejak awal. Semua query "panas" aplikasi ini (dropdown notifikasi
 * di SETIAP halaman, login, rekap laporan) sebelumnya jalan lewat full table scan karena
 * kolom filternya tidak pernah diindeks - lihat daftar per tabel di bawah.
 *
 * Migration ini SENGAJA idempotent (cek dulu indeksnya sudah ada atau belum) dan murni
 * aditif: tidak ada DROP kolom, tidak ada DELETE, tidak ada perubahan nilai data.
 * Aman dijalankan berulang di server manapun tanpa risiko kehilangan data.
 */
return new class extends Migration
{
    /**
     * [tabel => [nama_indeks => [kolom...]]]
     */
    private array $indeks = [
        // Dipakai dropdown "Aktivitas Terbaru" yang ikut ter-render di SETIAP halaman
        // owner & staff: where(id_owners)->latest('id')->limit(15). Tanpa indeks ini
        // query tersebut men-scan seluruh tabel log yang tumbuh tiap aksi CRUD.
        'activity_logs' => [
            'activity_logs_id_owners_id_index' => ['id_owners', 'id'],
        ],

        // Login (Owner::where('username')) dan ManajemenUser::render() (where id_owners).
        'owners' => [
            'owners_username_index' => ['username'],
        ],
        'users' => [
            'users_username_index' => ['username'],
            'users_id_owners_index' => ['id_owners'],
        ],
        'superadmins' => [], // username sudah unique dari migration aslinya

        // Laporan & Dashboard memfilter panen per periode tanggal.
        'panens' => [
            'panens_tanggal_index' => ['tanggal'],
            'panens_tanaman_id_tanggal_index' => ['tanaman_id', 'tanggal'],
        ],

        // whereNull('siklus_selesai_at') dipakai di hampir semua modul tanaman.
        'tanaman' => [
            'tanaman_id_owners_siklus_index' => ['id_owners', 'siklus_selesai_at'],
        ],

        // whereHas tahapans dengan filter jenis+status (KelolaPanen, Staff Dashboard),
        // dan pemindaian tahap "berjalan" untuk modal peringatan H-2.
        'tahapans' => [
            'tahapans_tanaman_jenis_status_index' => ['tanaman_id', 'jenis', 'status'],
            'tahapans_status_selesai_aktual_index' => ['status', 'tanggal_selesai_aktual'],
        ],

        // Rekap keuangan per periode.
        'keuangan' => [
            'keuangan_id_owners_tanggal_index' => ['id_owners', 'tanggal'],
        ],

        // Modal peringatan H-2 jadwal semprot.
        'jadwals' => [
            'jadwals_status_tanggal_rencana_index' => ['status', 'tanggal_rencana'],
        ],

        // Daftar galeri per owner, urut terbaru.
        'galeri' => [
            'galeri_id_owners_id_index' => ['id_owners', 'id'],
        ],

        // Inbox saran di dashboard superadmin.
        'sarans' => [
            'sarans_dibaca_id_index' => ['dibaca', 'id'],
        ],
    ];

    public function up(): void
    {
        // users.id_owners disimpan sebagai VARCHAR sejak migration awal, padahal isinya
        // selalu ID numerik dari tabel owners. Akibatnya MySQL harus meng-cast kolom itu
        // tiap kali dibandingkan dengan integer (where('id_owners', $owner->id)), dan
        // cast di sisi KOLOM membuat indeks apapun tidak terpakai. Diselaraskan ke
        // BIGINT UNSIGNED - tapi hanya kalau semua isinya memang numerik, supaya tidak
        // ada baris yang nilainya berubah/hilang diam-diam.
        $this->selaraskanTipeIdOwnersUsers();

        foreach ($this->indeks as $tabel => $daftar) {
            if (!Schema::hasTable($tabel)) {
                continue;
            }

            foreach ($daftar as $nama => $kolom) {
                if ($this->indeksAda($tabel, $nama) || !$this->semuaKolomAda($tabel, $kolom)) {
                    continue;
                }

                Schema::table($tabel, fn (Blueprint $t) => $t->index($kolom, $nama));
            }
        }
    }

    public function down(): void
    {
        foreach ($this->indeks as $tabel => $daftar) {
            if (!Schema::hasTable($tabel)) {
                continue;
            }

            foreach (array_keys($daftar) as $nama) {
                if ($this->indeksAda($tabel, $nama)) {
                    Schema::table($tabel, fn (Blueprint $t) => $t->dropIndex($nama));
                }
            }
        }
        // Tipe kolom users.id_owners sengaja TIDAK dikembalikan ke varchar - konversinya
        // searah dan tidak merusak apapun kalau dibiarkan sebagai bigint.
    }

    private function selaraskanTipeIdOwnersUsers(): void
    {
        if (!Schema::hasTable('users') || !Schema::hasColumn('users', 'id_owners')) {
            return;
        }

        $tipe = strtolower((string) Schema::getColumnType('users', 'id_owners'));
        if (str_contains($tipe, 'int')) {
            return; // sudah numerik, tidak perlu diapa-apakan
        }

        // Pengecekan dilakukan di PHP (bukan REGEXP di SQL) supaya migration ini tetap
        // jalan di driver manapun - REGEXP tidak tersedia di SQLite yang dipakai test.
        $adaNilaiTidakNumerik = DB::table('users')
            ->whereNotNull('id_owners')
            ->distinct()
            ->pluck('id_owners')
            ->contains(fn ($nilai) => !ctype_digit(trim((string) $nilai)));

        if ($adaNilaiTidakNumerik) {
            // Ada data di luar dugaan - biarkan apa adanya daripada mengubah/merusaknya.
            // Indeks di users.id_owners tetap dipasang di bawah (tetap membantu walau
            // kolomnya varchar, selama pembandingnya juga string).
            return;
        }

        Schema::table('users', function (Blueprint $table) {
            $table->unsignedBigInteger('id_owners')->change();
        });
    }

    private function indeksAda(string $tabel, string $nama): bool
    {
        foreach (Schema::getIndexes($tabel) as $indeks) {
            if (($indeks['name'] ?? null) === $nama) {
                return true;
            }
        }

        return false;
    }

    private function semuaKolomAda(string $tabel, array $kolom): bool
    {
        foreach ($kolom as $k) {
            if (!Schema::hasColumn($tabel, $k)) {
                return false;
            }
        }

        return true;
    }
};
