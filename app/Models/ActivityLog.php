<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ActivityLog extends Model
{
    protected $fillable = ['actor_type', 'actor_id', 'id_owners', 'actor_nama', 'aksi', 'modul', 'keterangan'];

    public static function catat(string $actorType, int $actorId, string $actorNama, string $aksi, string $modul, string $keterangan, ?int $idOwners = null): void
    {
        static::create([
            'actor_type' => $actorType,
            'actor_id' => $actorId,
            'id_owners' => $idOwners,
            'actor_nama' => $actorNama,
            'aksi' => $aksi,
            'modul' => $modul,
            'keterangan' => $keterangan,
        ]);
    }
}
