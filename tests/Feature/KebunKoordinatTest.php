<?php

namespace Tests\Feature;

use App\Livewire\Owner\KelolaKebun;
use App\Models\Kebun;
use App\Models\Owner;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Koordinat kebun - dasar validasi radius 20m fitur Absensi (lihat AbsensiTest.php).
 * Nullable & opsional: kebun tanpa koordinat tetap bisa dibuat/dipakai seperti biasa,
 * cuma Absensi untuk kebun itu terkunci sampai koordinatnya diisi.
 */
class KebunKoordinatTest extends TestCase
{
    use RefreshDatabase;

    private Owner $owner;

    protected function setUp(): void
    {
        parent::setUp();

        $this->owner = Owner::create([
            'nama' => 'Owner Uji', 'nama_usaha' => 'Kebun Uji', 'username' => 'owneruji',
            'password' => Hash::make('rahasia123'), 'alamat' => 'Jalan Uji', 'mode_langganan' => 'pro',
        ]);
    }

    public function test_jarak_meter_akurat_untuk_satu_derajat_lintang(): void
    {
        // Satu derajat lintang di ekuator/dekat ekuator = ~111.320 km, angka rujukan baku.
        $jarak = Kebun::jarakMeter(0, 0, 1, 0);

        $this->assertEqualsWithDelta(111320, $jarak, 200);
    }

    public function test_jarak_meter_nol_untuk_titik_yang_sama(): void
    {
        $this->assertEqualsWithDelta(0, Kebun::jarakMeter(-6.9, 107.6, -6.9, 107.6), 0.001);
    }

    public function test_terdekat_dengan_koordinat_null_kalau_tidak_ada_kebun_berkoordinat(): void
    {
        Kebun::create(['id_owners' => $this->owner->id, 'nama_kebun' => 'Tanpa Titik']);

        $hasil = Kebun::terdekatDenganKoordinat($this->owner->id, -6.9, 107.6);

        $this->assertNull($hasil);
    }

    public function test_terdekat_dengan_koordinat_mengabaikan_kebun_owner_lain(): void
    {
        $ownerLain = Owner::create([
            'nama' => 'Owner Lain', 'nama_usaha' => 'Kebun Lain', 'username' => 'ownerlain',
            'password' => Hash::make('rahasia123'), 'alamat' => 'Jalan Lain', 'mode_langganan' => 'pro',
        ]);
        Kebun::create(['id_owners' => $ownerLain->id, 'nama_kebun' => 'Kebun Owner Lain', 'lat' => -6.9, 'lng' => 107.6]);

        $hasil = Kebun::terdekatDenganKoordinat($this->owner->id, -6.9, 107.6);

        $this->assertNull($hasil, 'Kebun owner lain tidak boleh ikut dipertimbangkan');
    }

    public function test_owner_bisa_mengisi_koordinat_saat_membuat_kebun(): void
    {
        $this->withSession(['owner_id' => $this->owner->id]);

        Livewire::test(KelolaKebun::class)
            ->set('namaKebun_form', 'Kebun Baru')
            ->set('jumlahMeja_form', 5)
            ->set('lat_form', -6.914744)
            ->set('lng_form', 107.609810)
            ->call('saveKebun')
            ->assertHasNoErrors();

        $kebun = Kebun::where('nama_kebun', 'Kebun Baru')->first();
        $this->assertNotNull($kebun);
        $this->assertEqualsWithDelta(-6.914744, (float) $kebun->lat, 0.0001);
        $this->assertEqualsWithDelta(107.609810, (float) $kebun->lng, 0.0001);
        $this->assertTrue($kebun->punya_koordinat);
    }

    public function test_kebun_boleh_dibuat_tanpa_koordinat(): void
    {
        $this->withSession(['owner_id' => $this->owner->id]);

        Livewire::test(KelolaKebun::class)
            ->set('namaKebun_form', 'Kebun Tanpa Titik')
            ->set('jumlahMeja_form', 5)
            ->call('saveKebun')
            ->assertHasNoErrors();

        $kebun = Kebun::where('nama_kebun', 'Kebun Tanpa Titik')->first();
        $this->assertNotNull($kebun);
        $this->assertFalse($kebun->punya_koordinat);
    }

    public function test_isi_lat_tanpa_lng_ditolak(): void
    {
        $this->withSession(['owner_id' => $this->owner->id]);

        Livewire::test(KelolaKebun::class)
            ->set('namaKebun_form', 'Kebun Setengah Titik')
            ->set('jumlahMeja_form', 5)
            ->set('lat_form', -6.914744)
            ->call('saveKebun')
            ->assertHasErrors('lng_form');

        $this->assertNull(Kebun::where('nama_kebun', 'Kebun Setengah Titik')->first());
    }

    public function test_owner_bisa_mengedit_koordinat_kebun_yang_sudah_ada(): void
    {
        $kebun = Kebun::create(['id_owners' => $this->owner->id, 'nama_kebun' => 'Kebun Lama']);
        $this->withSession(['owner_id' => $this->owner->id]);

        Livewire::test(KelolaKebun::class)
            ->call('openEditKebun', $kebun->id)
            ->set('lat_form', -6.9)
            ->set('lng_form', 107.6)
            ->call('saveKebun')
            ->assertHasNoErrors();

        $kebun->refresh();
        $this->assertTrue($kebun->punya_koordinat);
    }
}
