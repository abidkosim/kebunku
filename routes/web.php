
<?php
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

Route::get('/', fn() => view('pages.owner-login'))->name('owner.login');

Route::prefix('superadmin')->group(function(){
    Route::get('/register', function(){
        return view('pages.superadmin-register');
    });

    Route::get('/login', fn() => view('pages.superadmin-login'))->name('superadmin.login');
    Route::get('/dashboard', fn() => view('pages.superadmin-dashboard'))->name('superadmin.dashboard');
});

Route::prefix('owner')->group(function(){
    Route::get('/login', fn() => redirect('/'));
    Route::get('/dashboard', fn() => view('pages.owner-dashboard'))->name('owner.dashboard');
    Route::get('/dashboard/user', fn() => view('pages.owner-user'))->name('owner.user');
    Route::get('/dashboard/tanaman', fn() => view('pages.owner-tanaman'))->name('owner.tanaman');
    Route::get('/dashboard/tanaman/kebun', fn() => view('pages.owner-kebun'))->name('owner.tanaman.kebun');
    Route::get('/dashboard/tanaman/semprot', fn() => view('pages.owner-semprot'))->name('owner.tanaman.semprot');
    Route::get('/dashboard/tanaman/panen', fn() => view('pages.owner-panen'))->name('owner.tanaman.panen');
    Route::get('/dashboard/pembeli', fn() => view('pages.owner-pembeli'))->name('owner.pembeli');
    Route::get('/dashboard/keuangan', fn() => view('pages.owner-keuangan'))->name('owner.keuangan');
    Route::get('/dashboard/laporan', fn() => view('pages.owner-laporan'))->name('owner.laporan');
    Route::get('/dashboard/akun', fn() => view('pages.owner-akun'))->name('owner.akun');
    Route::get('/dashboard/galeri', fn() => view('pages.owner-galeri'))->name('owner.galeri');
    Route::get('/dashboard/tandon', fn() => view('pages.owner-tandon'))->name('owner.tandon');
});

// Portal Staff (Teknisi & Keuangan) - login sama dengan Owner (di root "/"), fokus tampilan mobile.
Route::prefix('portal')->group(function () {
    Route::get('/dashboard', fn() => view('pages.staff-dashboard'))->name('portal.dashboard');
    Route::get('/tanaman', fn() => view('pages.owner-tanaman'))->name('portal.tanaman');
    Route::get('/tanaman/kebun', fn() => view('pages.owner-kebun'))->name('portal.tanaman.kebun');
    Route::get('/tanaman/semprot', fn() => view('pages.owner-semprot'))->name('portal.tanaman.semprot');
    Route::get('/tanaman/panen', fn() => view('pages.owner-panen'))->name('portal.tanaman.panen');
    Route::get('/keuangan', fn() => view('pages.owner-keuangan'))->name('portal.keuangan');
    Route::get('/laporan', fn() => view('pages.owner-laporan'))->name('portal.laporan');
    Route::get('/akun', fn() => view('pages.owner-akun'))->name('portal.akun');
    Route::get('/galeri', fn() => view('pages.owner-galeri'))->name('portal.galeri');
});
