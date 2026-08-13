@extends('layouts.app')

@section('title', 'Transaksi Sewa Gedung')

@section('content')
<div x-data="{
    showStatusModal: false,
    showDeleteModal: false,
    selectedId: null,
    selectedKode: '',
    deleteUrl: '',
    statusForm: { status_sewa: '', status_pembayaran: '', jumlah_dibayar: '' },
    statusUrl: '',

    openStatusModal(item) {
        this.statusForm = {
            status_sewa: item.status_sewa,
            status_pembayaran: item.status_pembayaran,
            jumlah_dibayar: item.jumlah_dibayar,
        };
        this.statusUrl = '/sewa/transaksi/' + item.id + '/status';
        this.showStatusModal = true;
    },

    openDeleteModal(url, kode) {
        this.deleteUrl = url;
        this.selectedKode = kode;
        this.showDeleteModal = true;
    }
}" class="space-y-6">

    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-xl font-bold text-slate-900">Transaksi Sewa Gedung / Lab</h2>
            <p class="text-sm text-slate-500">Kelola penyewaan gedung, fasilitas, status pembayaran</p>
        </div>
        <a href="{{ route('sewa.transaksi.create') }}"
            class="inline-flex items-center gap-2 px-4 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-xl text-sm shadow-md shadow-blue-600/25 transition-all">
            <i class="fa-solid fa-plus"></i> Buat Penyewaan
        </a>
    </div>

    {{-- Flash --}}
    @if(session('success'))
    <div class="flex items-center gap-3 px-4 py-3 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-xl text-sm">
        <i class="fa-solid fa-circle-check text-emerald-500"></i> {{ session('success') }}
    </div>
    @endif

    {{-- Summary Badges --}}
    @php
        $totalPenyewaan = $transaksiList->total();
        $statusCounts = \App\Models\SewaGedung::select('status_sewa', \Illuminate\Support\Facades\DB::raw('count(*) as total'))
            ->groupBy('status_sewa')->pluck('total', 'status_sewa');
    @endphp
    <div class="flex flex-wrap gap-3">
        @foreach(['booking' => ['label' => 'Booking', 'color' => 'amber'], 'disetujui' => ['label' => 'Disetujui', 'color' => 'blue'], 'berlangsung' => ['label' => 'Berlangsung', 'color' => 'violet'], 'selesai' => ['label' => 'Selesai', 'color' => 'emerald'], 'dibatalkan' => ['label' => 'Dibatalkan', 'color' => 'rose']] as $status => $cfg)
        <div class="flex items-center gap-2 px-4 py-2 bg-white border border-slate-200 rounded-xl shadow-sm text-sm">
            <span class="px-2 py-0.5 bg-{{ $cfg['color'] }}-100 text-{{ $cfg['color'] }}-700 rounded-md text-xs font-bold">{{ $statusCounts[$status] ?? 0 }}</span>
            <span class="font-medium text-slate-700">{{ $cfg['label'] }}</span>
        </div>
        @endforeach
    </div>

    {{-- Filter --}}
    <form method="GET" class="flex flex-wrap gap-3 items-center">
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari kode/nama penyewa..."
            class="flex-1 min-w-[200px] px-4 py-2.5 text-sm border border-slate-200 rounded-xl bg-white focus:outline-none focus:ring-2 focus:ring-blue-500">
        <select name="status_sewa" class="px-4 py-2.5 text-sm border border-slate-200 rounded-xl bg-white focus:outline-none focus:ring-2 focus:ring-blue-500">
            <option value="">Semua Status Sewa</option>
            @foreach(['booking' => 'Booking', 'disetujui' => 'Disetujui', 'berlangsung' => 'Berlangsung', 'selesai' => 'Selesai', 'dibatalkan' => 'Dibatalkan'] as $val => $lbl)
            <option value="{{ $val }}" {{ request('status_sewa') == $val ? 'selected' : '' }}>{{ $lbl }}</option>
            @endforeach
        </select>
        <select name="status_pembayaran" class="px-4 py-2.5 text-sm border border-slate-200 rounded-xl bg-white focus:outline-none focus:ring-2 focus:ring-blue-500">
            <option value="">Semua Pembayaran</option>
            <option value="belum_bayar" {{ request('status_pembayaran') == 'belum_bayar' ? 'selected' : '' }}>Belum Bayar</option>
            <option value="dp" {{ request('status_pembayaran') == 'dp' ? 'selected' : '' }}>DP</option>
            <option value="lunas" {{ request('status_pembayaran') == 'lunas' ? 'selected' : '' }}>Lunas</option>
        </select>
        <input type="date" name="tanggal_mulai" value="{{ request('tanggal_mulai') }}" class="px-4 py-2.5 text-sm border border-slate-200 rounded-xl bg-white focus:outline-none focus:ring-2 focus:ring-blue-500">
        <input type="date" name="tanggal_selesai" value="{{ request('tanggal_selesai') }}" class="px-4 py-2.5 text-sm border border-slate-200 rounded-xl bg-white focus:outline-none focus:ring-2 focus:ring-blue-500">
        <button type="submit" class="px-4 py-2.5 bg-slate-800 text-white text-sm rounded-xl hover:bg-slate-700 transition-all">
            <i class="fa-solid fa-search mr-1"></i> Filter
        </button>
        <a href="{{ route('sewa.transaksi.index') }}" class="px-4 py-2.5 bg-slate-100 text-slate-600 text-sm rounded-xl hover:bg-slate-200 transition-all">Reset</a>
    </form>

    {{-- Table --}}
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 border-b border-slate-200">
                    <tr class="text-left text-xs text-slate-500 font-semibold">
                        <th class="px-5 py-3.5">Kode Sewa</th>
                        <th class="px-5 py-3.5">Gedung / Lab</th>
                        <th class="px-5 py-3.5">Penyewa</th>
                        <th class="px-5 py-3.5">Tanggal Sewa</th>
                        <th class="px-5 py-3.5">Total Biaya</th>
                        <th class="px-5 py-3.5">Status Sewa</th>
                        <th class="px-5 py-3.5">Pembayaran</th>
                        <th class="px-5 py-3.5 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($transaksiList as $trx)
                    <tr class="hover:bg-slate-50 transition-colors">
                        <td class="px-5 py-3.5">
                            <span class="text-xs font-mono font-bold text-blue-700 bg-blue-50 px-2 py-1 rounded-lg">{{ $trx->kode_sewa }}</span>
                        </td>
                        <td class="px-5 py-3.5">
                            <p class="font-semibold text-slate-800">{{ $trx->gedung?->nama_gedung }}</p>
                            <p class="text-xs text-slate-400">{{ $trx->gedung?->kode_gedung }}</p>
                        </td>
                        <td class="px-5 py-3.5">
                            <p class="font-medium text-slate-800">{{ $trx->nama_penyewa }}</p>
                            <p class="text-xs text-slate-400">{{ $trx->instansi_penyewa ?? $trx->telepon_penyewa ?? '-' }}</p>
                        </td>
                        <td class="px-5 py-3.5">
                            <p class="text-slate-700">{{ formatTanggal($trx->tanggal_mulai, 'd/m/Y') }}</p>
                            <p class="text-xs text-slate-400">s/d {{ formatTanggal($trx->tanggal_selesai, 'd/m/Y') }}</p>
                            <p class="text-xs font-semibold text-blue-600">{{ $trx->lama_sewa }} hari</p>
                        </td>
                        <td class="px-5 py-3.5">
                            <p class="font-bold text-slate-800">{{ formatRupiah($trx->total_biaya) }}</p>
                            <p class="text-xs text-slate-400">Dibayar: {{ formatRupiah($trx->jumlah_dibayar) }}</p>
                        </td>
                        <td class="px-5 py-3.5">
                            @php $statusSewaMap = ['booking' => ['label'=>'Booking','cls'=>'bg-amber-100 text-amber-700 border-amber-200'], 'disetujui' => ['label'=>'Disetujui','cls'=>'bg-blue-100 text-blue-700 border-blue-200'], 'berlangsung' => ['label'=>'Berlangsung','cls'=>'bg-violet-100 text-violet-700 border-violet-200'], 'selesai' => ['label'=>'Selesai','cls'=>'bg-emerald-100 text-emerald-700 border-emerald-200'], 'dibatalkan' => ['label'=>'Dibatalkan','cls'=>'bg-rose-100 text-rose-700 border-rose-200']]; @endphp
                            <span class="text-xs px-2 py-1 rounded-full font-semibold border {{ $statusSewaMap[$trx->status_sewa]['cls'] ?? 'bg-slate-100 text-slate-700 border-slate-200' }}">
                                {{ $statusSewaMap[$trx->status_sewa]['label'] ?? ucfirst($trx->status_sewa) }}
                            </span>
                        </td>
                        <td class="px-5 py-3.5">
                            @php $statusByrMap = ['belum_bayar' => ['label'=>'Belum Bayar','cls'=>'bg-rose-100 text-rose-700 border-rose-200'], 'dp' => ['label'=>'DP','cls'=>'bg-amber-100 text-amber-700 border-amber-200'], 'lunas' => ['label'=>'Lunas','cls'=>'bg-emerald-100 text-emerald-700 border-emerald-200']]; @endphp
                            <span class="text-xs px-2 py-1 rounded-full font-semibold border {{ $statusByrMap[$trx->status_pembayaran]['cls'] ?? 'bg-slate-100 text-slate-700 border-slate-200' }}">
                                {{ $statusByrMap[$trx->status_pembayaran]['label'] ?? ucfirst($trx->status_pembayaran) }}
                            </span>
                        </td>
                        <td class="px-5 py-3.5 text-right">
                            <div class="flex items-center justify-end gap-1.5">
                                <a href="{{ route('sewa.transaksi.show', $trx->id) }}"
                                    class="p-2 text-slate-600 bg-slate-100 hover:bg-slate-200 rounded-lg text-xs transition-all" title="Detail">
                                    <i class="fa-solid fa-eye"></i>
                                </a>
                                <button @click="openStatusModal({{ json_encode($trx) }})"
                                    class="p-2 text-amber-600 bg-amber-50 hover:bg-amber-100 rounded-lg text-xs transition-all" title="Update Status">
                                    <i class="fa-solid fa-pen-to-square"></i>
                                </button>
                                <a href="{{ route('sewa.transaksi.cetak', $trx->id) }}" target="_blank"
                                    class="p-2 text-emerald-600 bg-emerald-50 hover:bg-emerald-100 rounded-lg text-xs transition-all" title="Cetak">
                                    <i class="fa-solid fa-print"></i>
                                </a>
                                <button @click="openDeleteModal('{{ route('sewa.transaksi.destroy', $trx->id) }}', '{{ $trx->kode_sewa }}')"
                                    class="p-2 text-rose-600 bg-rose-50 hover:bg-rose-100 rounded-lg text-xs transition-all" title="Hapus">
                                    <i class="fa-solid fa-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="px-5 py-16 text-center text-slate-400">
                            <i class="fa-solid fa-calendar-xmark text-4xl mb-3 block"></i>
                            Belum ada transaksi penyewaan gedung.
                            <br><a href="{{ route('sewa.transaksi.create') }}" class="text-blue-600 underline mt-2 inline-block">Buat Penyewaan Sekarang</a>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Pagination --}}
    {{ $transaksiList->links() }}

    {{-- ── Modal Update Status ── --}}
    <div x-show="showStatusModal" x-transition class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/40 backdrop-blur-sm" style="display:none">
        <div @click.outside="showStatusModal = false" class="bg-white rounded-2xl shadow-2xl w-full max-w-md">
            <div class="flex items-center justify-between p-6 border-b border-slate-100">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-xl bg-amber-100 flex items-center justify-center text-amber-600"><i class="fa-solid fa-pen-to-square"></i></div>
                    <h3 class="font-bold text-slate-800">Update Status Penyewaan</h3>
                </div>
                <button @click="showStatusModal = false" class="text-slate-400 hover:text-slate-600 p-1 rounded-lg"><i class="fa-solid fa-xmark text-lg"></i></button>
            </div>
            <form method="POST" :action="statusUrl" class="p-6 space-y-4">
                @csrf @method('PUT')
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1.5">Status Sewa</label>
                    <select name="status_sewa" x-model="statusForm.status_sewa" class="w-full px-3 py-2.5 text-sm border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="booking">Booking</option>
                        <option value="disetujui">Disetujui</option>
                        <option value="berlangsung">Berlangsung</option>
                        <option value="selesai">Selesai</option>
                        <option value="dibatalkan">Dibatalkan</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1.5">Status Pembayaran</label>
                    <select name="status_pembayaran" x-model="statusForm.status_pembayaran" class="w-full px-3 py-2.5 text-sm border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="belum_bayar">Belum Bayar</option>
                        <option value="dp">Uang Muka (DP)</option>
                        <option value="lunas">Lunas</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1.5">Jumlah Dibayar</label>
                    <div class="relative">
                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm">Rp</span>
                        <input type="number" name="jumlah_dibayar" x-model="statusForm.jumlah_dibayar" min="0"
                            class="w-full pl-10 pr-3 py-2.5 text-sm border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                </div>
                <div class="flex justify-end gap-3 pt-2 border-t border-slate-100">
                    <button type="button" @click="showStatusModal = false" class="px-4 py-2 text-sm text-slate-600 bg-slate-100 hover:bg-slate-200 rounded-xl transition-all">Batal</button>
                    <button type="submit" class="px-5 py-2 text-sm font-semibold text-white bg-amber-500 hover:bg-amber-600 rounded-xl transition-all shadow-md shadow-amber-500/25">Update Status</button>
                </div>
            </form>
        </div>
    </div>

    {{-- ── Modal Delete ── --}}
    <div x-show="showDeleteModal" x-transition class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/40 backdrop-blur-sm" style="display:none">
        <div @click.outside="showDeleteModal = false" class="bg-white rounded-2xl shadow-2xl w-full max-w-md p-6">
            <div class="text-center mb-5">
                <div class="w-16 h-16 rounded-full bg-rose-100 flex items-center justify-center mx-auto mb-3">
                    <i class="fa-solid fa-trash text-rose-600 text-2xl"></i>
                </div>
                <h3 class="font-bold text-slate-800 text-lg">Hapus Transaksi?</h3>
                <p class="text-slate-500 text-sm mt-1">Transaksi <strong x-text="selectedKode"></strong> akan dihapus permanen.</p>
            </div>
            <form method="POST" :action="deleteUrl">
                @csrf @method('DELETE')
                <div class="flex gap-3">
                    <button type="button" @click="showDeleteModal = false" class="flex-1 px-4 py-2.5 text-sm text-slate-600 bg-slate-100 hover:bg-slate-200 rounded-xl transition-all font-medium">Batal</button>
                    <button type="submit" class="flex-1 px-4 py-2.5 text-sm font-semibold text-white bg-rose-600 hover:bg-rose-700 rounded-xl transition-all">Hapus</button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
