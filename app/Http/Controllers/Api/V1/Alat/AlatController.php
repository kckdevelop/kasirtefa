<?php

namespace App\Http\Controllers\Api\V1\Alat;

use App\Http\Controllers\Controller;
use App\Models\Alat;
use App\Traits\ApiResponse;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AlatController extends Controller
{
    use ApiResponse;

    public function index(Request $request)
    {
        $query = Alat::with('kategori');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%")
                    ->orWhere('kode_alat', 'like', "%{$search}%")
                    ->orWhere('merek', 'like', "%{$search}%")
                    ->orWhere('serial_number', 'like', "%{$search}%");
            });
        }

        if ($request->filled('kategori_id')) {
            $query->where('kategori_alat_id', $request->kategori_id);
        }

        if ($request->filled('kondisi')) {
            $query->where('kondisi', $request->kondisi);
        }

        if ($request->filled('status_ketersediaan')) {
            $query->where('status_ketersediaan', $request->status_ketersediaan);
        }

        if ($request->filled('lokasi')) {
            $query->where('lokasi_penyimpanan', 'like', "%{$request->lokasi}%");
        }

        $sortBy = $request->get('sort_by', 'created_at');
        $sortDir = $request->get('sort_dir', 'desc');
        $perPage = $request->get('per_page', 15);

        $alat = $query->orderBy($sortBy, $sortDir)->paginate($perPage);

        return $this->successResponse($alat, 'Daftar alat');
    }

    public function store(Request $request)
    {
        $request->validate([
            'kategori_alat_id' => 'required|exists:kategori_alat,id',
            'nama' => 'required|string|max:200',
            'merek' => 'nullable|string|max:100',
            'tipe' => 'nullable|string|max:100',
            'serial_number' => 'nullable|string|max:100',
            'tahun_perolehan' => 'nullable|digits:4',
            'kondisi' => 'nullable|in:baik,cukup,rusak_ringan,rusak_berat',
            'status_ketersediaan' => 'nullable|in:tersedia,dipinjam,dalam_perbaikan,dikeluarkan',
            'lokasi_penyimpanan' => 'nullable|string|max:200',
            'jumlah_total' => 'required|integer|min:1',
            'satuan' => 'nullable|string|max:50',
            'harga_perolehan' => 'nullable|numeric|min:0',
            'sumber_perolehan' => 'nullable|in:dinas,bos,donasi,pembelian_sendiri,lainnya',
            'foto' => 'nullable|image|max:2048',
            'spesifikasi_teknis' => 'nullable|array',
            'cara_penggunaan' => 'nullable|string',
            'peringatan_keamanan' => 'nullable|string',
            'umur_pakai' => 'nullable|integer',
            'kalibrasi_terakhir' => 'nullable|date',
            'kalibrasi_berikutnya' => 'nullable|date',
            'catatan' => 'nullable|string',
            'status' => 'nullable|in:aktif,nonaktif',
        ]);

        $todayStr = Carbon::now()->format('Ymd');
        $lastCount = Alat::withTrashed()->whereDate('created_at', Carbon::today())->count();
        $kode = 'ALT-' . $todayStr . '-' . str_pad($lastCount + 1, 4, '0', STR_PAD_LEFT);

        $slug = Str::slug($request->nama);
        $originalSlug = $slug;
        $count = 1;
        while (Alat::where('slug', $slug)->exists()) {
            $slug = "{$originalSlug}-{$count}";
            $count++;
        }

        $fotoPath = null;
        if ($request->hasFile('foto')) {
            $fotoPath = $request->file('foto')->store('alat', 'public');
        }

        $alat = Alat::create([
            'kategori_alat_id' => $request->kategori_alat_id,
            'kode_alat' => $kode,
            'nama' => $request->nama,
            'slug' => $slug,
            'merek' => $request->merek,
            'tipe' => $request->tipe,
            'serial_number' => $request->serial_number,
            'tahun_perolehan' => $request->tahun_perolehan,
            'kondisi' => $request->kondisi ?? 'baik',
            'status_ketersediaan' => $request->status_ketersediaan ?? 'tersedia',
            'lokasi_penyimpanan' => $request->lokasi_penyimpanan,
            'jumlah_total' => $request->jumlah_total,
            'jumlah_tersedia' => $request->jumlah_total,
            'satuan' => $request->satuan ?? 'unit',
            'harga_perolehan' => $request->harga_perolehan,
            'sumber_perolehan' => $request->sumber_perolehan,
            'foto' => $fotoPath,
            'spesifikasi_teknis' => $request->spesifikasi_teknis,
            'cara_penggunaan' => $request->cara_penggunaan,
            'peringatan_keamanan' => $request->peringatan_keamanan,
            'umur_pakai' => $request->umur_pakai,
            'kalibrasi_terakhir' => $request->kalibrasi_terakhir,
            'kalibrasi_berikutnya' => $request->kalibrasi_berikutnya,
            'catatan' => $request->catatan,
            'status' => $request->status ?? 'aktif',
            'created_by' => auth()->id(),
        ]);

        return $this->successResponse($alat->load('kategori'), 'Alat berhasil ditambahkan', 201);
    }

    public function show($id)
    {
        $alat = Alat::with(['kategori', 'dokumentasi', 'riwayatKondisi.actor', 'riwayatPerawatan.creator'])->findOrFail($id);
        return $this->successResponse($alat, 'Detail lengkap alat');
    }

    public function update(Request $request, $id)
    {
        $alat = Alat::findOrFail($id);

        $request->validate([
            'kategori_alat_id' => 'sometimes|required|exists:kategori_alat,id',
            'nama' => 'sometimes|required|string|max:200',
            'merek' => 'nullable|string|max:100',
            'tipe' => 'nullable|string|max:100',
            'serial_number' => 'nullable|string|max:100',
            'tahun_perolehan' => 'nullable|digits:4',
            'kondisi' => 'nullable|in:baik,cukup,rusak_ringan,rusak_berat',
            'status_ketersediaan' => 'nullable|in:tersedia,dipinjam,dalam_perbaikan,dikeluarkan',
            'lokasi_penyimpanan' => 'nullable|string|max:200',
            'jumlah_total' => 'nullable|integer|min:1',
            'satuan' => 'nullable|string|max:50',
            'harga_perolehan' => 'nullable|numeric|min:0',
            'foto' => 'nullable|image|max:2048',
            'spesifikasi_teknis' => 'nullable|array',
            'cara_penggunaan' => 'nullable|string',
            'peringatan_keamanan' => 'nullable|string',
            'catatan' => 'nullable|string',
            'status' => 'nullable|in:aktif,nonaktif',
        ]);

        if ($request->has('nama') && $request->nama !== $alat->nama) {
            $slug = Str::slug($request->nama);
            $originalSlug = $slug;
            $count = 1;
            while (Alat::where('slug', $slug)->where('id', '!=', $id)->exists()) {
                $slug = "{$originalSlug}-{$count}";
                $count++;
            }
            $alat->slug = $slug;
        }

        if ($request->hasFile('foto')) {
            $alat->foto = $request->file('foto')->store('alat', 'public');
        }

        $alat->updated_by = auth()->id();
        $alat->update($request->except(['foto', 'slug']));

        return $this->successResponse($alat->load('kategori'), 'Alat berhasil diperbarui');
    }

    public function destroy($id)
    {
        $alat = Alat::findOrFail($id);
        $alat->delete();
        return $this->successResponse(null, 'Alat berhasil dihapus');
    }

    public function select2()
    {
        $data = Alat::where('status', 'aktif')
            ->where('status_ketersediaan', 'tersedia')
            ->select('id', 'kode_alat', 'nama', 'merek', 'jumlah_tersedia', 'satuan', 'kondisi')
            ->orderBy('nama')
            ->get();
        return $this->successResponse($data, 'Data dropdown alat');
    }

    public function findByKode($kode)
    {
        $alat = Alat::with(['kategori', 'dokumentasi'])->where('kode_alat', $kode)->firstOrFail();
        return $this->successResponse($alat, 'Data alat by kode');
    }

    public function riwayatKondisi($id)
    {
        $alat = Alat::findOrFail($id);
        $riwayat = $alat->riwayatKondisi()->with('actor')->get();
        return $this->successResponse($riwayat, 'Riwayat perubahan kondisi alat');
    }

    public function riwayatPerawatan($id)
    {
        $alat = Alat::findOrFail($id);
        $riwayat = $alat->riwayatPerawatan()->with('creator')->get();
        return $this->successResponse($riwayat, 'Riwayat perawatan alat');
    }

    public function riwayatPeminjaman($id)
    {
        $alat = Alat::findOrFail($id);
        $riwayat = $alat->detailPeminjaman()->with('peminjaman.peminjam')->latest()->paginate(15);
        return $this->successResponse($riwayat, 'Riwayat peminjaman alat');
    }
}
