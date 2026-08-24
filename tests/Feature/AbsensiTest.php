<?php

namespace Tests\Feature;

use App\Livewire\Owner\KelolaAbsensi;
use App\Models\Absensi;
use App\Models\Kebun;
use App\Models\Owner;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Log kunjungan ke kebun: Teknisi catat kunjungan (foto+GPS wajib, kegiatan opsional,
 * WAJIB dalam radius 20m dari kebun terdekat yang sudah punya koordinat), Owner lihat
 * rekap read-only. Keuangan sama sekali tidak punya akses ke fitur ini (bukan bagian
 * dari permintaan awal - lihat plaintext.txt item 53-54).
 */
class AbsensiTest extends TestCase
{
    use RefreshDatabase;

    /** Titik GPS "asli" dipakai kebun uji - kira-kira Bandung. */
    private const LAT_KEBUN = -6.914744;
    private const LNG_KEBUN = 107.609810;

    private Owner $owner;
    private User $teknisi;
    private User $keuangan;
    private Kebun $kebun;

    protected function setUp(): void
    {
        parent::setUp();

        // Tanpa ini, $foto_upload->store('absensi', 'public') di dalam komponen menulis
        // ke disk ASLI (storage/app/public/absensi) - file foto uji akan menumpuk di
        // folder produksi/lokal beneran tiap kali test ini jalan. Storage::fake()
        // mengalihkannya ke direktori sementara yang otomatis dibuang.
        Storage::fake('public');

        $this->owner = Owner::create([
            'nama' => 'Owner Uji', 'nama_usaha' => 'Kebun Uji', 'username' => 'owneruji',
            'password' => Hash::make('rahasia123'), 'alamat' => 'Jalan Uji', 'mode_langganan' => 'pro',
        ]);

        $this->teknisi = User::create([
            'id_owners' => $this->owner->id, 'nama' => 'Teknisi Uji', 'username' => 'teknisiuji',
            'password' => Hash::make('rahasia123'), 'alamat' => 'Jalan Uji', 'role' => 'teknisi',
        ]);

        $this->keuangan = User::create([
            'id_owners' => $this->owner->id, 'nama' => 'Keuangan Uji', 'username' => 'keuanganuji',
            'password' => Hash::make('rahasia123'), 'alamat' => 'Jalan Uji', 'role' => 'keuangan',
        ]);

        $this->kebun = Kebun::create([
            'id_owners' => $this->owner->id, 'nama_kebun' => 'Kebun Uji',
            'lat' => self::LAT_KEBUN, 'lng' => self::LNG_KEBUN,
        ]);
    }

    public function test_teknisi_bisa_mencatat_kunjungan_lengkap(): void
    {
        $this->withSession(['user_id' => $this->teknisi->id]);

        Livewire::test(KelolaAbsensi::class)
            ->call('openCatat')
            ->set('foto_upload', UploadedFile::fake()->image('kunjungan.jpg'))
            ->set('lokasiLat_form', self::LAT_KEBUN)
            ->set('lokasiLng_form', self::LNG_KEBUN)
            ->set('kegiatan_form', 'Semprot hama meja 3-5')
            ->call('simpanAbsensi')
            ->assertHasNoErrors();

        $this->assertSame(1, Absensi::count());

        $log = Absensi::first();
        $this->assertSame($this->owner->id, $log->id_owners);
        $this->assertSame($this->kebun->id, $log->id_kebun, 'Kebun terdekat harus otomatis tersimpan di catatan');
        $this->assertSame('teknisi', $log->actor_type);
        $this->assertSame($this->teknisi->id, $log->actor_id);
        $this->assertSame('Teknisi Uji', $log->actor_nama);
        $this->assertEqualsWithDelta(self::LAT_KEBUN, (float) $log->lokasi_lat, 0.0001);
        $this->assertEqualsWithDelta(self::LNG_KEBUN, (float) $log->lokasi_lng, 0.0001);
        $this->assertSame('Semprot hama meja 3-5', $log->kegiatan);
        $this->assertNotEmpty($log->foto);

        $this->assertDatabaseHas('activity_logs', [
            'id_owners' => $this->owner->id,
            'modul' => 'Absensi',
            'actor_id' => $this->teknisi->id,
        ]);
    }

    public function test_kegiatan_boleh_kosong(): void
    {
        $this->withSession(['user_id' => $this->teknisi->id]);

        Livewire::test(KelolaAbsensi::class)
            ->set('foto_upload', UploadedFile::fake()->image('kunjungan.jpg'))
            ->set('lokasiLat_form', self::LAT_KEBUN)
            ->set('lokasiLng_form', self::LNG_KEBUN)
            ->call('simpanAbsensi')
            ->assertHasNoErrors();

        $this->assertNull(Absensi::first()->kegiatan);
    }

    public function test_tanpa_foto_ditolak(): void
    {
        $this->withSession(['user_id' => $this->teknisi->id]);

        Livewire::test(KelolaAbsensi::class)
            ->set('lokasiLat_form', self::LAT_KEBUN)
            ->set('lokasiLng_form', self::LNG_KEBUN)
            ->call('simpanAbsensi')
            ->assertHasErrors('foto_upload');

        $this->assertSame(0, Absensi::count());
    }

    public function test_tanpa_lokasi_ditolak_dengan_pesan_jelas(): void
    {
        $this->withSession(['user_id' => $this->teknisi->id]);

        Livewire::test(KelolaAbsensi::class)
            ->set('foto_upload', UploadedFile::fake()->image('kunjungan.jpg'))
            ->call('simpanAbsensi')
            ->assertHasErrors(['lokasiLat_form', 'lokasiLng_form']);

        $this->assertSame(0, Absensi::count());
    }

    public function test_ditolak_jika_lebih_dari_20_meter_dari_kebun_terdekat(): void
    {
        $this->withSession(['user_id' => $this->teknisi->id]);

        // ~0.001 derajat lintang =~ 111m dari kebun - jauh di luar radius 20m.
        Livewire::test(KelolaAbsensi::class)
            ->set('foto_upload', UploadedFile::fake()->image('kunjungan.jpg'))
            ->set('lokasiLat_form', self::LAT_KEBUN + 0.001)
            ->set('lokasiLng_form', self::LNG_KEBUN)
            ->call('simpanAbsensi')
            ->assertHasErrors('lokasiLat_form');

        $this->assertSame(0, Absensi::count(), 'Kunjungan di luar radius 20m tidak boleh tersimpan');
    }

    public function test_ditolak_jika_owner_belum_punya_kebun_berkoordinat(): void
    {
        // Owner terpisah yang kebunnya BELUM punya koordinat sama sekali.
        $ownerBaru = Owner::create([
            'nama' => 'Owner Baru', 'nama_usaha' => 'Kebun Baru', 'username' => 'ownerbaru',
            'password' => Hash::make('rahasia123'), 'alamat' => 'Jalan Baru', 'mode_langganan' => 'pro',
        ]);
        $teknisiBaru = User::create([
            'id_owners' => $ownerBaru->id, 'nama' => 'Teknisi Baru', 'username' => 'teknisibaru',
            'password' => Hash::make('rahasia123'), 'alamat' => 'Jalan Baru', 'role' => 'teknisi',
        ]);
        Kebun::create(['id_owners' => $ownerBaru->id, 'nama_kebun' => 'Kebun Tanpa Titik']); // lat/lng kosong

        $this->withSession(['user_id' => $teknisiBaru->id]);

        Livewire::test(KelolaAbsensi::class)
            ->set('foto_upload', UploadedFile::fake()->image('kunjungan.jpg'))
            ->set('lokasiLat_form', self::LAT_KEBUN)
            ->set('lokasiLng_form', self::LNG_KEBUN)
            ->call('simpanAbsensi')
            ->assertHasErrors('lokasiLat_form');

        $this->assertSame(0, Absensi::count());
    }

    public function test_memilih_kebun_terdekat_di_antara_beberapa_kebun(): void
    {
        // Kebun kedua persis di titik GPS staff (jarak 0m); Kebun Uji dari setUp ada
        // di sekitar ~5.5m dari situ - staff sedang di Kebun Dekat, bukan Kebun Uji.
        $kebunDekat = Kebun::create([
            'id_owners' => $this->owner->id, 'nama_kebun' => 'Kebun Dekat',
            'lat' => self::LAT_KEBUN + 0.00005, 'lng' => self::LNG_KEBUN, // ~5.5m dari Kebun Uji
        ]);

        $this->withSession(['user_id' => $this->teknisi->id]);

        Livewire::test(KelolaAbsensi::class)
            ->set('foto_upload', UploadedFile::fake()->image('kunjungan.jpg'))
            ->set('lokasiLat_form', self::LAT_KEBUN + 0.00005)
            ->set('lokasiLng_form', self::LNG_KEBUN)
            ->call('simpanAbsensi')
            ->assertHasNoErrors();

        $this->assertSame($kebunDekat->id, Absensi::first()->id_kebun, 'Kebun yang jaraknya paling dekat harus yang tersimpan');
    }

    public function test_owner_hanya_bisa_melihat_rekap_tidak_bisa_mencatat(): void
    {
        $this->withSession(['owner_id' => $this->owner->id]);

        // Guard di server: openCatat() dan simpanAbsensi() harus diam-diam menolak,
        // bukan cuma disembunyikan lewat kondisi di Blade.
        Livewire::test(KelolaAbsensi::class)
            ->call('openCatat')
            ->assertSet('showModal', false);

        Livewire::test(KelolaAbsensi::class)
            ->set('foto_upload', UploadedFile::fake()->image('paksa.jpg'))
            ->set('lokasiLat_form', self::LAT_KEBUN)
            ->set('lokasiLng_form', self::LNG_KEBUN)
            ->call('simpanAbsensi');

        $this->assertSame(0, Absensi::count(), 'Owner tidak boleh bisa mencatat kunjungan walau method dipanggil paksa');
    }

    public function test_keuangan_tidak_bisa_membuka_halaman_absensi(): void
    {
        $response = $this->withSession(['user_id' => $this->keuangan->id])->get('/portal/absensi');

        $this->assertTrue($response->isRedirect(), 'Keuangan seharusnya tidak punya akses ke fitur Absensi sama sekali');
    }

    public function test_rekap_tampil_ke_owner(): void
    {
        Absensi::create([
            'id_owners' => $this->owner->id, 'id_kebun' => $this->kebun->id, 'actor_type' => 'teknisi', 'actor_id' => $this->teknisi->id,
            'actor_nama' => 'Teknisi Uji', 'foto' => 'absensi/contoh.jpg',
            'lokasi_lat' => self::LAT_KEBUN, 'lokasi_lng' => self::LNG_KEBUN, 'kegiatan' => 'Cek tandon',
        ]);

        $this->withSession(['owner_id' => $this->owner->id])
            ->get('/owner/dashboard/absensi')
            ->assertStatus(200)
            ->assertSee('Teknisi Uji')
            ->assertSee('Cek tandon')
            ->assertSee('Kebun Uji');
    }

    public function test_rekap_tampil_ke_teknisi(): void
    {
        Absensi::create([
            'id_owners' => $this->owner->id, 'id_kebun' => $this->kebun->id, 'actor_type' => 'teknisi', 'actor_id' => $this->teknisi->id,
            'actor_nama' => 'Teknisi Uji', 'foto' => 'absensi/contoh.jpg',
            'lokasi_lat' => self::LAT_KEBUN, 'lokasi_lng' => self::LNG_KEBUN, 'kegiatan' => 'Cek tandon',
        ]);

        $this->withSession(['user_id' => $this->teknisi->id])
            ->get('/portal/absensi')
            ->assertStatus(200)
            ->assertSee('Teknisi Uji');
    }

    public function test_riwayat_tetap_utuh_walau_kebunnya_dihapus(): void
    {
        $absensi = Absensi::create([
            'id_owners' => $this->owner->id, 'id_kebun' => $this->kebun->id, 'actor_type' => 'teknisi', 'actor_id' => $this->teknisi->id,
            'actor_nama' => 'Teknisi Uji', 'foto' => 'absensi/contoh.jpg',
            'lokasi_lat' => self::LAT_KEBUN, 'lokasi_lng' => self::LNG_KEBUN,
        ]);

        $this->kebun->delete();

        $absensi->refresh();
        $this->assertNull($absensi->id_kebun, 'FK harus null setelah kebun dihapus (nullOnDelete)');
        $this->assertNotEmpty($absensi->foto, 'Catatan kunjungan itu sendiri harus tetap ada');
    }

    public function test_absensi_lain_owner_tidak_bocor_ke_owner_lain(): void
    {
        $ownerLain = Owner::create([
            'nama' => 'Owner Lain', 'nama_usaha' => 'Kebun Lain', 'username' => 'ownerlain',
            'password' => Hash::make('rahasia123'), 'alamat' => 'Jalan Lain', 'mode_langganan' => 'pro',
        ]);

        Absensi::create([
            'id_owners' => $ownerLain->id, 'actor_type' => 'teknisi', 'actor_id' => 999,
            'actor_nama' => 'Teknisi Rahasia Owner Lain', 'foto' => 'absensi/rahasia.jpg',
            'lokasi_lat' => 0, 'lokasi_lng' => 0,
        ]);

        $this->withSession(['owner_id' => $this->owner->id])
            ->get('/owner/dashboard/absensi')
            ->assertStatus(200)
            ->assertDontSee('Teknisi Rahasia Owner Lain');
    }

    public function test_lokasi_maps_url_accessor(): void
    {
        $log = Absensi::create([
            'id_owners' => $this->owner->id, 'actor_type' => 'teknisi', 'actor_id' => $this->teknisi->id,
            'actor_nama' => 'Teknisi Uji', 'foto' => 'absensi/contoh.jpg',
            'lokasi_lat' => -6.914744, 'lokasi_lng' => 107.609810,
        ]);

        $this->assertStringContainsString('-6.914744', $log->lokasi_maps_url);
        $this->assertStringContainsString('107.609810', $log->lokasi_maps_url);
        $this->assertStringStartsWith('https://www.google.com/maps', $log->lokasi_maps_url);
    }

    // ---------- Filter, pencarian, rekap per karyawan (banyak Teknisi) ----------

    private User $teknisiKedua;

    private function buatDuaTeknisiDenganKunjungan(): void
    {
        $this->teknisiKedua = User::create([
            'id_owners' => $this->owner->id, 'nama' => 'Teknisi Kedua', 'username' => 'teknisikedua',
            'password' => Hash::make('rahasia123'), 'alamat' => 'Jalan Uji', 'role' => 'teknisi',
        ]);

        $kebunLain = Kebun::create([
            'id_owners' => $this->owner->id, 'nama_kebun' => 'Kebun Lain', 'lat' => -7.0, 'lng' => 108.0,
        ]);

        // Teknisi Uji: 2 kunjungan ke Kebun Uji.
        Absensi::create([
            'id_owners' => $this->owner->id, 'id_kebun' => $this->kebun->id, 'actor_type' => 'teknisi',
            'actor_id' => $this->teknisi->id, 'actor_nama' => 'Teknisi Uji', 'foto' => 'absensi/a.jpg',
            'lokasi_lat' => self::LAT_KEBUN, 'lokasi_lng' => self::LNG_KEBUN, 'kegiatan' => 'Semprot hama',
        ]);
        Absensi::create([
            'id_owners' => $this->owner->id, 'id_kebun' => $this->kebun->id, 'actor_type' => 'teknisi',
            'actor_id' => $this->teknisi->id, 'actor_nama' => 'Teknisi Uji', 'foto' => 'absensi/b.jpg',
            'lokasi_lat' => self::LAT_KEBUN, 'lokasi_lng' => self::LNG_KEBUN, 'kegiatan' => 'Cek tandon',
        ]);

        // Teknisi Kedua: 1 kunjungan ke Kebun Lain. Kegiatan sengaja BUKAN kata tunggal
        // "Panen" - itu juga muncul di sidebar navigasi (menu Kelola Tanaman > Panen),
        // jadi assertDontSee('Panen') akan salah gagal walau filter sudah benar.
        Absensi::create([
            'id_owners' => $this->owner->id, 'id_kebun' => $kebunLain->id, 'actor_type' => 'teknisi',
            'actor_id' => $this->teknisiKedua->id, 'actor_nama' => 'Teknisi Kedua', 'foto' => 'absensi/c.jpg',
            'lokasi_lat' => -7.0, 'lokasi_lng' => 108.0, 'kegiatan' => 'Panen raya cabai',
        ]);
    }

    public function test_filter_pencarian_kegiatan(): void
    {
        $this->buatDuaTeknisiDenganKunjungan();
        $this->withSession(['owner_id' => $this->owner->id]);

        // NB: assertSee/assertDontSee sengaja tidak pernah pakai kata tunggal "Panen" -
        // kata itu juga ada di menu sidebar (Kelola Tanaman > Panen) yang selalu
        // dirender di setiap halaman, jadi assertDontSee('Panen') akan selalu gagal
        // dan assertSee('Panen') akan selalu trivially benar terlepas dari filter.
        Livewire::test(KelolaAbsensi::class)
            ->set('search', 'tandon')
            ->assertSee('Cek tandon')
            ->assertDontSee('Semprot hama')
            ->assertDontSee('Panen raya cabai');
    }

    public function test_filter_per_teknisi(): void
    {
        $this->buatDuaTeknisiDenganKunjungan();
        $this->withSession(['owner_id' => $this->owner->id]);

        Livewire::test(KelolaAbsensi::class)
            ->set('filterTeknisiId', $this->teknisiKedua->id)
            ->assertSee('Panen raya cabai')
            ->assertDontSee('Semprot hama')
            ->assertDontSee('Cek tandon');
    }

    public function test_filter_per_kebun(): void
    {
        $this->buatDuaTeknisiDenganKunjungan();
        $kebunLain = Kebun::where('nama_kebun', 'Kebun Lain')->firstOrFail();
        $this->withSession(['owner_id' => $this->owner->id]);

        Livewire::test(KelolaAbsensi::class)
            ->set('filterKebunId', $kebunLain->id)
            ->assertSee('Panen raya cabai')
            ->assertDontSee('Semprot hama');
    }

    public function test_klik_kartu_rekap_toggle_filter_teknisi(): void
    {
        $this->buatDuaTeknisiDenganKunjungan();
        $this->withSession(['owner_id' => $this->owner->id]);

        $test = Livewire::test(KelolaAbsensi::class)
            ->call('filterKeTeknisi', $this->teknisi->id)
            ->assertSet('filterTeknisiId', $this->teknisi->id);

        // Klik kartu yang sama lagi -> filter dilepas (toggle), bukan tetap terkunci.
        $test->call('filterKeTeknisi', $this->teknisi->id)
            ->assertSet('filterTeknisiId', '');
    }

    public function test_rekap_per_teknisi_menghitung_benar(): void
    {
        $this->buatDuaTeknisiDenganKunjungan();
        $this->withSession(['owner_id' => $this->owner->id]);

        Livewire::test(KelolaAbsensi::class)
            ->call('setPeriode', 'semua')
            ->assertSeeInOrder(['Teknisi Uji', '2']) // Teknisi Uji (2 kunjungan) tampil sebelum Teknisi Kedua (1) - urut terbanyak
            ->assertSee('Teknisi Kedua')
            ->assertSee('1');
    }

    public function test_reset_filter_mengembalikan_ke_kondisi_awal(): void
    {
        $this->buatDuaTeknisiDenganKunjungan();
        $this->withSession(['owner_id' => $this->owner->id]);

        Livewire::test(KelolaAbsensi::class)
            ->set('search', 'tandon')
            ->set('filterTeknisiId', $this->teknisiKedua->id)
            ->set('filterKebunId', $this->kebun->id)
            ->call('resetFilter')
            ->assertSet('search', '')
            ->assertSet('filterTeknisiId', '')
            ->assertSet('filterKebunId', '')
            ->assertSee('Semprot hama')
            ->assertSee('Cek tandon')
            ->assertSee('Panen raya cabai');
    }

    public function test_filter_periode_di_luar_rentang_tidak_muncul(): void
    {
        $this->withSession(['owner_id' => $this->owner->id]);

        // 'created_at' TIDAK ada di $fillable Absensi (sengaja - lihat model), jadi
        // menyisipkannya lewat create() diam-diam diabaikan dan Eloquent tetap
        // mengisi now(). Untuk backdate di test, isi property-nya langsung lalu
        // matikan auto-timestamp untuk SAVE ini saja.
        $absensiLama = Absensi::create([
            'id_owners' => $this->owner->id, 'id_kebun' => $this->kebun->id, 'actor_type' => 'teknisi',
            'actor_id' => $this->teknisi->id, 'actor_nama' => 'Teknisi Uji', 'foto' => 'absensi/lama.jpg',
            'lokasi_lat' => self::LAT_KEBUN, 'lokasi_lng' => self::LNG_KEBUN, 'kegiatan' => 'Kunjungan tahun lalu',
        ]);
        $absensiLama->timestamps = false;
        $absensiLama->created_at = now()->subYear();
        $absensiLama->save();

        // Default mount() = periode Bulan Ini, jadi kunjungan setahun lalu tidak boleh muncul.
        Livewire::test(KelolaAbsensi::class)
            ->assertDontSee('Kunjungan tahun lalu');

        // Pindah ke "Semua" -> baru kelihatan.
        Livewire::test(KelolaAbsensi::class)
            ->call('setPeriode', 'semua')
            ->assertSee('Kunjungan tahun lalu');
    }
}
