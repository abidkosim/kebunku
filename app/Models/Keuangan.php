<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Keuangan extends Model
{
    protected $table = 'keuangan';

    protected $fillable = [
        'id_owners', 'jenis', 'kategori', 'jumlah', 'tanggal', 'catatan', 'dicatat_oleh',
    ];

    protected $casts = [
        'tanggal' => 'date',
        'jumlah' => 'decimal:2',
    ];

    public const KATEGORI_PEMASUKAN = ['Modal/Investasi', 'Penjualan Lain-lain', 'Lainnya'];
    public const KATEGORI_PENGELUARAN = ['Pupuk', 'Bibit', 'Gaji', 'Listrik', 'Perawatan/Alat', 'Lainnya'];

    public function owner()
    {
        return $this->belongsTo(Owner::class, 'id_owners');
    }
}
