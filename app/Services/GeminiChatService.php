<?php

namespace App\Services;

use App\Support\PanduanKebunku;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class GeminiChatService
{
    /**
     * $riwayatPesan: array berisi ['role' => 'user'|'assistant', 'content' => string].
     * Gemini pakai istilah role 'model' (bukan 'assistant') - dipetakan di sini
     * supaya pemanggil (HelpChat) tetap pakai istilah yang konsisten dengan tabel
     * chat/UI, tidak perlu tahu detail nama role punya Gemini.
     */
    public function kirim(array $riwayatPesan): string
    {
        $apiKey = config('services.gemini.key');

        if (!$apiKey) {
            throw new RuntimeException('GEMINI_API_KEY belum diatur di server.');
        }

        $model = config('services.gemini.model');
        $endpoint = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent";

        $contents = collect($riwayatPesan)->map(fn ($p) => [
            'role' => $p['role'] === 'assistant' ? 'model' : 'user',
            'parts' => [['text' => $p['content']]],
        ])->values()->toArray();

        $response = Http::withHeaders([
            'x-goog-api-key' => $apiKey,
            'content-type' => 'application/json',
        ])->timeout(30)->post($endpoint, [
            'system_instruction' => [
                'parts' => [['text' => PanduanKebunku::teks()]],
            ],
            'contents' => $contents,
            'generationConfig' => [
                // Model ini selalu "thinking" dulu sebelum jawab (thoughtsTokenCount
                // ikut memotong maxOutputTokens) - dibuat cukup longgar supaya jawaban
                // teksnya tidak terpotong MAX_TOKENS sebelum sempat keluar.
                'maxOutputTokens' => 2048,
            ],
        ]);

        if ($response->failed()) {
            throw new RuntimeException('Gagal menghubungi asisten AI (HTTP ' . $response->status() . ')');
        }

        $teks = collect($response->json('candidates.0.content.parts', []))
            ->pluck('text')
            ->implode('');

        return $teks !== '' ? $teks : 'Maaf, saya tidak mendapat jawaban. Coba tanyakan lagi ya.';
    }
}
