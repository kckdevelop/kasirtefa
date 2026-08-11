@extends('layouts.app')

@section('title', 'Lisensi Aplikasi')

@section('content')
<div x-data="{
    showModal: false,
    editMode: false,
    deleteModal: false,
    bayarModal: false,
    deleteId: null,
    deleteUrl: '',
    bayarId: null,
    bayarUrl: '',
    bayarNama: '',
    bayarHarga: '',
    formattedHarga: '',
    form: {
        id: null,
        tipe: 'beli',
        nama_pembeli: '',
        email: '',
        telepon: '',
        nama_sekolah: '',
        harga: '',
        keterangan: '',
        status: 'aktif',
        tanggal_beli: '',
        tanggal_jatuh_tempo: '',
        tanggal_mulai: '',
        lama_sewa: '12',
    },
    bayarForm: {
        metode_pembayaran: 'tunai',
        tanggal_pembayaran: '{{ now()->format('Y-m-d') }}',
        catatan_pembayaran: '',
    },
    openAdd() {
        this.editMode = false;
        this.form = { id: null, tipe: 'beli', nama_pembeli: '', email: '', telepon: '', nama_sekolah: '', harga: '', keterangan: '', status: 'aktif', tanggal_beli: '{{ now()->format('Y-m-d') }}', tanggal_jatuh_tempo: '', tanggal_mulai: '{{ now()->format('Y-m-d') }}', lama_sewa: '12' };
        this.formattedHarga = '';
        this.showModal = true;
    },
    openEdit(item) {
        this.editMode = true;
        this.form = {
            id: item.id,
            tipe: item.tipe,
            nama_pembeli: item.nama_pembeli,
            email: item.email || '',
            telepon: item.telepon || '',
            nama_sekolah: item.nama_sekolah || '',
            harga: item.harga,
            keterangan: item.keterangan || '',
            status: item.status,
            tanggal_beli: item.tanggal_beli || '',
            tanggal_jatuh_tempo: item.tanggal_jatuh_tempo || '',
            tanggal_mulai: item.tanggal_mulai || '',
            lama_sewa: item.lama_sewa ? String(item.lama_sewa) : '12',
        };
        this.formattedHarga = item.harga ? this.formatHarga(item.harga) : '';
        this.showModal = true;
    },
    openDelete(id, url) {
        this.deleteId = id;
        this.deleteUrl = url;
        this.deleteModal = true;
    },
    openBayar(id, url, nama, harga) {
        this.bayarId = id;
        this.bayarUrl = url;
        this.bayarNama = nama;
        this.bayarHarga = harga;
        this.bayarForm = { metode_pembayaran: 'tunai', tanggal_pembayaran: '{{ now()->format('Y-m-d') }}', catatan_pembayaran: '' };
        this.bayarModal = true;
    },
    get actionUrl() {
        if (this.editMode) {
            return '/tefa/lisensi/' + this.form.id;
        }
        return '{{ route('tefa.lisensi.store') }}';
    },
    formatHarga(val) {
        if (!val) return '';
        let clean = String(val).replace(/\D/g, '');
        if (!clean) return '';
        return parseInt(clean).toLocaleString('id-ID');
    },
    onHargaInput(e) {
        let clean = e.target.value.replace(/\D/g, '');
        this.form.harga = clean;
        this.formattedHarga = clean ? parseInt(clean).toLocaleString('id-ID') : '';
    }
}" class="space-y-6">

    {{-- Flash message --}}
    @if(session('success'))
    <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)"
        x-transition:leave="transition ease-in duration-300" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
        class="flex items-center gap-3 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-2xl px-5 py-3.5 shadow-sm">
        <i class="fa-solid fa-circle-check text-emerald-500 text-lg"></i>
        <span class="text-sm font-semibold">{{ session('success') }}</span>
        <button @click="show=false" class="ml-auto text-emerald-400 hover:text-emerald-600"><i class="fa-solid fa-xmark"></i></button>
    </div>
    @endif

    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-xl font-bold text-slate-900">Lisensi Aplikasi</h1>
            <p class="text-sm text-slate-500 mt-0.5">Kelola pembelian dan langganan lisensi perangkat lunak</p>
        </div>
        <button @click="openAdd()"
            class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold px-4 py-2.5 rounded-xl shadow transition">
            <i class="fa-solid fa-plus"></i> Tambah Lisensi
        </button>
    </div>

    {{-- Summary Cards --}}
    @php
        $totalAktif = \App\Models\LisensiAplikasi::aktif()->count();
        $segera = \App\Models\LisensiAplikasi::mendekatiKadaluarsa(30)->count();
        $totalBeli = \App\Models\LisensiAplikasi::where('tipe','beli')->aktif()->count();
        $totalSewa = \App\Models\LisensiAplikasi::where('tipe','berlangganan')->aktif()->count();
        $belumBayar = \App\Models\LisensiAplikasi::where('status_pembayaran','belum_bayar')->count();
    @endphp
    <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
        <div class="bg-white rounded-2xl border border-slate-200 p-4 flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center">
                <i class="fa-solid fa-key text-lg"></i>
            </div>
            <div>
                <p class="text-[11px] font-semibold text-slate-400 uppercase">Total Aktif</p>
                <p class="text-xl font-bold text-slate-900">{{ $totalAktif }}</p>
            </div>
        </div>
        <div class="bg-white rounded-2xl border border-slate-200 p-4 flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center">
                <i class="fa-solid fa-triangle-exclamation text-lg"></i>
            </div>
            <div>
                <p class="text-[11px] font-semibold text-slate-400 uppercase">Segera Berakhir</p>
                <p class="text-xl font-bold text-amber-600">{{ $segera }}</p>
            </div>
        </div>
        <div class="bg-white rounded-2xl border border-slate-200 p-4 flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
                <i class="fa-solid fa-bag-shopping text-lg"></i>
            </div>
            <div>
                <p class="text-[11px] font-semibold text-slate-400 uppercase">Lisensi Beli</p>
                <p class="text-xl font-bold text-slate-900">{{ $totalBeli }}</p>
            </div>
        </div>
        <div class="bg-white rounded-2xl border border-slate-200 p-4 flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center">
                <i class="fa-solid fa-rotate text-lg"></i>
            </div>
            <div>
                <p class="text-[11px] font-semibold text-slate-400 uppercase">Berlangganan</p>
                <p class="text-xl font-bold text-slate-900">{{ $totalSewa }}</p>
            </div>
        </div>
        <div class="bg-white rounded-2xl border {{ $belumBayar > 0 ? 'border-orange-200 bg-orange-50' : 'border-slate-200' }} p-4 flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl {{ $belumBayar > 0 ? 'bg-orange-100 text-orange-600' : 'bg-slate-50 text-slate-400' }} flex items-center justify-center">
                <i class="fa-solid fa-clock text-lg"></i>
            </div>
            <div>
                <p class="text-[11px] font-semibold {{ $belumBayar > 0 ? 'text-orange-500' : 'text-slate-400' }} uppercase">Belum Bayar</p>
                <p class="text-xl font-bold {{ $belumBayar > 0 ? 'text-orange-600' : 'text-slate-900' }}">{{ $belumBayar }}</p>
            </div>
        </div>
    </div>

    {{-- Filter Bar --}}
    <form method="GET" action="{{ route('tefa.lisensi.index') }}"
        class="bg-white rounded-2xl border border-slate-200 p-4 flex flex-wrap gap-3 items-end">
        <div class="flex-1 min-w-[160px]">
            <label class="text-xs font-semibold text-slate-500 block mb-1">Cari</label>
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Nama, nomor lisensi, sekolah..."
                class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>
        <div>
            <label class="text-xs font-semibold text-slate-500 block mb-1">Tipe</label>
            <select name="tipe" class="border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="">Semua Tipe</option>
                <option value="beli" {{ request('tipe')=='beli'?'selected':'' }}>Beli</option>
                <option value="berlangganan" {{ request('tipe')=='berlangganan'?'selected':'' }}>Berlangganan</option>
            </select>
        </div>
        <div>
            <label class="text-xs font-semibold text-slate-500 block mb-1">Status Lisensi</label>
            <select name="status" class="border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="">Semua Status</option>
                <option value="aktif" {{ request('status')=='aktif'?'selected':'' }}>Aktif</option>
                <option value="kadaluarsa" {{ request('status')=='kadaluarsa'?'selected':'' }}>Kadaluarsa</option>
                <option value="dibatalkan" {{ request('status')=='dibatalkan'?'selected':'' }}>Dibatalkan</option>
            </select>
        </div>
        <div>
            <label class="text-xs font-semibold text-slate-500 block mb-1">Status Bayar</label>
            <select name="status_pembayaran" class="border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="">Semua</option>
                <option value="lunas" {{ request('status_pembayaran')=='lunas'?'selected':'' }}>Lunas</option>
                <option value="belum_bayar" {{ request('status_pembayaran')=='belum_bayar'?'selected':'' }}>Belum Bayar</option>
            </select>
        </div>
        <button type="submit" class="bg-slate-700 text-white px-4 py-2 rounded-lg text-sm font-semibold hover:bg-slate-800 transition">
            <i class="fa-solid fa-magnifying-glass mr-1"></i> Filter
        </button>
        @if(request()->hasAny(['search','tipe','status','status_pembayaran']))
        <a href="{{ route('tefa.lisensi.index') }}" class="text-sm text-slate-500 hover:text-red-600 font-medium py-2">
            <i class="fa-solid fa-xmark mr-1"></i> Reset
        </a>
        @endif
    </form>

    {{-- Table --}}
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-slate-100 text-slate-400 text-[11px] font-semibold uppercase tracking-wider bg-slate-50">
                        <th class="py-3 px-4">Nomor Lisensi</th>
                        <th class="py-3 px-4">Pembeli / Sekolah</th>
                        <th class="py-3 px-4">Tipe</th>
                        <th class="py-3 px-4">Harga</th>
                        <th class="py-3 px-4">Tanggal Akhir</th>
                        <th class="py-3 px-4">Sisa</th>
                        <th class="py-3 px-4">Status Lisensi</th>
                        <th class="py-3 px-4">Status Bayar</th>
                        <th class="py-3 px-4 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-sm">
                    @forelse($lisensi as $item)
                    @php
                        $tanggalAkhir = $item->tanggal_akhir;
                        $sisaHari = $item->sisa_hari;
                        $rowClass = '';
                        if ($item->is_expired) {
                            $rowClass = 'bg-red-50';
                        } elseif ($item->isMenujuBerakhir(30)) {
                            $rowClass = 'bg-amber-50';
                        }
                    @endphp
                    <tr class="hover:bg-slate-50 {{ $rowClass }}">
                        <td class="py-3 px-4">
                            <span class="font-mono font-bold text-slate-800 text-xs">{{ $item->nomor_lisensi }}</span>
                        </td>
                        <td class="py-3 px-4">
                            <p class="font-semibold text-slate-800">{{ $item->nama_pembeli }}</p>
                            @if($item->nama_sekolah)
                            <p class="text-xs text-slate-500">{{ $item->nama_sekolah }}</p>
                            @endif
                        </td>
                        <td class="py-3 px-4">
                            @if($item->tipe === 'beli')
                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-700">
                                <i class="fa-solid fa-bag-shopping text-[10px]"></i> Beli
                            </span>
                            @else
                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold bg-purple-100 text-purple-700">
                                <i class="fa-solid fa-rotate text-[10px]"></i> Berlangganan {{ $item->lama_sewa }} Bln
                            </span>
                            @endif
                        </td>
                        <td class="py-3 px-4 font-semibold text-slate-800">
                            Rp {{ number_format($item->harga, 0, ',', '.') }}
                        </td>
                        <td class="py-3 px-4 text-slate-600">
                            {{ $tanggalAkhir ? $tanggalAkhir->format('d M Y') : '-' }}
                        </td>
                        <td class="py-3 px-4">
                            @if($item->status !== 'aktif')
                                <span class="text-slate-400 text-xs">-</span>
                            @elseif($item->is_expired)
                                <span class="text-red-600 font-bold text-xs">Kadaluarsa</span>
                            @elseif($tanggalAkhir && $sisaHari !== null)
                                @if($sisaHari <= 30)
                                <span class="text-amber-600 font-bold text-xs">{{ $sisaHari }} hari lagi</span>
                                @else
                                <span class="text-emerald-600 font-semibold text-xs">{{ $sisaHari }} hari lagi</span>
                                @endif
                            @else
                                <span class="text-slate-400 text-xs">-</span>
                            @endif
                        </td>
                        <td class="py-3 px-4">
                            @php
                                $statusBadge = match($item->status) {
                                    'aktif' => 'bg-emerald-100 text-emerald-700',
                                    'kadaluarsa' => 'bg-red-100 text-red-700',
                                    'dibatalkan' => 'bg-slate-100 text-slate-600',
                                    default => 'bg-slate-100 text-slate-600'
                                };
                            @endphp
                            <span class="inline-block px-2.5 py-1 rounded-full text-[10px] font-bold uppercase {{ $statusBadge }}">
                                {{ $item->status }}
                            </span>
                        </td>
                        {{-- Status Pembayaran --}}
                        <td class="py-3 px-4">
                            @if($item->status_pembayaran === 'lunas')
                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-700">
                                <i class="fa-solid fa-check text-[9px]"></i> LUNAS
                            </span>
                            @if($item->tanggal_pembayaran)
                            <p class="text-[10px] text-slate-400 mt-0.5">{{ $item->tanggal_pembayaran->format('d M Y') }}</p>
                            @endif
                            @else
                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-bold bg-orange-100 text-orange-700">
                                <i class="fa-solid fa-clock text-[9px]"></i> BELUM BAYAR
                            </span>
                            @endif
                        </td>
                        <td class="py-3 px-4 text-center">
                            <div class="flex items-center justify-center gap-1.5">
                                {{-- Cetak --}}
                                <a href="{{ route('tefa.lisensi.cetak', $item) }}" target="_blank"
                                    class="w-8 h-8 flex items-center justify-center rounded-lg text-slate-500 hover:bg-slate-100 transition" title="Cetak Bukti">
                                    <i class="fa-solid fa-print text-xs"></i>
                                </a>
                                {{-- Tandai Lunas --}}
                                @if($item->status_pembayaran !== 'lunas')
                                <button type="button"
                                    @click="openBayar({{ $item->id }}, '{{ route('tefa.lisensi.tandai-lunas', $item) }}', '{{ addslashes($item->nama_pembeli) }}', 'Rp {{ number_format($item->harga, 0, ',', '.') }}')"
                                    class="w-8 h-8 flex items-center justify-center rounded-lg text-emerald-600 hover:bg-emerald-50 transition" title="Tandai Lunas">
                                    <i class="fa-solid fa-money-bill-wave text-xs"></i>
                                </button>
                                @endif
                                {{-- Edit --}}
                                <button type="button"
                                    @click="openEdit({{ json_encode([
                                        'id' => $item->id,
                                        'tipe' => $item->tipe,
                                        'nama_pembeli' => $item->nama_pembeli,
                                        'email' => $item->email,
                                        'telepon' => $item->telepon,
                                        'nama_sekolah' => $item->nama_sekolah,
                                        'harga' => $item->harga,
                                        'keterangan' => $item->keterangan,
                                        'status' => $item->status,
                                        'tanggal_beli' => $item->tanggal_beli?->format('Y-m-d'),
                                        'tanggal_jatuh_tempo' => $item->tanggal_jatuh_tempo?->format('Y-m-d'),
                                        'tanggal_mulai' => $item->tanggal_mulai?->format('Y-m-d'),
                                        'lama_sewa' => $item->lama_sewa,
                                    ]) }})"
                                    class="w-8 h-8 flex items-center justify-center rounded-lg text-blue-600 hover:bg-blue-50 transition" title="Edit">
                                    <i class="fa-solid fa-pen text-xs"></i>
                                </button>
                                {{-- Hapus --}}
                                <button type="button"
                                    @click="openDelete({{ $item->id }}, '{{ route('tefa.lisensi.destroy', $item) }}')"
                                    class="w-8 h-8 flex items-center justify-center rounded-lg text-red-500 hover:bg-red-50 transition" title="Hapus">
                                    <i class="fa-solid fa-trash text-xs"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="text-center py-12 text-slate-400">
                            <i class="fa-solid fa-key text-4xl mb-3 block opacity-30"></i>
                            Belum ada data lisensi
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($lisensi->hasPages())
        <div class="px-6 py-4 border-t border-slate-100">
            {{ $lisensi->links() }}
        </div>
        @endif
    </div>

    {{-- ─── Modal Tambah / Edit ─── --}}
    <div x-show="showModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" @click="showModal = false"></div>
        <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-2xl max-h-[90vh] overflow-y-auto"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100">

            <div class="flex items-center justify-between p-6 border-b border-slate-100">
                <div>
                    <h3 class="font-bold text-slate-900 text-base" x-text="editMode ? 'Edit Lisensi' : 'Tambah Lisensi Baru'"></h3>
                    <p class="text-xs text-slate-500 mt-0.5">Isi detail lisensi pembelian atau langganan</p>
                </div>
                <button @click="showModal = false" class="w-8 h-8 flex items-center justify-center rounded-lg text-slate-400 hover:bg-slate-100 transition">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <form :action="actionUrl" method="POST" class="p-6 space-y-5">
                @csrf
                <template x-if="editMode"><input type="hidden" name="_method" value="PUT"></template>

                {{-- Tipe --}}
                <div>
                    <label class="text-sm font-semibold text-slate-700 block mb-2">Tipe Lisensi <span class="text-red-500">*</span></label>
                    <div class="grid grid-cols-2 gap-3">
                        <label :class="form.tipe==='beli' ? 'border-blue-500 bg-blue-50 text-blue-700' : 'border-slate-200 text-slate-600'"
                            class="flex items-center gap-3 border-2 rounded-xl p-3 cursor-pointer transition">
                            <input type="radio" name="tipe" value="beli" x-model="form.tipe" class="accent-blue-600">
                            <div>
                                <span class="font-semibold text-sm block">Beli</span>
                                <span class="text-xs opacity-75">Pembayaran sekali, jatuh tempo tertentu</span>
                            </div>
                        </label>
                        <label :class="form.tipe==='berlangganan' ? 'border-purple-500 bg-purple-50 text-purple-700' : 'border-slate-200 text-slate-600'"
                            class="flex items-center gap-3 border-2 rounded-xl p-3 cursor-pointer transition">
                            <input type="radio" name="tipe" value="berlangganan" x-model="form.tipe" class="accent-purple-600">
                            <div>
                                <span class="font-semibold text-sm block">Berlangganan</span>
                                <span class="text-xs opacity-75">Pembayaran berkala per bulan</span>
                            </div>
                        </label>
                    </div>
                </div>

                {{-- Info Pembeli --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="text-xs font-semibold text-slate-600 block mb-1">Nama Pembeli <span class="text-red-500">*</span></label>
                        <input type="text" name="nama_pembeli" x-model="form.nama_pembeli" required
                            class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                            placeholder="Nama lengkap">
                    </div>
                    <div>
                        <label class="text-xs font-semibold text-slate-600 block mb-1">Nama Sekolah / Instansi</label>
                        <input type="text" name="nama_sekolah" x-model="form.nama_sekolah"
                            class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                            placeholder="Opsional">
                    </div>
                    <div>
                        <label class="text-xs font-semibold text-slate-600 block mb-1">Email</label>
                        <input type="email" name="email" x-model="form.email"
                            class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                            placeholder="email@contoh.com">
                    </div>
                    <div>
                        <label class="text-xs font-semibold text-slate-600 block mb-1">Telepon</label>
                        <input type="text" name="telepon" x-model="form.telepon"
                            class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                            placeholder="08xxxxxxxxxx">
                    </div>
                </div>

                {{-- Harga --}}
                <div>
                    <label class="text-xs font-semibold text-slate-600 block mb-1">Harga Lisensi <span class="text-red-500">*</span></label>
                    <div class="flex">
                        <span class="inline-flex items-center px-3 bg-slate-100 border border-r-0 border-slate-200 rounded-l-lg text-sm text-slate-600 font-semibold">Rp</span>
                        <input type="text" x-model="formattedHarga" @input="onHargaInput($event)" placeholder="0" required
                            class="flex-1 border border-slate-200 rounded-r-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <input type="hidden" name="harga" :value="form.harga">
                    </div>
                </div>

                {{-- Tanggal untuk Tipe Beli --}}
                <div x-show="form.tipe === 'beli'" class="grid grid-cols-1 md:grid-cols-2 gap-4 p-4 bg-emerald-50 rounded-xl border border-emerald-100">
                    <div class="md:col-span-2">
                        <p class="text-xs font-bold text-emerald-700 mb-3 flex items-center gap-2">
                            <i class="fa-solid fa-bag-shopping"></i> Detail Pembelian
                        </p>
                    </div>
                    <div>
                        <label class="text-xs font-semibold text-slate-600 block mb-1">Tanggal Beli</label>
                        <input type="date" name="tanggal_beli" x-model="form.tanggal_beli"
                            class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">
                    </div>
                    <div>
                        <label class="text-xs font-semibold text-slate-600 block mb-1">Tanggal Jatuh Tempo <span class="text-red-500">*</span></label>
                        <input type="date" name="tanggal_jatuh_tempo" x-model="form.tanggal_jatuh_tempo"
                            class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">
                    </div>
                </div>

                {{-- Tanggal untuk Tipe Berlangganan --}}
                <div x-show="form.tipe === 'berlangganan'" class="p-4 bg-purple-50 rounded-xl border border-purple-100">
                    <p class="text-xs font-bold text-purple-700 mb-3 flex items-center gap-2">
                        <i class="fa-solid fa-rotate"></i> Detail Berlangganan
                    </p>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="text-xs font-semibold text-slate-600 block mb-1">Tanggal Mulai <span class="text-red-500">*</span></label>
                            <input type="date" name="tanggal_mulai" x-model="form.tanggal_mulai"
                                class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-purple-500 bg-white">
                        </div>
                        <div>
                            <label class="text-xs font-semibold text-slate-600 block mb-1">Lama Berlangganan <span class="text-red-500">*</span></label>
                            <div class="grid grid-cols-3 gap-2">
                                @foreach([1,3,6,12,24,36] as $bln)
                                <label :class="form.lama_sewa == '{{ $bln }}' ? 'bg-purple-600 text-white border-purple-600' : 'bg-white text-slate-700 border-slate-200 hover:border-purple-400'"
                                    class="flex flex-col items-center justify-center border-2 rounded-xl p-2 cursor-pointer transition text-center">
                                    <input type="radio" name="lama_sewa" value="{{ $bln }}" x-model="form.lama_sewa" class="sr-only">
                                    <span class="font-bold text-sm">{{ $bln }}</span>
                                    <span class="text-[10px] opacity-75">{{ $bln == 1 ? 'bulan' : ($bln >= 12 ? ($bln/12).'th' : 'bulan') }}</span>
                                </label>
                                @endforeach
                            </div>
                            <input type="hidden" name="lama_sewa" :value="form.lama_sewa">
                        </div>
                        <div x-show="form.tanggal_mulai && form.lama_sewa" class="md:col-span-2">
                            <div class="bg-white rounded-lg border border-purple-200 p-3 text-xs text-purple-700">
                                <i class="fa-solid fa-calendar-check mr-1"></i>
                                Akan berakhir: <strong x-text="(function() {
                                    if (!form.tanggal_mulai || !form.lama_sewa) return '-';
                                    let d = new Date(form.tanggal_mulai);
                                    d.setMonth(d.getMonth() + parseInt(form.lama_sewa));
                                    return d.toLocaleDateString('id-ID', { day:'numeric', month:'long', year:'numeric' });
                                })()"></strong>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Status (edit saja) --}}
                <div x-show="editMode">
                    <label class="text-xs font-semibold text-slate-600 block mb-1">Status</label>
                    <select name="status" x-model="form.status"
                        class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="aktif">Aktif</option>
                        <option value="kadaluarsa">Kadaluarsa</option>
                        <option value="dibatalkan">Dibatalkan</option>
                    </select>
                </div>

                {{-- Keterangan --}}
                <div>
                    <label class="text-xs font-semibold text-slate-600 block mb-1">Keterangan</label>
                    <textarea name="keterangan" x-model="form.keterangan" rows="2"
                        class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 resize-none"
                        placeholder="Catatan tambahan (opsional)"></textarea>
                </div>

                <div class="flex items-center justify-end gap-3 pt-2 border-t border-slate-100">
                    <button type="button" @click="showModal = false"
                        class="px-5 py-2 text-sm font-semibold text-slate-600 hover:bg-slate-100 rounded-lg transition">Batal</button>
                    <button type="submit"
                        class="px-6 py-2 text-sm font-semibold bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition shadow">
                        <i class="fa-solid fa-save mr-1.5"></i>
                        <span x-text="editMode ? 'Simpan Perubahan' : 'Tambah Lisensi'"></span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- ─── Modal Tandai Lunas ─── --}}
    <div x-show="bayarModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" @click="bayarModal = false"></div>
        <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-md"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100">

            {{-- Header --}}
            <div class="flex items-center gap-4 p-6 border-b border-slate-100">
                <div class="w-12 h-12 rounded-xl bg-emerald-100 flex items-center justify-center flex-shrink-0">
                    <i class="fa-solid fa-money-bill-wave text-emerald-600 text-xl"></i>
                </div>
                <div>
                    <h3 class="font-bold text-slate-900 text-base">Konfirmasi Pembayaran</h3>
                    <p class="text-xs text-slate-500 mt-0.5">Dana akan masuk ke rekap transaksi penjualan</p>
                </div>
                <button @click="bayarModal = false" class="ml-auto w-8 h-8 flex items-center justify-center rounded-lg text-slate-400 hover:bg-slate-100 transition">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <form :action="bayarUrl" method="POST" class="p-6 space-y-4">
                @csrf

                {{-- Info Lisensi --}}
                <div class="bg-slate-50 rounded-xl p-4 flex items-center justify-between">
                    <div>
                        <p class="text-xs text-slate-500 font-medium">Pembeli</p>
                        <p class="font-bold text-slate-800 text-sm" x-text="bayarNama"></p>
                    </div>
                    <div class="text-right">
                        <p class="text-xs text-slate-500 font-medium">Total Bayar</p>
                        <p class="font-bold text-emerald-700 text-lg" x-text="bayarHarga"></p>
                    </div>
                </div>

                {{-- Tanggal Pembayaran --}}
                <div>
                    <label class="text-xs font-semibold text-slate-600 block mb-1.5">Tanggal Pembayaran <span class="text-red-500">*</span></label>
                    <input type="date" name="tanggal_pembayaran" x-model="bayarForm.tanggal_pembayaran" required
                        class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500">
                </div>

                {{-- Metode Pembayaran --}}
                <div>
                    <label class="text-xs font-semibold text-slate-600 block mb-1.5">Metode Pembayaran <span class="text-red-500">*</span></label>
                    <div class="grid grid-cols-3 gap-2">
                        @foreach(['tunai' => ['icon'=>'fa-money-bill','label'=>'Tunai','color'=>'emerald'], 'transfer' => ['icon'=>'fa-building-columns','label'=>'Transfer','color'=>'blue'], 'qris' => ['icon'=>'fa-qrcode','label'=>'QRIS','color'=>'purple']] as $metode => $cfg)
                        <label
                            :class="bayarForm.metode_pembayaran === '{{ $metode }}'
                                ? 'border-{{ $cfg['color'] }}-500 bg-{{ $cfg['color'] }}-50 text-{{ $cfg['color'] }}-700'
                                : 'border-slate-200 text-slate-600 hover:border-slate-300'"
                            class="flex flex-col items-center gap-1.5 border-2 rounded-xl p-3 cursor-pointer transition text-center">
                            <input type="radio" name="metode_pembayaran" value="{{ $metode }}" x-model="bayarForm.metode_pembayaran" class="sr-only">
                            <i class="fa-solid {{ $cfg['icon'] }} text-base"></i>
                            <span class="text-xs font-semibold">{{ $cfg['label'] }}</span>
                        </label>
                        @endforeach
                    </div>
                </div>

                {{-- Catatan --}}
                <div>
                    <label class="text-xs font-semibold text-slate-600 block mb-1.5">Catatan (Opsional)</label>
                    <input type="text" name="catatan_pembayaran" x-model="bayarForm.catatan_pembayaran"
                        class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500"
                        placeholder="No. referensi, ket. pembayaran...">
                </div>

                {{-- Info otomatis rekap --}}
                <div class="bg-blue-50 border border-blue-200 rounded-xl p-3 flex items-start gap-2.5">
                    <i class="fa-solid fa-circle-info text-blue-500 mt-0.5 flex-shrink-0"></i>
                    <p class="text-xs text-blue-700 leading-relaxed">
                        Pembayaran yang dikonfirmasi akan <strong>otomatis masuk ke rekap transaksi penjualan</strong> sebagai pemasukan.
                    </p>
                </div>

                <div class="flex gap-3 pt-1">
                    <button type="button" @click="bayarModal = false"
                        class="flex-1 py-2.5 border border-slate-200 rounded-xl text-sm font-semibold text-slate-600 hover:bg-slate-50 transition">Batal</button>
                    <button type="submit"
                        class="flex-1 py-2.5 bg-emerald-600 text-white rounded-xl text-sm font-semibold hover:bg-emerald-700 transition shadow">
                        <i class="fa-solid fa-check mr-1.5"></i> Konfirmasi Lunas
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- ─── Delete Confirmation Modal ─── --}}
    <div x-show="deleteModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" @click="deleteModal = false"></div>
        <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-sm p-6 text-center"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100">
            <div class="w-14 h-14 rounded-full bg-red-100 flex items-center justify-center mx-auto mb-4">
                <i class="fa-solid fa-trash text-red-600 text-xl"></i>
            </div>
            <h3 class="font-bold text-slate-900 text-base mb-1">Hapus Lisensi?</h3>
            <p class="text-sm text-slate-500 mb-6">Data lisensi ini akan dihapus secara permanen dan tidak dapat dikembalikan.</p>
            <div class="flex gap-3">
                <button type="button" @click="deleteModal = false"
                    class="flex-1 py-2.5 border border-slate-200 rounded-xl text-sm font-semibold text-slate-600 hover:bg-slate-50 transition">Batal</button>
                <form :action="deleteUrl" method="POST" class="flex-1">
                    @csrf
                    @method('DELETE')
                    <button type="submit"
                        class="w-full py-2.5 bg-red-600 text-white rounded-xl text-sm font-semibold hover:bg-red-700 transition shadow">Hapus</button>
                </form>
            </div>
        </div>
    </div>

</div>
@endsection
