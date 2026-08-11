@extends('layouts.app')

@section('title', 'Detail Alat - '.$alat->nama)

@section('content')
<div class="space-y-6">

    <!-- Header -->
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-3">
            <a href="{{ route('alat.daftar.index') }}" class="w-9 h-9 rounded-xl bg-white border border-slate-200 flex items-center justify-center text-slate-600 hover:bg-slate-50 transition-colors">
                <i class="fa-solid fa-arrow-left"></i>
            </a>
            <div>
                <h2 class="text-xl font-bold text-slate-900">{{ $alat->nama }}</h2>
                <p class="text-xs font-mono text-slate-500">Kode: {{ $alat->kode_alat }} | Merek: {{ $alat->merek ?? '-' }}</p>
            </div>
        </div>
        <div class="flex items-center gap-2">
            <span class="px-3 py-1 rounded-full text-xs font-bold uppercase {{ getStatusColor($alat->status) }}">{{ $alat->status }}</span>
        </div>
    </div>

    <!-- Overview Grid -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- Main Info -->
        <div class="md:col-span-1 bg-white p-6 rounded-2xl border border-slate-200 shadow-sm space-y-4">
            <div class="w-full h-48 rounded-xl bg-slate-100 overflow-hidden flex items-center justify-center">
                @if($alat->foto)
                <img src="{{ asset('storage/'.$alat->foto) }}" class="w-full h-full object-cover" alt="{{ $alat->nama }}">
                @else
                <i class="fa-solid fa-wrench text-5xl text-slate-300"></i>
                @endif
            </div>

            <div class="space-y-2 border-t border-slate-100 pt-4 text-sm">
                <div class="flex justify-between">
                    <span class="text-slate-400">Kategori</span>
                    <span class="font-semibold text-slate-800">{{ $alat->kategori?->nama }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-400">Kondisi Saat Ini</span>
                    <span class="font-bold uppercase {{ $alat->kondisi == 'baik' ? 'text-emerald-600' : 'text-amber-600' }}">{{ str_replace('_', ' ', $alat->kondisi) }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-400">Stok Total / Tersedia</span>
                    <span class="font-bold text-slate-900">{{ $alat->stok_tersedia }} / {{ $alat->stok_total }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-400">Lokasi Penyimpanan</span>
                    <span class="font-semibold text-slate-700">{{ $alat->lokasi_penyimpanan ?? '-' }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-400">Denda Keterlambatan</span>
                    <span class="font-semibold text-rose-600">Rp {{ number_format($alat->nilai_denda ?? 0, 0, ',', '.') }}/hari</span>
                </div>
            </div>
            @if($alat->deskripsi)
            <div class="border-t border-slate-100 pt-3">
                <p class="text-xs text-slate-400 font-semibold mb-1">Deskripsi & Catatan</p>
                <p class="text-xs text-slate-600">{{ $alat->deskripsi }}</p>
            </div>
            @endif
        </div>

        <!-- Tabs & History -->
        <div class="md:col-span-2 space-y-6">
            <!-- Documentation Grid -->
            <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm space-y-4">
                <h3 class="font-bold text-slate-900 text-sm flex items-center gap-2">
                    <i class="fa-solid fa-camera text-blue-600"></i> Dokumentasi Alat
                </h3>
                <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                    @forelse($alat->dokumentasi as $doc)
                    <div class="rounded-xl overflow-hidden border border-slate-200 bg-slate-50 relative group">
                        <img src="{{ asset('storage/'.$doc->foto) }}" class="w-full h-24 object-cover">
                        <div class="p-2 text-[11px] font-medium text-slate-600 truncate">{{ $doc->keterangan ?? 'Dokumentasi' }}</div>
                    </div>
                    @empty
                    <p class="col-span-3 text-xs text-slate-400 py-4 text-center">Belum ada foto dokumentasi tambahan.</p>
                    @endforelse
                </div>
            </div>

            <!-- Maintenance & Condition History -->
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 space-y-4">
                <h3 class="font-bold text-slate-900 text-sm flex items-center gap-2">
                    <i class="fa-solid fa-clock-rotate-left text-indigo-600"></i> Riwayat Perawatan & Perbaikan
                </h3>
                <div class="divide-y divide-slate-100">
                    @forelse($alat->riwayatPerawatan as $perawatan)
                    <div class="py-3 text-sm flex justify-between items-start">
                        <div>
                            <p class="font-semibold text-slate-800">{{ $perawatan->jenis_perawatan ?? 'Perawatan Rutin' }}</p>
                            <p class="text-xs text-slate-500">{{ $perawatan->keterangan }}</p>
                        </div>
                        <div class="text-right">
                            <span class="text-xs font-semibold text-slate-400">{{ $perawatan->tanggal ? $perawatan->tanggal->format('d M Y') : '-' }}</span>
                            <p class="text-xs font-bold text-rose-600">Rp {{ number_format($perawatan->biaya ?? 0, 0, ',', '.') }}</p>
                        </div>
                    </div>
                    @empty
                    <p class="text-xs text-slate-400 py-4 text-center">Belum ada riwayat perawatan.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
