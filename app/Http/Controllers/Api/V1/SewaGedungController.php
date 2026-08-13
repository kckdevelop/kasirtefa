<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\GedungLab;
use App\Models\FasilitasGedung;
use App\Models\SewaGedung;
use App\Models\DetailSewaGedung;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SewaGedungController extends Controller
{
    // Gedung JSON List
    public function gedungIndex(Request $request)
    {
        $query = GedungLab::with('fasilitas');

        if ($request->filled('search')) {
            $q = $request->search;
            $query->where('nama_gedung', 'like', "%{$q}%")
                ->orWhere('kode_gedung', 'like', "%{$q}%");
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $gedung = $query->latest()->paginate($request->get('per_page', 15));

        return response()->json([
            'status'  => 'success',
            'message' => 'Daftar gedung/lab berhasil diambil.',
            'data'    => $gedung
        ]);
    }

    public function gedungShow($id)
    {
        $gedung = GedungLab::with('fasilitas')->findOrFail($id);

        return response()->json([
            'status'  => 'success',
            'message' => 'Detail gedung/lab.',
            'data'    => $gedung
        ]);
    }

    // Sewa Transaksi JSON List
    public function transaksiIndex(Request $request)
    {
        $query = SewaGedung::with(['gedung', 'details.fasilitas', 'user', 'pelanggan']);

        if ($request->filled('search')) {
            $q = $request->search;
            $query->where('kode_sewa', 'like', "%{$q}%")
                ->orWhere('nama_penyewa', 'like', "%{$q}%");
        }

        if ($request->filled('status_sewa')) {
            $query->where('status_sewa', $request->status_sewa);
        }

        $sewa = $query->latest()->paginate($request->get('per_page', 15));

        return response()->json([
            'status'  => 'success',
            'message' => 'Daftar transaksi penyewaan gedung.',
            'data'    => $sewa
        ]);
    }

    public function transaksiStore(Request $request)
    {
        $validated = $request->validate([
            'gedung_lab_id'      => 'required|exists:gedung_lab,id',
            'pelanggan_id'       => 'nullable|exists:pelanggan,id',
            'nama_penyewa'       => 'required|string|max:200',
            'telepon_penyewa'    => 'nullable|string|max:50',
            'instansi_penyewa'   => 'nullable|string|max:200',
            'tanggal_mulai'      => 'required|date',
            'tanggal_selesai'    => 'required|date|after_or_equal:tanggal_mulai',
            'status_pembayaran'  => 'required|in:belum_bayar,dp,lunas',
            'jumlah_dibayar'     => 'nullable|numeric|min:0',
            'catatan'            => 'nullable|string',
            'fasilitas'          => 'nullable|array',
            'fasilitas.*.id'     => 'required|exists:fasilitas_gedung,id',
            'fasilitas.*.jumlah' => 'required|integer|min:1',
        ]);

        DB::beginTransaction();
        try {
            $gedung = GedungLab::findOrFail($request->gedung_lab_id);

            $tglMulai = Carbon::parse($request->tanggal_mulai)->startOfDay();
            $tglSelesai = Carbon::parse($request->tanggal_selesai)->startOfDay();
            $lamaSewa = max(1, $tglMulai->diffInDays($tglSelesai) + 1);

            $hargaGedungPerHari = $gedung->harga_sewa_per_hari;
            $subtotalGedung = $hargaGedungPerHari * $lamaSewa;

            $subtotalFasilitas = 0;
            $detailItems = [];

            if ($request->has('fasilitas') && is_array($request->fasilitas)) {
                foreach ($request->fasilitas as $item) {
                    $fas = FasilitasGedung::find($item['id']);
                    if ($fas) {
                        $qty = (int)$item['jumlah'];
                        $hargaItem = $fas->harga_per_item;
                        $sub = $qty * $hargaItem * $lamaSewa;
                        $subtotalFasilitas += $sub;

                        $detailItems[] = [
                            'fasilitas_gedung_id' => $fas->id,
                            'nama_fasilitas'     => $fas->nama_fasilitas,
                            'jumlah'             => $qty,
                            'harga_per_item'     => $hargaItem,
                            'subtotal'           => $sub,
                        ];
                    }
                }
            }

            $totalBiaya = $subtotalGedung + $subtotalFasilitas;
            $jumlahDibayar = (float)($request->jumlah_dibayar ?? 0);

            if ($request->status_pembayaran === 'lunas') {
                $jumlahDibayar = $totalBiaya;
            }

            $kodeSewa = 'SWG-' . date('Ymd') . '-' . strtoupper(Str::random(4));

            $sewa = SewaGedung::create([
                'kode_sewa'           => $kodeSewa,
                'gedung_lab_id'       => $gedung->id,
                'user_id'             => $request->user()?->id,
                'pelanggan_id'        => $request->pelanggan_id,
                'nama_penyewa'        => $request->nama_penyewa,
                'telepon_penyewa'     => $request->telepon_penyewa,
                'instansi_penyewa'    => $request->instansi_penyewa,
                'tanggal_mulai'       => $tglMulai->toDateString(),
                'tanggal_selesai'     => $tglSelesai->toDateString(),
                'lama_sewa'           => $lamaSewa,
                'harga_sewa_gedung'   => $hargaGedungPerHari,
                'subtotal_gedung'     => $subtotalGedung,
                'subtotal_fasilitas'  => $subtotalFasilitas,
                'total_biaya'         => $totalBiaya,
                'jumlah_dibayar'      => $jumlahDibayar,
                'status_pembayaran'   => $request->status_pembayaran,
                'status_sewa'         => 'booking',
                'catatan'             => $request->catatan,
            ]);

            foreach ($detailItems as $det) {
                $det['sewa_gedung_id'] = $sewa->id;
                DetailSewaGedung::create($det);
            }

            DB::commit();

            return response()->json([
                'status'  => 'success',
                'message' => 'Transaksi penyewaan gedung berhasil dibuat.',
                'data'    => $sewa->load(['gedung', 'details.fasilitas'])
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status'  => 'error',
                'message' => 'Gagal membuat penyewaan: ' . $e->getMessage()
            ], 500);
        }
    }

    public function transaksiShow($id)
    {
        $sewa = SewaGedung::with(['gedung', 'user', 'pelanggan', 'details.fasilitas'])->findOrFail($id);

        return response()->json([
            'status'  => 'success',
            'message' => 'Detail transaksi sewa.',
            'data'    => $sewa
        ]);
    }
}
