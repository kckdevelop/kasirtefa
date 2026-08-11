<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\DetailPenjualan;
use App\Models\KategoriProduk;
use App\Models\LisensiAplikasi;
use App\Models\Produk;
use App\Models\StokMasuk;
use App\Models\StokKeluar;
use App\Models\TransaksiPenjualan;
use App\Services\Tefa\PenjualanService;
use App\Services\LaporanService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class TefahWebController extends Controller
{
    public function __construct(
        protected PenjualanService $penjualanService,
        protected LaporanService $laporanService
    ) {}

    // ─────────────── Kategori Produk ───────────────

    public function kategoriIndex()
    {
        return view('tefa.kategori.index', [
            'kategori' => KategoriProduk::withCount('produk')->orderBy('urutan')->orderBy('nama')->paginate(20),
        ]);
    }

    public function kategoriStore(Request $request)
    {
        $data = $request->validate([
            'nama'      => 'required|string|max:255|unique:kategori_produk,nama',
            'deskripsi' => 'nullable|string',
            'ikon'      => 'nullable|string|max:100',
            'urutan'    => 'nullable|integer|min:0',
            'status'    => 'required|in:aktif,nonaktif',
        ]);

        $data['slug'] = $this->generateUniqueSlug($data['nama'], 'kategori_produk');
        KategoriProduk::create($data);

        return redirect()->route('tefa.kategori.index')
            ->with('success', 'Kategori berhasil ditambahkan.');
    }

    public function kategoriUpdate(Request $request, KategoriProduk $kategoriProduk)
    {
        $data = $request->validate([
            'nama'      => 'required|string|max:255|unique:kategori_produk,nama,' . $kategoriProduk->id,
            'deskripsi' => 'nullable|string',
            'ikon'      => 'nullable|string|max:100',
            'urutan'    => 'nullable|integer|min:0',
            'status'    => 'required|in:aktif,nonaktif',
        ]);

        if ($data['nama'] !== $kategoriProduk->nama) {
            $data['slug'] = $this->generateUniqueSlug($data['nama'], 'kategori_produk', $kategoriProduk->id);
        }

        $kategoriProduk->update($data);

        return redirect()->route('tefa.kategori.index')
            ->with('success', 'Kategori berhasil diperbarui.');
    }

    public function kategoriDestroy(KategoriProduk $kategoriProduk)
    {
        if ($kategoriProduk->produk()->count() > 0) {
            return back()->with('error', 'Kategori tidak dapat dihapus karena masih memiliki produk.');
        }

        $kategoriProduk->delete();

        return redirect()->route('tefa.kategori.index')
            ->with('success', 'Kategori berhasil dihapus.');
    }

    /** Generate a unique slug for the given table */
    private function generateUniqueSlug(string $nama, string $table, ?int $excludeId = null): string
    {
        $slug = Str::slug($nama);
        $original = $slug;
        $i = 1;
        while (\DB::table($table)->where('slug', $slug)->when($excludeId, fn($q) => $q->where('id', '!=', $excludeId))->exists()) {
            $slug = "{$original}-{$i}";
            $i++;
        }
        return $slug;
    }

    // ─────────────── Produk ───────────────

    public function produkIndex(Request $request)
    {
        $query = Produk::with('kategori');

        if ($request->filled('search')) {
            $q = $request->search;
            $query->where(fn($q) => $q->where('nama', 'like', "%{$q}%")
                ->orWhere('kode_produk', 'like', "%{$q}%"));
        }
        if ($request->filled('kategori_id')) {
            $query->where('kategori_produk_id', $request->kategori_id);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $perPage = in_array((int)$request->input('per_page'), [10, 15, 25, 50, 100]) ? (int)$request->input('per_page') : 15;

        return view('tefa.produk.index', [
            'produk'  => $query->latest('id')->paginate($perPage)->withQueryString(),
            'kategori' => KategoriProduk::aktif()->orderBy('nama')->get(),
        ]);
    }

    public function produkStore(Request $request)
    {
        $data = $request->validate([
            'nama'               => 'required|string|max:255',
            'kategori_produk_id' => 'required|exists:kategori_produk,id',
            'satuan'             => 'required|string|max:50',
            'harga_jual'         => 'required|numeric|min:0',
            'harga_modal'        => 'nullable|numeric|min:0',
            'stok_minimum'       => 'nullable|integer|min:0',
            'status'             => 'required|in:aktif,nonaktif',
            'deskripsi'          => 'nullable|string',
            'foto'               => 'nullable|image|max:2048',
            'foto_base64'        => 'nullable|string',
        ]);

        if ($request->filled('foto_base64') && str_contains($request->foto_base64, 'base64,')) {
            $imageParts = explode(';base64,', $request->foto_base64);
            $imageDecoded = base64_decode($imageParts[1]);
            $fileName = 'produk/' . Str::random(20) . '.png';
            Storage::disk('public')->put($fileName, $imageDecoded);
            $data['foto'] = $fileName;
        } elseif ($request->hasFile('foto')) {
            $data['foto'] = $request->file('foto')->store('produk', 'public');
        }

        $todayStr = Carbon::now()->format('Ymd');
        $lastCount = Produk::withTrashed()->whereDate('created_at', Carbon::today())->count();

        $data['kode_produk'] = 'PRD-' . $todayStr . '-' . str_pad($lastCount + 1, 4, '0', STR_PAD_LEFT);
        $data['slug'] = $this->generateUniqueSlug($data['nama'], 'produk');
        $data['stok'] = 0;
        $data['created_by'] = Auth::id();
        Produk::create($data);

        return redirect()->route('tefa.produk.index')
            ->with('success', 'Produk berhasil ditambahkan.');
    }

    public function produkUpdate(Request $request, Produk $produk)
    {
        $data = $request->validate([
            'nama'               => 'sometimes|string|max:255',
            'kategori_produk_id' => 'sometimes|exists:kategori_produk,id',
            'harga_jual'         => 'sometimes|numeric|min:0',
            'harga_modal'        => 'nullable|numeric|min:0',
            'stok_minimum'       => 'nullable|integer|min:0',
            'status'             => 'sometimes|in:aktif,nonaktif',
            'foto'               => 'nullable|image|max:2048',
            'foto_base64'        => 'nullable|string',
        ]);

        if ($request->filled('foto_base64') && str_contains($request->foto_base64, 'base64,')) {
            if ($produk->foto) Storage::disk('public')->delete($produk->foto);
            $imageParts = explode(';base64,', $request->foto_base64);
            $imageDecoded = base64_decode($imageParts[1]);
            $fileName = 'produk/' . Str::random(20) . '.png';
            Storage::disk('public')->put($fileName, $imageDecoded);
            $data['foto'] = $fileName;
        } elseif ($request->hasFile('foto')) {
            if ($produk->foto) Storage::disk('public')->delete($produk->foto);
            $data['foto'] = $request->file('foto')->store('produk', 'public');
        }

        $produk->update($data);

        return redirect()->route('tefa.produk.index')
            ->with('success', 'Produk berhasil diperbarui.');
    }

    public function produkDestroy(Produk $produk)
    {
        if ($produk->foto) {
            Storage::disk('public')->delete($produk->foto);
        }
        $produk->delete();

        return redirect()->route('tefa.produk.index')
            ->with('success', 'Produk berhasil dihapus.');
    }

    public function produkBulkDestroy(Request $request)
    {
        $request->validate([
            'ids'   => 'required|array',
            'ids.*' => 'exists:produk,id',
        ]);

        $produks = Produk::whereIn('id', $request->ids)->get();
        $count = 0;
        foreach ($produks as $produk) {
            if ($produk->foto) {
                Storage::disk('public')->delete($produk->foto);
            }
            $produk->delete();
            $count++;
        }

        return redirect()->route('tefa.produk.index')
            ->with('success', $count . ' produk berhasil dihapus.');
    }

    // ─────────────── Stok Masuk ───────────────

    public function stokMasukIndex()
    {
        $stokMasuk = StokMasuk::with(['produk', 'creator'])->latest('tanggal')->paginate(20);
        $produk = Produk::aktif()->orderBy('nama')->get();

        return view('tefa.stok.masuk', compact('stokMasuk', 'produk'));
    }

    public function stokMasukStore(Request $request)
    {
        $data = $request->validate([
            'produk_id' => 'required|exists:produk,id',
            'jumlah'    => 'required|integer|min:1',
            'catatan'   => 'nullable|string',
            'keterangan'=> 'nullable|string',
            'tanggal'   => 'required|date',
            'sumber'    => 'nullable|string',
        ]);

        $todayStr = Carbon::now()->format('Ymd');
        $lastCount = StokMasuk::whereDate('created_at', Carbon::today())->count();
        $kode = 'SM-' . $todayStr . '-' . str_pad($lastCount + 1, 4, '0', STR_PAD_LEFT);

        $validSumber = ['produksi', 'pembelian', 'donasi', 'lainnya'];
        $sumberInput = strtolower($data['sumber'] ?? 'produksi');

        StokMasuk::create([
            'produk_id'      => $data['produk_id'],
            'kode_transaksi' => $kode,
            'tanggal'        => $data['tanggal'],
            'jumlah'         => $data['jumlah'],
            'sumber'         => in_array($sumberInput, $validSumber) ? $sumberInput : 'produksi',
            'keterangan'     => $data['keterangan'] ?? $data['catatan'] ?? null,
            'created_by'     => Auth::id() ?? 1,
        ]);

        // Update stok produk
        Produk::find($data['produk_id'])->increment('stok', $data['jumlah']);

        return redirect()->route('tefa.stok-masuk')
            ->with('success', 'Stok masuk berhasil dicatat.');
    }

    // ─────────────── Stok Keluar ───────────────

    public function stokKeluarIndex()
    {
        $stokKeluar = StokKeluar::with(['produk', 'creator'])->latest('tanggal')->paginate(20);
        $produk = Produk::aktif()->orderBy('nama')->get();

        return view('tefa.stok.keluar', compact('stokKeluar', 'produk'));
    }

    public function stokKeluarStore(Request $request)
    {
        $data = $request->validate([
            'produk_id' => 'required|exists:produk,id',
            'jumlah'    => 'required|integer|min:1',
            'alasan'    => 'nullable|string',
            'tujuan'    => 'nullable|string',
            'tanggal'   => 'required|date',
            'catatan'   => 'nullable|string',
            'keterangan'=> 'nullable|string',
        ]);

        $produk = Produk::findOrFail($data['produk_id']);
        if ($produk->stok < $data['jumlah']) {
            return back()->withErrors(['jumlah' => 'Jumlah melebihi stok yang tersedia (' . $produk->stok . ')']);
        }

        $todayStr = Carbon::now()->format('Ymd');
        $lastCount = StokKeluar::whereDate('created_at', Carbon::today())->count();
        $kode = 'SK-' . $todayStr . '-' . str_pad($lastCount + 1, 4, '0', STR_PAD_LEFT);

        $validTujuan = ['penjualan', 'penggunaan', 'rusak', 'kadaluarsa', 'lainnya'];
        $tujuanInput = strtolower($data['tujuan'] ?? $data['alasan'] ?? 'penggunaan');

        StokKeluar::create([
            'produk_id'      => $data['produk_id'],
            'kode_transaksi' => $kode,
            'tanggal'        => $data['tanggal'],
            'jumlah'         => $data['jumlah'],
            'tujuan'         => in_array($tujuanInput, $validTujuan) ? $tujuanInput : 'lainnya',
            'keterangan'     => $data['keterangan'] ?? $data['catatan'] ?? $data['alasan'] ?? null,
            'created_by'     => Auth::id() ?? 1,
        ]);

        $produk->decrement('stok', $data['jumlah']);

        return redirect()->route('tefa.stok-keluar')
            ->with('success', 'Stok keluar berhasil dicatat.');
    }

    // ─────────────── Transaksi ───────────────

    public function kasirIndex()
    {
        return view('tefa.kasir.index', [
            'produk' => Produk::aktif()->where('stok', '>', 0)
                ->with('kategori')->get(),
        ]);
    }

    public function kasirStore(Request $request)
    {
        $data = $request->validate([
            'items'              => 'required|array|min:1',
            'items.*.produk_id'  => 'required|exists:produks,id',
            'items.*.jumlah'     => 'required|integer|min:1',
            'metode_pembayaran'  => 'required|in:tunai,transfer,qris',
            'nominal_bayar'      => 'required|numeric|min:0',
            'diskon_persen'      => 'nullable|numeric|min:0|max:100',
            'customer_nama'      => 'nullable|string|max:255',
        ]);

        try {
            $transaksi = $this->penjualanService->prosesPenjualan($data, Auth::id());

            return response()->json([
                'success'      => true,
                'message'      => 'Transaksi berhasil',
                'transaksi_id' => $transaksi->id,
                'cetak_url'    => route('tefa.transaksi.cetak-struk', $transaksi->id),
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }

    // ─────────────── Transaksi History ───────────────

    public function transaksiIndex(Request $request)
    {
        $query = TransaksiPenjualan::with(['kasir', 'items.produk'])->latest('tanggal')->latest('id');

        if ($request->filled('tanggal_mulai')) {
            $query->whereDate('tanggal', '>=', $request->tanggal_mulai);
        }
        if ($request->filled('tanggal_selesai')) {
            $query->whereDate('tanggal', '<=', $request->tanggal_selesai);
        }
        if ($request->filled('metode_pembayaran')) {
            $query->where('metode_pembayaran', $request->metode_pembayaran);
        }

        $perPage = in_array((int)$request->input('per_page'), [10, 15, 25, 50, 100]) ? (int)$request->input('per_page') : 20;

        return view('tefa.transaksi.index', [
            'transaksi' => $query->paginate($perPage)->withQueryString(),
        ]);
    }

    public function transaksiDestroy(TransaksiPenjualan $transaksi)
    {
        $transaksi->delete();

        return redirect()->route('tefa.transaksi.index')
            ->with('success', 'Data transaksi berhasil dihapus.');
    }

    public function transaksiBulkDestroy(Request $request)
    {
        $request->validate([
            'ids'   => 'required|array',
            'ids.*' => 'exists:transaksi_penjualan,id',
        ]);

        $count = TransaksiPenjualan::whereIn('id', $request->ids)->delete();

        return redirect()->route('tefa.transaksi.index')
            ->with('success', $count . ' data transaksi berhasil dihapus.');
    }

    public function transaksiShow(TransaksiPenjualan $transaksi)
    {
        $transaksi->load(['kasir', 'items.produk']);
        return view('tefa.transaksi.show', compact('transaksi'));
    }

    public function cetakStruk(TransaksiPenjualan $transaksi)
    {
        $transaksi->load(['kasir', 'items.produk']);
        $pengaturan = \App\Models\PengaturanAplikasi::all()->pluck('nilai', 'kunci')->toArray();
        return view('tefa.transaksi.struk', compact('transaksi', 'pengaturan'));
    }

    // ─────────────── Lisensi Aplikasi ───────────────

    public function lisensiIndex(Request $request)
    {
        $query = LisensiAplikasi::with('creator')->latest();

        if ($request->filled('tipe')) {
            $query->where('tipe', $request->tipe);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(fn($q) => $q->where('nama_pembeli', 'like', "%{$s}%")
                ->orWhere('nomor_lisensi', 'like', "%{$s}%")
                ->orWhere('nama_sekolah', 'like', "%{$s}%"));
        }

        if ($request->filled('status_pembayaran')) {
            $query->where('status_pembayaran', $request->status_pembayaran);
        }

        return view('tefa.lisensi.index', [
            'lisensi' => $query->paginate(15)->withQueryString(),
        ]);
    }

    public function lisensiStore(Request $request)
    {
        $data = $request->validate([
            'tipe'                => 'required|in:beli,berlangganan',
            'nama_pembeli'        => 'required|string|max:255',
            'email'               => 'nullable|email|max:255',
            'telepon'             => 'nullable|string|max:20',
            'nama_sekolah'        => 'nullable|string|max:255',
            'harga'               => 'required|numeric|min:0',
            'keterangan'          => 'nullable|string',
            // Beli
            'tanggal_beli'        => 'required_if:tipe,beli|nullable|date',
            'tanggal_jatuh_tempo' => 'required_if:tipe,beli|nullable|date|after_or_equal:tanggal_beli',
            // Berlangganan
            'tanggal_mulai'       => 'required_if:tipe,berlangganan|nullable|date',
            'lama_sewa'           => 'required_if:tipe,berlangganan|nullable|integer|min:1',
        ]);

        // Generate nomor lisensi
        $prefix = strtoupper($data['tipe'] === 'beli' ? 'LIC' : 'SUB');
        $kode = $prefix . '-' . Carbon::now()->format('Ymd') . '-' . strtoupper(Str::random(6));
        $data['nomor_lisensi'] = $kode;

        // Hitung tanggal berakhir untuk berlangganan
        if ($data['tipe'] === 'berlangganan' && !empty($data['tanggal_mulai']) && !empty($data['lama_sewa'])) {
            $data['tanggal_berakhir'] = Carbon::parse($data['tanggal_mulai'])
                ->addMonths((int) $data['lama_sewa'])
                ->toDateString();
        }

        $data['status']     = 'aktif';
        $data['created_by'] = Auth::id();

        LisensiAplikasi::create($data);

        return redirect()->route('tefa.lisensi.index')
            ->with('success', 'Lisensi berhasil ditambahkan. Nomor: ' . $kode);
    }

    public function lisensiUpdate(Request $request, LisensiAplikasi $lisensi)
    {
        $data = $request->validate([
            'tipe'                => 'required|in:beli,berlangganan',
            'nama_pembeli'        => 'required|string|max:255',
            'email'               => 'nullable|email|max:255',
            'telepon'             => 'nullable|string|max:20',
            'nama_sekolah'        => 'nullable|string|max:255',
            'harga'               => 'required|numeric|min:0',
            'status'              => 'required|in:aktif,kadaluarsa,dibatalkan',
            'keterangan'          => 'nullable|string',
            'tanggal_beli'        => 'required_if:tipe,beli|nullable|date',
            'tanggal_jatuh_tempo' => 'required_if:tipe,beli|nullable|date',
            'tanggal_mulai'       => 'required_if:tipe,berlangganan|nullable|date',
            'lama_sewa'           => 'required_if:tipe,berlangganan|nullable|integer|min:1',
        ]);

        if ($data['tipe'] === 'berlangganan' && !empty($data['tanggal_mulai']) && !empty($data['lama_sewa'])) {
            $data['tanggal_berakhir'] = Carbon::parse($data['tanggal_mulai'])
                ->addMonths((int) $data['lama_sewa'])
                ->toDateString();
        }

        $lisensi->update($data);

        return redirect()->route('tefa.lisensi.index')
            ->with('success', 'Lisensi berhasil diperbarui.');
    }

    public function lisensiDestroy(LisensiAplikasi $lisensi)
    {
        $lisensi->delete();
        return redirect()->route('tefa.lisensi.index')
            ->with('success', 'Lisensi berhasil dihapus.');
    }

    public function lisensiCetak(LisensiAplikasi $lisensi)
    {
        $pengaturan = \App\Models\PengaturanAplikasi::all()->pluck('nilai', 'kunci')->toArray();
        return view('tefa.lisensi.cetak', compact('lisensi', 'pengaturan'));
    }

    public function lisensiTandaiLunas(Request $request, LisensiAplikasi $lisensi)
    {
        $data = $request->validate([
            'metode_pembayaran'  => 'required|string|in:tunai,transfer,qris',
            'tanggal_pembayaran' => 'required|date',
            'catatan_pembayaran' => 'nullable|string|max:500',
        ]);

        // Update status pembayaran lisensi
        $lisensi->update([
            'status_pembayaran'  => 'lunas',
            'tanggal_pembayaran' => $data['tanggal_pembayaran'],
            'metode_pembayaran'  => $data['metode_pembayaran'],
            'catatan_pembayaran' => $data['catatan_pembayaran'] ?? null,
        ]);

        // Masukkan ke rekap transaksi penjualan
        $kode = 'LIC-' . Carbon::now()->format('Ymd') . '-' . str_pad($lisensi->id, 4, '0', STR_PAD_LEFT);

        $transaksi = TransaksiPenjualan::create([
            'kode_transaksi'     => $kode,
            'tanggal'            => $data['tanggal_pembayaran'],
            'waktu'              => Carbon::now()->format('H:i:s'),
            'user_id'            => Auth::id(),
            'customer_nama'      => $lisensi->nama_pembeli,
            'customer_telepon'   => $lisensi->telepon,
            'subtotal'           => $lisensi->harga,
            'diskon_persen'      => 0,
            'diskon_nominal'     => 0,
            'total_akhir'        => $lisensi->harga,
            'metode_pembayaran'  => $data['metode_pembayaran'],
            'nominal_bayar'      => $lisensi->harga,
            'nominal_kembalian'  => 0,
            'no_referensi'       => $lisensi->nomor_lisensi,
            'status'             => 'lunas',
            'catatan'            => 'Pembayaran lisensi: ' . $lisensi->nomor_lisensi . ($lisensi->nama_sekolah ? ' — ' . $lisensi->nama_sekolah : ''),
        ]);

        // Buat detail penjualan (1 item: lisensi)
        $namaItem = 'Lisensi Aplikasi TEFa'
            . ($lisensi->tipe === 'berlangganan' ? " ({$lisensi->lama_sewa} Bulan)" : ' (Beli)');

        \App\Models\DetailPenjualan::create([
            'transaksi_penjualan_id' => $transaksi->id,
            'produk_id'              => null,
            'harga_satuan'           => $lisensi->harga,
            'jumlah'                 => 1,
            'subtotal'               => $lisensi->harga,
            'catatan'                => $namaItem,
        ]);

        return redirect()->route('tefa.lisensi.index')
            ->with('success', 'Pembayaran lisensi berhasil dicatat dan masuk ke rekap transaksi penjualan!');
    }

    // ─────────────── Reset Transaksi TEFa ───────────────

    public function resetTransaksiIndex()
    {
        $countStokMasuk  = StokMasuk::count();
        $countStokKeluar = StokKeluar::count();
        $countPenjualan  = TransaksiPenjualan::withTrashed()->count();
        $totalOmset      = TransaksiPenjualan::where('status', 'lunas')->sum('total_akhir');

        return view('tefa.reset.index', compact(
            'countStokMasuk',
            'countStokKeluar',
            'countPenjualan',
            'totalOmset'
        ));
    }

    public function resetTransaksiStore(Request $request)
    {
        $request->validate([
            'konfirmasi' => 'required|string|in:RESET TRANSAKSI',
        ], [
            'konfirmasi.required' => 'Wajib mengetik teks konfirmasi untuk melanjutkan.',
            'konfirmasi.in'       => 'Teks konfirmasi salah. Harap ketik "RESET TRANSAKSI" dengan huruf kapital.',
        ]);

        DB::transaction(function () use ($request) {
            // Hapus Detail Penjualan & Transaksi Penjualan
            DetailPenjualan::query()->delete();
            TransaksiPenjualan::withTrashed()->forceDelete();

            // Hapus Stok Masuk & Stok Keluar
            StokMasuk::query()->delete();
            StokKeluar::query()->delete();

            // Opsi reset jumlah stok produk menjadi 0
            if ($request->has('reset_stok_produk')) {
                Produk::query()->update(['stok' => 0]);
            }
        });

        return redirect()->route('tefa.reset-transaksi.index')
            ->with('success', 'Semua data transaksi penjualan, stok masuk, dan stok keluar berhasil di-reset!');
    }
}
