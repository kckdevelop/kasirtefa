@extends('layouts.app')

@section('title', 'Laporan Kondisi Alat')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-xl font-bold text-slate-900">Laporan Kondisi Alat</h2>
            <p class="text-sm text-slate-500">Rekapitulasi kondisi kelayakan, kerusakan, dan kehilangan alat</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="/api/v1/alat/laporan/kondisi-alat/export/pdf" target="_blank" class="px-3.5 py-2 bg-rose-600 hover:bg-rose-700 text-white font-semibold rounded-xl text-xs flex items-center gap-2 shadow-sm">
                <i class="fa-solid fa-file-pdf"></i> Export PDF
            </a>
        </div>
    </div>

    <!-- Condition Summary Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
        <div class="bg-white p-5 rounded-2xl border border-emerald-200 shadow-sm bg-emerald-50/20">
            <p class="text-xs font-semibold text-emerald-600 uppercase tracking-wider mb-1">Kondisi Baik</p>
            <p class="text-3xl font-extrabold text-emerald-700">{{ number_format($report['rekap']['baik'] ?? 0) }}</p>
        </div>
        <div class="bg-white p-5 rounded-2xl border border-amber-200 shadow-sm bg-amber-50/20">
            <p class="text-xs font-semibold text-amber-600 uppercase tracking-wider mb-1">Rusak Ringan</p>
            <p class="text-3xl font-extrabold text-amber-700">{{ number_format($report['rekap']['rusak_ringan'] ?? 0) }}</p>
        </div>
        <div class="bg-white p-5 rounded-2xl border border-rose-200 shadow-sm bg-rose-50/20">
            <p class="text-xs font-semibold text-rose-600 uppercase tracking-wider mb-1">Rusak Berat</p>
            <p class="text-3xl font-extrabold text-rose-700">{{ number_format($report['rekap']['rusak_berat'] ?? 0) }}</p>
        </div>
        <div class="bg-white p-5 rounded-2xl border border-slate-300 shadow-sm bg-slate-50">
            <p class="text-xs font-semibold text-slate-600 uppercase tracking-wider mb-1">Kondisi Hilang</p>
            <p class="text-3xl font-extrabold text-slate-800">{{ number_format($report['rekap']['hilang'] ?? 0) }}</p>
        </div>
    </div>

    <!-- Table -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead class="bg-slate-50 border-b border-slate-200">
                    <tr class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider">
                        <th class="py-3.5 px-4">Kode Alat</th>
                        <th class="py-3.5 px-4">Nama Alat</th>
                        <th class="py-3.5 px-4">Kategori</th>
                        <th class="py-3.5 px-4 text-center">Baik</th>
                        <th class="py-3.5 px-4 text-center">Rusak</th>
                        <th class="py-3.5 px-4 text-center">Hilang</th>
                        <th class="py-3.5 px-4">Lokasi</th>
                        <th class="py-3.5 px-4 text-center">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-sm">
                    @forelse($report['detail_perlu_perhatian'] ?? $report['items'] ?? [] as $item)
                    @php
                        $jBaik = ($item->jumlah_baik ?? 0) + ($item->jumlah_cukup ?? 0);
                        $jRusak = ($item->jumlah_rusak_ringan ?? 0) + ($item->jumlah_rusak_berat ?? 0);
                        $jHilang = $item->jumlah_hilang ?? 0;
                    @endphp
                    <tr class="hover:bg-slate-50">
                        <td class="py-3.5 px-4 font-mono text-xs text-slate-700 font-semibold">{{ $item->kode_alat ?? '-' }}</td>
                        <td class="py-3.5 px-4 font-semibold text-slate-900">{{ $item->nama ?? '-' }}</td>
                        <td class="py-3.5 px-4 text-slate-600">{{ $item->kategori?->nama ?? '-' }}</td>
                        <td class="py-3.5 px-4 text-center font-bold text-emerald-700">{{ $jBaik }}</td>
                        <td class="py-3.5 px-4 text-center font-bold text-amber-700">{{ $jRusak }}</td>
                        <td class="py-3.5 px-4 text-center font-bold text-rose-700">{{ $jHilang }}</td>
                        <td class="py-3.5 px-4 text-slate-600 text-xs">{{ $item->lokasi_penyimpanan ?? '-' }}</td>
                        <td class="py-3.5 px-4 text-center">
                            <span class="px-3 py-1 rounded-full text-xs font-bold uppercase {{ getStatusColor($item->status_ketersediaan ?? $item->status ?? 'tersedia') }}">{{ str_replace('_', ' ', $item->status_ketersediaan ?? $item->status ?? 'tersedia') }}</span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="py-12 text-center text-slate-400">Belum ada data kondisi alat.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
