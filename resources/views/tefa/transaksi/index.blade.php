@extends('layouts.app')

@section('title', 'Riwayat Transaksi Penjualan')

@section('content')
<div x-data="transaksiPage()" class="space-y-6">

    <!-- Page Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-xl font-bold text-slate-900">Riwayat Penjualan</h2>
            <p class="text-sm text-slate-500">Semua transaksi penjualan produk TEFa</p>
        </div>
        <div class="flex items-center gap-2">
            <template x-if="selectedIds.length > 0">
                <button @click="showBulkDeleteModal = true"
                    class="inline-flex items-center gap-2 px-4 py-2.5 bg-rose-600 hover:bg-rose-700 text-white font-semibold rounded-xl text-sm shadow-md shadow-rose-600/25 transition-all">
                    <i class="fa-solid fa-trash-can"></i> Hapus (<span x-text="selectedIds.length"></span>) Transaksi
                </button>
            </template>
            <a href="{{ route('tefa.kasir') }}" class="inline-flex items-center gap-2 px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-semibold rounded-xl text-sm shadow-md shadow-emerald-600/25 transition-all">
                <i class="fa-solid fa-cash-register"></i> Buka Kasir
            </a>
        </div>
    </div>

    <!-- Filter Bar -->
    <form method="GET" class="bg-white p-4 rounded-2xl border border-slate-200 shadow-sm flex flex-wrap gap-3 items-center">
        <input type="date" name="tanggal_mulai" value="{{ request('tanggal_mulai') }}"
            class="py-2 px-3 text-sm rounded-lg border border-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-500">
        <input type="date" name="tanggal_selesai" value="{{ request('tanggal_selesai') }}"
            class="py-2 px-3 text-sm rounded-lg border border-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-500">
        <select name="metode_pembayaran" class="py-2 px-3 text-sm rounded-lg border border-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">
            <option value="">Semua Metode</option>
            <option value="tunai" @selected(request('metode_pembayaran') == 'tunai')>Tunai</option>
            <option value="transfer" @selected(request('metode_pembayaran') == 'transfer')>Transfer</option>
            <option value="qris" @selected(request('metode_pembayaran') == 'qris')>QRIS</option>
        </select>
        <div class="flex items-center gap-1.5">
            <label class="text-xs font-semibold text-slate-500">Per Hal:</label>
            <select name="per_page" onchange="this.form.submit()" class="py-2 px-3 text-sm rounded-lg border border-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">
                <option value="10" @selected(request('per_page') == 10)>10</option>
                <option value="15" @selected(request('per_page') == 15)>15</option>
                <option value="20" @selected(request('per_page', 20) == 20)>20</option>
                <option value="25" @selected(request('per_page') == 25)>25</option>
                <option value="50" @selected(request('per_page') == 50)>50</option>
                <option value="100" @selected(request('per_page') == 100)>100</option>
            </select>
        </div>
        <button type="submit" class="px-4 py-2 bg-slate-800 text-white font-semibold rounded-lg text-sm hover:bg-slate-700 transition">Filter</button>
        <a href="{{ route('laporan.penjualan') }}" class="px-4 py-2 border border-blue-200 text-blue-700 font-semibold rounded-lg text-sm hover:bg-blue-50 flex items-center gap-2">
            <i class="fa-solid fa-file-chart-column"></i> Laporan Lengkap
        </a>
        @if(request()->hasAny(['tanggal_mulai', 'tanggal_selesai', 'metode_pembayaran', 'per_page']))
        <a href="{{ route('tefa.transaksi.index') }}" class="py-2 px-3 text-sm text-slate-500 hover:text-rose-600 font-medium">
            <i class="fa-solid fa-xmark mr-1"></i> Reset
        </a>
        @endif
    </form>

    <!-- Table -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead class="bg-slate-50 border-b border-slate-200">
                    <tr class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider">
                        <th class="py-3.5 px-4 w-10 text-center">
                            <input type="checkbox" @change="toggleSelectAll($event)" :checked="isAllSelected()"
                                class="rounded border-slate-300 text-blue-600 focus:ring-blue-500 cursor-pointer">
                        </th>
                        <th class="py-3.5 px-4">Kode</th>
                        <th class="py-3.5 px-4">Tanggal</th>
                        <th class="py-3.5 px-4">Kasir</th>
                        <th class="py-3.5 px-4">Customer</th>
                        <th class="py-3.5 px-4 text-center">Metode</th>
                        <th class="py-3.5 px-4 text-right">Total</th>
                        <th class="py-3.5 px-4 text-center">Status</th>
                        <th class="py-3.5 px-4 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-sm">
                    @forelse($transaksi as $t)
                    <tr class="hover:bg-slate-50 transition-colors" :class="selectedIds.includes({{ $t->id }}) ? 'bg-blue-50/50' : ''">
                        <td class="py-3.5 px-4 text-center">
                            <input type="checkbox" value="{{ $t->id }}" x-model.number="selectedIds"
                                class="rounded border-slate-300 text-blue-600 focus:ring-blue-500 cursor-pointer">
                        </td>
                        <td class="py-3.5 px-4">
                            <p class="font-bold text-slate-900 font-mono">{{ $t->kode_transaksi }}</p>
                            <p class="text-xs text-slate-400">{{ $t->items->count() }} item</p>
                        </td>
                        <td class="py-3.5 px-4 text-slate-600">
                            <p>{{ formatTanggal($t->tanggal) }}</p>
                            <p class="text-xs text-slate-400">{{ $t->waktu }}</p>
                        </td>
                        <td class="py-3.5 px-4 font-medium text-slate-700">{{ $t->kasir?->nama ?? '-' }}</td>
                        <td class="py-3.5 px-4 text-slate-600">{{ $t->customer_nama ?? 'Pelanggan Umum' }}</td>
                        <td class="py-3.5 px-4 text-center">
                            <span class="px-2.5 py-1 rounded-lg text-xs font-bold uppercase
                                {{ $t->metode_pembayaran == 'tunai' ? 'bg-slate-100 text-slate-700' : ($t->metode_pembayaran == 'qris' ? 'bg-purple-100 text-purple-700' : 'bg-blue-100 text-blue-700') }}">
                                {{ $t->metode_pembayaran }}
                            </span>
                        </td>
                        <td class="py-3.5 px-4 text-right font-bold text-slate-800">Rp {{ number_format($t->total_akhir, 0, ',', '.') }}</td>
                        <td class="py-3.5 px-4 text-center">
                            <span class="px-3 py-1 rounded-full text-xs font-bold uppercase {{ getStatusColor($t->status) }}">{{ $t->status }}</span>
                        </td>
                        <td class="py-3.5 px-4 text-center">
                            <div class="flex items-center justify-center gap-2">
                                <a href="{{ route('tefa.transaksi.show', $t->id) }}" title="Detail"
                                    class="w-8 h-8 rounded-lg bg-blue-100 text-blue-700 hover:bg-blue-200 flex items-center justify-center text-xs transition-colors">
                                    <i class="fa-solid fa-eye"></i>
                                </a>
                                <a href="{{ route('tefa.transaksi.cetak-struk', $t->id) }}" target="_blank" title="Cetak Struk"
                                    class="w-8 h-8 rounded-lg bg-slate-100 text-slate-700 hover:bg-slate-200 flex items-center justify-center text-xs transition-colors">
                                    <i class="fa-solid fa-print"></i>
                                </a>
                                <button title="Hapus Transaksi" 
                                    @click="openSingleDelete({{ $t->id }}, '{{ addslashes($t->kode_transaksi) }}')"
                                    class="w-8 h-8 rounded-lg bg-rose-100 text-rose-700 hover:bg-rose-200 flex items-center justify-center text-xs transition-colors">
                                    <i class="fa-solid fa-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="py-16 text-center text-slate-400">
                            <i class="fa-solid fa-receipt text-5xl mb-3 block opacity-20"></i>
                            <p class="text-sm">Belum ada data transaksi.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <!-- Pagination Footer -->
        <div class="px-6 py-4 border-t border-slate-100 flex flex-col sm:flex-row items-center justify-between gap-4 bg-slate-50/50">
            <div class="text-xs text-slate-500">
                Menampilkan <span class="font-semibold text-slate-800">{{ $transaksi->firstItem() ?? 0 }}</span>
                sampai <span class="font-semibold text-slate-800">{{ $transaksi->lastItem() ?? 0 }}</span>
                dari <span class="font-semibold text-slate-800">{{ $transaksi->total() }}</span> transaksi
            </div>
            <div>
                {{ $transaksi->links() }}
            </div>
        </div>
    </div>

    <!-- Single Delete Modal -->
    <div x-show="showSingleDeleteModal" x-cloak class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl w-full max-w-md shadow-2xl p-6" @click.stop>
            <div class="w-12 h-12 rounded-full bg-rose-100 text-rose-600 flex items-center justify-center mx-auto mb-4 text-xl">
                <i class="fa-solid fa-triangle-exclamation"></i>
            </div>
            <h3 class="text-lg font-bold text-slate-900 text-center mb-1">Hapus Transaksi</h3>
            <p class="text-sm text-slate-500 text-center mb-6">
                Apakah Anda yakin ingin menghapus data transaksi <strong class="text-slate-800" x-text="deleteItem.kode"></strong>? Data yang dihapus tidak dapat dikembalikan.
            </p>

            <form :action="`/tefa/transaksi/${deleteItem.id}`" method="POST" class="flex gap-3 justify-end">
                @csrf
                @method('DELETE')
                <button type="button" @click="showSingleDeleteModal = false" class="w-1/2 py-2.5 border border-slate-200 rounded-xl text-sm font-semibold text-slate-600 hover:bg-slate-50">
                    Batal
                </button>
                <button type="submit" class="w-1/2 py-2.5 bg-rose-600 hover:bg-rose-700 text-white font-semibold rounded-xl text-sm shadow-md shadow-rose-600/20">
                    Ya, Hapus
                </button>
            </form>
        </div>
    </div>

    <!-- Multi Delete Modal -->
    <div x-show="showBulkDeleteModal" x-cloak class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl w-full max-w-md shadow-2xl p-6" @click.stop>
            <div class="w-12 h-12 rounded-full bg-rose-100 text-rose-600 flex items-center justify-center mx-auto mb-4 text-xl">
                <i class="fa-solid fa-trash-can"></i>
            </div>
            <h3 class="text-lg font-bold text-slate-900 text-center mb-1">Hapus Multi Transaksi</h3>
            <p class="text-sm text-slate-500 text-center mb-6">
                Apakah Anda yakin ingin menghapus <strong class="text-slate-800"><span x-text="selectedIds.length"></span> transaksi</strong> yang dipilih? Tindakan ini tidak dapat dibatalkan.
            </p>

            <form action="{{ route('tefa.transaksi.bulk-destroy') }}" method="POST" class="space-y-4">
                @csrf
                @method('DELETE')
                <template x-for="id in selectedIds" :key="id">
                    <input type="hidden" name="ids[]" :value="id">
                </template>

                <div class="flex gap-3 justify-end">
                    <button type="button" @click="showBulkDeleteModal = false" class="w-1/2 py-2.5 border border-slate-200 rounded-xl text-sm font-semibold text-slate-600 hover:bg-slate-50">
                        Batal
                    </button>
                    <button type="submit" class="w-1/2 py-2.5 bg-rose-600 hover:bg-rose-700 text-white font-semibold rounded-xl text-sm shadow-md shadow-rose-600/20">
                        Ya, Hapus Semua
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>

<script>
function transaksiPage() {
    return {
        currentPageIds: [{{ $transaksi->pluck('id')->implode(',') }}],
        selectedIds: [],
        showSingleDeleteModal: false,
        deleteItem: { id: null, kode: '' },
        showBulkDeleteModal: false,

        toggleSelectAll(e) {
            if (e.target.checked) {
                this.selectedIds = [...new Set([...this.selectedIds, ...this.currentPageIds])];
            } else {
                this.selectedIds = this.selectedIds.filter(id => !this.currentPageIds.includes(id));
            }
        },
        isAllSelected() {
            return this.currentPageIds.length > 0 && this.currentPageIds.every(id => this.selectedIds.includes(id));
        },
        openSingleDelete(id, kode) {
            this.deleteItem = { id: id, kode: kode };
            this.showSingleDeleteModal = true;
        }
    };
}
</script>
@endsection
