<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class users extends Model
{
    protected $table = 'users';
    public $timestamps = false; // karena kamu pakai unsignedInteger, bukan timestamps()
    protected $fillable = ['id_owners','nama','username','password','alamat','created_at'];
}
