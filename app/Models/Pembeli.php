<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pembeli extends Model
{
    protected $table = 'pembeli';
    protected $fillable = ['id_owners', 'nama', 'kontak'];

    public function owner()
    {
        return $this->belongsTo(Owner::class, 'id_owners');
    }

    public function panens()
    {
        return $this->hasMany(Panen::class);
    }

    public function getTotalHutangAttribute(): float
    {
        return $this->panens
            ->filter(fn ($p) => $p->harga_per_kg !== null)
            ->sum(fn ($p) => max(0, $p->total_harga - $p->jumlah_dibayar));
    }
}
