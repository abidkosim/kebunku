import Swal from 'sweetalert2';
import { Chart, LineController, LineElement, PointElement, LinearScale, TimeScale, CategoryScale, Filler, Tooltip } from 'chart.js';
import './echo';

Chart.register(LineController, LineElement, PointElement, LinearScale, TimeScale, CategoryScale, Filler, Tooltip);

window.Swal = Swal;
window.Chart = Chart;

/**
 * Grafik riwayat sensor Monitor Tandon. wire:ignore dipasang di containernya
 * (lihat monitor-tandon.blade.php) supaya Livewire tidak pernah menyentuh ulang
 * elemen canvas-nya - update data dilakukan manual lewat Chart.update() saat
 * event browser "tandon-grafik-data" (di-dispatch dari MonitorTandon::render())
 * diterima, bukan lewat re-render Livewire biasa.
 *
 * PENTING: instance Chart.js JANGAN PERNAH disimpan sebagai properti reaktif
 * Alpine (x-data) - Chart.js punya referensi melingkar di dalam dirinya sendiri
 * (canvas -> chart -> canvas, dst), dan proxy reactivity Alpine yang deep-wrap
 * itu bikin "Maximum call stack size exceeded" begitu chart.update() dipanggil.
 * Makanya object "charts" di sini sengaja jadi closure variable BIASA (di luar
 * object yang di-return ke x-data), bukan properti reaktif.
 */
function buatChartTandon(canvas, warna) {
    return new Chart(canvas, {
        type: 'line',
        data: {
            labels: [],
            datasets: [{
                data: [],
                borderColor: warna,
                backgroundColor: warna + '22',
                tension: 0.3,
                pointRadius: 0,
                borderWidth: 2,
                fill: true,
            }],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            animation: { duration: 300 },
            plugins: { legend: { display: false } },
            scales: {
                x: { ticks: { maxTicksLimit: 6, font: { size: 9 } }, grid: { display: false } },
                y: { ticks: { font: { size: 9 } } },
            },
        },
    });
}

function terapkanDataChart(chart, labels, data) {
    chart.data.labels = labels;
    chart.data.datasets[0].data = data;
    chart.update();
}

window.tandonChart = function () {
    const charts = {};

    return {
        tandonId: null,
        kosong: true,

        init(tandonId) {
            this.tandonId = tandonId;
            Chart.getChart(this.$refs.canvasPpm)?.destroy();
            Chart.getChart(this.$refs.canvasPh)?.destroy();
            Chart.getChart(this.$refs.canvasSuhu)?.destroy();
            charts.ppm = buatChartTandon(this.$refs.canvasPpm, '#0ea5e9');
            charts.ph = buatChartTandon(this.$refs.canvasPh, '#10b981');
            charts.suhu = buatChartTandon(this.$refs.canvasSuhu, '#f59e0b');
        },

        onData(detail) {
            if (detail.tandonId !== this.tandonId) return;
            const g = detail.grafik;
            this.kosong = !g.labels.length;
            terapkanDataChart(charts.ppm, g.labels, g.ppm);
            terapkanDataChart(charts.ph, g.labels, g.ph);
            terapkanDataChart(charts.suhu, g.labels, g.suhu);
        },
    };
};

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