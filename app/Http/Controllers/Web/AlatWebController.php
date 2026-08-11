<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Alat;
use App\Models\DendaPeminjaman;
use App\Models\KategoriAlat;
use App\Models\PeminjamanAlat;
use App\Models\DetailPeminjaman;
use App\Models\Pelanggan;
use App\Models\User;
use App\Services\Alat\PeminjamanService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class AlatWebController extends Controller
{
    public function __construct(
        protected PeminjamanService $peminjamanService
    ) {}

    // ─────────────── Alat Master Data ───────────────

    public function index(Request $request)
    {
        $query = Alat::with('kategori')
            ->withCount('peminjamanAktif');

        if ($request->filled('search')) {
            $q = $request->search;
            $query->where(function ($query) use ($q) {
                $query->where('nama', 'like', "%{$q}%")
                    ->orWhere('kode_alat', 'like', "%{$q}%")
                    ->orWhere('merek', 'like', "%{$q}%");
            });
        }

        if ($request->filled('kategori_id')) {
            $query->where('kategori_alat_id', $request->kategori_id);
        }
        if ($request->filled('status')) {
            $query->where('status_ketersediaan', $request->status);
        }
        if ($request->filled('kondisi')) {
            $query->where('kondisi', $request->kondisi);
        }

        return view('alat.daftar.index', [
            'alat'    => $query->paginate(15)->withQueryString(),
            'kategori' => KategoriAlat::aktif()->orderBy('nama')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nama'                => 'required|string|max:255',
            'kategori_alat_id'    => 'required|exists:kategori_alat,id',
            'merek'               => 'nullable|string|max:100',
            'tipe'                => 'nullable|string|max:100',
            'serial_number'       => 'nullable|string|max:100',
            'tahun_perolehan'     => 'nullable|integer|min:1900|max:' . date('Y'),
            'jumlah_baik'         => 'nullable|integer|min:0',
            'jumlah_cukup'        => 'nullable|integer|min:0',
            'jumlah_rusak_ringan' => 'nullable|integer|min:0',
            'jumlah_rusak_berat'  => 'nullable|integer|min:0',
            'jumlah_hilang'       => 'nullable|integer|min:0',
            'satuan'              => 'nullable|string|max:50',
            'lokasi_penyimpanan'  => 'nullable|string|max:255',
            'harga_perolehan'     => 'nullable|numeric|min:0',
            'sumber_perolehan'    => 'nullable|in:dinas,bos,donasi,pembelian_sendiri,lainnya',
            'deskripsi'           => 'nullable|string',
            'foto'                => 'nullable|image|max:2048',
        ]);

        // Hitung total dari semua kondisi
        $jBaik         = (int)($data['jumlah_baik'] ?? 0);
        $jCukup        = (int)($data['jumlah_cukup'] ?? 0);
        $jRusakRingan  = (int)($data['jumlah_rusak_ringan'] ?? 0);
        $jRusakBerat   = (int)($data['jumlah_rusak_berat'] ?? 0);
        $jHilang       = (int)($data['jumlah_hilang'] ?? 0);

        if ($jBaik === 0 && $jCukup === 0 && $jRusakRingan === 0 && $jRusakBerat === 0 && $jHilang === 0) {
            $fallbackTotal = (int)($request->input('stok_total', $request->input('jumlah_total', 1)));
            $jBaik = max(1, $fallbackTotal);
            $data['jumlah_baik'] = $jBaik;
        }

        $jumlahTotal   = $jBaik + $jCukup + $jRusakRingan + $jRusakBerat + $jHilang;

        if ($jumlahTotal < 1) {
            return back()->withErrors(['jumlah_baik' => 'Total jumlah alat harus minimal 1.'])->withInput();
        }

        // Tentukan kondisi dominan (kondisi terbanyak)
        $kondisiMap = [
            'baik'         => $jBaik,
            'cukup'        => $jCukup,
            'rusak_ringan' => $jRusakRingan,
            'rusak_berat'  => $jRusakBerat,
        ];
        $kondisiDominan = array_search(max($kondisiMap), $kondisiMap);

        if ($request->hasFile('foto')) {
            $data['foto'] = $request->file('foto')->store('alat', 'public');
        }

        $todayStr  = Carbon::now()->format('Ymd');
        $lastCount = Alat::withTrashed()->whereDate('created_at', Carbon::today())->count();

        $data['kode_alat']            = 'ALT-' . $todayStr . '-' . str_pad($lastCount + 1, 4, '0', STR_PAD_LEFT);
        $data['slug']                 = $this->generateUniqueSlug($data['nama']);
        $data['jumlah_total']         = $jumlahTotal;
        $data['jumlah_tersedia']      = $jBaik + $jCukup; // yang bisa dipinjam
        $data['jumlah_hilang']        = $jHilang;
        $data['kondisi']              = $kondisiDominan;
        $data['status']               = 'aktif';
        $data['status_ketersediaan']  = ($jBaik + $jCukup) > 0 ? 'tersedia' : 'dalam_perbaikan';
        $data['catatan']              = $data['deskripsi'] ?? null;
        $data['created_by']           = Auth::id();

        unset($data['deskripsi']);

        Alat::create($data);

        return redirect()->route('alat.daftar.index')
            ->with('success', 'Alat berhasil ditambahkan.');
    }

    public function show(Alat $alat)
    {
        $alat->load([
            'kategori',
            'riwayatKondisi.actor',
            'riwayatPerawatan.creator',
            'peminjamanAktif.peminjaman.peminjam',
        ]);

        return view('alat.daftar.show', compact('alat'));
    }

    public function update(Request $request, Alat $alat)
    {
        $data = $request->validate([
            'nama'                => 'required|string|max:255',
            'kategori_alat_id'    => 'required|exists:kategori_alat,id',
            'merek'               => 'nullable|string|max:100',
            'tipe'                => 'nullable|string|max:100',
            'serial_number'       => 'nullable|string|max:100',
            'tahun_perolehan'     => 'nullable|integer|min:1900|max:' . date('Y'),
            'jumlah_baik'         => 'nullable|integer|min:0',
            'jumlah_cukup'        => 'nullable|integer|min:0',
            'jumlah_rusak_ringan' => 'nullable|integer|min:0',
            'jumlah_rusak_berat'  => 'nullable|integer|min:0',
            'jumlah_hilang'       => 'nullable|integer|min:0',
            'status'              => 'required|in:aktif,nonaktif',
            'status_ketersediaan' => 'nullable|in:tersedia,dipinjam,dalam_perbaikan,dikeluarkan',
            'satuan'              => 'nullable|string|max:50',
            'lokasi_penyimpanan'  => 'nullable|string|max:255',
            'harga_perolehan'     => 'nullable|numeric|min:0',
            'sumber_perolehan'    => 'nullable|in:dinas,bos,donasi,pembelian_sendiri,lainnya',
            'deskripsi'           => 'nullable|string',
            'foto'                => 'nullable|image|max:2048',
        ]);

        // Hitung ulang total
        $jBaik         = (int)($data['jumlah_baik'] ?? 0);
        $jCukup        = (int)($data['jumlah_cukup'] ?? 0);
        $jRusakRingan  = (int)($data['jumlah_rusak_ringan'] ?? 0);
        $jRusakBerat   = (int)($data['jumlah_rusak_berat'] ?? 0);
        $jHilang       = (int)($data['jumlah_hilang'] ?? 0);
        $jumlahTotal   = $jBaik + $jCukup + $jRusakRingan + $jRusakBerat + $jHilang;

        // Kondisi dominan
        $kondisiMap = [
            'baik'         => $jBaik,
            'cukup'        => $jCukup,
            'rusak_ringan' => $jRusakRingan,
            'rusak_berat'  => $jRusakBerat,
        ];
        $kondisiDominan = array_search(max($kondisiMap), $kondisiMap);

        $data['jumlah_total']    = $jumlahTotal;
        $data['jumlah_tersedia'] = $jBaik + $jCukup;
        $data['jumlah_hilang']   = $jHilang;
        $data['kondisi']         = $kondisiDominan;

        if ($request->hasFile('foto')) {
            if ($alat->foto) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($alat->foto);
            }
            $data['foto'] = $request->file('foto')->store('alat', 'public');
        }

        $data['catatan']    = $data['deskripsi'] ?? $alat->catatan;
        $data['updated_by'] = Auth::id();

        unset($data['deskripsi']);

        $alat->update($data);

        return redirect()->route('alat.daftar.index')
            ->with('success', 'Data alat berhasil diperbarui.');
    }

    public function destroy(Alat $alat)
    {
        // Cek jika alat sedang dipinjam
        if ($alat->peminjamanAktif()->exists()) {
            return redirect()->route('alat.daftar.index')
                ->with('error', 'Alat tidak dapat dihapus karena sedang dipinjam.');
        }

        // Hapus foto jika ada
        if ($alat->foto) {
            \Illuminate\Support\Facades\Storage::disk('public')->delete($alat->foto);
        }

        $alat->delete();

        return redirect()->route('alat.daftar.index')
            ->with('success', 'Alat berhasil dihapus.');
    }

    // ─────────────── Kategori Alat ───────────────

    public function kategoriIndex()
    {
        return view('alat.kategori.index', [
            'kategori' => KategoriAlat::withCount('alat')->paginate(20),
        ]);
    }

    public function kategoriStore(Request $request)
    {
        $data = $request->validate([
            'nama'      => 'required|string|max:255|unique:kategori_alat,nama',
            'deskripsi' => 'nullable|string',
            'ikon'      => 'nullable|string|max:100',
            'urutan'    => 'nullable|integer|min:0',
            'status'    => 'required|in:aktif,nonaktif',
        ]);

        $data['slug'] = $this->generateUniqueSlug($data['nama']);
        KategoriAlat::create($data);

        return redirect()->route('alat.kategori.index')
            ->with('success', 'Kategori alat berhasil ditambahkan.');
    }

    public function kategoriUpdate(Request $request, KategoriAlat $kategoriAlat)
    {
        $data = $request->validate([
            'nama'      => 'required|string|max:255|unique:kategori_alat,nama,' . $kategoriAlat->id,
            'deskripsi' => 'nullable|string',
            'ikon'      => 'nullable|string|max:100',
            'urutan'    => 'nullable|integer|min:0',
            'status'    => 'required|in:aktif,nonaktif',
        ]);

        if ($data['nama'] !== $kategoriAlat->nama) {
            $data['slug'] = $this->generateUniqueSlug($data['nama'], $kategoriAlat->id);
        }

        $kategoriAlat->update($data);

        return redirect()->route('alat.kategori.index')
            ->with('success', 'Kategori alat berhasil diperbarui.');
    }

    /** Generate a unique slug for kategori_alat */
    private function generateUniqueSlug(string $nama, ?int $excludeId = null): string
    {
        $slug = Str::slug($nama);
        $original = $slug;
        $i = 1;
        while (DB::table('kategori_alat')->where('slug', $slug)->when($excludeId, fn($q) => $q->where('id', '!=', $excludeId))->exists()) {
            $slug = "{$original}-{$i}";
            $i++;
        }
        return $slug;
    }

    // ─────────────── Peminjaman ───────────────

    public function peminjamanIndex(Request $request)
    {
        $query = PeminjamanAlat::with(['pelanggan', 'userPeminjam', 'items.alat', 'approver'])
            ->latest('tanggal_pinjam');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('tanggal_mulai')) {
            $query->whereDate('tanggal_pinjam', '>=', $request->tanggal_mulai);
        }
        if ($request->filled('tanggal_selesai')) {
            $query->whereDate('tanggal_pinjam', '<=', $request->tanggal_selesai);
        }

        $pelangganList = Pelanggan::aktif()->orderBy('nama')->get();
        $alatList      = Alat::where('status', 'aktif')->where('jumlah_tersedia', '>', 0)->orderBy('nama')->get();

        return view('alat.peminjaman.index', [
            'peminjaman'    => $query->paginate(20)->withQueryString(),
            'pelangganList' => $pelangganList,
            'alatList'      => $alatList,
        ]);
    }

    public function peminjamanStore(Request $request)
    {
        $data = $request->validate([
            'pelanggan_id'            => 'required|exists:pelanggan,id',
            'tanggal_pinjam'          => 'required|date',
            'tanggal_kembali_rencana' => 'required|date|after_or_equal:tanggal_pinjam',
            'keperluan'               => 'required|string|max:500',
            'tujuan_penggunaan'       => 'nullable|string|max:255',
            'lokasi_penggunaan'       => 'nullable|string|max:255',
            'catatan_peminjam'        => 'nullable|string',
            'items'                   => 'required|array|min:1',
            'items.*.alat_id'         => 'required|exists:alat,id',
            'items.*.jumlah'          => 'required|integer|min:1',
        ]);

        try {
            $peminjaman = $this->peminjamanService->createPeminjaman($data, Auth::id());

            if ($request->has('auto_approve') && $request->auto_approve == '1') {
                $this->peminjamanService->approvePeminjaman($peminjaman->id, Auth::id(), 'Disetujui langsung oleh Admin');
            }

            return redirect()->route('alat.peminjaman.index')
                ->with('success', "Peminjaman {$peminjaman->kode_peminjaman} berhasil ditambahkan.");
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage())->withInput();
        }
    }

    public function peminjamanUpdate(Request $request, PeminjamanAlat $peminjaman)
    {
        $data = $request->validate([
            'pelanggan_id'            => 'required|exists:pelanggan,id',
            'tanggal_pinjam'          => 'required|date',
            'tanggal_kembali_rencana' => 'required|date|after_or_equal:tanggal_pinjam',
            'keperluan'               => 'required|string|max:500',
            'tujuan_penggunaan'       => 'nullable|string|max:255',
            'lokasi_penggunaan'       => 'nullable|string|max:255',
            'status'                  => 'required|in:menunggu_persetujuan,disetujui,dipinjam,dikembalikan,terlambat,ditolak',
            'catatan_peminjam'        => 'nullable|string',
        ]);

        try {
            $peminjaman->update([
                'pelanggan_id'            => $data['pelanggan_id'],
                'tanggal_pinjam'          => $data['tanggal_pinjam'],
                'tanggal_kembali_rencana' => $data['tanggal_kembali_rencana'],
                'keperluan'               => $data['keperluan'],
                'tujuan_penggunaan'       => $data['tujuan_penggunaan'],
                'lokasi_penggunaan'       => $data['lokasi_penggunaan'],
                'status'                  => $data['status'],
                'catatan_peminjam'        => $data['catatan_peminjam'],
            ]);

            return redirect()->route('alat.peminjaman.index')
                ->with('success', "Peminjaman {$peminjaman->kode_peminjaman} berhasil diperbarui.");
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage())->withInput();
        }
    }

    public function peminjamanDestroy(PeminjamanAlat $peminjaman)
    {
        try {
            DB::transaction(function () use ($peminjaman) {
                if (in_array($peminjaman->status, ['disetujui', 'dipinjam', 'terlambat'])) {
                    foreach ($peminjaman->items as $item) {
                        if ($item->alat) {
                            $item->alat->increment('jumlah_tersedia', $item->jumlah_pinjam);
                            if ($item->alat->jumlah_tersedia > 0) {
                                $item->alat->update(['status_ketersediaan' => 'tersedia']);
                            }
                        }
                    }
                }
                $peminjaman->items()->delete();
                $peminjaman->delete();
            });

            return redirect()->route('alat.peminjaman.index')
                ->with('success', "Peminjaman {$peminjaman->kode_peminjaman} berhasil dihapus.");
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function peminjamanShow(PeminjamanAlat $peminjaman)
    {
        $peminjaman->load(['peminjam', 'items.alat', 'approver', 'denda']);
        return view('alat.peminjaman.show', compact('peminjaman'));
    }

    public function peminjamanApprove(Request $request, PeminjamanAlat $peminjaman)
    {
        try {
            $this->peminjamanService->approve($peminjaman, Auth::id(), $request->catatan_admin);
            return redirect()->route('alat.peminjaman.index')
                ->with('success', "Peminjaman {$peminjaman->kode_peminjaman} berhasil disetujui.");
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function peminjamanReject(Request $request, PeminjamanAlat $peminjaman)
    {
        $request->validate(['rejection_reason' => 'required|string|min:5']);
        try {
            $this->peminjamanService->reject($peminjaman, Auth::id(), $request->rejection_reason);
            return redirect()->route('alat.peminjaman.index')
                ->with('success', "Peminjaman {$peminjaman->kode_peminjaman} telah ditolak.");
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function peminjamanProsesPengembalian(Request $request, PeminjamanAlat $peminjaman)
    {
        $request->validate([
            'catatan_pengembalian' => 'nullable|string',
            'kondisi_alat'         => 'required|in:baik,rusak_ringan,rusak_berat',
        ]);

        try {
            $this->peminjamanService->prosesPengembalian(
                $peminjaman,
                Auth::id(),
                $request->kondisi_alat,
                $request->catatan_pengembalian
            );
            return redirect()->route('alat.peminjaman.show', $peminjaman)
                ->with('success', 'Pengembalian alat berhasil diproses.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    // ─────────────── Denda ───────────────

    public function dendaIndex()
    {
        return view('alat.denda.index', [
            'denda' => DendaPeminjaman::with(['peminjaman.peminjam', 'detailPeminjaman.alat'])
                ->latest()->paginate(20),
        ]);
    }

    public function dendaBayar(DendaPeminjaman $denda)
    {
        if ($denda->status === 'sudah_dibayar') {
            return back()->with('error', 'Denda sudah dibayar sebelumnya.');
        }

        $denda->update([
            'status'       => 'sudah_dibayar',
            'tanggal_bayar' => now(),
        ]);

        return back()->with('success', 'Denda berhasil ditandai lunas.');
    }
}
