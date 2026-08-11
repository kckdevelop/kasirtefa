@extends('layouts.app')

@section('title', 'Laporan Inventaris Alat')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-xl font-bold text-slate-900">Laporan Inventaris Alat</h2>
            <p class="text-sm text-slate-500">Ringkasan status dan rincian kondisi seluruh aset peralatan (Baik, Rusak, Hilang)</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="/api/v1/alat/laporan/inventaris/export/excel" class="px-3.5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-semibold rounded-xl text-xs flex items-center gap-2 shadow-sm">
                <i class="fa-solid fa-file-excel"></i> Export Excel
            </a>
            <a href="/api/v1/alat/laporan/inventaris/export/pdf" target="_blank" class="px-3.5 py-2 bg-rose-600 hover:bg-rose-700 text-white font-semibold rounded-xl text-xs flex items-center gap-2 shadow-sm">
                <i class="fa-solid fa-file-pdf"></i> Export PDF
            </a>
        </div>
    </div>

    {{-- Ringkasan Kondisi Cards --}}
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
        <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-sm">
            <div class="flex items-center justify-between">
                <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Total Unit</p>
                <div class="w-8 h-8 rounded-lg bg-slate-100 flex items-center justify-center text-slate-600 text-sm">
                    <i class="fa-solid fa-boxes-stacked"></i>
                </div>
            </div>
            <p class="text-2xl font-extrabold text-slate-900 mt-2">{{ number_format($report['ringkasan']['total_unit'] ?? 0) }}</p>
        </div>
        <div class="bg-white p-4 rounded-2xl border border-emerald-200 shadow-sm bg-emerald-50/20">
            <div class="flex items-center justify-between">
                <p class="text-xs font-semibold text-emerald-600 uppercase tracking-wider">Baik</p>
                <div class="w-8 h-8 rounded-lg bg-emerald-100 flex items-center justify-center text-emerald-700 text-sm">
                    <i class="fa-solid fa-circle-check"></i>
                </div>
            </div>
            <p class="text-2xl font-extrabold text-emerald-700 mt-2">{{ number_format($report['ringkasan']['total_baik'] ?? 0) }}</p>
        </div>
        <div class="bg-white p-4 rounded-2xl border border-amber-200 shadow-sm bg-amber-50/20">
            <div class="flex items-center justify-between">
                <p class="text-xs font-semibold text-amber-600 uppercase tracking-wider">Rusak</p>
                <div class="w-8 h-8 rounded-lg bg-amber-100 flex items-center justify-center text-amber-700 text-sm">
                    <i class="fa-solid fa-triangle-exclamation"></i>
                </div>
            </div>
            <p class="text-2xl font-extrabold text-amber-700 mt-2">{{ number_format($report['ringkasan']['total_rusak'] ?? 0) }}</p>
        </div>
        <div class="bg-white p-4 rounded-2xl border border-rose-200 shadow-sm bg-rose-50/20">
            <div class="flex items-center justify-between">
                <p class="text-xs font-semibold text-rose-600 uppercase tracking-wider">Hilang</p>
                <div class="w-8 h-8 rounded-lg bg-rose-100 flex items-center justify-center text-rose-700 text-sm">
                    <i class="fa-solid fa-circle-xmark"></i>
                </div>
            </div>
            <p class="text-2xl font-extrabold text-rose-700 mt-2">{{ number_format($report['ringkasan']['total_hilang'] ?? 0) }}</p>
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
                        <th class="py-3.5 px-4 text-center">Stok Total</th>
                        <th class="py-3.5 px-4 text-center">Stok Tersedia</th>
                        <th class="py-3.5 px-4 text-center text-emerald-700">Baik</th>
                        <th class="py-3.5 px-4 text-center text-amber-700">Rusak</th>
                        <th class="py-3.5 px-4 text-center text-rose-700">Hilang</th>
                        <th class="py-3.5 px-4 text-center">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-sm">
                    @forelse($report['items'] ?? $report['data'] ?? [] as $item)
                    @php
                        $jBaik = ($item->jumlah_baik ?? 0) + ($item->jumlah_cukup ?? 0);
                        if ($jBaik == 0 && ($item->jumlah_total ?? 0) > 0 && ($item->jumlah_rusak_ringan ?? 0) == 0 && ($item->jumlah_rusak_berat ?? 0) == 0 && ($item->jumlah_hilang ?? 0) == 0) {
                            $jBaik = $item->jumlah_total ?? 1;
                        }
                        $jRusak = ($item->jumlah_rusak_ringan ?? 0) + ($item->jumlah_rusak_berat ?? 0);
                        $jHilang = $item->jumlah_hilang ?? 0;
                        $stokTotal = $item->jumlah_total ?? $item->stok_total ?? 0;
                        $stokTersedia = $item->jumlah_tersedia ?? $item->stok_tersedia ?? 0;
                    @endphp
                    <tr class="hover:bg-slate-50">
                        <td class="py-3.5 px-4 font-mono text-xs text-slate-700 font-semibold">{{ $item->kode_alat ?? '-' }}</td>
                        <td class="py-3.5 px-4 font-semibold text-slate-900">{{ $item->nama ?? '-' }}</td>
                        <td class="py-3.5 px-4 text-slate-600">{{ $item->kategori?->nama ?? '-' }}</td>
                        <td class="py-3.5 px-4 text-center font-bold text-slate-800">{{ $stokTotal }}</td>
                        <td class="py-3.5 px-4 text-center font-bold text-emerald-700">{{ $stokTersedia }}</td>
                        <td class="py-3.5 px-4 text-center">
                            <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-700">
                                {{ $jBaik }}
                            </span>
                        </td>
                        <td class="py-3.5 px-4 text-center">
                            <span class="px-2.5 py-1 rounded-full text-xs font-bold {{ $jRusak > 0 ? 'bg-amber-100 text-amber-700' : 'bg-slate-100 text-slate-500' }}">
                                {{ $jRusak }}
                            </span>
                        </td>
                        <td class="py-3.5 px-4 text-center">
                            <span class="px-2.5 py-1 rounded-full text-xs font-bold {{ $jHilang > 0 ? 'bg-rose-100 text-rose-700' : 'bg-slate-100 text-slate-500' }}">
                                {{ $jHilang }}
                            </span>
                        </td>
                        <td class="py-3.5 px-4 text-center">
                            <span class="px-3 py-1 rounded-full text-xs font-bold uppercase {{ getStatusColor($item->status_ketersediaan ?? $item->status ?? 'tersedia') }}">
                                {{ str_replace('_', ' ', $item->status_ketersediaan ?? $item->status ?? 'tersedia') }}
                            </span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="py-12 text-center text-slate-400">Tidak ada data inventaris.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
