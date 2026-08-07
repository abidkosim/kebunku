<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Owner extends Model
{
    protected $fillable = ['nama','nama_usaha','username','password','alamat','foto','kunci_monitor','notifikasi_dibaca_at','remember_token','mode_langganan','trial_berakhir_at','pro_berakhir_at'];

    protected $casts = [
        'trial_berakhir_at' => 'datetime',
        'pro_berakhir_at' => 'datetime',
        'notifikasi_dibaca_at' => 'datetime',
    ];

    public function users() { return $this->hasMany(User::class, 'id_owners'); }
    public function tanaman() { return $this->hasMany(Tanaman::class, 'id_owners'); }
    public function kebun() { return $this->hasMany(Kebun::class, 'id_owners'); }
    public function pembeli() { return $this->hasMany(Pembeli::class, 'id_owners'); }

    public function getFotoUrlAttribute(): ?string
    {
        return $this->foto ? asset('storage/'.$this->foto) : null;
    }

    /**
     * Trial: penuh akses selama belum lewat trial_berakhir_at.
     * Pro: penuh akses kalau pro_berakhir_at null (tanpa batas) atau belum lewat.
     */
    public function punyaAksesPenuh(): bool
    {
        if ($this->mode_langganan === 'trial') {
            return $this->trial_berakhir_at !== null && now()->lt($this->trial_berakhir_at);
        }

        return $this->pro_berakhir_at === null || now()->lt($this->pro_berakhir_at);
    }
}
