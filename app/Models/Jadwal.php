<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Jadwal extends Model
{
    // Tabel jadwals sekarang khusus aktivitas semprot (berulang) per tanaman.
    protected $fillable = ['tanaman_id', 'tanggal_rencana', 'tanggal_selesai', 'status', 'catatan'];

    protected $casts = [
        'tanggal_rencana' => 'date',
        'tanggal_selesai' => 'date',
    ];

    public function tanaman()
    {
        return $this->belongsTo(Tanaman::class);
    }
}
