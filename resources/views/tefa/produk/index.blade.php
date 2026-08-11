@extends('layouts.app')

@section('title', 'Daftar Produk TEFa')

@section('content')
<div x-data="produkPage()" class="space-y-6">

    <!-- Page Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-xl font-bold text-slate-900">Daftar Produk</h2>
            <p class="text-sm text-slate-500">Kelola semua produk & jasa TEFa</p>
        </div>
        <div class="flex items-center gap-2">
            <template x-if="selectedIds.length > 0">
                <button @click="showBulkDeleteModal = true"
                    class="inline-flex items-center gap-2 px-4 py-2.5 bg-rose-600 hover:bg-rose-700 text-white font-semibold rounded-xl text-sm shadow-md shadow-rose-600/25 transition-all">
                    <i class="fa-solid fa-trash-can"></i> Hapus (<span x-text="selectedIds.length"></span>) Produk
                </button>
            </template>
            <button @click="openCreate()"
                class="inline-flex items-center gap-2 px-4 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-xl text-sm shadow-md shadow-blue-600/25 transition-all">
                <i class="fa-solid fa-plus"></i> Tambah Produk
            </button>
        </div>
    </div>

    <!-- Filter Bar -->
    <form method="GET" class="bg-white p-4 rounded-2xl border border-slate-200 shadow-sm flex flex-wrap gap-3 items-center">
        <div class="flex-1 min-w-48">
            <div class="relative">
                <i class="fa-solid fa-search absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm"></i>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari produk..."
                    class="w-full pl-9 pr-4 py-2 text-sm rounded-lg border border-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
        </div>
        <select name="kategori_id" class="py-2 px-3 text-sm rounded-lg border border-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">
            <option value="">Semua Kategori</option>
            @foreach($kategori as $k)
            <option value="{{ $k->id }}" @selected(request('kategori_id') == $k->id)>{{ $k->nama }}</option>
            @endforeach
        </select>
        <select name="status" class="py-2 px-3 text-sm rounded-lg border border-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">
            <option value="">Semua Status</option>
            <option value="aktif" @selected(request('status') == 'aktif')>Aktif</option>
            <option value="nonaktif" @selected(request('status') == 'nonaktif')>Nonaktif</option>
        </select>
        <div class="flex items-center gap-1.5">
            <label class="text-xs font-semibold text-slate-500">Per Hal:</label>
            <select name="per_page" onchange="this.form.submit()" class="py-2 px-3 text-sm rounded-lg border border-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">
                <option value="10" @selected(request('per_page') == 10)>10</option>
                <option value="15" @selected(request('per_page', 15) == 15)>15</option>
                <option value="25" @selected(request('per_page') == 25)>25</option>
                <option value="50" @selected(request('per_page') == 50)>50</option>
                <option value="100" @selected(request('per_page') == 100)>100</option>
            </select>
        </div>
        <button type="submit" class="px-4 py-2 bg-slate-800 text-white font-semibold rounded-lg text-sm hover:bg-slate-700 transition">
            Filter
        </button>
        @if(request()->hasAny(['search', 'kategori_id', 'status', 'per_page']))
        <a href="{{ route('tefa.produk.index') }}" class="py-2 px-3 text-sm text-slate-500 hover:text-rose-600 font-medium">
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
                        <th class="py-3.5 px-4">Produk</th>
                        <th class="py-3.5 px-4">Kategori</th>
                        <th class="py-3.5 px-4">Harga Jual</th>
                        <th class="py-3.5 px-4 text-center">Stok</th>
                        <th class="py-3.5 px-4 text-center">Status</th>
                        <th class="py-3.5 px-4 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-sm">
                    @forelse($produk as $p)
                    <tr class="hover:bg-slate-50 transition-colors" :class="selectedIds.includes({{ $p->id }}) ? 'bg-blue-50/50' : ''">
                        <td class="py-3.5 px-4 text-center">
                            <input type="checkbox" value="{{ $p->id }}" x-model.number="selectedIds"
                                class="rounded border-slate-300 text-blue-600 focus:ring-blue-500 cursor-pointer">
                        </td>
                        <td class="py-3.5 px-4">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-lg bg-slate-100 overflow-hidden flex-shrink-0 border border-slate-200">
                                    @if($p->foto)
                                    <img src="{{ asset('storage/'.$p->foto) }}" class="w-full h-full object-cover" alt="{{ $p->nama }}">
                                    @else
                                    <div class="w-full h-full flex items-center justify-center text-slate-400">
                                        <i class="fa-solid fa-box text-lg"></i>
                                    </div>
                                    @endif
                                </div>
                                <div>
                                    <p class="font-semibold text-slate-900">{{ $p->nama }}</p>
                                    <p class="text-xs text-slate-400">{{ $p->kode_produk }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="py-3.5 px-4">
                            <span class="px-2.5 py-1 rounded-lg bg-blue-50 text-blue-700 text-xs font-semibold">{{ $p->kategori?->nama }}</span>
                        </td>
                        <td class="py-3.5 px-4 font-bold text-slate-800">Rp {{ number_format($p->harga_jual, 0, ',', '.') }}</td>
                        <td class="py-3.5 px-4 text-center">
                            <span class="text-sm font-bold {{ $p->stok <= $p->stok_minimum ? 'text-rose-600' : 'text-emerald-700' }}">
                                {{ $p->stok }}
                            </span>
                            <span class="text-xs text-slate-400"> / min.{{ $p->stok_minimum }}</span>
                        </td>
                        <td class="py-3.5 px-4 text-center">
                            <span class="px-3 py-1 rounded-full text-xs font-bold uppercase {{ getStatusColor($p->status) }}">{{ $p->status }}</span>
                        </td>
                        <td class="py-3.5 px-4">
                            <div class="flex items-center justify-center gap-2">
                                <a href="{{ route('tefa.kasir') }}?produk={{ $p->id }}" title="Jual di POS Kasir"
                                    class="w-8 h-8 rounded-lg bg-emerald-100 text-emerald-700 hover:bg-emerald-200 flex items-center justify-center text-xs transition-colors">
                                    <i class="fa-solid fa-cash-register"></i>
                                </a>
                                <button title="Edit Produk" 
                                    @click="openEdit({{ json_encode($p) }})"
                                    class="w-8 h-8 rounded-lg bg-amber-100 text-amber-700 hover:bg-amber-200 flex items-center justify-center text-xs transition-colors">
                                    <i class="fa-solid fa-pen"></i>
                                </button>
                                <button title="Hapus Produk" 
                                    @click="openSingleDelete({{ $p->id }}, '{{ addslashes($p->nama) }}')"
                                    class="w-8 h-8 rounded-lg bg-rose-100 text-rose-700 hover:bg-rose-200 flex items-center justify-center text-xs transition-colors">
                                    <i class="fa-solid fa-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="py-16 text-center text-slate-400">
                            <i class="fa-solid fa-box-open text-5xl mb-3 block opacity-20"></i>
                            <p class="text-sm">Belum ada produk. Klik "Tambah Produk" untuk mulai.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <!-- Pagination Footer -->
        <div class="px-6 py-4 border-t border-slate-100 flex flex-col sm:flex-row items-center justify-between gap-4 bg-slate-50/50">
            <div class="text-xs text-slate-500">
                Menampilkan <span class="font-semibold text-slate-800">{{ $produk->firstItem() ?? 0 }}</span>
                sampai <span class="font-semibold text-slate-800">{{ $produk->lastItem() ?? 0 }}</span>
                dari <span class="font-semibold text-slate-800">{{ $produk->total() }}</span> produk
            </div>
            <div>
                {{ $produk->links() }}
            </div>
        </div>
    </div>

    <!-- Add/Edit Modal -->
    <div x-show="showModal" x-cloak class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 flex items-start justify-center pt-10 px-4 overflow-y-auto">
        <div class="bg-white rounded-2xl w-full max-w-2xl shadow-2xl p-6 my-4" @click.stop>
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-lg font-bold text-slate-900" x-text="editMode ? 'Edit Produk' : 'Tambah Produk Baru'"></h3>
                <button @click="showModal = false" class="text-slate-400 hover:text-slate-600 transition-colors">
                    <i class="fa-solid fa-times text-xl"></i>
                </button>
            </div>

            <form :action="actionUrl" method="POST" enctype="multipart/form-data" class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                @csrf
                <template x-if="editMode">
                    <input type="hidden" name="_method" value="PUT">
                </template>

                <div class="sm:col-span-2">
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Nama Produk *</label>
                    <input type="text" name="nama" x-model="form.nama" required placeholder="Nama lengkap produk..."
                        class="w-full px-3 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Kategori *</label>
                    <select name="kategori_produk_id" x-model="form.kategori_produk_id" required class="w-full px-3 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">
                        <option value="">-- Pilih Kategori --</option>
                        @foreach($kategori as $k)
                        <option value="{{ $k->id }}">{{ $k->nama }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Satuan *</label>
                    <input type="text" name="satuan" x-model="form.satuan" required placeholder="pcs, kg, liter, meter..."
                        class="w-full px-3 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Harga Jual *</label>
                    <div class="flex rounded-xl overflow-hidden border border-slate-200 focus-within:ring-2 focus-within:ring-blue-500 bg-white">
                        <span class="px-3 bg-slate-100 border-r border-slate-200 text-slate-600 font-bold text-xs flex items-center">Rp</span>
                        <input type="text" x-model="formattedHargaJual" @input="onHargaJualInput($event)" placeholder="0" required
                            class="w-full px-3 py-2.5 text-sm font-semibold text-slate-900 focus:outline-none bg-transparent">
                    </div>
                    <input type="hidden" name="harga_jual" :value="rawHargaJual">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Harga Modal</label>
                    <div class="flex rounded-xl overflow-hidden border border-slate-200 focus-within:ring-2 focus-within:ring-blue-500 bg-white">
                        <span class="px-3 bg-slate-100 border-r border-slate-200 text-slate-600 font-bold text-xs flex items-center">Rp</span>
                        <input type="text" x-model="formattedHargaModal" @input="onHargaModalInput($event)" placeholder="0"
                            class="w-full px-3 py-2.5 text-sm font-semibold text-slate-900 focus:outline-none bg-transparent">
                    </div>
                    <input type="hidden" name="harga_modal" :value="rawHargaModal">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Stok Minimum Alert</label>
                    <input type="number" name="stok_minimum" x-model="form.stok_minimum" min="0" placeholder="5"
                        class="w-full px-3 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Status</label>
                    <select name="status" x-model="form.status" class="w-full px-3 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">
                        <option value="aktif">Aktif</option>
                        <option value="nonaktif">Nonaktif</option>
                    </select>
                </div>

                <!-- Drag & Drop Photo Upload + Editor -->
                <div class="sm:col-span-2">
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Foto Produk</label>
                    <input type="hidden" name="foto_base64" :value="fotoBase64">
                    
                    <div 
                        @dragover.prevent="isDragging = true"
                        @dragleave.prevent="isDragging = false"
                        @drop.prevent="handleDrop($event)"
                        :class="isDragging ? 'border-blue-500 bg-blue-50/50 ring-4 ring-blue-500/10' : 'border-slate-200 bg-slate-50/70 hover:bg-slate-50 hover:border-slate-300'"
                        class="border-2 border-dashed rounded-2xl p-4 text-center transition-all relative">
                        
                        <input type="file" x-ref="fotoInput" name="foto" @change="handleFileSelect($event)" accept="image/*" class="hidden">
                        
                        <!-- Uploaded / Selected Image Editor Preview -->
                        <template x-if="imagePreviewUrl">
                            <div class="space-y-3">
                                <div class="relative w-48 h-48 mx-auto rounded-xl overflow-hidden bg-slate-900 border border-slate-200 shadow-md flex items-center justify-center">
                                    <img :src="imagePreviewUrl" 
                                        :style="`transform: rotate(${rotation}deg) scaleX(${isFlipped ? -1 : 1}); transition: transform 0.2s ease;`" 
                                        class="max-w-full max-h-full object-contain" alt="Preview Foto">
                                </div>

                                <!-- Image Editor Toolbar -->
                                <div class="flex items-center justify-center gap-1.5 flex-wrap">
                                    <button type="button" @click="rotateImage(-90)" title="Putar Kiri 90°"
                                        class="px-2.5 py-1.5 bg-white border border-slate-200 rounded-lg text-xs font-semibold text-slate-700 hover:bg-blue-50 hover:text-blue-600 shadow-sm transition-all flex items-center gap-1">
                                        <i class="fa-solid fa-rotate-left"></i> Putar Kiri
                                    </button>
                                    <button type="button" @click="rotateImage(90)" title="Putar Kanan 90°"
                                        class="px-2.5 py-1.5 bg-white border border-slate-200 rounded-lg text-xs font-semibold text-slate-700 hover:bg-blue-50 hover:text-blue-600 shadow-sm transition-all flex items-center gap-1">
                                        <i class="fa-solid fa-rotate-right"></i> Putar Kanan
                                    </button>
                                    <button type="button" @click="flipImage()" title="Cermin / Flip Horizontal"
                                        class="px-2.5 py-1.5 bg-white border border-slate-200 rounded-lg text-xs font-semibold text-slate-700 hover:bg-blue-50 hover:text-blue-600 shadow-sm transition-all flex items-center gap-1">
                                        <i class="fa-solid fa-arrows-left-right"></i> Flip
                                    </button>
                                    <button type="button" @click="$refs.fotoInput.click()" title="Ganti Foto"
                                        class="px-2.5 py-1.5 bg-white border border-slate-200 rounded-lg text-xs font-semibold text-slate-700 hover:bg-slate-100 shadow-sm transition-all flex items-center gap-1">
                                        <i class="fa-solid fa-upload"></i> Ganti
                                    </button>
                                    <button type="button" @click="removePhoto()" title="Hapus Foto"
                                        class="px-2.5 py-1.5 bg-rose-50 border border-rose-200 rounded-lg text-xs font-semibold text-rose-600 hover:bg-rose-100 shadow-sm transition-all flex items-center gap-1">
                                        <i class="fa-solid fa-trash"></i> Hapus
                                    </button>
                                </div>
                            </div>
                        </template>
                        
                        <!-- Default Drop Box Drop Zone -->
                        <template x-if="!imagePreviewUrl">
                            <div @click="$refs.fotoInput.click()" class="cursor-pointer py-4">
                                <div class="w-12 h-12 rounded-full bg-blue-50 text-blue-600 flex items-center justify-center mx-auto mb-2 text-xl shadow-sm">
                                    <i class="fa-solid fa-cloud-arrow-up"></i>
                                </div>
                                <p class="text-sm font-semibold text-slate-800">Tarik & lepas foto produk di sini</p>
                                <p class="text-xs text-slate-400 mt-0.5">atau <span class="text-blue-600 underline">klik untuk memilih file</span> (PNG, JPG, max 2MB)</p>
                            </div>
                        </template>
                    </div>
                </div>

                <div class="sm:col-span-2 flex gap-3 justify-end pt-2 border-t border-slate-100">
                    <button type="button" @click="showModal = false" class="px-5 py-2.5 border border-slate-200 rounded-xl text-sm font-semibold text-slate-600 hover:bg-slate-50">
                        Batal
                    </button>
                    <button type="submit" class="px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-xl text-sm shadow-md shadow-blue-600/20">
                        <i class="fa-solid fa-save mr-2"></i> <span x-text="editMode ? 'Simpan Perubahan' : 'Simpan Produk'"></span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Single Delete Confirmation Modal -->
    <div x-show="showSingleDeleteModal" x-cloak class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl w-full max-w-md shadow-2xl p-6" @click.stop>
            <div class="w-12 h-12 rounded-full bg-rose-100 text-rose-600 flex items-center justify-center mx-auto mb-4 text-xl">
                <i class="fa-solid fa-triangle-exclamation"></i>
            </div>
            <h3 class="text-lg font-bold text-slate-900 text-center mb-1">Hapus Produk</h3>
            <p class="text-sm text-slate-500 text-center mb-6">
                Apakah Anda yakin ingin menghapus produk <strong class="text-slate-800" x-text="deleteItem.nama"></strong>? Data yang dihapus tidak dapat dikembalikan.
            </p>

            <form :action="`/tefa/produk/${deleteItem.id}`" method="POST" class="flex gap-3 justify-end">
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

    <!-- Multi Delete Confirmation Modal -->
    <div x-show="showBulkDeleteModal" x-cloak class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl w-full max-w-md shadow-2xl p-6" @click.stop>
            <div class="w-12 h-12 rounded-full bg-rose-100 text-rose-600 flex items-center justify-center mx-auto mb-4 text-xl">
                <i class="fa-solid fa-trash-can"></i>
            </div>
            <h3 class="text-lg font-bold text-slate-900 text-center mb-1">Hapus Multi Produk</h3>
            <p class="text-sm text-slate-500 text-center mb-6">
                Apakah Anda yakin ingin menghapus <strong class="text-slate-800"><span x-text="selectedIds.length"></span> produk</strong> yang dipilih? Tindakan ini tidak dapat dibatalkan.
            </p>

            <form action="{{ route('tefa.produk.bulk-destroy') }}" method="POST" class="space-y-4">
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
function produkPage() {
    return {
        showModal: false,
        editMode: false,
        actionUrl: '{{ route('tefa.produk.store') }}',
        form: { id: null, nama: '', kategori_produk_id: '', satuan: 'pcs', stok_minimum: 5, status: 'aktif' },
        
        // Multi Delete & Single Delete States
        currentPageIds: [{{ $produk->pluck('id')->implode(',') }}],
        selectedIds: [],
        showSingleDeleteModal: false,
        deleteItem: { id: null, nama: '' },
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
        openSingleDelete(id, nama) {
            this.deleteItem = { id: id, nama: nama };
            this.showSingleDeleteModal = true;
        },

        // Harga formatting
        rawHargaJual: 0,
        formattedHargaJual: '',
        rawHargaModal: 0,
        formattedHargaModal: '',
        
        // Image Upload & Editor
        imagePreviewUrl: null,
        existingPhotoUrl: null,
        fotoBase64: '',
        isDragging: false,
        rotation: 0,
        isFlipped: false,
        
        formatRupiah(num) {
            if (num === null || num === undefined || num === '') return '';
            let val = num.toString().replace(/[^0-9]/g, '');
            if (!val) return '';
            return new Intl.NumberFormat('id-ID').format(val);
        },
        parseNumber(str) {
            if (!str) return 0;
            return parseInt(str.toString().replace(/[^0-9]/g, '')) || 0;
        },
        
        onHargaJualInput(e) {
            let clean = this.parseNumber(e.target.value);
            this.rawHargaJual = clean;
            this.formattedHargaJual = clean > 0 ? this.formatRupiah(clean) : '';
        },
        onHargaModalInput(e) {
            let clean = this.parseNumber(e.target.value);
            this.rawHargaModal = clean;
            this.formattedHargaModal = clean > 0 ? this.formatRupiah(clean) : '';
        },
        
        openCreate() {
            this.editMode = false;
            this.actionUrl = '{{ route('tefa.produk.store') }}';
            this.form = { id: null, nama: '', kategori_produk_id: '', satuan: 'pcs', stok_minimum: 5, status: 'aktif' };
            this.rawHargaJual = 0;
            this.formattedHargaJual = '';
            this.rawHargaModal = 0;
            this.formattedHargaModal = '';
            this.removePhoto();
            this.showModal = true;
        },
        openEdit(item) {
            this.editMode = true;
            this.actionUrl = '/tefa/produk/' + item.id;
            this.form = {
                id: item.id,
                nama: item.nama || '',
                kategori_produk_id: item.kategori_produk_id || '',
                satuan: item.satuan || 'pcs',
                stok_minimum: item.stok_minimum ?? 5,
                status: item.status || 'aktif'
            };
            this.rawHargaJual = item.harga_jual || 0;
            this.formattedHargaJual = this.rawHargaJual ? this.formatRupiah(this.rawHargaJual) : '';
            this.rawHargaModal = item.harga_modal || 0;
            this.formattedHargaModal = this.rawHargaModal ? this.formatRupiah(this.rawHargaModal) : '';
            
            this.removePhoto();
            if (item.foto) {
                this.existingPhotoUrl = '{{ asset('storage') }}/' + item.foto;
                this.imagePreviewUrl = this.existingPhotoUrl;
            }
            this.showModal = true;
        },
        
        // Image Drag Drop & Editor
        handleFileSelect(e) {
            const file = e.target.files[0];
            if (file) this.loadImage(file);
        },
        handleDrop(e) {
            this.isDragging = false;
            const files = e.dataTransfer.files;
            if (files.length > 0 && files[0].type.startsWith('image/')) {
                this.$refs.fotoInput.files = files;
                this.loadImage(files[0]);
            }
        },
        loadImage(file) {
            const reader = new FileReader();
            reader.onload = (e) => {
                this.imagePreviewUrl = e.target.result;
                this.rotation = 0;
                this.isFlipped = false;
                this.fotoBase64 = '';
            };
            reader.readAsDataURL(file);
        },
        rotateImage(deg) {
            this.rotation = (this.rotation + deg) % 360;
            this.applyImageTransformations();
        },
        flipImage() {
            this.isFlipped = !this.isFlipped;
            this.applyImageTransformations();
        },
        removePhoto() {
            this.imagePreviewUrl = null;
            this.existingPhotoUrl = null;
            this.fotoBase64 = '';
            this.rotation = 0;
            this.isFlipped = false;
            if (this.$refs.fotoInput) this.$refs.fotoInput.value = '';
        },
        applyImageTransformations() {
            if (!this.imagePreviewUrl) return;
            const img = new Image();
            img.crossOrigin = 'anonymous';
            img.onload = () => {
                const canvas = document.createElement('canvas');
                const ctx = canvas.getContext('2d');
                
                const rad = (this.rotation * Math.PI) / 180;
                const sin = Math.abs(Math.sin(rad));
                const cos = Math.abs(Math.cos(rad));
                
                canvas.width = img.width * cos + img.height * sin;
                canvas.height = img.width * sin + img.height * cos;
                
                ctx.translate(canvas.width / 2, canvas.height / 2);
                ctx.rotate(rad);
                ctx.scale(this.isFlipped ? -1 : 1, 1);
                ctx.drawImage(img, -img.width / 2, -img.height / 2);
                
                this.fotoBase64 = canvas.toDataURL('image/png');
            };
            img.src = this.imagePreviewUrl;
        }
    };
}
</script>
@endsection
