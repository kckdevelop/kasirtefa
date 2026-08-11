@extends('layouts.app')

@section('title', 'Daftar Alat & Barang')

@section('content')
<div x-data="{ showModal: false, editMode: false, form: {}, currentId: null }" class="space-y-6">

    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-xl font-bold text-slate-900">Data Alat & Barang</h2>
            <p class="text-sm text-slate-500">Kelola inventaris peralatan dan barang (laptop, komputer, dll)</p>
        </div>
        @can('manage alat')
        <button @click="showModal = true; editMode = false; form = { kondisi: 'baik', status: 'tersedia' }"
            class="inline-flex items-center gap-2 px-4 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-xl text-sm shadow-md shadow-blue-600/25 transition-all">
            <i class="fa-solid fa-plus"></i> Tambah Alat / Barang
        </button>
        @endcan
    </div>

    <!-- Statistics Cards -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
        @php
        $totalAlat = $alat->total();
        $tersedia = $alat->where('status', 'tersedia')->count();
        $dipinjam = $alat->where('status', 'dipinjam')->count();
        $perbaikan = $alat->where('status', 'perbaikan')->count();
        @endphp
        <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-sm">
            <p class="text-xs text-slate-500 font-medium mb-1">Total Alat</p>
            <p class="text-2xl font-bold text-slate-900">{{ $totalAlat }}</p>
        </div>
        <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-sm">
            <p class="text-xs text-emerald-600 font-medium mb-1">Tersedia</p>
            <p class="text-2xl font-bold text-emerald-700">{{ $alat->where('status', 'tersedia')->total() ?? $tersedia }}</p>
        </div>
        <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-sm">
            <p class="text-xs text-blue-600 font-medium mb-1">Dipinjam</p>
            <p class="text-2xl font-bold text-blue-700">{{ $dipinjam }}</p>
        </div>
        <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-sm">
            <p class="text-xs text-amber-600 font-medium mb-1">Perbaikan</p>
            <p class="text-2xl font-bold text-amber-700">{{ $perbaikan }}</p>
        </div>
    </div>

    <!-- Filter Bar -->
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
            <option value="perbaikan" @selected(request('status') == 'perbaikan')>Perbaikan</option>
            <option value="rusak" @selected(request('status') == 'rusak')>Rusak</option>
        </select>
        <select name="kondisi" class="py-2 px-3 text-sm rounded-lg border border-slate-200 bg-white focus:outline-none focus:ring-2 focus:ring-blue-500">
            <option value="">Semua Kondisi</option>
            <option value="baik" @selected(request('kondisi') == 'baik')>Baik</option>
            <option value="rusak_ringan" @selected(request('kondisi') == 'rusak_ringan')>Rusak Ringan</option>
            <option value="rusak_berat" @selected(request('kondisi') == 'rusak_berat')>Rusak Berat</option>
        </select>
        <button type="submit" class="px-4 py-2 bg-slate-800 text-white font-semibold rounded-lg text-sm hover:bg-slate-700">Filter</button>
    </form>

    <!-- Table -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead class="bg-slate-50 border-b border-slate-200">
                    <tr class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider">
                        <th class="py-3.5 px-4">Alat</th>
                        <th class="py-3.5 px-4">Kategori</th>
                        <th class="py-3.5 px-4">Kode Aset</th>
                        <th class="py-3.5 px-4 text-center">Kondisi</th>
                        <th class="py-3.5 px-4 text-center">Stok</th>
                        <th class="py-3.5 px-4 text-center">Status</th>
                        <th class="py-3.5 px-4 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-sm">
                    @forelse($alat as $a)
                    <tr class="hover:bg-slate-50">
                        <td class="py-3.5 px-4">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-lg bg-slate-100 overflow-hidden flex-shrink-0">
                                    @if($a->foto)
                                    <img src="{{ asset('storage/'.$a->foto) }}" class="w-full h-full object-cover" alt="{{ $a->nama }}">
                                    @else
                                    <div class="w-full h-full flex items-center justify-center text-slate-400"><i class="fa-solid fa-wrench"></i></div>
                                    @endif
                                </div>
                                <div>
                                    <p class="font-semibold text-slate-900">{{ $a->nama }}</p>
                                    <p class="text-xs text-slate-400">{{ $a->merek }} {{ $a->model ? '- '.$a->model : '' }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="py-3.5 px-4">
                            <span class="px-2.5 py-1 rounded-lg bg-indigo-50 text-indigo-700 text-xs font-semibold">{{ $a->kategori?->nama }}</span>
                        </td>
                        <td class="py-3.5 px-4 font-mono text-xs text-slate-600">{{ $a->kode_alat }}</td>
                        <td class="py-3.5 px-4 text-center">
                            <span class="px-2.5 py-1 rounded-full text-xs font-bold uppercase {{ $a->kondisi == 'baik' ? 'bg-emerald-100 text-emerald-700' : ($a->kondisi == 'rusak_ringan' ? 'bg-amber-100 text-amber-700' : 'bg-rose-100 text-rose-700') }}">
                                {{ str_replace('_', ' ', $a->kondisi) }}
                            </span>
                        </td>
                        <td class="py-3.5 px-4 text-center font-bold text-slate-800">{{ $a->stok_tersedia }} / {{ $a->stok_total }}</td>
                        <td class="py-3.5 px-4 text-center">
                            <span class="px-3 py-1 rounded-full text-xs font-bold uppercase {{ getStatusColor($a->status) }}">{{ $a->status }}</span>
                        </td>
                        <td class="py-3.5 px-4">
                            <div class="flex items-center justify-center gap-1.5">
                                <a href="{{ route('alat.data.show', $a->id) }}" title="Detail"
                                    class="w-8 h-8 rounded-lg bg-blue-100 text-blue-700 hover:bg-blue-200 flex items-center justify-center text-xs transition-colors">
                                    <i class="fa-solid fa-eye"></i>
                                </a>
                                @can('manage alat')
                                <button title="Edit"
                                    @click="editMode=true; currentId={{ $a->id }}; form={{ json_encode(['id'=>$a->id,'nama'=>$a->nama,'kategori_alat_id'=>$a->kategori_alat_id,'kondisi'=>$a->kondisi,'status'=>$a->status,'stok_total'=>$a->stok_total]) }}; showModal=true"
                                    class="w-8 h-8 rounded-lg bg-amber-100 text-amber-700 hover:bg-amber-200 flex items-center justify-center text-xs transition-colors">
                                    <i class="fa-solid fa-pen"></i>
                                </button>
                                @endcan
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="py-16 text-center text-slate-400">
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

    <!-- Add/Edit Alat Modal -->
    <div x-show="showModal" x-cloak class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 flex items-start justify-center pt-10 px-4 overflow-y-auto">
        <div class="bg-white rounded-2xl w-full max-w-2xl shadow-2xl p-6 my-4" @click.stop>
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-lg font-bold text-slate-900" x-text="editMode ? 'Edit Alat' : 'Tambah Alat Baru'"></h3>
                <button @click="showModal = false" class="text-slate-400 hover:text-slate-600"><i class="fa-solid fa-times text-xl"></i></button>
            </div>
            <form method="POST" enctype="multipart/form-data" class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                @csrf
                <div class="sm:col-span-2">
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Nama Alat *</label>
                    <input type="text" name="nama" x-model="form.nama" required placeholder="Nama lengkap alat..."
                        class="w-full px-3 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Kategori *</label>
                    <select name="kategori_alat_id" x-model="form.kategori_alat_id" required class="w-full px-3 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">
                        <option value="">-- Pilih Kategori --</option>
                        @foreach($kategori as $k)
                        <option value="{{ $k->id }}">{{ $k->nama }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Merek</label>
                    <input type="text" name="merek" x-model="form.merek" placeholder="Bosch, Makita, dll..."
                        class="w-full px-3 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Stok Total *</label>
                    <input type="number" name="stok_total" x-model="form.stok_total" required min="1"
                        class="w-full px-3 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Kondisi</label>
                    <select name="kondisi" x-model="form.kondisi" class="w-full px-3 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">
                        <option value="baik">Baik</option>
                        <option value="rusak_ringan">Rusak Ringan</option>
                        <option value="rusak_berat">Rusak Berat</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Nilai Denda/Hari (Rp)</label>
                    <input type="number" name="nilai_denda" x-model="form.nilai_denda" min="0" placeholder="0"
                        class="w-full px-3 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Lokasi Penyimpanan</label>
                    <input type="text" name="lokasi_penyimpanan" x-model="form.lokasi_penyimpanan" placeholder="Rak A, Lemari B..."
                        class="w-full px-3 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div class="sm:col-span-2">
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Foto Alat</label>
                    <input type="file" name="foto" accept="image/*"
                        class="w-full text-sm text-slate-500 file:mr-3 file:py-2 file:px-4 file:rounded-lg file:border-0 file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                </div>
                <div class="sm:col-span-2">
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Deskripsi</label>
                    <textarea name="deskripsi" rows="2" placeholder="Spesifikasi dan catatan alat..."
                        class="w-full px-3 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 resize-none"></textarea>
                </div>
                <div class="sm:col-span-2 flex gap-3 justify-end pt-2 border-t border-slate-100">
                    <button type="button" @click="showModal = false" class="px-5 py-2.5 border border-slate-200 rounded-xl text-sm font-semibold text-slate-600">Batal</button>
                    <button type="submit" class="px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-xl text-sm">
                        <i class="fa-solid fa-save mr-2"></i> Simpan Alat
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
