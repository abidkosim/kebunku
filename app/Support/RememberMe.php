<?php

namespace App\Support;

use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Str;

/**
 * "Ingat saya" manual - app ini tidak pakai Laravel Auth/Authenticatable (Owner/User/
 * Superadmin cuma Eloquent Model biasa, login manual via session()), jadi remember-me
 * juga dibikin manual: cookie berisi "{id}|{tokenplaintext}", cuma HASH token yang
 * disimpan di kolom remember_token tiap model (bukan token mentah).
 */
class RememberMe
{
    private const DURASI_HARI = 30;

    public static function ingat(string $cookieName, $model): void
    {
        $token = Str::random(60);
        $model->update(['remember_token' => hash('sha256', $token)]);
        // PENTING: Cookie::queue() tidak menangani named argument dengan benar (named arg
        // "bocor" jadi positional berikutnya, path jadi "1" alih-alih "/") - selalu pakai
        // argumen posisional murni di sini.
        Cookie::queue($cookieName, $model->id.'|'.$token, 60 * 24 * self::DURASI_HARI, '/', null, null, true);
    }

    public static function cariDariCookie(string $cookieName, string $modelClass)
    {
        $nilai = request()->cookie($cookieName);

        if (!$nilai || !str_contains($nilai, '|')) {
            return null;
        }

        [$id, $token] = explode('|', $nilai, 2);
        $model = $modelClass::find($id);

        if (!$model || !$model->remember_token || !hash_equals($model->remember_token, hash('sha256', $token))) {
            return null;
        }

        return $model;
    }

    public static function lupakan(string $cookieName, $model = null): void
    {
        if ($model) {
            $model->update(['remember_token' => null]);
        }
        Cookie::queue(Cookie::forget($cookieName));
    }
}
