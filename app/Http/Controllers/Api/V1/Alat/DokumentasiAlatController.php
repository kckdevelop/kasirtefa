<?php

namespace App\Http\Controllers\Api\V1\Alat;

use App\Http\Controllers\Controller;
use App\Models\DokumentasiAlat;
use App\Services\Alat\DokumentasiService;
use App\Traits\ApiResponse;
use Exception;
use Illuminate\Http\Request;

class DokumentasiAlatController extends Controller
{
    use ApiResponse;

    protected $dokService;

    public function __construct(DokumentasiService $dokService)
    {
        $this->dokService = $dokService;
    }

    public function index($alat_id)
    {
        $list = DokumentasiAlat::where('alat_id', $alat_id)->orderBy('urutan')->get();
        return $this->successResponse($list, 'Daftar dokumentasi alat');
    }

    public function store(Request $request, $alat_id)
    {
        $request->validate([
            'file' => 'required|file|max:10240', // Max 10MB
            'jenis' => 'required|in:foto,video,dokumen,manual,sertifikat,lainnya',
            'judul' => 'nullable|string|max:200',
            'deskripsi' => 'nullable|string',
        ]);

        try {
            $doc = $this->dokService->uploadDokumentasi($alat_id, $request->all(), auth()->id());
            return $this->successResponse($doc, 'Dokumentasi berhasil diunggah', 201);
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), 400);
        }
    }

    public function show($id)
    {
        $doc = DokumentasiAlat::with(['alat', 'uploader'])->findOrFail($id);
        return $this->successResponse($doc, 'Detail dokumentasi alat');
    }

    public function update(Request $request, $id)
    {
        $doc = DokumentasiAlat::findOrFail($id);
        $request->validate([
            'judul' => 'sometimes|required|string|max:200',
            'deskripsi' => 'nullable|string',
            'jenis' => 'sometimes|required|in:foto,video,dokumen,manual,sertifikat,lainnya',
        ]);

        $doc->update($request->only(['judul', 'deskripsi', 'jenis']));
        return $this->successResponse($doc, 'Dokumentasi berhasil diperbarui');
    }

    public function destroy($id)
    {
        try {
            $this->dokService->deleteDokumentasi($id);
            return $this->successResponse(null, 'Dokumentasi berhasil dihapus');
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), 400);
        }
    }

    public function reorder(Request $request)
    {
        $request->validate([
            'order' => 'required|array',
            'order.*' => 'exists:dokumentasi_alat,id',
        ]);

        $this->dokService->reorderDokumentasi($request->order);
        return $this->successResponse(null, 'Urutan dokumentasi berhasil diperbarui');
    }
}
