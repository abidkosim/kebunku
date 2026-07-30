<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Owner extends Model
{
    protected $fillable = ['nama','nama_usaha','username','password','alamat','created_at'];
    public function users() { return $this->hasMany(User::class, 'id_owners'); }
}
