<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\GedungLab;
use App\Models\FasilitasGedung;
use App\Models\SewaGedung;
use App\Models\DetailSewaGedung;
use App\Models\Pelanggan;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SewaGedungWebController extends Controller
{
    // ─────────────── Gedung / Lab Master ───────────────

    public function gedungIndex(Request $request)
    {
        $query = GedungLab::with(['fasilitas' => function ($q) {
            $q->orderBy('nama_fasilitas');
        }])->withCount('sewa');

        if ($request->filled('search')) {
            $q = $request->search;
            $query->where(function ($sub) use ($q) {
                $sub->where('nama_gedung', 'like', "%{$q}%")
                    ->orWhere('kode_gedung', 'like', "%{$q}%")
                    ->orWhere('lokasi', 'like', "%{$q}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $gedungList = $query->latest()->paginate(10)->withQueryString();

        return view('sewa.gedung.index', compact('gedungList'));
    }

    public function gedungStore(Request $request)
    {
        $data = $request->validate([
            'kode_gedung'          => 'required|string|max:50|unique:gedung_lab,kode_gedung',
            'nama_gedung'          => 'required|string|max:200',
            'lokasi'               => 'nullable|string|max:255',
            'kapasitas'            => 'required|integer|min:0',
            'harga_sewa_per_hari'  => 'required|numeric|min:0',
            'deskripsi'            => 'nullable|string',
            'status'               => 'required|in:tersedia,diperbaiki,nonaktif',
            'foto'                 => 'nullable|image|max:2048',
        ]);

        if ($request->hasFile('foto')) {
            $path = $request->file('foto')->store('gedung', 'public');
            $data['foto'] = 'storage/' . $path;
        }

        GedungLab::create($data);

        return redirect()->back()->with('success', 'Gedung/Lab berhasil ditambahkan.');
    }

    public function gedungUpdate(Request $request, $id)
    {
        $gedung = GedungLab::findOrFail($id);

        $data = $request->validate([
            'kode_gedung'          => 'required|string|max:50|unique:gedung_lab,kode_gedung,' . $id,
            'nama_gedung'          => 'required|string|max:200',
            'lokasi'               => 'nullable|string|max:255',
            'kapasitas'            => 'required|integer|min:0',
            'harga_sewa_per_hari'  => 'required|numeric|min:0',
            'deskripsi'            => 'nullable|string',
            'status'               => 'required|in:tersedia,diperbaiki,nonaktif',
            'foto'                 => 'nullable|image|max:2048',
        ]);

        if ($request->hasFile('foto')) {
            $path = $request->file('foto')->store('gedung', 'public');
            $data['foto'] = 'storage/' . $path;
        }

        $gedung->update($data);

        return redirect()->back()->with('success', 'Data Gedung/Lab berhasil diperbarui.');
    }

    public function gedungDestroy($id)
    {
        $gedung = GedungLab::findOrFail($id);
        $gedung->delete();

        return redirect()->back()->with('success', 'Gedung/Lab berhasil dihapus.');
    }

    // ─────────────── Fasilitas / Alat Gedung ───────────────

    public function fasilitasStore(Request $request)
    {
        $data = $request->validate([
            'gedung_lab_id'   => 'required|exists:gedung_lab,id',
            'nama_fasilitas'  => 'required|string|max:200',
            'kode_fasilitas'  => 'nullable|string|max:50',
            'jumlah_tersedia' => 'required|integer|min:1',
            'harga_per_item'  => 'required|numeric|min:0',
            'satuan'          => 'required|string|max:50',
            'keterangan'      => 'nullable|string',
            'status'          => 'required|in:baik,perbaikan,rusak',
        ]);

        FasilitasGedung::create($data);

        return redirect()->back()->with('success', 'Fasilitas/Alat gedung berhasil ditambahkan.');
    }

    public function fasilitasUpdate(Request $request, $id)
    {
        $fasilitas = FasilitasGedung::findOrFail($id);

        $data = $request->validate([
            'nama_fasilitas'  => 'required|string|max:200',
            'kode_fasilitas'  => 'nullable|string|max:50',
            'jumlah_tersedia' => 'required|integer|min:1',
            'harga_per_item'  => 'required|numeric|min:0',
            'satuan'          => 'required|string|max:50',
            'keterangan'      => 'nullable|string',
            'status'          => 'required|in:baik,perbaikan,rusak',
        ]);

        $fasilitas->update($data);

        return redirect()->back()->with('success', 'Fasilitas/Alat gedung berhasil diperbarui.');
    }

    public function fasilitasDestroy($id)
    {
        $fasilitas = FasilitasGedung::findOrFail($id);
        $fasilitas->delete();

        return redirect()->back()->with('success', 'Fasilitas/Alat gedung berhasil dihapus.');
    }

    public function getFasilitasJson($gedungId)
    {
        $fasilitas = FasilitasGedung::where('gedung_lab_id', $gedungId)
            ->where('status', 'baik')
            ->orderBy('nama_fasilitas')
            ->get();

        $gedung = GedungLab::find($gedungId);

        return response()->json([
            'status' => 'success',
            'gedung' => $gedung,
            'fasilitas' => $fasilitas
        ]);
    }

    // ─────────────── Transaksi Penyewaan Gedung ───────────────

    public function transaksiIndex(Request $request)
    {
        $query = SewaGedung::with(['gedung', 'user', 'pelanggan', 'details']);

        if ($request->filled('search')) {
            $q = $request->search;
            $query->where(function ($sub) use ($q) {
                $sub->where('kode_sewa', 'like', "%{$q}%")
                    ->orWhere('nama_penyewa', 'like', "%{$q}%")
                    ->orWhere('instansi_penyewa', 'like', "%{$q}%")
                    ->orWhereHas('gedung', function ($g) use ($q) {
                        $g->where('nama_gedung', 'like', "%{$q}%");
                    });
            });
        }

        if ($request->filled('status_sewa')) {
            $query->where('status_sewa', $request->status_sewa);
        }

        if ($request->filled('status_pembayaran')) {
            $query->where('status_pembayaran', $request->status_pembayaran);
        }

        if ($request->filled('tanggal_mulai') && $request->filled('tanggal_selesai')) {
            $query->whereBetween('tanggal_mulai', [$request->tanggal_mulai, $request->tanggal_selesai]);
        }

        $transaksiList = $query->latest()->paginate(15)->withQueryString();
        $gedungList = GedungLab::where('status', 'tersedia')->get();

        return view('sewa.transaksi.index', compact('transaksiList', 'gedungList'));
    }

    public function transaksiCreate()
    {
        $gedungList = GedungLab::with(['fasilitas' => function ($q) {
            $q->where('status', 'baik');
        }])->where('status', 'tersedia')->get();

        $pelangganList = Pelanggan::orderBy('nama')->get();

        return view('sewa.transaksi.create', compact('gedungList', 'pelangganList'));
    }

    public function transaksiStore(Request $request)
    {
        $request->validate([
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
                        // Subtotal per fasilitas = jumlah item * harga per item * lama hari sewa
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
                'user_id'             => Auth::id(),
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

            return redirect()->route('sewa.transaksi.show', $sewa->id)->with('success', 'Transaksi penyewaan gedung berhasil dibuat.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withInput()->with('error', 'Gagal membuat penyewaan: ' . $e->getMessage());
        }
    }

    public function transaksiShow($id)
    {
        $sewa = SewaGedung::with(['gedung', 'user', 'pelanggan', 'details.fasilitas'])->findOrFail($id);

        return view('sewa.transaksi.show', compact('sewa'));
    }

    public function transaksiUpdateStatus(Request $request, $id)
    {
        $sewa = SewaGedung::findOrFail($id);

        $data = $request->validate([
            'status_sewa'       => 'nullable|in:booking,disetujui,berlangsung,selesai,dibatalkan',
            'status_pembayaran' => 'nullable|in:belum_bayar,dp,lunas',
            'jumlah_dibayar'    => 'nullable|numeric|min:0',
        ]);

        if (isset($data['status_sewa'])) {
            $sewa->status_sewa = $data['status_sewa'];
        }

        if (isset($data['status_pembayaran'])) {
            $sewa->status_pembayaran = $data['status_pembayaran'];
            if ($data['status_pembayaran'] === 'lunas') {
                $sewa->jumlah_dibayar = $sewa->total_biaya;
            }
        }

        if (isset($data['jumlah_dibayar'])) {
            $sewa->jumlah_dibayar = $data['jumlah_dibayar'];
            if ($sewa->jumlah_dibayar >= $sewa->total_biaya) {
                $sewa->status_pembayaran = 'lunas';
            } elseif ($sewa->jumlah_dibayar > 0) {
                $sewa->status_pembayaran = 'dp';
            }
        }

        $sewa->save();

        return redirect()->back()->with('success', 'Status transaksi berhasil diperbarui.');
    }

    public function transaksiCetak($id)
    {
        $sewa = SewaGedung::with(['gedung', 'user', 'pelanggan', 'details.fasilitas'])->findOrFail($id);

        return view('sewa.transaksi.cetak', compact('sewa'));
    }

    public function transaksiDestroy($id)
    {
        $sewa = SewaGedung::findOrFail($id);
        $sewa->delete();

        return redirect()->route('sewa.transaksi.index')->with('success', 'Transaksi sewa berhasil dihapus.');
    }

    // ─────────────── Laporan Sewa Gedung ───────────────

    public function laporanIndex(Request $request)
    {
        $startDate = $request->get('tanggal_mulai', date('Y-m-01'));
        $endDate   = $request->get('tanggal_selesai', date('Y-m-t'));

        $query = SewaGedung::with(['gedung', 'user', 'details'])
            ->whereBetween('tanggal_mulai', [$startDate, $endDate]);

        if ($request->filled('gedung_id')) {
            $query->where('gedung_lab_id', $request->gedung_id);
        }

        if ($request->filled('status_sewa')) {
            $query->where('status_sewa', $request->status_sewa);
        }

        $transaksi = $query->latest()->get();
        $gedungList = GedungLab::all();

        $totalSewa      = $transaksi->count();
        $totalPendapatan = $transaksi->where('status_pembayaran', 'lunas')->sum('total_biaya');
        $totalFasilitas  = $transaksi->sum('subtotal_fasilitas');

        return view('laporan.sewa_gedung', compact(
            'transaksi',
            'gedungList',
            'startDate',
            'endDate',
            'totalSewa',
            'totalPendapatan',
            'totalFasilitas'
        ));
    }
}
