@extends('layouts.app')

@section('title', 'Daftar Alat & Barang')

@section('content')
<div x-data="{
    showModal: false,
    editMode: false,
    deleteModal: false,
    deleteUrl: '',
    deleteNama: '',
    form: {
        id: null,
        nama: '',
        kategori_alat_id: '',
        merek: '',
        tipe: '',
        serial_number: '',
        tahun_perolehan: '',
        status: 'aktif',
        status_ketersediaan: 'tersedia',
        jumlah_baik: 0,
        jumlah_cukup: 0,
        jumlah_rusak_ringan: 0,
        jumlah_rusak_berat: 0,
        jumlah_hilang: 0,
        satuan: 'unit',
        lokasi_penyimpanan: '',
        harga_perolehan: '',
        sumber_perolehan: '',
        deskripsi: '',
    },
    get jumlahTotal() {
        return (parseInt(this.form.jumlah_baik) || 0)
             + (parseInt(this.form.jumlah_cukup) || 0)
             + (parseInt(this.form.jumlah_rusak_ringan) || 0)
             + (parseInt(this.form.jumlah_rusak_berat) || 0)
             + (parseInt(this.form.jumlah_hilang) || 0);
    },
    get jumlahTersedia() {
        return (parseInt(this.form.jumlah_baik) || 0)
             + (parseInt(this.form.jumlah_cukup) || 0);
    },
    openAdd() {
        this.editMode = false;
        this.form = { id: null, nama: '', kategori_alat_id: '', merek: '', tipe: '', serial_number: '',
            tahun_perolehan: '', status: 'aktif', status_ketersediaan: 'tersedia',
            jumlah_baik: 0, jumlah_cukup: 0, jumlah_rusak_ringan: 0, jumlah_rusak_berat: 0, jumlah_hilang: 0,
            satuan: 'unit', lokasi_penyimpanan: '', harga_perolehan: '', sumber_perolehan: '', deskripsi: '' };
        this.showModal = true;
    },
    openEdit(item) {
        this.editMode = true;
        let jBaik = parseInt(item.jumlah_baik) || 0;
        let jCukup = parseInt(item.jumlah_cukup) || 0;
        let jRRingan = parseInt(item.jumlah_rusak_ringan) || 0;
        let jRBerat = parseInt(item.jumlah_rusak_berat) || 0;
        let jHilang = parseInt(item.jumlah_hilang) || 0;

        if (jBaik === 0 && jCukup === 0 && jRRingan === 0 && jRBerat === 0 && jHilang === 0) {
            const total = parseInt(item.jumlah_total) || 1;
            if (item.kondisi === 'cukup') jCukup = total;
            else if (item.kondisi === 'rusak_ringan') jRRingan = total;
            else if (item.kondisi === 'rusak_berat') jRBerat = total;
            else jBaik = total;
        }

        this.form = {
            ...item,
            jumlah_baik: jBaik,
            jumlah_cukup: jCukup,
            jumlah_rusak_ringan: jRRingan,
            jumlah_rusak_berat: jRBerat,
            jumlah_hilang: jHilang
        };
        this.showModal = true;
    },
    openDelete(url, nama) {
        this.deleteUrl = url;
        this.deleteNama = nama;
        this.deleteModal = true;
    },
    get actionUrl() {
        return this.editMode ? '/alat/daftar/' + this.form.id : '{{ route('alat.daftar.store') }}';
    }
}" class="space-y-6">

    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-xl font-bold text-slate-900">Daftar Alat & Barang</h2>
            <p class="text-sm text-slate-500">Kelola inventaris peralatan dan barang TEFa</p>
        </div>
        <button @click="openAdd()"
            class="inline-flex items-center gap-2 px-4 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-xl text-sm shadow-md shadow-blue-600/25 transition-all">
            <i class="fa-solid fa-plus"></i> Tambah Alat
        </button>
    </div>

    {{-- Statistics Cards --}}
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
        @php
            $totalAlat  = $alat->total();
            $totalBaik  = \App\Models\Alat::sum(\Illuminate\Support\Facades\DB::raw('COALESCE(jumlah_baik, 0) + COALESCE(jumlah_cukup, 0)'));
            $totalRusak = \App\Models\Alat::sum(\Illuminate\Support\Facades\DB::raw('COALESCE(jumlah_rusak_ringan, 0) + COALESCE(jumlah_rusak_berat, 0)'));
            $totalHilang = \App\Models\Alat::sum('jumlah_hilang');
        @endphp
        <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-sm">
            <p class="text-xs text-slate-500 font-medium mb-1">Total Alat</p>
            <p class="text-2xl font-bold text-slate-900">{{ $totalAlat }}</p>
        </div>
        <div class="bg-white p-4 rounded-2xl border border-emerald-200 shadow-sm bg-emerald-50/20">
            <p class="text-xs text-emerald-600 font-medium mb-1">Kondisi Baik</p>
            <p class="text-2xl font-bold text-emerald-700">{{ number_format($totalBaik) }}</p>
        </div>
        <div class="bg-white p-4 rounded-2xl border border-amber-200 shadow-sm bg-amber-50/20">
            <p class="text-xs text-amber-600 font-medium mb-1">Kondisi Rusak</p>
            <p class="text-2xl font-bold text-amber-700">{{ number_format($totalRusak) }}</p>
        </div>
        <div class="bg-white p-4 rounded-2xl border border-rose-200 shadow-sm bg-rose-50/20">
            <p class="text-xs text-rose-600 font-medium mb-1">Kondisi Hilang</p>
            <p class="text-2xl font-bold text-rose-700">{{ number_format($totalHilang) }}</p>
        </div>
    </div>

    {{-- Filter Bar --}}
    <form method="GET" class="bg-white p-4 rounded-2xl border border-slate-200 shadow-sm flex flex-wrap gap-3">
        <div class="flex-1 min-w-48 relative">
            <i class="fa-solid fa-search absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm"></i>
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama alat atau kode..."
                class="w-full pl-9 pr-4 py-2 text-sm rounded-lg border border-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>
        <select name="kategori_id" class="py-2 px-3 text-sm rounded-lg border border-slate-200 bg-white focus:outline-none focus:ring-2 focus:ring-blue-500">
            <option value="">Semua Kategori</option>
            @foreach($kategori as $k)
            <option value="{{ $k->id }}" @selected(request('kategori_id') == $k->id)>{{ $k->nama }}</option>
            @endforeach
        </select>
        <select name="status" class="py-2 px-3 text-sm rounded-lg border border-slate-200 bg-white focus:outline-none focus:ring-2 focus:ring-blue-500">
            <option value="">Semua Status</option>
            <option value="tersedia" @selected(request('status') == 'tersedia')>Tersedia</option>
            <option value="dipinjam" @selected(request('status') == 'dipinjam')>Dipinjam</option>
            <option value="dalam_perbaikan" @selected(request('status') == 'dalam_perbaikan')>Dalam Perbaikan</option>
            <option value="dikeluarkan" @selected(request('status') == 'dikeluarkan')>Dikeluarkan</option>
        </select>
        <button type="submit" class="px-4 py-2 bg-slate-800 text-white font-semibold rounded-lg text-sm hover:bg-slate-700 transition">Filter</button>
        @if(request()->hasAny(['search','kategori_id','status']))
        <a href="{{ route('alat.daftar.index') }}" class="py-2 px-3 text-sm text-slate-500 hover:text-red-600 font-medium">
            <i class="fa-solid fa-xmark mr-1"></i> Reset
        </a>
        @endif
    </form>

    {{-- Table --}}
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead class="bg-slate-50 border-b border-slate-200">
                    <tr class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider">
                        <th class="py-3.5 px-4">Alat / Barang</th>
                        <th class="py-3.5 px-4">Kategori</th>
                        <th class="py-3.5 px-4">Kode</th>
                        <th class="py-3.5 px-4 text-center">Stok Total</th>
                        <th class="py-3.5 px-4 text-center">Tersedia</th>
                        <th class="py-3.5 px-4 text-center text-emerald-700">Baik</th>
                        <th class="py-3.5 px-4 text-center text-amber-700">Rusak</th>
                        <th class="py-3.5 px-4 text-center text-rose-700">Hilang</th>
                        <th class="py-3.5 px-4 text-center">Status</th>
                        <th class="py-3.5 px-4 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-sm">
                    @forelse($alat as $a)
                    @php
                        $jBaik = ($a->jumlah_baik ?? 0) + ($a->jumlah_cukup ?? 0);
                        if ($jBaik == 0 && ($a->jumlah_total ?? 0) > 0 && ($a->jumlah_rusak_ringan ?? 0) == 0 && ($a->jumlah_rusak_berat ?? 0) == 0 && ($a->jumlah_hilang ?? 0) == 0) {
                            $jBaik = $a->jumlah_total ?? 1;
                        }
                        $jRusak = ($a->jumlah_rusak_ringan ?? 0) + ($a->jumlah_rusak_berat ?? 0);
                        $jHilang = $a->jumlah_hilang ?? 0;
                    @endphp
                    <tr class="hover:bg-slate-50 transition-colors">
                        <td class="py-3.5 px-4">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-lg bg-slate-100 overflow-hidden flex-shrink-0">
                                    @if($a->foto)
                                    <img src="{{ asset('storage/'.$a->foto) }}" class="w-full h-full object-cover" alt="{{ $a->nama }}">
                                    @else
                                    <div class="w-full h-full flex items-center justify-center text-slate-400">
                                        <i class="fa-solid fa-wrench"></i>
                                    </div>
                                    @endif
                                </div>
                                <div>
                                    <p class="font-semibold text-slate-900">{{ $a->nama }}</p>
                                    <p class="text-xs text-slate-400">
                                        {{ $a->merek }}{{ $a->tipe ? ' — '.$a->tipe : '' }}
                                    </p>
                                </div>
                            </div>
                        </td>
                        <td class="py-3.5 px-4">
                            <span class="px-2.5 py-1 rounded-lg bg-indigo-50 text-indigo-700 text-xs font-semibold">
                                {{ $a->kategori?->nama ?? '-' }}
                            </span>
                        </td>
                        <td class="py-3.5 px-4 font-mono text-xs text-slate-600">{{ $a->kode_alat }}</td>
                        <td class="py-3.5 px-4 text-center font-bold text-slate-800">
                            {{ $a->jumlah_total }} <span class="text-xs text-slate-400 font-normal">{{ $a->satuan }}</span>
                        </td>
                        <td class="py-3.5 px-4 text-center font-bold text-emerald-700">
                            {{ $a->jumlah_tersedia ?? 0 }} <span class="text-xs text-slate-400 font-normal">{{ $a->satuan }}</span>
                        </td>
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
                            @php
                                $sk = $a->status_ketersediaan ?? 'tersedia';
                                $skColor = match($sk) {
                                    'tersedia'       => 'bg-emerald-100 text-emerald-700',
                                    'dipinjam'       => 'bg-blue-100 text-blue-700',
                                    'dalam_perbaikan'=> 'bg-amber-100 text-amber-700',
                                    'dikeluarkan'    => 'bg-slate-100 text-slate-600',
                                    default          => 'bg-slate-100 text-slate-500',
                                };
                            @endphp
                            <span class="px-3 py-1 rounded-full text-xs font-bold uppercase {{ $skColor }}">
                                {{ str_replace('_', ' ', $sk) }}
                            </span>
                        </td>
                        <td class="py-3.5 px-4">
                            <div class="flex items-center justify-center gap-1.5">
                                <a href="{{ route('alat.detail', $a->id) }}" title="Lihat Detail"
                                    class="w-8 h-8 rounded-lg bg-blue-100 text-blue-700 hover:bg-blue-200 flex items-center justify-center text-xs transition-colors">
                                    <i class="fa-solid fa-eye"></i>
                                </a>
                                <button type="button" title="Edit"
                                    @click="openEdit({{ json_encode([
                                        'id'                   => $a->id,
                                        'nama'                 => $a->nama,
                                        'kategori_alat_id'     => $a->kategori_alat_id,
                                        'merek'                => $a->merek,
                                        'tipe'                 => $a->tipe,
                                        'serial_number'        => $a->serial_number,
                                        'tahun_perolehan'      => $a->tahun_perolehan,
                                        'kondisi'              => $a->kondisi,
                                        'status'               => $a->status,
                                        'status_ketersediaan'  => $a->status_ketersediaan,
                                        'jumlah_total'         => $a->jumlah_total,
                                        'jumlah_baik'          => $a->jumlah_baik ?? 0,
                                        'jumlah_cukup'         => $a->jumlah_cukup ?? 0,
                                        'jumlah_rusak_ringan'  => $a->jumlah_rusak_ringan ?? 0,
                                        'jumlah_rusak_berat'   => $a->jumlah_rusak_berat ?? 0,
                                        'jumlah_hilang'        => $a->jumlah_hilang ?? 0,
                                        'satuan'               => $a->satuan,
                                        'lokasi_penyimpanan'   => $a->lokasi_penyimpanan,
                                        'harga_perolehan'      => $a->harga_perolehan,
                                        'sumber_perolehan'     => $a->sumber_perolehan,
                                        'deskripsi'            => $a->catatan,
                                    ]) }})"
                                    class="w-8 h-8 rounded-lg bg-amber-100 text-amber-700 hover:bg-amber-200 flex items-center justify-center text-xs transition-colors">
                                    <i class="fa-solid fa-pen"></i>
                                </button>
                                <button type="button" title="Hapus"
                                    @click="openDelete('{{ route('alat.daftar.destroy', $a->id) }}', '{{ $a->nama }}')"
                                    class="w-8 h-8 rounded-lg bg-red-100 text-red-600 hover:bg-red-200 flex items-center justify-center text-xs transition-colors">
                                    <i class="fa-solid fa-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="10" class="py-16 text-center text-slate-400">
                            <i class="fa-solid fa-toolbox text-5xl mb-3 block opacity-20"></i>
                            <p class="text-sm">Belum ada data alat. Klik "Tambah Alat" untuk memulai.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($alat->hasPages())
        <div class="px-4 py-3 border-t border-slate-100">{{ $alat->links() }}</div>
        @endif
    </div>

    {{-- ========= MODAL TAMBAH / EDIT ========= --}}
    <div x-show="showModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" @click="showModal = false"></div>
        <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-2xl max-h-[92vh] overflow-y-auto"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100">

            {{-- Modal Header --}}
            <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100 sticky top-0 bg-white z-10">
                <div>
                    <h3 class="font-bold text-slate-900 text-base" x-text="editMode ? 'Edit Data Alat' : 'Tambah Alat / Barang'"></h3>
                    <p class="text-xs text-slate-500 mt-0.5">Isi detail informasi alat atau barang inventaris</p>
                </div>
                <button @click="showModal = false" class="w-8 h-8 rounded-lg text-slate-400 hover:bg-slate-100 flex items-center justify-center transition">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            {{-- Modal Form --}}
            <form :action="actionUrl" method="POST" enctype="multipart/form-data" class="p-6 space-y-5">
                @csrf
                <template x-if="editMode"><input type="hidden" name="_method" value="PUT"></template>

                {{-- Nama & Kategori --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="md:col-span-2">
                        <label class="text-xs font-semibold text-slate-600 block mb-1">Nama Alat / Barang <span class="text-red-500">*</span></label>
                        <input type="text" name="nama" x-model="form.nama" required
                            class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                            placeholder="Contoh: Laptop ASUS ROG, Obeng Set, dst.">
                    </div>
                    <div>
                        <label class="text-xs font-semibold text-slate-600 block mb-1">Kategori <span class="text-red-500">*</span></label>
                        <select name="kategori_alat_id" x-model="form.kategori_alat_id" required
                            class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">
                            <option value="">-- Pilih Kategori --</option>
                            @foreach($kategori as $k)
                            <option value="{{ $k->id }}">{{ $k->nama }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="text-xs font-semibold text-slate-600 block mb-1">Merek</label>
                        <input type="text" name="merek" x-model="form.merek"
                            class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                            placeholder="Contoh: ASUS, Bosch, dll.">
                    </div>
                    <div>
                        <label class="text-xs font-semibold text-slate-600 block mb-1">Tipe / Model</label>
                        <input type="text" name="tipe" x-model="form.tipe"
                            class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                            placeholder="Tipe atau model">
                    </div>
                    <div>
                        <label class="text-xs font-semibold text-slate-600 block mb-1">Serial Number / No. Seri</label>
                        <input type="text" name="serial_number" x-model="form.serial_number"
                            class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                            placeholder="Nomor seri (opsional)">
                    </div>
                    <div>
                        <label class="text-xs font-semibold text-slate-600 block mb-1">Tahun Perolehan</label>
                        <input type="number" name="tahun_perolehan" x-model="form.tahun_perolehan"
                            min="1900" :max="{{ date('Y') }}"
                            class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                            placeholder="{{ date('Y') }}">
                    </div>
                </div>

                {{-- Jumlah Per Kondisi --}}
                <div class="p-4 bg-slate-50 rounded-xl border border-slate-100 space-y-3">
                    <div class="flex items-center justify-between mb-1">
                        <label class="text-xs font-bold text-slate-700">Jumlah Alat per Kondisi <span class="text-red-500">*</span></label>
                        <div class="text-xs font-semibold">
                            <span class="text-slate-500">Total: </span>
                            <span class="text-blue-700 font-bold" x-text="jumlahTotal"></span>
                            <span class="text-slate-400 ml-1">| Tersedia: </span>
                            <span class="text-emerald-700 font-bold" x-text="jumlahTersedia"></span>
                        </div>
                    </div>
                    <div class="grid grid-cols-2 md:grid-cols-5 gap-3">
                        <div class="bg-emerald-50 border border-emerald-200 rounded-lg p-3">
                            <label class="text-[11px] font-bold text-emerald-700 block mb-1.5">
                                <i class="fa-solid fa-circle-check mr-1"></i>Kondisi Baik
                            </label>
                            <input type="number" name="jumlah_baik" x-model.number="form.jumlah_baik" min="0"
                                class="w-full border border-emerald-300 bg-white rounded-lg px-2 py-1.5 text-sm font-bold text-emerald-800 text-center focus:outline-none focus:ring-2 focus:ring-emerald-500">
                        </div>
                        <div class="bg-blue-50 border border-blue-200 rounded-lg p-3">
                            <label class="text-[11px] font-bold text-blue-700 block mb-1.5">
                                <i class="fa-solid fa-circle-half-stroke mr-1"></i>Cukup
                            </label>
                            <input type="number" name="jumlah_cukup" x-model.number="form.jumlah_cukup" min="0"
                                class="w-full border border-blue-300 bg-white rounded-lg px-2 py-1.5 text-sm font-bold text-blue-800 text-center focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                        <div class="bg-amber-50 border border-amber-200 rounded-lg p-3">
                            <label class="text-[11px] font-bold text-amber-700 block mb-1.5">
                                <i class="fa-solid fa-triangle-exclamation mr-1"></i>Rusak Ringan
                            </label>
                            <input type="number" name="jumlah_rusak_ringan" x-model.number="form.jumlah_rusak_ringan" min="0"
                                class="w-full border border-amber-300 bg-white rounded-lg px-2 py-1.5 text-sm font-bold text-amber-800 text-center focus:outline-none focus:ring-2 focus:ring-amber-500">
                        </div>
                        <div class="bg-rose-50 border border-rose-200 rounded-lg p-3">
                            <label class="text-[11px] font-bold text-rose-700 block mb-1.5">
                                <i class="fa-solid fa-circle-xmark mr-1"></i>Rusak Berat
                            </label>
                            <input type="number" name="jumlah_rusak_berat" x-model.number="form.jumlah_rusak_berat" min="0"
                                class="w-full border border-rose-300 bg-white rounded-lg px-2 py-1.5 text-sm font-bold text-rose-800 text-center focus:outline-none focus:ring-2 focus:ring-rose-500">
                        </div>
                        <div class="bg-slate-100 border border-slate-300 rounded-lg p-3">
                            <label class="text-[11px] font-bold text-slate-700 block mb-1.5">
                                <i class="fa-solid fa-circle-minus mr-1"></i>Hilang
                            </label>
                            <input type="number" name="jumlah_hilang" x-model.number="form.jumlah_hilang" min="0"
                                class="w-full border border-slate-300 bg-white rounded-lg px-2 py-1.5 text-sm font-bold text-slate-800 text-center focus:outline-none focus:ring-2 focus:ring-slate-500">
                        </div>
                    </div>
                    <p class="text-[11px] text-slate-500 mt-1">
                        <i class="fa-solid fa-circle-info mr-1"></i>
                        Kondisi dominan & status ketersediaan akan dihitung otomatis oleh sistem.
                    </p>
                </div>

                {{-- Satuan & Status (edit mode) --}}
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <div>
                        <label class="text-xs font-semibold text-slate-600 block mb-1">Satuan</label>
                        <input type="text" name="satuan" x-model="form.satuan"
                            class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                            placeholder="unit, pcs, set">
                    </div>
                    <div x-show="editMode">
                        <label class="text-xs font-semibold text-slate-600 block mb-1">Status Alat</label>
                        <select name="status" x-model="form.status"
                            class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="aktif">Aktif</option>
                            <option value="nonaktif">Nonaktif</option>
                        </select>
                    </div>
                    <div x-show="editMode">
                        <label class="text-xs font-semibold text-slate-600 block mb-1">Ketersediaan</label>
                        <select name="status_ketersediaan" x-model="form.status_ketersediaan"
                            class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="tersedia">Tersedia</option>
                            <option value="dipinjam">Dipinjam</option>
                            <option value="dalam_perbaikan">Dalam Perbaikan</option>
                            <option value="dikeluarkan">Dikeluarkan</option>
                        </select>
                    </div>
                </div>

                {{-- Lokasi & Perolehan --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="text-xs font-semibold text-slate-600 block mb-1">Lokasi Penyimpanan</label>
                        <input type="text" name="lokasi_penyimpanan" x-model="form.lokasi_penyimpanan"
                            class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                            placeholder="Contoh: Lemari A, Ruang Lab, dll.">
                    </div>
                    <div>
                        <label class="text-xs font-semibold text-slate-600 block mb-1">Sumber Perolehan</label>
                        <select name="sumber_perolehan" x-model="form.sumber_perolehan"
                            class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">-- Pilih Sumber --</option>
                            <option value="dinas">Dari Dinas</option>
                            <option value="bos">Dana BOS</option>
                            <option value="donasi">Donasi / Hibah</option>
                            <option value="pembelian_sendiri">Pembelian Sendiri</option>
                            <option value="lainnya">Lainnya</option>
                        </select>
                    </div>
                    <div>
                        <label class="text-xs font-semibold text-slate-600 block mb-1">Harga Perolehan (Rp)</label>
                        <div class="flex">
                            <span class="inline-flex items-center px-3 bg-slate-100 border border-r-0 border-slate-200 rounded-l-lg text-xs text-slate-600 font-semibold">Rp</span>
                            <input type="number" name="harga_perolehan" x-model="form.harga_perolehan" min="0"
                                class="flex-1 border border-slate-200 rounded-r-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                                placeholder="0">
                        </div>
                    </div>
                    <div>
                        <label class="text-xs font-semibold text-slate-600 block mb-1">Foto Alat</label>
                        <input type="file" name="foto" accept="image/*"
                            class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm text-slate-600 file:mr-3 file:py-1 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-blue-50 file:text-blue-700">
                    </div>
                </div>

                {{-- Deskripsi --}}
                <div>
                    <label class="text-xs font-semibold text-slate-600 block mb-1">Deskripsi / Catatan</label>
                    <textarea name="deskripsi" x-model="form.deskripsi" rows="2"
                        class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 resize-none"
                        placeholder="Keterangan tambahan mengenai alat (opsional)"></textarea>
                </div>

                {{-- Actions --}}
                <div class="flex items-center justify-end gap-3 pt-2 border-t border-slate-100">
                    <button type="button" @click="showModal = false"
                        class="px-5 py-2 text-sm font-semibold text-slate-600 hover:bg-slate-100 rounded-lg transition">Batal</button>
                    <button type="submit"
                        class="px-6 py-2 text-sm font-semibold bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition shadow">
                        <i class="fa-solid fa-save mr-1.5"></i>
                        <span x-text="editMode ? 'Simpan Perubahan' : 'Tambah Alat'"></span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- ========= MODAL KONFIRMASI HAPUS ========= --}}
    <div x-show="deleteModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" @click="deleteModal = false"></div>
        <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-sm p-6 text-center"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100">
            <div class="w-14 h-14 rounded-full bg-red-100 flex items-center justify-center mx-auto mb-4">
                <i class="fa-solid fa-trash text-red-600 text-xl"></i>
            </div>
            <h3 class="font-bold text-slate-900 text-base mb-1">Hapus Alat?</h3>
            <p class="text-sm text-slate-500 mb-1">Anda akan menghapus:</p>
            <p class="font-semibold text-slate-800 mb-5" x-text="deleteNama"></p>
            <p class="text-xs text-rose-600 bg-rose-50 rounded-lg p-2 mb-5">
                <i class="fa-solid fa-triangle-exclamation mr-1"></i>
                Data yang dihapus tidak dapat dikembalikan.
            </p>
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
