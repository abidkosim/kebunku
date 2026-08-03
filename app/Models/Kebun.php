<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Kebun extends Model
{
    protected $table = 'kebun';
    protected $fillable = ['id_owners', 'nama_kebun'];

    public function owner()
    {
        return $this->belongsTo(Owner::class, 'id_owners');
    }

    public function meja()
    {
        return $this->hasMany(Meja::class);
    }

    public function tandons()
    {
        return $this->hasMany(Tandon::class, 'id_kebun');
    }
}
