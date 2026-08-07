<?php

namespace App\Livewire\Owner\Concerns;

use Illuminate\Support\Facades\Cache;

/**
 * Cache read-query per modul CRUD via Redis (Cache::tags), scoped per owner supaya
 * data antar-owner tidak pernah tercampur. Pola: render() bungkus query berat dengan
 * rememberOwnerCache(), lalu setiap method yang menulis (create/update/delete) panggil
 * forgetOwnerCache() dengan tag yang sama persis - itu satu-satunya kontrak yang harus
 * dijaga supaya data tidak pernah basi.
 *
 * TTL tetap dipasang sebagai jaring pengaman (bukan andalan utama) - kalau ada tempat
 * yang lupa di-flush, data paling lama basi selama TTL, bukan selamanya.
 */
trait CachesOwnerData
{
    protected function rememberOwnerCache(array $tags, string $key, int $ttl, \Closure $callback)
    {
        return Cache::tags($this->ownerCacheTags($tags))->remember($key, $ttl, $callback);
    }

    protected function forgetOwnerCache(array $tags): void
    {
        Cache::tags($this->ownerCacheTags($tags))->flush();
    }

    private function ownerCacheTags(array $tags): array
    {
        $ownerId = $this->owner->id;

        return array_map(fn (string $tag) => "owner{$ownerId}:{$tag}", $tags);
    }
}
