@extends('layouts.app')

@section('title', 'Stok Keluar TEFa')

@section('content')
<div x-data="{ showModal: false }" class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-xl font-bold text-slate-900">Stok Keluar TEFa</h2>
            <p class="text-sm text-slate-500">Pencatatan pengeluaran stok non-penjualan (rusak, expired, pemakaian internal)</p>
        </div>
        <button @click="showModal = true"
            class="inline-flex items-center gap-2 px-4 py-2.5 bg-rose-600 hover:bg-rose-700 text-white font-semibold rounded-xl text-sm shadow-md shadow-rose-600/25 transition-all">
            <i class="fa-solid fa-minus"></i> Catat Stok Keluar
        </button>
    </div>

    <!-- Table -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead class="bg-slate-50 border-b border-slate-200">
                    <tr class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider">
                        <th class="py-3.5 px-4">Tanggal & Ref</th>
                        <th class="py-3.5 px-4">Produk</th>
                        <th class="py-3.5 px-4 text-center">Jumlah</th>
                        <th class="py-3.5 px-4">Alasan</th>
                        <th class="py-3.5 px-4">Keterangan</th>
                        <th class="py-3.5 px-4">Petugas</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-sm">
                    @forelse($stokKeluar as $item)
                    <tr class="hover:bg-slate-50">
                        <td class="py-3.5 px-4">
                            <p class="font-semibold text-slate-900">{{ $item->tanggal ? $item->tanggal->format('d M Y') : '-' }}</p>
                            <p class="text-xs font-mono text-slate-400">{{ $item->nomor_referensi ?? '-' }}</p>
                        </td>
                        <td class="py-3.5 px-4 font-semibold text-slate-800">{{ $item->produk?->nama }}</td>
                        <td class="py-3.5 px-4 text-center">
                            <span class="px-2.5 py-1 rounded-full bg-rose-100 text-rose-800 font-bold text-xs">
                                -{{ $item->jumlah }} {{ $item->produk?->satuan }}
                            </span>
                        </td>
                        <td class="py-3.5 px-4">
                            <span class="px-2 py-0.5 rounded bg-slate-100 text-slate-700 text-xs font-semibold uppercase">{{ $item->alasan }}</span>
                        </td>
                        <td class="py-3.5 px-4 text-slate-500 text-xs">{{ $item->catatan ?? '-' }}</td>
                        <td class="py-3.5 px-4 text-slate-600 text-xs">{{ $item->creator?->nama ?? $item->creator?->name }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="py-12 text-center text-slate-400">Belum ada catatan stok keluar.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($stokKeluar->hasPages())
        <div class="px-4 py-3 border-t border-slate-100">{{ $stokKeluar->links() }}</div>
        @endif
    </div>

    <!-- Modal Form -->
    <div x-show="showModal" x-cloak class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl w-full max-w-lg shadow-2xl p-6" @click.stop>
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-bold text-slate-900">Catat Stok Keluar Baru</h3>
                <button @click="showModal = false" class="text-slate-400 hover:text-slate-600"><i class="fa-solid fa-times text-xl"></i></button>
            </div>
            <form action="{{ route('tefa.stok-keluar.store') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Pilih Produk *</label>
                    <select name="produk_id" required class="w-full px-3 py-2 rounded-xl border border-slate-200 text-sm bg-white">
                        <option value="">-- Pilih Produk --</option>
                        @foreach($produk as $p)
                        <option value="{{ $p->id }}">{{ $p->nama }} (Stok: {{ $p->stok }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Jumlah Keluar *</label>
                        <input type="number" name="jumlah" required min="1" class="w-full px-3 py-2 rounded-xl border border-slate-200 text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Tanggal *</label>
                        <input type="date" name="tanggal" value="{{ date('Y-m-d') }}" required class="w-full px-3 py-2 rounded-xl border border-slate-200 text-sm">
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Alasan Keluar *</label>
                    <select name="alasan" required class="w-full px-3 py-2 rounded-xl border border-slate-200 text-sm bg-white">
                        <option value="rusak">Rusak</option>
                        <option value="kadaluarsa">Kadaluarsa / Expired</option>
                        <option value="internal">Pemakaian Internal / Praktek</option>
                        <option value="hilang">Hilang</option>
                        <option value="lainnya">Lainnya</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Catatan / Keterangan</label>
                    <textarea name="catatan" rows="2" placeholder="Detail alasan pengeluaran stok..." class="w-full px-3 py-2 rounded-xl border border-slate-200 text-sm resize-none"></textarea>
                </div>
                <div class="flex gap-3 justify-end pt-3 border-t border-slate-100">
                    <button type="button" @click="showModal = false" class="px-4 py-2 border border-slate-200 rounded-xl text-sm font-semibold text-slate-600">Batal</button>
                    <button type="submit" class="px-4 py-2 bg-rose-600 text-white font-semibold rounded-xl text-sm hover:bg-rose-700">Simpan Stok Keluar</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
