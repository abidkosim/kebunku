<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Saran extends Model
{
    protected $fillable = ['id_owners', 'actor_type', 'actor_id', 'actor_nama', 'pesan', 'dibaca'];

    protected $casts = [
        'dibaca' => 'boolean',
    ];

    public function owner()
    {
        return $this->belongsTo(Owner::class, 'id_owners');
    }
}
