@extends('layouts.app')
@php use Illuminate\Support\Str; @endphp

@section('title', 'Data Gedung & Lab')

@section('content')
<div x-data="{
    showAddGedungModal: false,
    showEditGedungModal: false,
    showDeleteModal: false,
    showAddFasilitasModal: false,
    showEditFasilitasModal: false,
    showDeleteFasilitasModal: false,
    activeTab: null,

    editGedungForm: {},
    deleteName: '',
    deleteUrl: '',

    editFasilitasForm: {},
    deleteFasilitasName: '',
    deleteFasilitasUrl: '',

    openEditGedung(item) {
        this.editGedungForm = {
            id: item.id,
            kode_gedung: item.kode_gedung,
            nama_gedung: item.nama_gedung,
            lokasi: item.lokasi ?? '',
            kapasitas: item.kapasitas,
            harga_sewa_per_hari: item.harga_sewa_per_hari,
            deskripsi: item.deskripsi ?? '',
            status: item.status,
        };
        this.showEditGedungModal = true;
    },

    openDeleteGedung(url, name) {
        this.deleteUrl = url;
        this.deleteName = name;
        this.showDeleteModal = true;
    },

    openEditFasilitas(item) {
        this.editFasilitasForm = {
            id: item.id,
            gedung_lab_id: item.gedung_lab_id,
            nama_fasilitas: item.nama_fasilitas,
            kode_fasilitas: item.kode_fasilitas ?? '',
            jumlah_tersedia: item.jumlah_tersedia,
            harga_per_item: item.harga_per_item,
            satuan: item.satuan,
            keterangan: item.keterangan ?? '',
            status: item.status,
        };
        this.showEditFasilitasModal = true;
    },

    openDeleteFasilitas(url, name) {
        this.deleteFasilitasUrl = url;
        this.deleteFasilitasName = name;
        this.showDeleteFasilitasModal = true;
    },

    toggleTab(id) {
        this.activeTab = this.activeTab === id ? null : id;
    }
}" class="space-y-6">

    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-xl font-bold text-slate-900">Data Gedung & Lab</h2>
            <p class="text-sm text-slate-500">Kelola gedung/ruang lab beserta fasilitas dan harga sewa</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('sewa.transaksi.create') }}"
                class="inline-flex items-center gap-2 px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-semibold rounded-xl text-sm shadow-md shadow-emerald-600/25 transition-all">
                <i class="fa-solid fa-calendar-plus"></i> Buat Penyewaan
            </a>
            <button @click="showAddGedungModal = true"
                class="inline-flex items-center gap-2 px-4 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-xl text-sm shadow-md shadow-blue-600/25 transition-all">
                <i class="fa-solid fa-plus"></i> Tambah Gedung
            </button>
        </div>
    </div>

    {{-- Flash Messages --}}
    @if(session('success'))
    <div class="flex items-center gap-3 px-4 py-3 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-xl text-sm">
        <i class="fa-solid fa-circle-check text-emerald-500"></i> {{ session('success') }}
    </div>
    @endif
    @if(session('error'))
    <div class="flex items-center gap-3 px-4 py-3 bg-rose-50 border border-rose-200 text-rose-800 rounded-xl text-sm">
        <i class="fa-solid fa-circle-exclamation text-rose-500"></i> {{ session('error') }}
    </div>
    @endif

    {{-- Filter --}}
    <form method="GET" class="flex flex-wrap gap-3 items-center">
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama/kode gedung..."
            class="flex-1 min-w-[200px] px-4 py-2.5 text-sm border border-slate-200 rounded-xl bg-white focus:outline-none focus:ring-2 focus:ring-blue-500">
        <select name="status" class="px-4 py-2.5 text-sm border border-slate-200 rounded-xl bg-white focus:outline-none focus:ring-2 focus:ring-blue-500">
            <option value="">Semua Status</option>
            <option value="tersedia" {{ request('status') == 'tersedia' ? 'selected' : '' }}>Tersedia</option>
            <option value="diperbaiki" {{ request('status') == 'diperbaiki' ? 'selected' : '' }}>Diperbaiki</option>
            <option value="nonaktif" {{ request('status') == 'nonaktif' ? 'selected' : '' }}>Nonaktif</option>
        </select>
        <button type="submit" class="px-4 py-2.5 bg-slate-800 text-white text-sm rounded-xl hover:bg-slate-700 transition-all">
            <i class="fa-solid fa-search mr-1"></i> Cari
        </button>
        <a href="{{ route('sewa.gedung.index') }}" class="px-4 py-2.5 bg-slate-100 text-slate-600 text-sm rounded-xl hover:bg-slate-200 transition-all">Reset</a>
    </form>

    {{-- Gedung List --}}
    <div class="space-y-4">
        @forelse($gedungList as $gedung)
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
            {{-- Gedung Header Row --}}
            <div class="flex items-center justify-between px-6 py-4">
                <div class="flex items-center gap-4">
                    {{-- Expand/Collapse --}}
                    <button @click="toggleTab({{ $gedung->id }})"
                        class="w-9 h-9 flex items-center justify-center rounded-xl bg-blue-50 text-blue-600 hover:bg-blue-100 transition-all">
                        <i class="fa-solid" :class="activeTab === {{ $gedung->id }} ? 'fa-chevron-up' : 'fa-chevron-down'"></i>
                    </button>
                    <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-blue-500 to-indigo-600 flex items-center justify-center text-white text-xl shadow-md shadow-blue-200">
                        <i class="fa-solid fa-building"></i>
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <h3 class="font-bold text-slate-900 text-base">{{ $gedung->nama_gedung }}</h3>
                            <span class="text-xs font-mono px-2 py-0.5 bg-slate-100 text-slate-500 rounded-md">{{ $gedung->kode_gedung }}</span>
                            <span class="text-xs px-2 py-0.5 rounded-full font-semibold border
                                {{ $gedung->status === 'tersedia' ? 'bg-emerald-100 text-emerald-700 border-emerald-200' : ($gedung->status === 'diperbaiki' ? 'bg-amber-100 text-amber-700 border-amber-200' : 'bg-slate-100 text-slate-600 border-slate-200') }}">
                                {{ ucfirst($gedung->status) }}
                            </span>
                        </div>
                        <div class="flex items-center gap-4 mt-1 text-sm text-slate-500">
                            @if($gedung->lokasi)
                            <span><i class="fa-solid fa-location-dot mr-1 text-rose-400"></i>{{ $gedung->lokasi }}</span>
                            @endif
                            <span><i class="fa-solid fa-users mr-1 text-blue-400"></i>Kap. {{ number_format($gedung->kapasitas) }} orang</span>
                            <span class="font-semibold text-emerald-700"><i class="fa-solid fa-tag mr-1"></i>{{ formatRupiah($gedung->harga_sewa_per_hari) }}/hari</span>
                            <span><i class="fa-solid fa-layer-group mr-1 text-violet-400"></i>{{ $gedung->fasilitas->count() }} fasilitas</span>
                            <span><i class="fa-solid fa-calendar-check mr-1 text-amber-400"></i>{{ $gedung->sewa_count }} penyewaan</span>
                        </div>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <button @click="openEditGedung({{ json_encode($gedung) }})"
                        class="px-3 py-2 text-sm text-blue-600 bg-blue-50 hover:bg-blue-100 rounded-lg transition-all">
                        <i class="fa-solid fa-pen-to-square"></i>
                    </button>
                    <button @click="openDeleteGedung('{{ route('sewa.gedung.destroy', $gedung->id) }}', '{{ $gedung->nama_gedung }}')"
                        class="px-3 py-2 text-sm text-rose-600 bg-rose-50 hover:bg-rose-100 rounded-lg transition-all">
                        <i class="fa-solid fa-trash"></i>
                    </button>
                </div>
            </div>

            {{-- Fasilitas Section (Accordion) --}}
            <div x-show="activeTab === {{ $gedung->id }}" x-transition class="border-t border-slate-100 px-6 py-4 bg-slate-50">
                <div class="flex items-center justify-between mb-4">
                    <h4 class="font-semibold text-slate-700 text-sm"><i class="fa-solid fa-list mr-1 text-blue-500"></i>Daftar Fasilitas / Alat</h4>
                    <button @click="showAddFasilitasModal = true; editFasilitasForm.gedung_lab_id = {{ $gedung->id }}"
                        class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-all">
                        <i class="fa-solid fa-plus"></i> Tambah Fasilitas
                    </button>
                </div>

                @if($gedung->fasilitas->isEmpty())
                <div class="text-center py-8 text-slate-400 text-sm">
                    <i class="fa-solid fa-box-open text-3xl mb-2 block"></i>
                    Belum ada fasilitas. Tambahkan fasilitas untuk gedung ini.
                </div>
                @else
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="text-left text-xs text-slate-500 border-b border-slate-200">
                                <th class="pb-2 font-semibold">Fasilitas</th>
                                <th class="pb-2 font-semibold">Kode</th>
                                <th class="pb-2 font-semibold">Tersedia</th>
                                <th class="pb-2 font-semibold">Harga/Item</th>
                                <th class="pb-2 font-semibold">Status</th>
                                <th class="pb-2 font-semibold text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach($gedung->fasilitas as $fas)
                            <tr class="hover:bg-white transition-colors">
                                <td class="py-2.5 pr-4">
                                    <div class="font-semibold text-slate-800">{{ $fas->nama_fasilitas }}</div>
                                    @if($fas->keterangan)
                                    <div class="text-xs text-slate-500">{{ Str::limit($fas->keterangan, 50) }}</div>
                                    @endif
                                </td>
                                <td class="py-2.5 pr-4">
                                    <span class="text-xs font-mono text-slate-500">{{ $fas->kode_fasilitas ?? '-' }}</span>
                                </td>
                                <td class="py-2.5 pr-4">
                                    <span class="font-semibold text-slate-700">{{ number_format($fas->jumlah_tersedia) }} {{ $fas->satuan }}</span>
                                </td>
                                <td class="py-2.5 pr-4">
                                    <span class="font-bold text-emerald-700">{{ formatRupiah($fas->harga_per_item) }}</span>
                                    <div class="text-xs text-slate-400">per {{ $fas->satuan }}/hari</div>
                                </td>
                                <td class="py-2.5 pr-4">
                                    <span class="text-xs px-2 py-0.5 rounded-full font-semibold border
                                        {{ $fas->status === 'baik' ? 'bg-emerald-100 text-emerald-700 border-emerald-200' : ($fas->status === 'perbaikan' ? 'bg-amber-100 text-amber-700 border-amber-200' : 'bg-rose-100 text-rose-700 border-rose-200') }}">
                                        {{ ucfirst($fas->status) }}
                                    </span>
                                </td>
                                <td class="py-2.5 text-right">
                                    <div class="flex items-center justify-end gap-1.5">
                                        <button @click="openEditFasilitas({{ json_encode($fas) }})"
                                            class="p-1.5 text-blue-600 bg-blue-50 hover:bg-blue-100 rounded-lg transition-all text-xs">
                                            <i class="fa-solid fa-pen-to-square"></i>
                                        </button>
                                        <button @click="openDeleteFasilitas('{{ route('sewa.fasilitas.destroy', $fas->id) }}', '{{ $fas->nama_fasilitas }}')"
                                            class="p-1.5 text-rose-600 bg-rose-50 hover:bg-rose-100 rounded-lg transition-all text-xs">
                                            <i class="fa-solid fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @endif
            </div>
        </div>
        @empty
        <div class="bg-white rounded-2xl border border-slate-200 py-16 text-center">
            <i class="fa-solid fa-building text-5xl text-slate-300 mb-3 block"></i>
            <p class="text-slate-500 font-medium">Belum ada data gedung/lab.</p>
            <p class="text-slate-400 text-sm mt-1">Klik "Tambah Gedung" untuk mulai menambahkan.</p>
        </div>
        @endforelse
    </div>

    {{-- Pagination --}}
    {{ $gedungList->links() }}

    {{-- ── Modal Tambah Gedung ── --}}
    <div x-show="showAddGedungModal" x-transition class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/40 backdrop-blur-sm" style="display:none">
        <div @click.outside="showAddGedungModal = false" class="bg-white rounded-2xl shadow-2xl w-full max-w-2xl max-h-[90vh] overflow-y-auto">
            <div class="flex items-center justify-between p-6 border-b border-slate-100">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-xl bg-blue-100 flex items-center justify-center text-blue-600"><i class="fa-solid fa-building"></i></div>
                    <h3 class="font-bold text-slate-800">Tambah Gedung / Lab</h3>
                </div>
                <button @click="showAddGedungModal = false" class="text-slate-400 hover:text-slate-600 p-1 rounded-lg"><i class="fa-solid fa-xmark text-lg"></i></button>
            </div>
            <form method="POST" action="{{ route('sewa.gedung.store') }}" enctype="multipart/form-data" class="p-6 space-y-4">
                @csrf
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1.5">Kode Gedung <span class="text-rose-500">*</span></label>
                        <input type="text" name="kode_gedung" required placeholder="LAB-001" class="w-full px-3 py-2.5 text-sm border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1.5">Nama Gedung / Lab <span class="text-rose-500">*</span></label>
                        <input type="text" name="nama_gedung" required placeholder="Lab Komputer Utama" class="w-full px-3 py-2.5 text-sm border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1.5">Lokasi</label>
                        <input type="text" name="lokasi" placeholder="Gedung A, Lantai 2" class="w-full px-3 py-2.5 text-sm border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1.5">Kapasitas (orang) <span class="text-rose-500">*</span></label>
                        <input type="number" name="kapasitas" min="0" value="0" required class="w-full px-3 py-2.5 text-sm border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1.5">Harga Sewa Gedung / Hari <span class="text-rose-500">*</span></label>
                        <div class="relative">
                            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm">Rp</span>
                            <input type="number" name="harga_sewa_per_hari" min="0" step="1000" value="0" required class="w-full pl-10 pr-3 py-2.5 text-sm border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1.5">Status <span class="text-rose-500">*</span></label>
                        <select name="status" required class="w-full px-3 py-2.5 text-sm border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="tersedia">Tersedia</option>
                            <option value="diperbaiki">Diperbaiki</option>
                            <option value="nonaktif">Nonaktif</option>
                        </select>
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1.5">Deskripsi</label>
                    <textarea name="deskripsi" rows="3" placeholder="Deskripsi gedung/lab..." class="w-full px-3 py-2.5 text-sm border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 resize-none"></textarea>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1.5">Foto Gedung</label>
                    <input type="file" name="foto" accept="image/*" class="w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-600 hover:file:bg-blue-100">
                </div>
                <div class="flex justify-end gap-3 pt-2 border-t border-slate-100">
                    <button type="button" @click="showAddGedungModal = false" class="px-4 py-2 text-sm text-slate-600 bg-slate-100 hover:bg-slate-200 rounded-xl transition-all">Batal</button>
                    <button type="submit" class="px-5 py-2 text-sm font-semibold text-white bg-blue-600 hover:bg-blue-700 rounded-xl transition-all shadow-md shadow-blue-600/25">Simpan Gedung</button>
                </div>
            </form>
        </div>
    </div>

    {{-- ── Modal Edit Gedung ── --}}
    <div x-show="showEditGedungModal" x-transition class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/40 backdrop-blur-sm" style="display:none">
        <div @click.outside="showEditGedungModal = false" class="bg-white rounded-2xl shadow-2xl w-full max-w-2xl max-h-[90vh] overflow-y-auto">
            <div class="flex items-center justify-between p-6 border-b border-slate-100">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-xl bg-amber-100 flex items-center justify-center text-amber-600"><i class="fa-solid fa-pen-to-square"></i></div>
                    <h3 class="font-bold text-slate-800">Edit Gedung / Lab</h3>
                </div>
                <button @click="showEditGedungModal = false" class="text-slate-400 hover:text-slate-600 p-1 rounded-lg"><i class="fa-solid fa-xmark text-lg"></i></button>
            </div>
            <form method="POST" :action="'{{ url('/sewa/gedung') }}/' + editGedungForm.id" enctype="multipart/form-data" class="p-6 space-y-4">
                @csrf @method('PUT')
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1.5">Kode Gedung <span class="text-rose-500">*</span></label>
                        <input type="text" name="kode_gedung" x-model="editGedungForm.kode_gedung" required class="w-full px-3 py-2.5 text-sm border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1.5">Nama Gedung / Lab <span class="text-rose-500">*</span></label>
                        <input type="text" name="nama_gedung" x-model="editGedungForm.nama_gedung" required class="w-full px-3 py-2.5 text-sm border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1.5">Lokasi</label>
                        <input type="text" name="lokasi" x-model="editGedungForm.lokasi" class="w-full px-3 py-2.5 text-sm border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1.5">Kapasitas (orang) <span class="text-rose-500">*</span></label>
                        <input type="number" name="kapasitas" x-model="editGedungForm.kapasitas" min="0" required class="w-full px-3 py-2.5 text-sm border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1.5">Harga Sewa / Hari <span class="text-rose-500">*</span></label>
                        <div class="relative">
                            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm">Rp</span>
                            <input type="number" name="harga_sewa_per_hari" x-model="editGedungForm.harga_sewa_per_hari" min="0" step="1000" required class="w-full pl-10 pr-3 py-2.5 text-sm border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1.5">Status <span class="text-rose-500">*</span></label>
                        <select name="status" x-model="editGedungForm.status" required class="w-full px-3 py-2.5 text-sm border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="tersedia">Tersedia</option>
                            <option value="diperbaiki">Diperbaiki</option>
                            <option value="nonaktif">Nonaktif</option>
                        </select>
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1.5">Deskripsi</label>
                    <textarea name="deskripsi" rows="3" x-model="editGedungForm.deskripsi" class="w-full px-3 py-2.5 text-sm border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 resize-none"></textarea>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1.5">Ganti Foto (opsional)</label>
                    <input type="file" name="foto" accept="image/*" class="w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-600 hover:file:bg-blue-100">
                </div>
                <div class="flex justify-end gap-3 pt-2 border-t border-slate-100">
                    <button type="button" @click="showEditGedungModal = false" class="px-4 py-2 text-sm text-slate-600 bg-slate-100 hover:bg-slate-200 rounded-xl transition-all">Batal</button>
                    <button type="submit" class="px-5 py-2 text-sm font-semibold text-white bg-amber-500 hover:bg-amber-600 rounded-xl transition-all shadow-md shadow-amber-500/25">Perbarui Data</button>
                </div>
            </form>
        </div>
    </div>

    {{-- ── Modal Delete Gedung ── --}}
    <div x-show="showDeleteModal" x-transition class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/40 backdrop-blur-sm" style="display:none">
        <div @click.outside="showDeleteModal = false" class="bg-white rounded-2xl shadow-2xl w-full max-w-md p-6">
            <div class="text-center mb-5">
                <div class="w-16 h-16 rounded-full bg-rose-100 flex items-center justify-center mx-auto mb-3">
                    <i class="fa-solid fa-trash text-rose-600 text-2xl"></i>
                </div>
                <h3 class="font-bold text-slate-800 text-lg">Hapus Gedung?</h3>
                <p class="text-slate-500 text-sm mt-1">Gedung <strong x-text="deleteName"></strong> beserta seluruh fasilitas dan riwayat sewa akan dihapus permanen.</p>
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

    {{-- ── Modal Tambah Fasilitas ── --}}
    <div x-show="showAddFasilitasModal" x-transition class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/40 backdrop-blur-sm" style="display:none">
        <div @click.outside="showAddFasilitasModal = false" class="bg-white rounded-2xl shadow-2xl w-full max-w-xl max-h-[90vh] overflow-y-auto">
            <div class="flex items-center justify-between p-6 border-b border-slate-100">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-xl bg-violet-100 flex items-center justify-center text-violet-600"><i class="fa-solid fa-plus"></i></div>
                    <h3 class="font-bold text-slate-800">Tambah Fasilitas / Alat</h3>
                </div>
                <button @click="showAddFasilitasModal = false" class="text-slate-400 hover:text-slate-600 p-1 rounded-lg"><i class="fa-solid fa-xmark text-lg"></i></button>
            </div>
            <form method="POST" action="{{ route('sewa.fasilitas.store') }}" class="p-6 space-y-4">
                @csrf
                <input type="hidden" name="gedung_lab_id" x-model="editFasilitasForm.gedung_lab_id">
                <div class="grid grid-cols-2 gap-4">
                    <div class="col-span-2">
                        <label class="block text-xs font-semibold text-slate-600 mb-1.5">Nama Fasilitas / Alat <span class="text-rose-500">*</span></label>
                        <input type="text" name="nama_fasilitas" required placeholder="Proyektor HD" class="w-full px-3 py-2.5 text-sm border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1.5">Kode Fasilitas</label>
                        <input type="text" name="kode_fasilitas" placeholder="FAC-001" class="w-full px-3 py-2.5 text-sm border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1.5">Jumlah Tersedia <span class="text-rose-500">*</span></label>
                        <input type="number" name="jumlah_tersedia" min="1" value="1" required class="w-full px-3 py-2.5 text-sm border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1.5">Harga / Item / Hari <span class="text-rose-500">*</span></label>
                        <div class="relative">
                            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm">Rp</span>
                            <input type="number" name="harga_per_item" min="0" step="1000" value="0" required class="w-full pl-10 pr-3 py-2.5 text-sm border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1.5">Satuan <span class="text-rose-500">*</span></label>
                        <input type="text" name="satuan" value="unit" required class="w-full px-3 py-2.5 text-sm border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1.5">Status <span class="text-rose-500">*</span></label>
                        <select name="status" required class="w-full px-3 py-2.5 text-sm border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="baik">Baik</option>
                            <option value="perbaikan">Perbaikan</option>
                            <option value="rusak">Rusak</option>
                        </select>
                    </div>
                    <div class="col-span-2">
                        <label class="block text-xs font-semibold text-slate-600 mb-1.5">Keterangan</label>
                        <textarea name="keterangan" rows="2" class="w-full px-3 py-2.5 text-sm border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 resize-none"></textarea>
                    </div>
                </div>
                <div class="flex justify-end gap-3 pt-2 border-t border-slate-100">
                    <button type="button" @click="showAddFasilitasModal = false" class="px-4 py-2 text-sm text-slate-600 bg-slate-100 hover:bg-slate-200 rounded-xl transition-all">Batal</button>
                    <button type="submit" class="px-5 py-2 text-sm font-semibold text-white bg-violet-600 hover:bg-violet-700 rounded-xl transition-all shadow-md shadow-violet-600/25">Simpan Fasilitas</button>
                </div>
            </form>
        </div>
    </div>

    {{-- ── Modal Edit Fasilitas ── --}}
    <div x-show="showEditFasilitasModal" x-transition class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/40 backdrop-blur-sm" style="display:none">
        <div @click.outside="showEditFasilitasModal = false" class="bg-white rounded-2xl shadow-2xl w-full max-w-xl max-h-[90vh] overflow-y-auto">
            <div class="flex items-center justify-between p-6 border-b border-slate-100">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-xl bg-amber-100 flex items-center justify-center text-amber-600"><i class="fa-solid fa-pen-to-square"></i></div>
                    <h3 class="font-bold text-slate-800">Edit Fasilitas / Alat</h3>
                </div>
                <button @click="showEditFasilitasModal = false" class="text-slate-400 hover:text-slate-600 p-1 rounded-lg"><i class="fa-solid fa-xmark text-lg"></i></button>
            </div>
            <form method="POST" :action="'{{ url('/sewa/fasilitas') }}/' + editFasilitasForm.id" class="p-6 space-y-4">
                @csrf @method('PUT')
                <div class="grid grid-cols-2 gap-4">
                    <div class="col-span-2">
                        <label class="block text-xs font-semibold text-slate-600 mb-1.5">Nama Fasilitas / Alat <span class="text-rose-500">*</span></label>
                        <input type="text" name="nama_fasilitas" x-model="editFasilitasForm.nama_fasilitas" required class="w-full px-3 py-2.5 text-sm border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1.5">Kode Fasilitas</label>
                        <input type="text" name="kode_fasilitas" x-model="editFasilitasForm.kode_fasilitas" class="w-full px-3 py-2.5 text-sm border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1.5">Jumlah Tersedia <span class="text-rose-500">*</span></label>
                        <input type="number" name="jumlah_tersedia" x-model="editFasilitasForm.jumlah_tersedia" min="1" required class="w-full px-3 py-2.5 text-sm border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1.5">Harga / Item / Hari <span class="text-rose-500">*</span></label>
                        <div class="relative">
                            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm">Rp</span>
                            <input type="number" name="harga_per_item" x-model="editFasilitasForm.harga_per_item" min="0" step="1000" required class="w-full pl-10 pr-3 py-2.5 text-sm border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1.5">Satuan <span class="text-rose-500">*</span></label>
                        <input type="text" name="satuan" x-model="editFasilitasForm.satuan" required class="w-full px-3 py-2.5 text-sm border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1.5">Status <span class="text-rose-500">*</span></label>
                        <select name="status" x-model="editFasilitasForm.status" required class="w-full px-3 py-2.5 text-sm border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="baik">Baik</option>
                            <option value="perbaikan">Perbaikan</option>
                            <option value="rusak">Rusak</option>
                        </select>
                    </div>
                    <div class="col-span-2">
                        <label class="block text-xs font-semibold text-slate-600 mb-1.5">Keterangan</label>
                        <textarea name="keterangan" rows="2" x-model="editFasilitasForm.keterangan" class="w-full px-3 py-2.5 text-sm border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 resize-none"></textarea>
                    </div>
                </div>
                <div class="flex justify-end gap-3 pt-2 border-t border-slate-100">
                    <button type="button" @click="showEditFasilitasModal = false" class="px-4 py-2 text-sm text-slate-600 bg-slate-100 hover:bg-slate-200 rounded-xl transition-all">Batal</button>
                    <button type="submit" class="px-5 py-2 text-sm font-semibold text-white bg-amber-500 hover:bg-amber-600 rounded-xl transition-all shadow-md shadow-amber-500/25">Perbarui</button>
                </div>
            </form>
        </div>
    </div>

    {{-- ── Modal Delete Fasilitas ── --}}
    <div x-show="showDeleteFasilitasModal" x-transition class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/40 backdrop-blur-sm" style="display:none">
        <div @click.outside="showDeleteFasilitasModal = false" class="bg-white rounded-2xl shadow-2xl w-full max-w-md p-6">
            <div class="text-center mb-5">
                <div class="w-16 h-16 rounded-full bg-rose-100 flex items-center justify-center mx-auto mb-3">
                    <i class="fa-solid fa-trash text-rose-600 text-2xl"></i>
                </div>
                <h3 class="font-bold text-slate-800 text-lg">Hapus Fasilitas?</h3>
                <p class="text-slate-500 text-sm mt-1">Fasilitas <strong x-text="deleteFasilitasName"></strong> akan dihapus permanen.</p>
            </div>
            <form method="POST" :action="deleteFasilitasUrl">
                @csrf @method('DELETE')
                <div class="flex gap-3">
                    <button type="button" @click="showDeleteFasilitasModal = false" class="flex-1 px-4 py-2.5 text-sm text-slate-600 bg-slate-100 hover:bg-slate-200 rounded-xl transition-all font-medium">Batal</button>
                    <button type="submit" class="flex-1 px-4 py-2.5 text-sm font-semibold text-white bg-rose-600 hover:bg-rose-700 rounded-xl transition-all">Hapus</button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
