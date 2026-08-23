/**
 * Bundle inti - dimuat di SETIAP halaman, jadi sengaja dijaga tetap kecil.
 *
 * Dulu berkas ini juga mengimpor Chart.js dan Laravel Echo + Pusher secara langsung,
 * sehingga SEMUA halaman (termasuk halaman login) ikut mengunduh keduanya walau tidak
 * dipakai sama sekali. Sekarang:
 *   - Chart.js  -> resources/js/grafik.js, hanya di halaman Monitor Tandon
 *   - Echo      -> resources/js/realtime.js, hanya di halaman yang memang realtime
 *   - SweetAlert -> diunduh saat notifikasi PERTAMA muncul (dynamic import di bawah),
 *                   bukan saat halaman dibuka
 */

let swalPromise = null;

/**
 * Memuat SweetAlert2 sekali saja, saat benar-benar dibutuhkan. Hasil promise-nya
 * disimpan supaya pemanggilan berikutnya memakai modul yang sudah diunduh.
 */
export function muatSwal() {
    if (!swalPromise) {
        swalPromise = import('sweetalert2').then((modul) => {
            window.Swal = modul.default;
            return modul.default;
        });
    }

    return swalPromise;
}

window.muatSwal = muatSwal;

async function tampilkanAlert(opsi) {
    const Swal = await muatSwal();
    return Swal.fire(opsi);
}

window.tampilkanAlert = tampilkanAlert;

document.addEventListener('livewire:init', () => {
    Livewire.on('alert-success', (e) => {
        tampilkanAlert({
            icon: 'success',
            title: 'Berhasil',
            text: e.message,
            confirmButtonColor: '#0f172a',
            timer: 2000,
            background: '#fff',
        });
    });

    Livewire.on('alert-error', (e) => {
        tampilkanAlert({
            icon: 'error',
            title: 'Gagal',
            text: e.message,
            confirmButtonColor: '#0f172a',
            background: '#fff',
        });
    });
});
