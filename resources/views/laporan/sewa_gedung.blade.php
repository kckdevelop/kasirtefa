@extends('layouts.app')

@section('title', 'Laporan Sewa Gedung / Lab')

@section('content')
<div class="space-y-6">
    {{-- Header --}}
    <div class="flex items-center justify-between flex-wrap gap-3">
        <div>
            <h2 class="text-xl font-bold text-slate-900">Laporan Sewa Gedung / Lab</h2>
            <p class="text-sm text-slate-500">Ringkasan pendapatan dan statistik penyewaan gedung</p>
        </div>
    </div>

    {{-- Filter --}}
    <form method="GET" class="bg-white rounded-2xl border border-slate-200 p-5 shadow-sm">
        <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1.5">Dari Tanggal</label>
                <input type="date" name="tanggal_mulai" value="{{ $startDate }}"
                    class="w-full px-3 py-2.5 text-sm border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1.5">Sampai Tanggal</label>
                <input type="date" name="tanggal_selesai" value="{{ $endDate }}"
                    class="w-full px-3 py-2.5 text-sm border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1.5">Gedung / Lab</label>
                <select name="gedung_id" class="w-full px-3 py-2.5 text-sm border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">Semua Gedung</option>
                    @foreach($gedungList as $g)
                    <option value="{{ $g->id }}" {{ request('gedung_id') == $g->id ? 'selected' : '' }}>{{ $g->nama_gedung }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1.5">Status Sewa</label>
                <select name="status_sewa" class="w-full px-3 py-2.5 text-sm border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">Semua Status</option>
                    @foreach(['booking'=>'Booking','disetujui'=>'Disetujui','berlangsung'=>'Berlangsung','selesai'=>'Selesai','dibatalkan'=>'Dibatalkan'] as $val => $lbl)
                    <option value="{{ $val }}" {{ request('status_sewa') == $val ? 'selected' : '' }}>{{ $lbl }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="flex gap-3 mt-4">
            <button type="submit" class="px-5 py-2.5 bg-blue-600 text-white text-sm font-semibold rounded-xl hover:bg-blue-700 transition-all">
                <i class="fa-solid fa-search mr-1"></i> Tampilkan Laporan
            </button>
            <a href="{{ route('sewa.laporan.index') }}" class="px-5 py-2.5 bg-slate-100 text-slate-600 text-sm rounded-xl hover:bg-slate-200 transition-all">Reset</a>
        </div>
    </form>

    {{-- Summary Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-sm">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 rounded-xl bg-blue-100 flex items-center justify-center text-blue-600">
                    <i class="fa-solid fa-calendar-check text-xl"></i>
                </div>
                <div>
                    <p class="text-xs font-semibold text-slate-500">Total Penyewaan</p>
                    <p class="text-2xl font-bold text-slate-800">{{ number_format($totalSewa) }}</p>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-sm">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 rounded-xl bg-emerald-100 flex items-center justify-center text-emerald-600">
                    <i class="fa-solid fa-coins text-xl"></i>
                </div>
                <div>
                    <p class="text-xs font-semibold text-slate-500">Total Pendapatan (Lunas)</p>
                    <p class="text-lg font-bold text-emerald-700">{{ formatRupiah($totalPendapatan) }}</p>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-sm">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 rounded-xl bg-violet-100 flex items-center justify-center text-violet-600">
                    <i class="fa-solid fa-layer-group text-xl"></i>
                </div>
                <div>
                    <p class="text-xs font-semibold text-slate-500">Pendapatan Fasilitas</p>
                    <p class="text-lg font-bold text-violet-700">{{ formatRupiah($totalFasilitas) }}</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Table --}}
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="flex items-center justify-between px-5 py-4 border-b border-slate-100">
            <h3 class="font-bold text-slate-800 text-sm">Daftar Transaksi Sewa</h3>
            <span class="text-xs text-slate-500">{{ $transaksi->count() }} transaksi</span>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 border-b border-slate-200">
                    <tr class="text-left text-xs text-slate-500 font-semibold">
                        <th class="px-5 py-3">Kode Sewa</th>
                        <th class="px-5 py-3">Gedung</th>
                        <th class="px-5 py-3">Penyewa</th>
                        <th class="px-5 py-3">Tanggal Sewa</th>
                        <th class="px-5 py-3">Lama</th>
                        <th class="px-5 py-3 text-right">Total Biaya</th>
                        <th class="px-5 py-3 text-right">Subtotal Fasilitas</th>
                        <th class="px-5 py-3">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($transaksi as $trx)
                    @php $statusSewaMap = ['booking' => 'bg-amber-100 text-amber-700', 'disetujui' => 'bg-blue-100 text-blue-700', 'berlangsung' => 'bg-violet-100 text-violet-700', 'selesai' => 'bg-emerald-100 text-emerald-700', 'dibatalkan' => 'bg-rose-100 text-rose-700']; @endphp
                    <tr class="hover:bg-slate-50 transition-colors">
                        <td class="px-5 py-3">
                            <a href="{{ route('sewa.transaksi.show', $trx->id) }}" class="text-xs font-mono font-bold text-blue-700 hover:underline">{{ $trx->kode_sewa }}</a>
                        </td>
                        <td class="px-5 py-3">
                            <p class="font-semibold text-slate-800">{{ $trx->gedung?->nama_gedung }}</p>
                        </td>
                        <td class="px-5 py-3">
                            <p class="text-slate-700">{{ $trx->nama_penyewa }}</p>
                            <p class="text-xs text-slate-400">{{ $trx->instansi_penyewa ?? '-' }}</p>
                        </td>
                        <td class="px-5 py-3">
                            <p class="text-slate-700">{{ formatTanggal($trx->tanggal_mulai, 'd/m/Y') }}</p>
                            <p class="text-xs text-slate-400">s/d {{ formatTanggal($trx->tanggal_selesai, 'd/m/Y') }}</p>
                        </td>
                        <td class="px-5 py-3">
                            <span class="font-semibold text-blue-600">{{ $trx->lama_sewa }} hari</span>
                        </td>
                        <td class="px-5 py-3 text-right">
                            <span class="font-bold text-slate-800">{{ formatRupiah($trx->total_biaya) }}</span>
                        </td>
                        <td class="px-5 py-3 text-right">
                            <span class="font-semibold text-violet-700">{{ formatRupiah($trx->subtotal_fasilitas) }}</span>
                        </td>
                        <td class="px-5 py-3">
                            <span class="text-xs px-2 py-1 rounded-full font-semibold {{ $statusSewaMap[$trx->status_sewa] ?? 'bg-slate-100 text-slate-700' }}">
                                {{ ucfirst($trx->status_sewa) }}
                            </span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="px-5 py-16 text-center text-slate-400">
                            <i class="fa-solid fa-chart-column text-4xl mb-3 block"></i>
                            Tidak ada data transaksi pada periode ini.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
                @if($transaksi->count() > 0)
                <tfoot class="bg-slate-50 border-t border-slate-200">
                    <tr>
                        <td colspan="5" class="px-5 py-3 text-xs font-bold text-slate-500 uppercase tracking-wide">Total</td>
                        <td class="px-5 py-3 text-right font-bold text-slate-800">{{ formatRupiah($transaksi->sum('total_biaya')) }}</td>
                        <td class="px-5 py-3 text-right font-bold text-violet-700">{{ formatRupiah($transaksi->sum('subtotal_fasilitas')) }}</td>
                        <td></td>
                    </tr>
                </tfoot>
                @endif
            </table>
        </div>
    </div>
</div>
@endsection
