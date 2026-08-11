<?php

namespace App\Http\Controllers\Api\V1\Alat;

use App\Http\Controllers\Controller;
use App\Models\PerawatanAlat;
use App\Traits\ApiResponse;
use Carbon\Carbon;
use Illuminate\Http\Request;

class PerawatanAlatController extends Controller
{
    use ApiResponse;

    public function index($alat_id)
    {
        $list = PerawatanAlat::where('alat_id', $alat_id)->latest()->get();
        return $this->successResponse($list, 'Daftar riwayat perawatan alat');
    }

    public function store(Request $request, $alat_id)
    {
        $request->validate([
            'jenis' => 'required|in:preventif,korektif,kalibrasi,lainnya',
            'tanggal_mulai' => 'required|date',
            'tanggal_selesai' => 'nullable|date|after_or_equal:tanggal_mulai',
            'biaya' => 'nullable|numeric|min:0',
            'pelaksana' => 'nullable|string|max:200',
            'deskripsi_pekerjaan' => 'required|string',
            'hasil' => 'nullable|string',
            'status' => 'nullable|in:direncanakan,berlangsung,selesai,batal',
            'bukti_foto' => 'nullable|image|max:2048',
        ]);

        $todayStr = Carbon::now()->format('Ymd');
        $lastCount = PerawatanAlat::whereDate('created_at', Carbon::today())->count();
        $kode = 'RAW-' . $todayStr . '-' . str_pad($lastCount + 1, 4, '0', STR_PAD_LEFT);

        $fotoPath = null;
        if ($request->hasFile('bukti_foto')) {
            $fotoPath = $request->file('bukti_foto')->store('perawatan', 'public');
        }

        $perawatan = PerawatanAlat::create([
            'alat_id' => $alat_id,
            'kode_perawatan' => $kode,
            'jenis' => $request->jenis,
            'tanggal_mulai' => $request->tanggal_mulai,
            'tanggal_selesai' => $request->tanggal_selesai,
            'biaya' => $request->biaya ?? 0,
            'pelaksana' => $request->pelaksana,
            'deskripsi_pekerjaan' => $request->deskripsi_pekerjaan,
            'hasil' => $request->hasil,
            'status' => $request->status ?? 'direncanakan',
            'bukti_foto' => $fotoPath,
            'created_by' => auth()->id(),
        ]);

        return $this->successResponse($perawatan, 'Data perawatan alat berhasil ditambahkan', 201);
    }

    public function show($id)
    {
        $perawatan = PerawatanAlat::with(['alat', 'creator'])->findOrFail($id);
        return $this->successResponse($perawatan, 'Detail perawatan alat');
    }

    public function update(Request $request, $id)
    {
        $perawatan = PerawatanAlat::findOrFail($id);

        $request->validate([
            'jenis' => 'sometimes|required|in:preventif,korektif,kalibrasi,lainnya',
            'tanggal_mulai' => 'sometimes|required|date',
            'tanggal_selesai' => 'nullable|date',
            'biaya' => 'nullable|numeric|min:0',
            'pelaksana' => 'nullable|string|max:200',
            'deskripsi_pekerjaan' => 'sometimes|required|string',
            'hasil' => 'nullable|string',
            'status' => 'nullable|in:direncanakan,berlangsung,selesai,batal',
            'bukti_foto' => 'nullable|image|max:2048',
        ]);

        if ($request->hasFile('bukti_foto')) {
            $perawatan->bukti_foto = $request->file('bukti_foto')->store('perawatan', 'public');
        }

        $perawatan->update($request->except('bukti_foto'));
        return $this->successResponse($perawatan, 'Data perawatan alat berhasil diperbarui');
    }

    public function destroy($id)
    {
        $perawatan = PerawatanAlat::findOrFail($id);
        $perawatan->delete();
        return $this->successResponse(null, 'Data perawatan alat berhasil dihapus');
    }
}
