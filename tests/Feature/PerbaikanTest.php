<?php

namespace Tests\Feature;

use App\Livewire\Owner\Auth\Login;
use App\Livewire\Owner\KelolaPanen;
use App\Models\Kebun;
use App\Models\Meja;
use App\Models\Owner;
use App\Models\Panen;
use App\Models\Pembeli;
use App\Models\Tahapan;
use App\Models\Tanaman;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Test untuk bug & celah yang diperbaiki pada audit ini. Masing-masing test GAGAL
 * pada kode sebelum perbaikan, jadi berfungsi sebagai penjaga supaya masalahnya
 * tidak diam-diam kembali lagi.
 */
class PerbaikanTest extends TestCase
{
    use RefreshDatabase;

    private Owner $owner;

    protected function setUp(): void
    {
        parent::setUp();

        $this->owner = Owner::create([
            'nama' => 'Owner Uji',
            'nama_usaha' => 'Kebun Uji',
            'username' => 'owneruji',
            'password' => Hash::make('rahasia123'),
            'alamat' => 'Jalan Uji',
            'mode_langganan' => 'pro',
        ]);
    }

    // ---------- Keamanan login ----------

    public function test_id_sesi_berganti_setelah_login_berhasil(): void
    {
        $this->startSession();
        $idSebelum = session()->getId();

        Livewire::test(Login::class)
            ->set('username', 'owneruji')
            ->set('password', 'rahasia123')
            ->call('login');

        $this->assertNotSame(
            $idSebelum,
            session()->getId(),
            'ID sesi tidak berganti setelah login - masih rawan session fixation'
        );
        $this->assertSame($this->owner->id, session('owner_id'));
    }

    public function test_login_dibatasi_setelah_beberapa_kali_gagal(): void
    {
        RateLimiter::clear('login:owneruji|127.0.0.1');

        for ($i = 0; $i < 5; $i++) {
            Livewire::test(Login::class)
                ->set('username', 'owneruji')
                ->set('password', 'salah-terus')
                ->call('login')
                ->assertHasErrors('username');
        }

        // Percobaan ke-6 harus ditolak oleh pembatas, dan password yang BENAR pun
        // tidak boleh langsung meloloskan selama masih dalam masa kunci.
        Livewire::test(Login::class)
            ->set('username', 'owneruji')
            ->set('password', 'rahasia123')
            ->call('login')
            ->assertHasErrors('username');

        $this->assertNull(session('owner_id'), 'Login lolos padahal sedang dalam masa kunci');
    }

    public function test_login_dengan_password_benar_tetap_berhasil(): void
    {
        RateLimiter::clear('login:owneruji|127.0.0.1');

        Livewire::test(Login::class)
            ->set('username', 'owneruji')
            ->set('password', 'rahasia123')
            ->call('login')
            ->assertHasNoErrors()
            ->assertRedirect('/owner/dashboard');
    }

    public function test_staff_bisa_login_dan_diarahkan_ke_portal(): void
    {
        User::create([
            'id_owners' => $this->owner->id,
            'nama' => 'Teknisi Uji',
            'username' => 'teknisiuji',
            'password' => Hash::make('rahasia123'),
            'alamat' => 'Jalan Uji',
            'role' => 'teknisi',
        ]);

        RateLimiter::clear('login:teknisiuji|127.0.0.1');

        Livewire::test(Login::class)
            ->set('username', 'teknisiuji')
            ->set('password', 'rahasia123')
            ->call('login')
            ->assertHasNoErrors()
            ->assertRedirect('/portal/dashboard');
    }

    // ---------- Alur ----------

    public function test_pengguna_yang_sudah_login_tidak_dibawa_ke_halaman_login_lagi(): void
    {
        $this->withSession(['owner_id' => $this->owner->id])
            ->get('/')
            ->assertRedirect('/owner/dashboard');
    }

    // ---------- Bug perhitungan pembayaran ----------

    public function test_harga_per_kg_yang_diperbaiki_ikut_tersimpan_saat_catat_pembayaran(): void
    {
        $panen = $this->buatPanen(hargaPerKg: 15000, dibayar: 0); // total 10kg x 15.000 = 150.000

        // Owner memperbaiki harga jadi 16.000 -> kekurangan seharusnya jadi 160.000.
        Livewire::test(KelolaPanen::class)
            ->call('openCatatPembayaran', $panen->id)
            ->set('hargaPerKgBayar_form', 16000)
            ->set('tambahanBayar_form', 160000)
            ->call('simpanPembayaran')
            ->assertHasNoErrors();

        $panen->refresh();

        $this->assertEquals(
            16000,
            (float) $panen->harga_per_kg,
            'Harga per kg hasil koreksi tidak tersimpan - server masih memakai harga lama'
        );
        $this->assertEquals(160000, (float) $panen->jumlah_dibayar);
        $this->assertEquals(0.0, $panen->sisa_hutang, 'Transaksi seharusnya sudah lunas');
    }

    public function test_pembayaran_melebihi_total_tetap_ditolak(): void
    {
        $panen = $this->buatPanen(hargaPerKg: 15000, dibayar: 0);

        Livewire::test(KelolaPanen::class)
            ->call('openCatatPembayaran', $panen->id)
            ->set('hargaPerKgBayar_form', 15000)
            ->set('tambahanBayar_form', 999999)
            ->call('simpanPembayaran')
            ->assertHasErrors('tambahanBayar_form');

        $this->assertEquals(0, (float) $panen->refresh()->jumlah_dibayar);
    }

    public function test_harga_bisa_ditentukan_untuk_panen_yang_tadinya_hutang_tanpa_harga(): void
    {
        $panen = $this->buatPanen(hargaPerKg: null, dibayar: 0);

        Livewire::test(KelolaPanen::class)
            ->call('openCatatPembayaran', $panen->id)
            ->set('hargaPerKgBayar_form', 12000)
            ->set('tambahanBayar_form', 120000)
            ->call('simpanPembayaran')
            ->assertHasNoErrors();

        $panen->refresh();
        $this->assertEquals(12000, (float) $panen->harga_per_kg);
        $this->assertEquals('lunas', $panen->status_pembayaran);
    }

    private function buatPanen(?float $hargaPerKg, float $dibayar): Panen
    {
        $this->withSession(['owner_id' => $this->owner->id]);
        session(['owner_id' => $this->owner->id]);

        $kebun = Kebun::create(['id_owners' => $this->owner->id, 'nama_kebun' => 'Kebun A']);
        $meja = Meja::create(['kebun_id' => $kebun->id, 'nomor' => 1]);
        $tanaman = Tanaman::create([
            'id_owners' => $this->owner->id,
            'meja_id' => $meja->id,
            'nama_tanaman' => 'Selada',
        ]);

        // KelolaPanen hanya menampilkan tanaman yang pendewasaannya sudah selesai.
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

        $pembeli = Pembeli::create(['id_owners' => $this->owner->id, 'nama' => 'Pembeli A']);

        return Panen::create([
            'tanaman_id' => $tanaman->id,
            'pembeli_id' => $pembeli->id,
            'pemanen' => 'Owner Uji',
            'tanggal' => now(),
            'berat_kg' => 10,
            'harga_per_kg' => $hargaPerKg,
            'jumlah_dibayar' => $dibayar,
        ]);
    }
}
