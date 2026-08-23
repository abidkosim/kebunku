<?php

namespace Tests\Feature;

use App\Models\Jadwal;
use App\Models\Kebun;
use App\Models\Keuangan;
use App\Models\Meja;
use App\Models\Owner;
use App\Models\Panen;
use App\Models\Pembeli;
use App\Models\Tahapan;
use App\Models\Tanaman;
use App\Models\Tandon;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/**
 * Smoke test seluruh halaman: memastikan tiap route benar-benar ter-render (bukan 500),
 * bahwa halaman yang belum login selalu dilempar ke login, dan bahwa jumlah query per
 * halaman tidak meledak (penjaga regresi N+1).
 *
 * Database test = SQLite in-memory (lihat phpunit.xml), jadi test ini TIDAK PERNAH
 * menyentuh data MySQL asli.
 */
class SemuaHalamanTest extends TestCase
{
    use RefreshDatabase;

    private Owner $owner;

    protected function setUp(): void
    {
        parent::setUp();
        $this->siapkanData();
    }

    private function siapkanData(): void
    {
        $this->owner = Owner::create([
            'nama' => 'Owner Uji',
            'nama_usaha' => 'Kebun Uji',
            'username' => 'owneruji',
            'password' => Hash::make('rahasia123'),
            'alamat' => 'Jalan Uji',
            'mode_langganan' => 'pro',
        ]);

        User::create([
            'id_owners' => $this->owner->id,
            'nama' => 'Teknisi Uji',
            'username' => 'teknisiuji',
            'password' => Hash::make('rahasia123'),
            'alamat' => 'Jalan Uji',
            'role' => 'teknisi',
        ]);

        User::create([
            'id_owners' => $this->owner->id,
            'nama' => 'Keuangan Uji',
            'username' => 'keuanganuji',
            'password' => Hash::make('rahasia123'),
            'alamat' => 'Jalan Uji',
            'role' => 'keuangan',
        ]);

        $kebun = Kebun::create(['id_owners' => $this->owner->id, 'nama_kebun' => 'Kebun A']);
        Tandon::create(['id_kebun' => $kebun->id, 'nama' => 'Tandon A']);

        $pembeli = Pembeli::create(['id_owners' => $this->owner->id, 'nama' => 'Pembeli A']);

        // Beberapa tanaman + panen supaya daftar tidak kosong dan hitungan query
        // benar-benar mewakili halaman berisi (bukan halaman kosong).
        foreach (range(1, 6) as $i) {
            $meja = Meja::create(['kebun_id' => $kebun->id, 'nomor' => $i]);
            $tanaman = Tanaman::create([
                'id_owners' => $this->owner->id,
                'meja_id' => $meja->id,
                'nama_tanaman' => "Selada {$i}",
            ]);

            Tahapan::create([
                'tanaman_id' => $tanaman->id,
                'jenis' => 'pendewasaan',
                'jumlah_awal' => 100,
                'jumlah_lolos' => 90,
                'durasi_rencana' => 20,
                'tanggal_mulai' => now()->subDays(30),
                'tanggal_selesai_rencana' => now()->subDays(10),
                'tanggal_selesai_aktual' => now()->subDays(10),
                'status' => 'selesai',
            ]);

            Jadwal::create([
                'tanaman_id' => $tanaman->id,
                'tanggal_rencana' => now()->addDays(5),
                'status' => 'belum',
            ]);

            foreach (range(1, 3) as $j) {
                Panen::create([
                    'tanaman_id' => $tanaman->id,
                    'pembeli_id' => $pembeli->id,
                    'pemanen' => 'Owner Uji',
                    'tanggal' => now()->subDays($j),
                    'berat_kg' => 10.5,
                    'harga_per_kg' => 15000,
                    'jumlah_dibayar' => $j === 1 ? 0 : 157500,
                ]);
            }
        }

        Keuangan::create([
            'id_owners' => $this->owner->id,
            'jenis' => 'pengeluaran',
            'kategori' => 'Pupuk',
            'jumlah' => 250000,
            'tanggal' => now(),
            'dicatat_oleh' => 'Owner Uji',
        ]);
    }

    private function halamanOwner(): array
    {
        return [
            'dashboard' => '/owner/dashboard',
            'user' => '/owner/dashboard/user',
            'tanaman' => '/owner/dashboard/tanaman',
            'kebun' => '/owner/dashboard/tanaman/kebun',
            'semprot' => '/owner/dashboard/tanaman/semprot',
            'panen' => '/owner/dashboard/tanaman/panen',
            'pembeli' => '/owner/dashboard/pembeli',
            'keuangan' => '/owner/dashboard/keuangan',
            'laporan' => '/owner/dashboard/laporan',
            'akun' => '/owner/dashboard/akun',
            'galeri' => '/owner/dashboard/galeri',
            'tandon' => '/owner/dashboard/tandon',
        ];
    }

    private function halamanStaff(): array
    {
        return [
            'dashboard' => '/portal/dashboard',
            'tanaman' => '/portal/tanaman',
            'kebun' => '/portal/tanaman/kebun',
            'semprot' => '/portal/tanaman/semprot',
            'panen' => '/portal/tanaman/panen',
            'akun' => '/portal/akun',
            'galeri' => '/portal/galeri',
        ];
    }

    public function test_semua_halaman_owner_ter_render(): void
    {
        foreach ($this->halamanOwner() as $nama => $url) {
            $response = $this->withSession(['owner_id' => $this->owner->id])->get($url);

            $this->assertSame(200, $response->status(), "Halaman owner '{$nama}' ({$url}) gagal render");
        }
    }

    public function test_semua_halaman_staff_ter_render(): void
    {
        foreach (['teknisi', 'keuangan'] as $role) {
            $staff = User::where('role', $role)->firstOrFail();

            foreach ($this->halamanStaff() as $nama => $url) {
                $response = $this->withSession(['user_id' => $staff->id])->get($url);

                $this->assertContains(
                    $response->status(),
                    [200, 302],
                    "Halaman staff '{$nama}' ({$url}) sebagai {$role} error: HTTP {$response->status()}"
                );
            }
        }
    }

    public function test_halaman_owner_menolak_pengunjung_tanpa_login(): void
    {
        foreach ($this->halamanOwner() as $nama => $url) {
            $response = $this->get($url);

            $this->assertTrue(
                $response->isRedirect(),
                "Halaman owner '{$nama}' ({$url}) TIDAK melempar pengunjung tanpa login ke halaman lain (HTTP {$response->status()})"
            );
        }
    }

    public function test_staff_tidak_bisa_membuka_halaman_khusus_owner(): void
    {
        $teknisi = User::where('role', 'teknisi')->firstOrFail();

        // Halaman ini requireRole(['owner']) - teknisi harus dilempar balik.
        foreach (['/owner/dashboard/user', '/owner/dashboard/pembeli', '/owner/dashboard/tandon'] as $url) {
            $response = $this->withSession(['user_id' => $teknisi->id])->get($url);

            $this->assertTrue(
                $response->isRedirect(),
                "Teknisi bisa membuka halaman khusus owner {$url} (HTTP {$response->status()})"
            );
        }
    }

    /**
     * Penjaga regresi N+1: kalau nanti ada yang tidak sengaja mengembalikan pola
     * "tarik semua baris lalu jumlahkan di PHP", jumlah query halaman terkait akan
     * melonjak dan test ini gagal.
     */
    public function test_jumlah_query_per_halaman_tetap_wajar(): void
    {
        $batas = 40;
        $hasil = [];

        foreach ($this->halamanOwner() as $nama => $url) {
            DB::flushQueryLog();
            DB::enableQueryLog();

            $this->withSession(['owner_id' => $this->owner->id])->get($url);

            $jumlah = count(DB::getQueryLog());
            DB::disableQueryLog();

            $hasil[$nama] = $jumlah;
        }

        $terlalu = array_filter($hasil, fn ($n) => $n > $batas);

        $this->assertEmpty(
            $terlalu,
            'Halaman berikut menjalankan lebih dari '.$batas.' query: '.json_encode($terlalu)
                .' | seluruh hasil: '.json_encode($hasil)
        );
    }

    public function test_login_owner_dan_staff_berhasil(): void
    {
        $this->get('/')->assertStatus(200);

        $owner = Owner::where('username', 'owneruji')->first();
        $this->assertNotNull($owner, 'Owner tidak ditemukan lewat username (indeks/kolom username rusak?)');
        $this->assertTrue(Hash::check('rahasia123', $owner->password), 'Password owner tidak lagi cocok');

        $teknisi = User::where('username', 'teknisiuji')->first();
        $this->assertNotNull($teknisi, 'User tidak ditemukan lewat username');
        $this->assertTrue(Hash::check('rahasia123', $teknisi->password), 'Password user tidak lagi cocok');
        $this->assertSame($this->owner->id, (int) $teknisi->id_owners, 'Relasi user->owner rusak setelah kolom id_owners diubah jadi bigint');
    }

    public function test_monitor_publik_hanya_bisa_dibuka_dengan_kunci_benar(): void
    {
        $this->owner->update(['kunci_monitor' => 'kunciujimonitor1234567890abcd']);

        $this->get('/monitor/kunciujimonitor1234567890abcd')->assertStatus(200);
        $this->get('/monitor/kunci-yang-salah')->assertStatus(404);
    }
}
