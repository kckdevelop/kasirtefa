<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\PengaturanAplikasi;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;

class PengaturanController extends Controller
{
    use ApiResponse;

    public function index()
    {
        $settings = PengaturanAplikasi::all()->groupBy('kategori');
        return $this->successResponse($settings, 'Daftar semua pengaturan aplikasi');
    }

    public function updateBatch(Request $request)
    {
        $request->validate([
            'pengaturan' => 'required|array',
        ]);

        foreach ($request->pengaturan as $kunci => $nilai) {
            PengaturanAplikasi::where('kunci', $kunci)->update(['nilai' => is_array($nilai) ? json_encode($nilai) : (string)$nilai]);
        }

        return $this->successResponse(null, 'Pengaturan aplikasi berhasil diperbarui');
    }

    public function getByKey($kunci)
    {
        $value = PengaturanAplikasi::get($kunci);
        return $this->successResponse(['kunci' => $kunci, 'nilai' => $value], 'Detail pengaturan');
    }

    public function updateByKey(Request $request, $kunci)
    {
        $request->validate([
            'nilai' => 'required',
        ]);

        $setting = PengaturanAplikasi::set($kunci, $request->nilai);
        return $this->successResponse($setting, 'Pengaturan berhasil diperbarui');
    }
}
