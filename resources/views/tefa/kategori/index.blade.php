@extends('layouts.app')

@section('title', 'Kategori Produk')

@section('content')
<div x-data="{ 
    showModal: false, 
    editMode: false,
    actionUrl: '{{ route('tefa.kategori.store') }}',
    form: { id: null, nama: '', deskripsi: '', ikon: '', urutan: 0, status: 'aktif' },
    iconSearch: '',
    availableIcons: [
        'fa-solid fa-utensils',
        'fa-solid fa-mug-hot',
        'fa-solid fa-burger',
        'fa-solid fa-bowl-food',
        'fa-solid fa-shirt',
        'fa-solid fa-laptop',
        'fa-solid fa-mobile-screen',
        'fa-solid fa-wrench',
        'fa-solid fa-screwdriver-wrench',
        'fa-solid fa-box-open',
        'fa-solid fa-tags',
        'fa-solid fa-basket-shopping',
        'fa-solid fa-cart-shopping',
        'fa-solid fa-store',
        'fa-solid fa-print',
        'fa-solid fa-scissors',
        'fa-solid fa-spray-can-sparkles',
        'fa-solid fa-car',
        'fa-solid fa-book',
        'fa-solid fa-graduation-cap',
        'fa-solid fa-camera',
        'fa-solid fa-paint-roller',
        'fa-solid fa-couch',
        'fa-solid fa-pump-soap',
        'fa-solid fa-heart-pulse',
        'fa-solid fa-microchip',
        'fa-solid fa-bolt',
        'fa-solid fa-gears',
        'fa-solid fa-layer-group',
        'fa-solid fa-cubes',
        'fa-solid fa-gift',
        'fa-solid fa-receipt',
        'fa-solid fa-hammer',
        'fa-solid fa-briefcase',
        'fa-solid fa-plug',
        'fa-solid fa-ticket'
    ],
    get filteredIcons() {
        if (!this.iconSearch) return this.availableIcons;
        const q = this.iconSearch.toLowerCase();
        return this.availableIcons.filter(i => i.toLowerCase().includes(q));
    },
    openCreate() {
        this.editMode = false;
        this.actionUrl = '{{ route('tefa.kategori.store') }}';
        this.form = { id: null, nama: '', deskripsi: '', ikon: '', urutan: 0, status: 'aktif' };
        this.iconSearch = '';
        this.showModal = true;
    },
    openEdit(item) {
        this.editMode = true;
        this.actionUrl = '/tefa/kategori/' + item.id;
        this.form = { 
            id: item.id, 
            nama: item.nama || '', 
            deskripsi: item.deskripsi || '', 
            ikon: item.ikon || '', 
            urutan: item.urutan ?? 0, 
            status: item.status || 'aktif' 
        };
        this.iconSearch = '';
        this.showModal = true;
    }
}" class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-xl font-bold text-slate-900">Kategori Produk</h2>
            <p class="text-sm text-slate-500">Kelola kategori produk & jasa TEFa</p>
        </div>
        <button @click="openCreate()"
            class="inline-flex items-center gap-2 px-4 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-xl text-sm shadow-md shadow-blue-600/25 transition-all">
            <i class="fa-solid fa-plus"></i> Tambah Kategori
        </button>
    </div>

    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <table class="w-full text-left border-collapse">
            <thead class="bg-slate-50 border-b border-slate-200">
                <tr class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider">
                    <th class="py-3.5 px-4">Nama Kategori</th>
                    <th class="py-3.5 px-4 text-center">Jumlah Produk</th>
                    <th class="py-3.5 px-4 text-center">Status</th>
                    <th class="py-3.5 px-4 text-center">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 text-sm">
                @forelse($kategori as $k)
                <tr class="hover:bg-slate-50">
                    <td class="py-3.5 px-4">
                        <div class="flex items-center gap-3">
                            @if($k->ikon)
                            <div class="w-10 h-10 rounded-lg bg-blue-50 text-blue-700 flex items-center justify-center">
                                <i class="{{ $k->ikon }}"></i>
                            </div>
                            @endif
                            <div>
                                <p class="font-semibold text-slate-900">{{ $k->nama }}</p>
                                <p class="text-xs text-slate-400">{{ $k->slug }}</p>
                            </div>
                        </div>
                    </td>
                    <td class="py-3.5 px-4 text-center">
                        <span class="px-3 py-1 bg-blue-50 text-blue-700 rounded-lg font-bold text-xs">{{ $k->produk_count }} Produk</span>
                    </td>
                    <td class="py-3.5 px-4 text-center">
                        <span class="px-3 py-1 rounded-full text-xs font-bold uppercase {{ getStatusColor($k->status) }}">{{ $k->status }}</span>
                    </td>
                    <td class="py-3.5 px-4 text-center">
                        <div class="flex items-center justify-center gap-2">
                            <button @click="openEdit({{ json_encode($k) }})" class="w-8 h-8 rounded-lg bg-amber-100 text-amber-700 hover:bg-amber-200 inline-flex items-center justify-center text-xs transition-all" title="Edit Kategori">
                                <i class="fa-solid fa-pen"></i>
                            </button>
                            <form action="{{ route('tefa.kategori.destroy', $k->id) }}" method="POST" class="inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus kategori produk ini?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="w-8 h-8 rounded-lg bg-red-100 text-red-600 hover:bg-red-200 inline-flex items-center justify-center text-xs transition-all" title="Hapus Kategori">
                                    <i class="fa-solid fa-trash"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" class="py-16 text-center text-slate-400">
                        <i class="fa-solid fa-tags text-5xl mb-3 block opacity-20"></i>
                        <p class="text-sm">Belum ada kategori produk.</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
        @if($kategori->hasPages())
        <div class="px-4 py-3 border-t border-slate-100">{{ $kategori->links() }}</div>
        @endif
    </div>

    <!-- Modal Form (Tambah / Edit Kategori) -->
    <div x-show="showModal" x-cloak class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl w-full max-w-md shadow-2xl p-6" @click.stop>
            <div class="flex items-center justify-between mb-5">
                <h3 class="text-lg font-bold text-slate-900" x-text="editMode ? 'Edit Kategori Produk' : 'Tambah Kategori Produk'"></h3>
                <button @click="showModal = false" class="text-slate-400 hover:text-slate-600"><i class="fa-solid fa-times text-xl"></i></button>
            </div>
            <form method="POST" :action="actionUrl" class="space-y-4">
                @csrf
                <template x-if="editMode">
                    <input type="hidden" name="_method" value="PUT">
                </template>

                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Nama Kategori *</label>
                    <input type="text" name="nama" required x-model="form.nama" placeholder="Contoh: Makanan & Minuman"
                        class="w-full px-3 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Deskripsi</label>
                    <textarea name="deskripsi" x-model="form.deskripsi" rows="2" placeholder="Deskripsi kategori..."
                        class="w-full px-3 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 resize-none"></textarea>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Ikon Kategori</label>
                    <div class="flex items-center gap-2 mb-2">
                        <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-600 border border-blue-200 flex items-center justify-center text-lg flex-shrink-0 shadow-sm">
                            <i :class="form.ikon ? form.ikon : 'fa-solid fa-icons'"></i>
                        </div>
                        <input type="text" name="ikon" x-model="form.ikon" placeholder="fa-solid fa-utensils"
                            class="w-full px-3 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>

                    <!-- List Pilihan Icon Free -->
                    <div class="bg-slate-50 p-2.5 rounded-xl border border-slate-200">
                        <div class="flex items-center justify-between mb-2 gap-2">
                            <span class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Pilih Ikon Gratis</span>
                            <input type="text" x-model="iconSearch" placeholder="Cari ikon..." class="px-2.5 py-1 text-xs rounded-lg border border-slate-200 bg-white focus:outline-none focus:ring-1 focus:ring-blue-500">
                        </div>
                        <div class="grid grid-cols-6 gap-1.5 max-h-36 overflow-y-auto p-1">
                            <template x-for="icon in filteredIcons" :key="icon">
                                <button type="button" 
                                    @click="form.ikon = icon" 
                                    :class="form.ikon === icon ? 'bg-blue-600 text-white shadow-sm ring-2 ring-blue-400 font-bold scale-105' : 'bg-white text-slate-600 hover:bg-blue-50 hover:text-blue-600 border border-slate-200'"
                                    class="h-9 rounded-lg flex items-center justify-center text-sm transition-all" 
                                    :title="icon">
                                    <i :class="icon"></i>
                                </button>
                            </template>
                        </div>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Urutan</label>
                        <input type="number" name="urutan" x-model="form.urutan" min="0" placeholder="0"
                            class="w-full px-3 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Status</label>
                        <select name="status" x-model="form.status" class="w-full px-3 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">
                            <option value="aktif">Aktif</option>
                            <option value="nonaktif">Nonaktif</option>
                        </select>
                    </div>
                </div>
                <div class="flex gap-3 justify-end pt-2">
                    <button type="button" @click="showModal = false" class="px-5 py-2.5 border border-slate-200 rounded-xl text-sm font-semibold text-slate-600">Batal</button>
                    <button type="submit" class="px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-xl text-sm" x-text="editMode ? 'Simpan Perubahan' : 'Simpan'"></button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
