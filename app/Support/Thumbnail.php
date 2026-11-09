<?php

namespace App\Support;

use Illuminate\Support\Facades\Storage;

/**
 * Pembuat thumbnail JPEG kecil dari foto di disk 'public'.
 *
 * Dipakai supaya daftar (Absensi, Galeri) tidak menampilkan foto asli kamera HP yang
 * ukurannya bisa 5-8 MB per foto hanya untuk kotak 48-56 px. Foto asli TIDAK diubah -
 * tetap utuh sebagai dokumentasi dan tetap dibuka saat thumbnail diklik.
 */
class Thumbnail
{
    /**
     * @return string|null path thumbnail (relatif disk public), null kalau gagal
     */
    public static function buat(string $pathAsli, string $folderTujuan, int $maks = 600, int $kualitas = 80): ?string
    {
        $disk = Storage::disk('public');
        if (!$disk->exists($pathAsli)) {
            return null;
        }

        $gambar = @imagecreatefromstring($disk->get($pathAsli));
        if ($gambar === false) {
            return null;
        }

        $gambar = self::luruskanOrientasi($gambar, $disk->path($pathAsli));

        $lebar = imagesx($gambar);
        $tinggi = imagesy($gambar);
        $rasio = min($maks / $lebar, $maks / $tinggi, 1);
        $lebarBaru = max(1, (int) round($lebar * $rasio));
        $tinggiBaru = max(1, (int) round($tinggi * $rasio));

        $thumb = imagecreatetruecolor($lebarBaru, $tinggiBaru);
        // latar putih supaya PNG/WebP transparan tidak jadi hitam di JPEG
        imagefill($thumb, 0, 0, imagecolorallocate($thumb, 255, 255, 255));
        imagecopyresampled($thumb, $gambar, 0, 0, 0, 0, $lebarBaru, $tinggiBaru, $lebar, $tinggi);
        imageinterlace($thumb, true);

        ob_start();
        imagejpeg($thumb, null, $kualitas);
        $isi = ob_get_clean();

        imagedestroy($gambar);
        imagedestroy($thumb);

        $pathThumb = rtrim($folderTujuan, '/').'/'.pathinfo($pathAsli, PATHINFO_FILENAME).'.jpg';
        $disk->put($pathThumb, $isi);

        return $pathThumb;
    }

    /**
     * Foto kamera HP sering disimpan "miring" + tag EXIF Orientation. GD mengabaikan tag
     * itu, jadi tanpa ini thumbnail bisa tampil terbalik/menyamping.
     */
    private static function luruskanOrientasi(\GdImage $gambar, string $pathPenuh): \GdImage
    {
        if (!function_exists('exif_read_data')) {
            return $gambar;
        }

        $exif = @exif_read_data($pathPenuh);
        $orientasi = $exif['Orientation'] ?? 1;

        $hasil = match ((int) $orientasi) {
            3 => imagerotate($gambar, 180, 0),
            6 => imagerotate($gambar, -90, 0),
            8 => imagerotate($gambar, 90, 0),
            default => $gambar,
        };

        if ($hasil !== $gambar && $hasil !== false) {
            imagedestroy($gambar);
            return $hasil;
        }

        return $gambar;
    }
}
