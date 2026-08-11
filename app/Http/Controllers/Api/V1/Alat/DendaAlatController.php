<?php

namespace App\Http\Controllers\Api\V1\Alat;

use App\Http\Controllers\Controller;
use App\Models\DendaPeminjaman;
use App\Traits\ApiResponse;
use Carbon\Carbon;
use Illuminate\Http\Request;

class DendaAlatController extends Controller
{
    use ApiResponse;

    public function index(Request $request)
    {
        $query = DendaPeminjaman::with(['peminjaman.peminjam', 'detailPeminjaman.alat']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $list = $query->latest()->paginate($request->get('per_page', 15));
        return $this->successResponse($list, 'Daftar denda peminjaman');
    }

    public function bayar(Request $request, $id)
    {
        $denda = DendaPeminjaman::findOrFail($id);

        $request->validate([
            'metode_pembayaran' => 'required|string|max:50',
            'bukti_bayar' => 'nullable|image|max:2048',
            'catatan' => 'nullable|string',
        ]);

        $buktiPath = null;
        if ($request->hasFile('bukti_bayar')) {
            $buktiPath = $request->file('bukti_bayar')->store('denda', 'public');
        }

        $denda->update([
            'status' => 'sudah_bayar',
            'metode_pembayaran' => $request->metode_pembayaran,
            'bukti_bayar' => $buktiPath ?? $denda->bukti_bayar,
            'tanggal_bayar' => Carbon::now()->toDateString(),
            'catatan' => $request->catatan ?? $denda->catatan,
        ]);

        return $this->successResponse($denda, 'Pembayaran denda berhasil diproses');
    }

    public function bebaskan(Request $request, $id)
    {
        $denda = DendaPeminjaman::findOrFail($id);
        $request->validate([
            'catatan' => 'required|string',
        ]);

        $denda->update([
            'status' => 'dibebaskan',
            'catatan' => 'Dibebaskan: ' . $request->catatan,
        ]);

        return $this->successResponse($denda, 'Denda berhasil dibebaskan');
    }
}
