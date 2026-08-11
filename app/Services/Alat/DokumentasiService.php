<?php

namespace App\Services\Alat;

use App\Models\Alat;
use App\Models\DokumentasiAlat;
use Exception;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class DokumentasiService
{
    public function uploadDokumentasi(int $alatId, array $data, int $userId)
    {
        $alat = Alat::findOrFail($alatId);

        $file = $data['file'];
        $originalName = $file->getClientOriginalName();
        $mimeType = $file->getClientMimeType();
        $fileSize = $file->getSize();

        $jenis = $data['jenis'] ?? 'foto';
        $subFolder = match ($jenis) {
            'foto' => 'dokumentasi/foto',
            'video' => 'dokumentasi/video',
            'dokumen' => 'dokumentasi/dokumen',
            'manual' => 'dokumentasi/manual',
            'sertifikat' => 'dokumentasi/sertifikat',
            default => 'dokumentasi/lainnya',
        };

        $filename = time() . '_' . Str::random(10) . '.' . $file->getClientOriginalExtension();
        $filePath = $file->storeAs($subFolder, $filename, 'public');

        $thumbnailPath = null;
        if (str_contains($mimeType, 'image')) {
            $thumbnailPath = $filePath;
        }

        $lastUrutan = DokumentasiAlat::where('alat_id', $alatId)->max('urutan') ?? 0;

        return DokumentasiAlat::create([
            'alat_id' => $alatId,
            'jenis' => $jenis,
            'judul' => $data['judul'] ?? pathinfo($originalName, PATHINFO_FILENAME),
            'deskripsi' => $data['deskripsi'] ?? null,
            'file_path' => $filePath,
            'file_nama_asli' => $originalName,
            'file_ukuran' => $fileSize,
            'file_tipe' => $mimeType,
            'thumbnail' => $thumbnailPath,
            'urutan' => $lastUrutan + 1,
            'uploaded_by' => $userId,
        ]);
    }

    public function deleteDokumentasi(int $id)
    {
        $doc = DokumentasiAlat::findOrFail($id);

        if (Storage::disk('public')->exists($doc->file_path)) {
            Storage::disk('public')->delete($doc->file_path);
        }

        if ($doc->thumbnail && $doc->thumbnail !== $doc->file_path && Storage::disk('public')->exists($doc->thumbnail)) {
            Storage::disk('public')->delete($doc->thumbnail);
        }

        $doc->delete();
        return true;
    }

    public function reorderDokumentasi(array $orderData)
    {
        foreach ($orderData as $index => $id) {
            DokumentasiAlat::where('id', $id)->update(['urutan' => $index + 1]);
        }
        return true;
    }
}
