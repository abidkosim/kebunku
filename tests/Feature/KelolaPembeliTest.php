<?php

namespace Tests\Feature;

use App\Livewire\Owner\KelolaPembeli;
use App\Models\Kebun;
use App\Models\Meja;
use App\Models\Owner;
use App\Models\Panen;
use App\Models\Pembeli;
use App\Models\Tanaman;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Kartu ringkasan (total pembeli, total kg, kg menunggu harga, kg belum lunas, total
 * hutang) + filter periode transaksi & status hutang di halaman Kelola Pembeli
 * (item 60 - sebelumnya halaman ini cuma daftar tanpa gambaran menyeluruh).
 */
class KelolaPembeliTest extends TestCase
{
    use RefreshDatabase;

    private Owner $owner;
    private Tanaman $tanaman;

    protected function setUp(): void
    {
        parent::setUp();

        $this->owner = Owner::create([
            'nama' => 'Owner Uji', 'nama_usaha' => 'Kebun Uji', 'username' => 'owneruji',
            'password' => Hash::make('rahasia123'), 'alamat' => 'Jalan Uji', 'mode_langganan' => 'pro',
        ]);

        $kebun = Kebun::create(['id_owners' => $this->owner->id, 'nama_kebun' => 'Kebun Uji']);
        $meja = Meja::create(['kebun_id' => $kebun->id, 'nomor' => 1]);
        $this->tanaman = Tanaman::create([
            'id_owners' => $this->owner->id, 'meja_id' => $meja->id, 'nama_tanaman' => 'Selada',
        ]);

        $this->withSession(['owner_id' => $this->owner->id]);
    }

    private function buatPembeli(string $nama): Pembeli
    {
        return Pembeli::create(['id_owners' => $this->owner->id, 'nama' => $nama]);
    }

    private function buatPanen(Pembeli $pembeli, ?float $hargaPerKg, float $dibayar, float $beratKg = 10, ?string $tanggal = null): Panen
    {
        return Panen::create([
            'tanaman_id' => $this->tanaman->id,
            'pembeli_id' => $pembeli->id,
            'pemanen' => 'Uji',
            'tanggal' => $tanggal ?? now()->toDateString(),
            'berat_kg' => $beratKg,
            'harga_per_kg' => $hargaPerKg,
            'jumlah_dibayar' => $dibayar,
        ]);
    }

    public function test_kartu_ringkasan_menghitung_total_pembeli_dan_kg_dengan_benar(): void
    {
        $lunas = $this->buatPembeli('Pembeli Lunas');
        $hutang = $this->buatPembeli('Pembeli Hutang');
        $menunggu = $this->buatPembeli('Pembeli Menunggu');

        $this->buatPanen($lunas, hargaPerKg: 5000, dibayar: 50000, beratKg: 10); // lunas, 10kg
        $this->buatPanen($hutang, hargaPerKg: 5000, dibayar: 20000, beratKg: 10); // sebagian, sisa 30rb, 10kg
        $this->buatPanen($menunggu, hargaPerKg: null, dibayar: 0, beratKg: 7); // menunggu harga, 7kg

        Livewire::test(KelolaPembeli::class)
            ->assertViewHas('ringkasan', function ($ringkasan) {
                return $ringkasan['total_pembeli'] === 3
                    && (float) $ringkasan['total_kg'] === 27.0
                    && (float) $ringkasan['kg_menunggu_harga'] === 7.0
                    && (float) $ringkasan['kg_belum_lunas'] === 10.0
                    && (float) $ringkasan['total_hutang'] === 30000.0;
            });
    }

    public function test_ringkasan_menghormati_filter_periode_tapi_total_pembeli_tidak(): void
    {
        $pembeli = $this->buatPembeli('Pembeli Lama');
        $this->buatPanen($pembeli, hargaPerKg: 5000, dibayar: 0, beratKg: 20, tanggal: now()->subYear()->toDateString());

        // Default (belum difilter tanggal) = "Semua", jadi transaksi tahun lalu tetap ikut kehitung.
        Livewire::test(KelolaPembeli::class)
            ->assertViewHas('ringkasan', fn ($r) => (float) $r['total_kg'] === 20.0 && $r['total_pembeli'] === 1);

        // Pindah ke "Bulan Ini" -> transaksi tahun lalu tidak boleh ikut kehitung di
        // kg/hutang, TAPI total_pembeli tetap 1 (pembeli itu sendiri masih terdaftar,
        // cuma transaksinya yang di luar periode).
        Livewire::test(KelolaPembeli::class)
            ->call('setPeriode', 'bulan-ini')
            ->assertViewHas('ringkasan', fn ($r) => (float) $r['total_kg'] === 0.0 && $r['total_pembeli'] === 1);
    }

    public function test_filter_status_menyaring_daftar_pembeli(): void
    {
        $lunas = $this->buatPembeli('Pembeli Lunas');
        $hutangPenuh = $this->buatPembeli('Pembeli Hutang Penuh');
        $sebagian = $this->buatPembeli('Pembeli Sebagian');
        $menunggu = $this->buatPembeli('Pembeli Menunggu');

        $this->buatPanen($lunas, 5000, 50000, 10);
        $this->buatPanen($hutangPenuh, 5000, 0, 10);
        $this->buatPanen($sebagian, 5000, 20000, 10);
        $this->buatPanen($menunggu, null, 0, 10);

        Livewire::test(KelolaPembeli::class)
            ->set('filterStatus', 'lunas')
            ->assertSee('Pembeli Lunas')
            ->assertDontSee('Pembeli Hutang Penuh')
            ->assertDontSee('Pembeli Sebagian')
            ->assertDontSee('Pembeli Menunggu');

        Livewire::test(KelolaPembeli::class)
            ->set('filterStatus', 'hutang')
            ->assertSee('Pembeli Hutang Penuh')
            ->assertDontSee('Pembeli Lunas')
            ->assertDontSee('Pembeli Sebagian')
            ->assertDontSee('Pembeli Menunggu');

        Livewire::test(KelolaPembeli::class)
            ->set('filterStatus', 'sebagian')
            ->assertSee('Pembeli Sebagian')
            ->assertDontSee('Pembeli Lunas')
            ->assertDontSee('Pembeli Hutang Penuh')
            ->assertDontSee('Pembeli Menunggu');

        Livewire::test(KelolaPembeli::class)
            ->set('filterStatus', 'menunggu_harga')
            ->assertSee('Pembeli Menunggu')
            ->assertDontSee('Pembeli Lunas')
            ->assertDontSee('Pembeli Hutang Penuh')
            ->assertDontSee('Pembeli Sebagian');
    }

    public function test_filter_status_ikut_menghormati_periode(): void
    {
        $pembeli = $this->buatPembeli('Pembeli Musiman');
        // Transaksi ber-hutang, tapi TAHUN LALU - di luar periode "Bulan Ini".
        $this->buatPanen($pembeli, 5000, 0, 10, now()->subYear()->toDateString());

        // Difilter ke "hutang" DAN "Bulan Ini" sekaligus - pembeli ini TIDAK BOLEH
        // muncul, karena dalam periode itu dia tidak punya transaksi berharga sama
        // sekali (statusnya jadi 'menunggu_harga' untuk periode itu, bukan 'hutang').
        Livewire::test(KelolaPembeli::class)
            ->call('setPeriode', 'bulan-ini')
            ->set('filterStatus', 'hutang')
            ->assertDontSee('Pembeli Musiman');

        Livewire::test(KelolaPembeli::class)
            ->call('setPeriode', 'bulan-ini')
            ->set('filterStatus', 'menunggu_harga')
            ->assertSee('Pembeli Musiman');

        // Balik ke "Semua" periode - baru kelihatan lagi di filter "hutang".
        Livewire::test(KelolaPembeli::class)
            ->call('setPeriode', 'semua')
            ->set('filterStatus', 'hutang')
            ->assertSee('Pembeli Musiman');
    }

    public function test_reset_filter_mengembalikan_semua_filter(): void
    {
        $pembeli = $this->buatPembeli('Pembeli Uji');
        $this->buatPanen($pembeli, 5000, 0, 10);

        Livewire::test(KelolaPembeli::class)
            ->set('search', 'tidak ketemu apa-apa')
            ->set('filterStatus', 'lunas')
            ->call('setPeriode', 'bulan-ini')
            ->call('resetFilter')
            ->assertSet('search', '')
            ->assertSet('filterStatus', '')
            ->assertSet('dariTanggal', null)
            ->assertSet('sampaiTanggal', null)
            ->assertSee('Pembeli Uji');
    }
}
