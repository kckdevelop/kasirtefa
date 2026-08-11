@extends('layouts.app')

@section('title', 'Laporan Peminjaman Alat')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-xl font-bold text-slate-900">Laporan Peminjaman</h2>
            <p class="text-sm text-slate-500">Ringkasan peminjaman dan denda alat</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('laporan.peminjaman') }}?{{ http_build_query(array_merge(request()->all(), ['export' => 'excel'])) }}"
                class="inline-flex items-center gap-2 px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-semibold rounded-xl text-sm">
                <i class="fa-solid fa-file-excel"></i> Export Excel
            </a>
            <a href="{{ route('laporan.peminjaman') }}?{{ http_build_query(array_merge(request()->all(), ['export' => 'pdf'])) }}"
                class="inline-flex items-center gap-2 px-4 py-2.5 bg-rose-600 hover:bg-rose-700 text-white font-semibold rounded-xl text-sm">
                <i class="fa-solid fa-file-pdf"></i> Export PDF
            </a>
        </div>
    </div>

    <!-- Filter -->
    <form method="GET" class="bg-white p-4 rounded-2xl border border-slate-200 shadow-sm flex flex-wrap gap-3">
        <input type="date" name="tanggal_mulai" value="{{ $filter['tanggal_mulai'] }}"
            class="py-2 px-3 text-sm rounded-lg border border-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-500">
        <input type="date" name="tanggal_selesai" value="{{ $filter['tanggal_selesai'] }}"
            class="py-2 px-3 text-sm rounded-lg border border-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-500">
        <select name="status" class="py-2 px-3 text-sm rounded-lg border border-slate-200 bg-white focus:outline-none focus:ring-2 focus:ring-blue-500">
            <option value="">Semua Status</option>
            <option value="dipinjam" @selected(request('status')=='dipinjam')>Dipinjam</option>
            <option value="dikembalikan" @selected(request('status')=='dikembalikan')>Dikembalikan</option>
            <option value="terlambat" @selected(request('status')=='terlambat')>Terlambat</option>
        </select>
        <button type="submit" class="px-4 py-2 bg-blue-600 text-white font-semibold rounded-lg text-sm hover:bg-blue-700">Tampilkan</button>
    </form>

    <!-- Summary Cards -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
            <p class="text-xs font-semibold text-slate-500 mb-1">Total Peminjaman</p>
            <p class="text-3xl font-bold text-slate-900">{{ $laporan['total_peminjaman'] ?? 0 }}</p>
        </div>
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
            <p class="text-xs font-semibold text-rose-600 mb-1">Kasus Terlambat</p>
            <p class="text-3xl font-bold text-rose-600">{{ $laporan['total_terlambat'] ?? 0 }}</p>
        </div>
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
            <p class="text-xs font-semibold text-amber-600 mb-1">Total Denda</p>
            <p class="text-2xl font-bold text-amber-600">Rp {{ number_format($laporan['total_denda'] ?? 0, 0, ',', '.') }}</p>
        </div>
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
            <p class="text-xs font-semibold text-emerald-600 mb-1">Rata-rata Durasi</p>
            <p class="text-3xl font-bold text-emerald-700">{{ $laporan['rata_durasi'] ?? 0 }} <span class="text-sm font-normal">hari</span></p>
        </div>
    </div>

    <!-- Detail Table -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead class="bg-slate-50 border-b border-slate-200">
                    <tr class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider">
                        <th class="py-3.5 px-4">Kode</th>
                        <th class="py-3.5 px-4">Peminjam</th>
                        <th class="py-3.5 px-4">Tgl Pinjam</th>
                        <th class="py-3.5 px-4">Tgl Kembali</th>
                        <th class="py-3.5 px-4">Durasi</th>
                        <th class="py-3.5 px-4 text-right">Denda</th>
                        <th class="py-3.5 px-4 text-center">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-sm">
                    @forelse($peminjaman as $p)
                    <tr class="hover:bg-slate-50">
                        <td class="py-3 px-4 font-mono font-bold text-slate-800">{{ $p->kode_peminjaman }}</td>
                        <td class="py-3 px-4">
                            <p class="font-medium text-slate-800">{{ $p->peminjam?->nama }}</p>
                            <p class="text-xs text-slate-400">{{ $p->peminjam?->kelas }}</p>
                        </td>
                        <td class="py-3 px-4 text-slate-600">{{ formatTanggal($p->tanggal_pinjam) }}</td>
                        <td class="py-3 px-4 text-slate-600">{{ $p->tanggal_kembali_aktual ? formatTanggal($p->tanggal_kembali_aktual) : '-' }}</td>
                        <td class="py-3 px-4">
                            @if($p->tanggal_kembali_aktual)
                            {{ \Carbon\Carbon::parse($p->tanggal_pinjam)->diffInDays($p->tanggal_kembali_aktual) }} hari
                            @else
                            <span class="text-slate-400">-</span>
                            @endif
                        </td>
                        <td class="py-3 px-4 text-right font-bold {{ $p->denda_total > 0 ? 'text-rose-600' : 'text-slate-400' }}">
                            {{ $p->denda_total > 0 ? 'Rp ' . number_format($p->denda_total, 0, ',', '.') : '-' }}
                        </td>
                        <td class="py-3 px-4 text-center">
                            <span class="px-3 py-1 rounded-full text-xs font-bold uppercase {{ getStatusColor($p->status) }}">
                                {{ str_replace('_', ' ', $p->status) }}
                            </span>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="7" class="py-12 text-center text-slate-400 text-sm">Tidak ada data untuk periode ini.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($peminjaman->hasPages())
        <div class="px-4 py-3 border-t border-slate-100">{{ $peminjaman->appends(request()->all())->links() }}</div>
        @endif
    </div>
</div>
@endsection
