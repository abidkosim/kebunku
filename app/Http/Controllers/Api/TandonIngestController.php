<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Tandon;
use App\Services\TandonIngestService;
use Illuminate\Http\Request;

/**
 * Dipanggil oleh ESP32 main (gateway) lewat HTTPS setelah dia kumpulkan data dari
 * ESP32 tiap tandon via MQTT - lihat plaintext.txt poin 35 buat alur lengkapnya.
 * Laravel sama sekali tidak bicara MQTT, cuma terima hasil akhirnya di sini.
 */
class TandonIngestController extends Controller
{
    public function store(Request $request, Tandon $tandon, TandonIngestService $ingest)
    {
        $token = $request->header('X-Device-Token');

        if (!$token || !hash_equals((string) $tandon->device_token, $token)) {
            return response()->json(['message' => 'Token perangkat tidak valid'], 401);
        }

        if ($tandon->sumber_data !== 'iot') {
            return response()->json(['message' => 'Tandon ini masih dalam mode simulasi, aktifkan mode IoT dulu di pengaturan'], 403);
        }

        $data = $request->validate([
            'ppm' => 'required|numeric|min:0|max:2000',
            'ph' => 'required|numeric|min:0|max:14',
            'suhu' => 'required|numeric|min:0|max:100',
        ]);

        $ingest->simpanBacaan($tandon, (float) $data['ppm'], (float) $data['ph'], (float) $data['suhu'], sumber: 'iot');

        return response()->json(['message' => 'Bacaan tersimpan']);
    }
}
