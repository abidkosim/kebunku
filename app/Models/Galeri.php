<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Galeri extends Model
{
    protected $table = 'galeri';

    protected $fillable = [
        'id_owners', 'jenis', 'file', 'thumbnail', 'status', 'keterangan', 'actor_type', 'actor_id', 'actor_nama',
    ];

    public function owner()
    {
        return $this->belongsTo(Owner::class, 'id_owners');
    }

    public function getFileUrlAttribute(): string
    {
        return asset('storage/'.$this->file);
    }

    public function getThumbnailUrlAttribute(): ?string
    {
        return $this->thumbnail ? asset('storage/'.$this->thumbnail) : null;
    }
}
