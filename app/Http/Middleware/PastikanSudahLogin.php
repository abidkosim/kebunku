<?php

namespace App\Http\Middleware;

use App\Support\SesiAktor;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Penjaga di depan route panel Owner & Staff.
 *
 * Sebelumnya route-route ini sama sekali tanpa middleware: pengunjung yang belum login
 * tetap membuat Laravel membangun seluruh halaman (layout, aset, mount komponen Livewire)
 * lebih dulu, baru komponennya memutuskan redirect ke '/'. Sekarang ditolak di lapisan
 * paling awal - lebih murah, dan proteksinya tidak lagi bergantung pada tiap komponen
 * mengingat untuk memanggil loadAuthenticatedOwner().
 *
 * Pengecekan di dalam komponen SENGAJA tetap dipertahankan sebagai lapis kedua
 * (defense in depth) - middleware ini tidak menggantikannya.
 */
class PastikanSudahLogin
{
    public function handle(Request $request, Closure $next, ?string $peran = null): Response
    {
        $aktor = app(SesiAktor::class);

        if (!$aktor->terautentikasi()) {
            if ($aktor->sesiBasi()) {
                $request->session()->forget(['owner_id', 'owner_nama', 'user_id', 'user_nama', 'user_role']);
            }

            return redirect('/');
        }

        // 'owner' = khusus pemilik, 'staff' = khusus teknisi/keuangan.
        if ($peran === 'owner' && $aktor->tipe() !== 'owner') {
            return redirect('/portal/dashboard');
        }

        if ($peran === 'staff' && $aktor->tipe() === 'owner') {
            return redirect('/owner/dashboard');
        }

        return $next($request);
    }
}
