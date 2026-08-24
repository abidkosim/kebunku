<?php

namespace App\Livewire;

use App\Livewire\Owner\Concerns\RequiresOwnerAuth;
use App\Services\GeminiChatService;
use Livewire\Component;
use Throwable;

/**
 * Tombol bantuan permanen (floating button) berisi live chat AI - dipakai
 * SEMUA role (owner/teknisi/keuangan) sebagai panduan pemakaian aplikasi.
 * Di-embed sekali di shell owner/staff, sama seperti pola
 * App\Livewire\Owner\SaranMasukanModal.
 *
 * Riwayat chat disimpan di session (bukan tabel baru) per actor, supaya
 * percakapan tetap ada saat pindah halaman tapi otomatis bersih saat logout
 * (session di-forget saat logout, lihat RequiresOwnerAuth::logout()).
 */
class HelpChat extends Component
{
    use RequiresOwnerAuth;

    /** Dikirim ke Anthropic maksimal ini pesan terakhir - kontrol biaya token. */
    private const MAKS_RIWAYAT_DIKIRIM = 20;

    public array $pesanPesan = [];
    public string $pertanyaan = '';
    public bool $mengirim = false;

    public function mount()
    {
        if ($redirect = $this->loadAuthenticatedOwner()) {
            return $redirect;
        }

        $this->pesanPesan = session($this->kunciSesi(), []);
    }

    protected function kunciSesi(): string
    {
        return "help_chat_{$this->actorType}_{$this->actorId}";
    }

    public function tanyaCepat(string $teks): void
    {
        $this->pertanyaan = $teks;
        $this->kirim();
    }

    public function kirim(): void
    {
        $teks = trim($this->pertanyaan);

        if ($teks === '' || mb_strlen($teks) > 1000) {
            return;
        }

        $this->pesanPesan[] = ['role' => 'user', 'content' => $teks];
        $this->pertanyaan = '';
        $this->mengirim = true;

        try {
            $riwayatUntukApi = collect($this->pesanPesan)
                ->slice(-self::MAKS_RIWAYAT_DIKIRIM)
                ->map(fn ($p) => ['role' => $p['role'], 'content' => $p['content']])
                ->values()
                ->toArray();

            $jawaban = app(GeminiChatService::class)->kirim($riwayatUntukApi);
            $this->pesanPesan[] = ['role' => 'assistant', 'content' => $jawaban];
        } catch (Throwable $e) {
            report($e);
            $this->pesanPesan[] = [
                'role' => 'assistant',
                'content' => 'Maaf, terjadi gangguan menghubungi asisten AI. Coba lagi sebentar ya.',
            ];
        }

        $this->mengirim = false;
        session([$this->kunciSesi() => $this->pesanPesan]);
    }

    public function resetChat(): void
    {
        $this->pesanPesan = [];
        session()->forget($this->kunciSesi());
    }

    public function render()
    {
        return view('livewire.help-chat');
    }
}
