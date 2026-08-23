<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class User extends Model
{
    protected $table = 'users';
    protected $fillable = ['id_owners','nama','username','password','alamat','role','foto','remember_token'];

    // Sama seperti Owner & Superadmin: rahasia tidak pernah ikut ter-serialize.
    protected $hidden = ['password', 'remember_token'];

    public function owner()
    {
        return $this->belongsTo(Owner::class, 'id_owners');
    }

    public function getFotoUrlAttribute(): ?string
    {
        return $this->foto ? asset('storage/'.$this->foto) : null;
    }
}
