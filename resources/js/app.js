import Swal from 'sweetalert2';

window.Swal = Swal;

document.addEventListener('livewire:init', () => {
    Livewire.on('alert-success', (e) => {
        Swal.fire({
            icon: 'success',
            title: 'Berhasil',
            text: e.message,
            confirmButtonColor: '#0f172a',
            timer: 2000,
            background: '#fff',
            customClass: { popup: 'rounded-' }
        });
    });
    Livewire.on('alert-error', (e) => {
        Swal.fire({
            icon: 'error',
            title: 'Gagal',
            text: e.message,
            confirmButtonColor: '#0f172a',
            background: '#fff',
            customClass: { popup: 'rounded-' }
        });
    });
});