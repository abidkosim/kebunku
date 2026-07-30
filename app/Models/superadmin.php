<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class superadmin extends Model
{
    protected $table = 'superadmins';
    protected $fillable = ['nama','username','password'];
    protected $hidden = ['password'];
}
