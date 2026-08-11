@extends('layouts.app')

@section('title', 'Detail Transaksi Penjualan')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">

    <div class="flex items-center justify-between">
        <div class="flex items-center gap-3">
            <a href="{{ route('tefa.transaksi.index') }}" class="w-9 h-9 rounded-xl bg-white border border-slate-200 flex items-center justify-center text-slate-600 hover:bg-slate-50 transition-colors">
                <i class="fa-solid fa-arrow-left"></i>
            </a>
            <div>
                <h2 class="text-xl font-bold text-slate-900">Detail Transaksi Penjualan</h2>
                <p class="text-xs font-mono text-slate-500">Kode: {{ $transaksi->kode_transaksi }}</p>
            </div>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('tefa.transaksi.cetak-struk', $transaksi->id) }}" target="_blank" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-semibold rounded-xl text-sm shadow-sm transition-all flex items-center gap-2">
                <i class="fa-solid fa-print"></i> Cetak Struk
            </a>
        </div>
    </div>

    <!-- Transaction Header Card -->
    <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-sm grid grid-cols-2 sm:grid-cols-4 gap-4">
        <div>
            <p class="text-xs text-slate-400 font-medium mb-1">Tanggal Transaksi</p>
            <p class="text-sm font-semibold text-slate-900">{{ $transaksi->tanggal ? $transaksi->tanggal->format('d M Y, H:i') : '-' }}</p>
        </div>
        <div>
            <p class="text-xs text-slate-400 font-medium mb-1">Kasir</p>
            <p class="text-sm font-semibold text-slate-900">{{ $transaksi->kasir?->nama ?? $transaksi->kasir?->name ?? 'System' }}</p>
        </div>
        <div>
            <p class="text-xs text-slate-400 font-medium mb-1">Metode Pembayaran</p>
            <p class="text-sm font-semibold uppercase text-blue-600">{{ $transaksi->metode_pembayaran }}</p>
        </div>
        <div>
            <p class="text-xs text-slate-400 font-medium mb-1">Status</p>
            <span class="px-3 py-1 rounded-full text-xs font-bold uppercase {{ getStatusColor($transaksi->status) }}">{{ $transaksi->status }}</span>
        </div>
    </div>

    <!-- Items Table -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="p-4 bg-slate-50 border-b border-slate-200 font-semibold text-sm text-slate-800">
            Daftar Produk / Item Dibeli
        </div>
        <table class="w-full text-left border-collapse">
            <thead class="bg-slate-50 border-b border-slate-200">
                <tr class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider">
                    <th class="py-3 px-4">Item</th>
                    <th class="py-3 px-4 text-center">Harga Satuan</th>
                    <th class="py-3 px-4 text-center">Jumlah</th>
                    <th class="py-3 px-4 text-right">Subtotal</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 text-sm">
                @foreach($transaksi->items as $item)
                <tr class="hover:bg-slate-50">
                    <td class="py-3 px-4">
                        <p class="font-semibold text-slate-900">{{ $item->nama_produk ?? $item->produk?->nama }}</p>
                    </td>
                    <td class="py-3 px-4 text-center">Rp {{ number_format($item->harga_satuan, 0, ',', '.') }}</td>
                    <td class="py-3 px-4 text-center font-bold text-slate-800">{{ $item->jumlah }}</td>
                    <td class="py-3 px-4 text-right font-bold text-slate-900">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        <div class="p-6 bg-slate-50 border-t border-slate-200 space-y-2 max-w-xs ml-auto text-sm">
            <div class="flex justify-between text-slate-600">
                <span>Subtotal</span>
                <span>Rp {{ number_format($transaksi->subtotal ?? $transaksi->total_bayar, 0, ',', '.') }}</span>
            </div>
            <div class="flex justify-between text-slate-600">
                <span>Diskon</span>
                <span>Rp {{ number_format($transaksi->diskon ?? 0, 0, ',', '.') }}</span>
            </div>
            <div class="flex justify-between text-base font-bold text-slate-900 pt-2 border-t border-slate-200">
                <span>Total Bayar</span>
                <span class="text-emerald-600">Rp {{ number_format($transaksi->total_bayar, 0, ',', '.') }}</span>
            </div>
            <div class="flex justify-between text-xs text-slate-500">
                <span>Dibayar</span>
                <span>Rp {{ number_format($transaksi->jumlah_dibayar ?? $transaksi->total_bayar, 0, ',', '.') }}</span>
            </div>
            <div class="flex justify-between text-xs text-slate-500">
                <span>Kembalian</span>
                <span>Rp {{ number_format($transaksi->kembalian ?? 0, 0, ',', '.') }}</span>
            </div>
        </div>
    </div>
</div>
@endsection
