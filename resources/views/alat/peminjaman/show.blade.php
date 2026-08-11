@extends('layouts.app')

@section('title', 'Detail Peminjaman Alat')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">

    <div class="flex items-center justify-between">
        <div class="flex items-center gap-3">
            <a href="{{ route('alat.peminjaman.index') }}" class="w-9 h-9 rounded-xl bg-white border border-slate-200 flex items-center justify-center text-slate-600 hover:bg-slate-50 transition-colors">
                <i class="fa-solid fa-arrow-left"></i>
            </a>
            <div>
                <h2 class="text-xl font-bold text-slate-900">Detail Peminjaman Alat</h2>
                <p class="text-xs font-mono text-slate-500">Kode: {{ $peminjaman->kode_peminjaman }}</p>
            </div>
        </div>
        <div class="flex items-center gap-2">
            <span class="px-3.5 py-1.5 rounded-full text-xs font-bold uppercase {{ getStatusColor($peminjaman->status) }}">
                {{ str_replace('_', ' ', $peminjaman->status) }}
            </span>
        </div>
    </div>

    <!-- Loan Info Card -->
    <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-sm grid grid-cols-1 sm:grid-cols-3 gap-6">
        <div>
            <p class="text-xs text-slate-400 font-medium mb-1">Peminjam</p>
            <p class="text-sm font-semibold text-slate-900">{{ $peminjaman->peminjam?->nama ?? $peminjaman->peminjam?->name }}</p>
            <p class="text-xs text-slate-400">{{ $peminjaman->peminjam?->jurusan }} {{ $peminjaman->peminjam?->kelas }}</p>
        </div>
        <div>
            <p class="text-xs text-slate-400 font-medium mb-1">Tanggal Pinjam & Target Kembali</p>
            <p class="text-sm font-semibold text-slate-900">{{ $peminjaman->tanggal_pinjam ? $peminjaman->tanggal_pinjam->format('d M Y') : '-' }}</p>
            <p class="text-xs font-semibold text-amber-600">s/d {{ $peminjaman->tanggal_kembali_rencana ? $peminjaman->tanggal_kembali_rencana->format('d M Y') : '-' }}</p>
        </div>
        <div>
            <p class="text-xs text-slate-400 font-medium mb-1">Status Pengembalian</p>
            @if($peminjaman->tanggal_kembali_aktual)
            <p class="text-sm font-semibold text-emerald-600">Dikembalikan: {{ $peminjaman->tanggal_kembali_aktual->format('d M Y') }}</p>
            @else
            <p class="text-sm font-semibold text-amber-600">Belum Dikembalikan</p>
            @endif
        </div>
    </div>

    <!-- Items Table -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="p-4 bg-slate-50 border-b border-slate-200 font-semibold text-sm text-slate-800">
            Daftar Alat Dipinjam
        </div>
        <table class="w-full text-left border-collapse">
            <thead class="bg-slate-50 border-b border-slate-200">
                <tr class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider">
                    <th class="py-3 px-4">Alat</th>
                    <th class="py-3 px-4 text-center">Jumlah</th>
                    <th class="py-3 px-4 text-center">Kondisi Pinjam</th>
                    <th class="py-3 px-4 text-center">Kondisi Kembali</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 text-sm">
                @foreach($peminjaman->items as $item)
                <tr class="hover:bg-slate-50">
                    <td class="py-3 px-4 font-semibold text-slate-900">{{ $item->alat?->nama }}</td>
                    <td class="py-3 px-4 text-center font-bold text-slate-800">{{ $item->jumlah }}</td>
                    <td class="py-3 px-4 text-center">
                        <span class="px-2.5 py-0.5 rounded bg-emerald-100 text-emerald-700 text-xs font-semibold uppercase">{{ $item->kondisi_pinjam ?? 'Baik' }}</span>
                    </td>
                    <td class="py-3 px-4 text-center">
                        <span class="px-2.5 py-0.5 rounded bg-slate-100 text-slate-700 text-xs font-semibold uppercase">{{ $item->kondisi_kembali ?? '-' }}</span>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <!-- Denda section if exists -->
    @if($peminjaman->denda)
    <div class="bg-rose-50 rounded-2xl border border-rose-200 p-6 shadow-sm space-y-2">
        <h4 class="font-bold text-rose-900 text-sm flex items-center gap-2">
            <i class="fa-solid fa-triangle-exclamation"></i> Denda Terkait Peminjaman Ini
        </h4>
        <div class="flex justify-between text-sm text-rose-800">
            <span>Jenis Denda: {{ $peminjaman->denda->jenis_denda }}</span>
            <span class="font-bold">Jumlah: Rp {{ number_format($peminjaman->denda->jumlah_denda, 0, ',', '.') }}</span>
        </div>
        <p class="text-xs text-rose-700">Status Bayar: <span class="font-bold uppercase">{{ $peminjaman->denda->status }}</span></p>
    </div>
    @endif
</div>
@endsection
