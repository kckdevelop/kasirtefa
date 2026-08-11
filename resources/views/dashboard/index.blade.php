@extends('layouts.app')

@section('title', 'Dashboard Overview')

@section('content')
<div class="space-y-6">
    {{-- Stat Cards Grid: Row 1 (TEFa) --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
        {{-- Card 1: Omzet Bulan Ini --}}
        <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Omzet Bulan Ini</p>
                <h3 class="text-2xl font-bold text-slate-900 mt-1">Rp {{ number_format($omzetBulanIni, 0, ',', '.') }}</h3>
                <span class="text-xs font-medium inline-flex items-center gap-1 mt-2
                    {{ $pertumbuhanOmzet >= 0 ? 'text-emerald-600' : 'text-red-500' }}">
                    <i class="fa-solid {{ $pertumbuhanOmzet >= 0 ? 'fa-arrow-trend-up' : 'fa-arrow-trend-down' }}"></i>
                    {{ $pertumbuhanOmzet >= 0 ? '+' : '' }}{{ $pertumbuhanOmzet }}% vs bln lalu
                </span>
            </div>
            <div class="w-12 h-12 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center text-xl">
                <i class="fa-solid fa-wallet"></i>
            </div>
        </div>

        {{-- Card 2: Transaksi Hari Ini --}}
        <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Total Produk TEFa</p>
                <h3 class="text-2xl font-bold text-slate-900 mt-1">{{ number_format($totalProduk) }} Item</h3>
                <span class="text-xs font-medium text-amber-600 inline-flex items-center gap-1 mt-2">
                    <i class="fa-solid fa-triangle-exclamation"></i> {{ $stokMenipis->count() }} Stok Menipis
                </span>
            </div>
            <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-xl">
                <i class="fa-solid fa-boxes-stacked"></i>
            </div>
        </div>

        {{-- Card 3: Alat Tersedia --}}
        <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Alat Tersedia</p>
                <h3 class="text-2xl font-bold text-slate-900 mt-1">{{ number_format($alatTersedia) }} Unit</h3>
                <span class="text-xs font-medium text-blue-600 inline-flex items-center gap-1 mt-2">
                    <i class="fa-solid fa-hand-holding"></i> {{ $sedangDipinjam }} Sedang Dipinjam
                </span>
            </div>
            <div class="w-12 h-12 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-xl">
                <i class="fa-solid fa-wrench"></i>
            </div>
        </div>

        {{-- Card 4: Peminjaman Terlambat --}}
        <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Peminjaman Terlambat</p>
                <h3 class="text-2xl font-bold text-rose-600 mt-1">{{ number_format($peminjamanTerlambat->count()) }} Kasus</h3>
                <span class="text-xs font-medium text-rose-500 inline-flex items-center gap-1 mt-2">
                    <i class="fa-solid fa-clock"></i> Perlu Tindakan
                </span>
            </div>
            <div class="w-12 h-12 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center text-xl">
                <i class="fa-solid fa-clock-rotate-left"></i>
            </div>
        </div>
    </div>

    {{-- Ringkasan Transaksi: Row 2 --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
        {{-- Stok Masuk Bulan Ini --}}
        <div class="bg-gradient-to-br from-teal-500 to-teal-600 p-5 rounded-2xl shadow-sm text-white flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold text-teal-100 uppercase tracking-wider">Stok Masuk Bln Ini</p>
                <h3 class="text-2xl font-bold mt-1">{{ number_format($stokMasukBulanIni) }}</h3>
                <span class="text-xs text-teal-200 mt-1 block">unit masuk gudang</span>
            </div>
            <div class="w-12 h-12 rounded-xl bg-white/20 flex items-center justify-center text-xl">
                <i class="fa-solid fa-arrow-down-to-bracket"></i>
            </div>
        </div>

        {{-- Stok Keluar Bulan Ini --}}
        <div class="bg-gradient-to-br from-orange-500 to-orange-600 p-5 rounded-2xl shadow-sm text-white flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold text-orange-100 uppercase tracking-wider">Stok Keluar Bln Ini</p>
                <h3 class="text-2xl font-bold mt-1">{{ number_format($stokKeluarBulanIni) }}</h3>
                <span class="text-xs text-orange-200 mt-1 block">unit keluar gudang</span>
            </div>
            <div class="w-12 h-12 rounded-xl bg-white/20 flex items-center justify-center text-xl">
                <i class="fa-solid fa-arrow-up-from-bracket"></i>
            </div>
        </div>

        {{-- Lisensi Aktif --}}
        <div class="bg-gradient-to-br from-violet-500 to-violet-600 p-5 rounded-2xl shadow-sm text-white flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold text-violet-100 uppercase tracking-wider">Lisensi Aktif</p>
                <h3 class="text-2xl font-bold mt-1">{{ number_format($lisensiAktif) }}</h3>
                <span class="text-xs text-violet-200 mt-1 block">{{ $lisensiSegera }} segera berakhir</span>
            </div>
            <div class="w-12 h-12 rounded-xl bg-white/20 flex items-center justify-center text-xl">
                <i class="fa-solid fa-key"></i>
            </div>
        </div>

        {{-- Transaksi Hari Ini --}}
        <div class="bg-gradient-to-br from-blue-500 to-blue-600 p-5 rounded-2xl shadow-sm text-white flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold text-blue-100 uppercase tracking-wider">Transaksi Hari Ini</p>
                <h3 class="text-2xl font-bold mt-1">{{ number_format($transaksiHariIni) }}</h3>
                <span class="text-xs text-blue-200 mt-1 block">transaksi lunas</span>
            </div>
            <div class="w-12 h-12 rounded-xl bg-white/20 flex items-center justify-center text-xl">
                <i class="fa-solid fa-chart-line"></i>
            </div>
        </div>
    </div>

    {{-- Alert Banners --}}
    @if($stokMenipis->count() > 0)
    <div class="bg-amber-50 border border-amber-200 rounded-2xl p-5">
        <div class="flex items-center gap-3 mb-3">
            <i class="fa-solid fa-triangle-exclamation text-amber-600 text-lg"></i>
            <h4 class="font-bold text-amber-900 text-sm">Peringatan: {{ $stokMenipis->count() }} Produk di Bawah Stok Minimum</h4>
        </div>
        <div class="flex flex-wrap gap-2">
            @foreach($stokMenipis->take(6) as $item)
            <span class="inline-flex items-center gap-2 px-3 py-1 bg-white border border-amber-200 rounded-lg text-xs font-semibold text-amber-800">
                <span>{{ $item->nama }}</span>
                <span class="px-1.5 py-0.5 rounded bg-amber-100 text-amber-900 text-[10px]">{{ $item->stok }} {{ $item->satuan }}</span>
            </span>
            @endforeach
        </div>
    </div>
    @endif

    @if($lisensiSegera > 0)
    <div class="bg-violet-50 border border-violet-200 rounded-2xl p-5 flex items-center gap-4">
        <i class="fa-solid fa-key text-violet-500 text-2xl"></i>
        <div>
            <h4 class="font-bold text-violet-900 text-sm">{{ $lisensiSegera }} Lisensi Aplikasi Akan Segera Berakhir</h4>
            <p class="text-xs text-violet-600 mt-0.5">Cek dan perbarui lisensi sebelum kadaluarsa.</p>
        </div>
        <a href="{{ route('tefa.lisensi.index') }}?status=aktif"
            class="ml-auto text-xs font-semibold text-violet-600 hover:text-violet-800 whitespace-nowrap">
            Lihat Lisensi &rarr;
        </a>
    </div>
    @endif

    {{-- Tables Grid --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {{-- Recent Sales Table --}}
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="font-bold text-slate-800 text-base">Transaksi Penjualan Terbaru</h3>
                <a href="{{ route('tefa.transaksi.index') }}" class="text-xs font-semibold text-blue-600 hover:text-blue-700">Lihat Semua &rarr;</a>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b border-slate-100 text-slate-400 text-[11px] font-semibold uppercase tracking-wider">
                            <th class="py-3 px-2">Kode</th>
                            <th class="py-3 px-2">Tanggal</th>
                            <th class="py-3 px-2 text-right">Total</th>
                            <th class="py-3 px-2 text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-xs">
                        @forelse($recentTransactions as $trx)
                        <tr class="hover:bg-slate-50">
                            <td class="py-3 px-2 font-bold text-slate-900">{{ $trx->kode_transaksi }}</td>
                            <td class="py-3 px-2 text-slate-600">{{ formatTanggal($trx->tanggal) }}</td>
                            <td class="py-3 px-2 text-right font-semibold text-slate-800">Rp {{ number_format($trx->total_akhir, 0, ',', '.') }}</td>
                            <td class="py-3 px-2 text-center">
                                <span class="px-2.5 py-1 rounded-md text-[10px] font-bold uppercase {{ getStatusColor($trx->status) }}">
                                    {{ $trx->status }}
                                </span>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="text-center py-6 text-slate-400">Belum ada transaksi.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Recent Tool Borrowing Table --}}
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="font-bold text-slate-800 text-base">Peminjaman Alat Terbaru</h3>
                <a href="{{ route('alat.peminjaman.index') }}" class="text-xs font-semibold text-blue-600 hover:text-blue-700">Lihat Semua &rarr;</a>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b border-slate-100 text-slate-400 text-[11px] font-semibold uppercase tracking-wider">
                            <th class="py-3 px-2">Kode</th>
                            <th class="py-3 px-2">Peminjam</th>
                            <th class="py-3 px-2">Tgl Pinjam</th>
                            <th class="py-3 px-2 text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-xs">
                        @forelse($recentPeminjaman as $pnm)
                        <tr class="hover:bg-slate-50">
                            <td class="py-3 px-2 font-bold text-slate-900">{{ $pnm->kode_peminjaman }}</td>
                            <td class="py-3 px-2 text-slate-700 font-medium">{{ $pnm->peminjam?->nama ?? '-' }}</td>
                            <td class="py-3 px-2 text-slate-600">{{ formatTanggal($pnm->tanggal_pinjam) }}</td>
                            <td class="py-3 px-2 text-center">
                                <span class="px-2.5 py-1 rounded-md text-[10px] font-bold uppercase {{ getStatusColor($pnm->status) }}">
                                    {{ str_replace('_', ' ', $pnm->status) }}
                                </span>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="text-center py-6 text-slate-400">Belum ada peminjaman.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Lisensi Terbaru --}}
    @if($recentLisensi->count() > 0)
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="font-bold text-slate-800 text-base flex items-center gap-2">
                <i class="fa-solid fa-key text-violet-500"></i> Lisensi Terbaru
            </h3>
            <a href="{{ route('tefa.lisensi.index') }}" class="text-xs font-semibold text-blue-600 hover:text-blue-700">Lihat Semua &rarr;</a>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-slate-100 text-slate-400 text-[11px] font-semibold uppercase tracking-wider">
                        <th class="py-3 px-2">Nomor Lisensi</th>
                        <th class="py-3 px-2">Pembeli</th>
                        <th class="py-3 px-2">Tipe</th>
                        <th class="py-3 px-2">Berakhir</th>
                        <th class="py-3 px-2 text-center">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-xs">
                    @foreach($recentLisensi as $lis)
                    <tr class="hover:bg-slate-50">
                        <td class="py-3 px-2 font-mono font-bold text-slate-800 text-[11px]">{{ $lis->nomor_lisensi }}</td>
                        <td class="py-3 px-2 text-slate-700">
                            <span class="font-medium">{{ $lis->nama_pembeli }}</span>
                            @if($lis->nama_sekolah)
                            <span class="block text-slate-400 text-[10px]">{{ $lis->nama_sekolah }}</span>
                            @endif
                        </td>
                        <td class="py-3 px-2">
                            @if($lis->tipe === 'beli')
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-700">Beli</span>
                            @else
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-purple-100 text-purple-700">Langganan {{ $lis->lama_sewa }}bln</span>
                            @endif
                        </td>
                        <td class="py-3 px-2 text-slate-600">
                            {{ $lis->tanggal_akhir ? $lis->tanggal_akhir->format('d M Y') : '-' }}
                        </td>
                        <td class="py-3 px-2 text-center">
                            @php
                                $badge = match($lis->status) {
                                    'aktif' => 'bg-emerald-100 text-emerald-700',
                                    'kadaluarsa' => 'bg-red-100 text-red-700',
                                    default => 'bg-slate-100 text-slate-500'
                                };
                            @endphp
                            <span class="px-2.5 py-1 rounded-full text-[10px] font-bold uppercase {{ $badge }}">
                                {{ $lis->status }}
                            </span>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif
</div>
@endsection
