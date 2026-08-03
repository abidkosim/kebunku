<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Meja extends Model
{
    protected $table = 'meja';
    protected $fillable = ['kebun_id', 'nomor'];

    public function kebun()
    {
        return $this->belongsTo(Kebun::class);
    }

    public function tanaman()
    {
        return $this->hasMany(Tanaman::class);
    }
}
