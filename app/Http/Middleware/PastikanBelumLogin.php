<?php

namespace App\Http\Middleware;

use App\Support\SesiAktor;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Kebalikan PastikanSudahLogin: dipasang di halaman login.
 *
 * Perbaikan alur - dulu pengguna yang sudah login lalu membuka '/' (atau menekan tombol
 * Back sampai ke halaman login) tetap disuguhi form login lagi, seolah-olah sesinya
 * hilang. Sekarang langsung diantar ke dashboard yang sesuai perannya.
 */
class PastikanBelumLogin
{
    public function handle(Request $request, Closure $next): Response
    {
        $aktor = app(SesiAktor::class);

        if ($aktor->terautentikasi()) {
            return redirect($aktor->tipe() === 'owner' ? '/owner/dashboard' : '/portal/dashboard');
        }

        return $next($request);
    }
}
