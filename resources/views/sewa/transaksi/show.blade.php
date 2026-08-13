@extends('layouts.app')

@section('title', 'Detail Penyewaan - ' . $sewa->kode_sewa)

@section('content')
@php
$statusSewaMap = [
    'booking' => ['label'=>'Booking','cls'=>'bg-amber-100 text-amber-700 border-amber-200'],
    'disetujui' => ['label'=>'Disetujui','cls'=>'bg-blue-100 text-blue-700 border-blue-200'],
    'berlangsung' => ['label'=>'Berlangsung','cls'=>'bg-violet-100 text-violet-700 border-violet-200'],
    'selesai' => ['label'=>'Selesai','cls'=>'bg-emerald-100 text-emerald-700 border-emerald-200'],
    'dibatalkan' => ['label'=>'Dibatalkan','cls'=>'bg-rose-100 text-rose-700 border-rose-200']
];
$statusByrMap = [
    'belum_bayar' => ['label'=>'Belum Bayar','cls'=>'bg-rose-100 text-rose-700 border-rose-200'],
    'dp' => ['label'=>'DP (Uang Muka)','cls'=>'bg-amber-100 text-amber-700 border-amber-200'],
    'lunas' => ['label'=>'Lunas','cls'=>'bg-emerald-100 text-emerald-700 border-emerald-200']
];
$sisaTagihan = max(0, $sewa->total_biaya - $sewa->jumlah_dibayar);
$kembalian = max(0, $sewa->jumlah_dibayar - $sewa->total_biaya);
@endphp

<div class="space-y-6">
    {{-- Header --}}
    <div class="flex items-center justify-between flex-wrap gap-3">
        <div class="flex items-center gap-3">
            <a href="{{ route('sewa.transaksi.index') }}" class="p-2 text-slate-500 hover:text-slate-800 hover:bg-slate-100 rounded-xl transition-all">
                <i class="fa-solid fa-arrow-left"></i>
            </a>
            <div>
                <div class="flex items-center gap-2 flex-wrap">
                    <h2 class="text-xl font-bold text-slate-900">{{ $sewa->kode_sewa }}</h2>
                    <span class="text-xs px-2.5 py-1 rounded-full font-semibold border {{ $statusSewaMap[$sewa->status_sewa]['cls'] ?? 'bg-slate-100 text-slate-700 border-slate-200' }}">
                        {{ $statusSewaMap[$sewa->status_sewa]['label'] ?? ucfirst($sewa->status_sewa) }}
                    </span>
                    <span class="text-xs px-2.5 py-1 rounded-full font-semibold border {{ $statusByrMap[$sewa->status_pembayaran]['cls'] ?? 'bg-slate-100 text-slate-700 border-slate-200' }}">
                        {{ $statusByrMap[$sewa->status_pembayaran]['label'] ?? ucfirst($sewa->status_pembayaran) }}
                    </span>
                </div>
                <p class="text-sm text-slate-500 mt-1">Dibuat oleh {{ $sewa->user?->nama ?? $sewa->user?->name ?? '-' }} · {{ formatTanggalWaktu($sewa->created_at) }}</p>
            </div>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('sewa.transaksi.cetak', $sewa->id) }}" target="_blank"
                class="inline-flex items-center gap-2 px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-semibold rounded-xl text-sm shadow-md shadow-emerald-600/25 transition-all">
                <i class="fa-solid fa-print"></i> Cetak Struk / Kwitansi (A4)
            </a>
        </div>
    </div>

    @if(session('success'))
    <div class="flex items-center gap-3 px-4 py-3 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-xl text-sm">
        <i class="fa-solid fa-circle-check text-emerald-500"></i> {{ session('success') }}
    </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- LEFT col --}}
        <div class="lg:col-span-2 space-y-5">

            {{-- Gedung Info --}}
            <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-sm">
                <h3 class="font-bold text-slate-700 text-sm mb-4 flex items-center gap-2">
                    <i class="fa-solid fa-building text-blue-500"></i> Informasi Gedung / Lab
                </h3>
                <div class="grid grid-cols-2 gap-x-6 gap-y-3 text-sm">
                    <div>
                        <p class="text-slate-400 text-xs font-medium">Nama Gedung</p>
                        <p class="font-semibold text-slate-800 mt-0.5">{{ $sewa->gedung?->nama_gedung ?? '-' }}</p>
                    </div>
                    <div>
                        <p class="text-slate-400 text-xs font-medium">Kode Gedung</p>
                        <p class="font-mono font-semibold text-slate-700 mt-0.5">{{ $sewa->gedung?->kode_gedung ?? '-' }}</p>
                    </div>
                    <div>
                        <p class="text-slate-400 text-xs font-medium">Lokasi</p>
                        <p class="font-medium text-slate-700 mt-0.5">{{ $sewa->gedung?->lokasi ?? '-' }}</p>
                    </div>
                    <div>
                        <p class="text-slate-400 text-xs font-medium">Harga Sewa / Hari</p>
                        <p class="font-semibold text-emerald-700 mt-0.5">{{ formatRupiah($sewa->harga_sewa_gedung) }}</p>
                    </div>
                </div>
            </div>

            {{-- Penyewa Info --}}
            <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-sm">
                <h3 class="font-bold text-slate-700 text-sm mb-4 flex items-center gap-2">
                    <i class="fa-solid fa-user text-violet-500"></i> Informasi Penyewa
                </h3>
                <div class="grid grid-cols-2 gap-x-6 gap-y-3 text-sm">
                    <div>
                        <p class="text-slate-400 text-xs font-medium">Nama Penyewa</p>
                        <p class="font-semibold text-slate-800 mt-0.5">{{ $sewa->nama_penyewa }}</p>
                    </div>
                    <div>
                        <p class="text-slate-400 text-xs font-medium">Telepon</p>
                        <p class="font-medium text-slate-700 mt-0.5">{{ $sewa->telepon_penyewa ?? '-' }}</p>
                    </div>
                    <div>
                        <p class="text-slate-400 text-xs font-medium">Instansi</p>
                        <p class="font-medium text-slate-700 mt-0.5">{{ $sewa->instansi_penyewa ?? '-' }}</p>
                    </div>
                    <div>
                        <p class="text-slate-400 text-xs font-medium">Pelanggan Terdaftar</p>
                        <p class="font-medium text-slate-700 mt-0.5">{{ $sewa->pelanggan?->nama ?? '-' }}</p>
                    </div>
                </div>
            </div>

            {{-- Tanggal Info --}}
            <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-sm">
                <h3 class="font-bold text-slate-700 text-sm mb-4 flex items-center gap-2">
                    <i class="fa-solid fa-calendar-days text-amber-500"></i> Periode Sewa
                </h3>
                <div class="grid grid-cols-3 gap-4 text-sm">
                    <div class="text-center p-3 bg-slate-50 rounded-xl border border-slate-100">
                        <p class="text-slate-400 text-xs font-medium mb-1">Tanggal Mulai</p>
                        <p class="font-bold text-slate-800">{{ formatTanggal($sewa->tanggal_mulai, 'd/m/Y') }}</p>
                    </div>
                    <div class="text-center p-3 bg-blue-50 rounded-xl border border-blue-100">
                        <p class="text-blue-400 text-xs font-medium mb-1">Lama Sewa</p>
                        <p class="font-bold text-blue-700 text-lg">{{ $sewa->lama_sewa }}</p>
                        <p class="text-blue-400 text-xs">hari</p>
                    </div>
                    <div class="text-center p-3 bg-slate-50 rounded-xl border border-slate-100">
                        <p class="text-slate-400 text-xs font-medium mb-1">Tanggal Selesai</p>
                        <p class="font-bold text-slate-800">{{ formatTanggal($sewa->tanggal_selesai, 'd/m/Y') }}</p>
                    </div>
                </div>
            </div>

            {{-- Fasilitas --}}
            @if($sewa->details->count() > 0)
            <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-sm">
                <h3 class="font-bold text-slate-700 text-sm mb-4 flex items-center gap-2">
                    <i class="fa-solid fa-list text-violet-500"></i> Fasilitas / Alat yang Disewa
                </h3>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="text-left text-xs text-slate-500 border-b border-slate-100">
                                <th class="pb-2 font-semibold">Fasilitas</th>
                                <th class="pb-2 font-semibold text-center">Jumlah</th>
                                <th class="pb-2 font-semibold text-right">Harga/Item/Hari</th>
                                <th class="pb-2 font-semibold text-right">Subtotal ({{ $sewa->lama_sewa }} hari)</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50">
                            @foreach($sewa->details as $detail)
                            <tr>
                                <td class="py-3 pr-4 font-medium text-slate-800">{{ $detail->nama_fasilitas }}</td>
                                <td class="py-3 text-center text-slate-600">{{ $detail->jumlah }}</td>
                                <td class="py-3 text-right text-slate-600">{{ formatRupiah($detail->harga_per_item) }}</td>
                                <td class="py-3 text-right font-semibold text-emerald-700">{{ formatRupiah($detail->subtotal) }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            @endif

            {{-- Catatan --}}
            @if($sewa->catatan)
            <div class="bg-amber-50 border border-amber-200 rounded-2xl p-5">
                <p class="text-xs font-semibold text-amber-700 mb-1"><i class="fa-solid fa-note-sticky mr-1"></i>Catatan</p>
                <p class="text-sm text-amber-800">{{ $sewa->catatan }}</p>
            </div>
            @endif
        </div>

        {{-- RIGHT: Ringkasan Biaya & Calculations + Update Status --}}
        <div class="space-y-4">
            {{-- Ringkasan Biaya & Calculations --}}
            <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-sm">
                <h3 class="font-bold text-slate-700 text-sm mb-4 flex items-center gap-2">
                    <i class="fa-solid fa-calculator text-emerald-500"></i> Penghitungan Transaksi & Pembayaran
                </h3>
                <div class="space-y-3 text-sm">
                    <div class="flex justify-between text-slate-600">
                        <span>Subtotal Gedung</span>
                        <span class="font-semibold">{{ formatRupiah($sewa->subtotal_gedung) }}</span>
                    </div>
                    <div class="flex justify-between text-slate-600">
                        <span>Subtotal Fasilitas</span>
                        <span class="font-semibold">{{ formatRupiah($sewa->subtotal_fasilitas) }}</span>
                    </div>
                    <div class="flex justify-between font-bold text-slate-800 bg-blue-50 rounded-xl p-3 border border-blue-100">
                        <span>Total Biaya Sewa</span>
                        <span class="text-blue-700 text-base">{{ formatRupiah($sewa->total_biaya) }}</span>
                    </div>

                    <div class="border-t border-dashed border-slate-200 pt-3 space-y-2">
                        <div class="flex justify-between items-center text-xs">
                            <span class="font-semibold text-slate-600">Status Pembayaran</span>
                            <span class="text-xs px-2.5 py-1 rounded-full font-bold border {{ $statusByrMap[$sewa->status_pembayaran]['cls'] ?? 'bg-slate-100 text-slate-700 border-slate-200' }}">
                                {{ $statusByrMap[$sewa->status_pembayaran]['label'] ?? ucfirst($sewa->status_pembayaran) }}
                            </span>
                        </div>

                        <div class="flex justify-between text-slate-600">
                            <span>Jumlah Dibayar</span>
                            <span class="font-bold text-emerald-700">{{ formatRupiah($sewa->jumlah_dibayar) }}</span>
                        </div>

                        @if($sewa->status_pembayaran === 'dp' || $sisaTagihan > 0)
                        <div class="flex justify-between text-rose-700 bg-rose-50 rounded-xl p-3 border border-rose-200">
                            <div>
                                <p class="font-semibold text-xs">Sisa Tagihan (Pelunasan)</p>
                                <p class="text-[11px] text-rose-500">Total Biaya - Jumlah DP</p>
                            </div>
                            <span class="font-bold text-base">{{ formatRupiah($sisaTagihan) }}</span>
                        </div>
                        @elseif($sewa->status_pembayaran === 'lunas')
                        <div class="flex items-center justify-between text-emerald-700 bg-emerald-50 rounded-xl p-3 border border-emerald-200">
                            <div class="flex items-center gap-2">
                                <i class="fa-solid fa-circle-check text-base text-emerald-600"></i>
                                <div>
                                    <p class="font-bold text-xs">Status Pembayaran LUNAS</p>
                                    <p class="text-[11px] text-emerald-600">Sisa Tagihan: Rp 0</p>
                                </div>
                            </div>
                            @if($kembalian > 0)
                            <span class="font-bold text-xs text-blue-700">Kembali: {{ formatRupiah($kembalian) }}</span>
                            @endif
                        </div>
                        @else
                        <div class="flex justify-between text-slate-600 bg-slate-50 rounded-xl p-3 border border-slate-200 text-xs">
                            <span class="font-semibold">Belum Dibayar</span>
                            <span class="font-bold text-rose-600">{{ formatRupiah($sewa->total_biaya) }}</span>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Update Status --}}
            <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-sm">
                <h3 class="font-bold text-slate-700 text-sm mb-4 flex items-center gap-2">
                    <i class="fa-solid fa-gear text-amber-500"></i> Update Status Pembayaran & Sewa
                </h3>
                <form method="POST" action="{{ route('sewa.transaksi.update-status', $sewa->id) }}" class="space-y-3">
                    @csrf @method('PUT')
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1.5">Status Sewa</label>
                        <select name="status_sewa" class="w-full px-3 py-2.5 text-sm border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500">
                            @foreach(['booking'=>'Booking','disetujui'=>'Disetujui','berlangsung'=>'Berlangsung','selesai'=>'Selesai','dibatalkan'=>'Dibatalkan'] as $val => $lbl)
                            <option value="{{ $val }}" {{ $sewa->status_sewa === $val ? 'selected' : '' }}>{{ $lbl }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1.5">Status Pembayaran</label>
                        <select name="status_pembayaran" class="w-full px-3 py-2.5 text-sm border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="belum_bayar" {{ $sewa->status_pembayaran === 'belum_bayar' ? 'selected' : '' }}>Belum Bayar</option>
                            <option value="dp" {{ $sewa->status_pembayaran === 'dp' ? 'selected' : '' }}>Uang Muka (DP)</option>
                            <option value="lunas" {{ $sewa->status_pembayaran === 'lunas' ? 'selected' : '' }}>Lunas</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1.5">Jumlah Dibayar (DP / Pelunasan)</label>
                        <div class="relative">
                            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm">Rp</span>
                            <input type="number" name="jumlah_dibayar" value="{{ $sewa->jumlah_dibayar }}" min="0"
                                class="w-full pl-10 pr-3 py-2.5 text-sm border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                    </div>
                    <button type="submit" class="w-full py-2.5 bg-amber-500 hover:bg-amber-600 text-white font-semibold rounded-xl text-sm transition-all shadow-md shadow-amber-500/25">
                        <i class="fa-solid fa-floppy-disk mr-1"></i> Update Status
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
