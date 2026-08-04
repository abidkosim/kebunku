<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Owner extends Model
{
    protected $fillable = ['nama','nama_usaha','username','password','alamat','foto','kunci_monitor'];
    public function users() { return $this->hasMany(User::class, 'id_owners'); }
    public function tanaman() { return $this->hasMany(Tanaman::class, 'id_owners'); }
    public function kebun() { return $this->hasMany(Kebun::class, 'id_owners'); }
    public function pembeli() { return $this->hasMany(Pembeli::class, 'id_owners'); }

    public function getFotoUrlAttribute(): ?string
    {
        return $this->foto ? asset('storage/'.$this->foto) : null;
    }
}
