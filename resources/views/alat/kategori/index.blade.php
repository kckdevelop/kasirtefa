@extends('layouts.app')

@section('title', 'Kategori Alat & Barang')

@section('content')
<div x-data="{ showModal: false, editMode: false, form: {} }" class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-xl font-bold text-slate-900">Kategori Alat & Barang</h2>
            <p class="text-sm text-slate-500">Kelola pengelompokan jenis dan kategori alat serta barang inventaris</p>
        </div>
        <button @click="showModal = true; editMode = false; form = {status: 'aktif', urutan: 0}"
            class="inline-flex items-center gap-2 px-4 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-xl text-sm shadow-md shadow-blue-600/25 transition-all">
            <i class="fa-solid fa-plus"></i> Tambah Kategori Alat & Barang
        </button>
    </div>

    <!-- Table -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead class="bg-slate-50 border-b border-slate-200">
                    <tr class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider">
                        <th class="py-3.5 px-4">Nama Kategori</th>
                        <th class="py-3.5 px-4">Kode</th>
                        <th class="py-3.5 px-4">Deskripsi</th>
                        <th class="py-3.5 px-4 text-center">Jumlah Alat</th>
                        <th class="py-3.5 px-4 text-center">Status</th>
                        <th class="py-3.5 px-4 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-sm">
                    @forelse($kategori as $item)
                    <tr class="hover:bg-slate-50">
                        <td class="py-3.5 px-4 font-semibold text-slate-900">{{ $item->nama }}</td>
                        <td class="py-3.5 px-4 font-mono text-xs text-slate-600">{{ $item->kode ?? '-' }}</td>
                        <td class="py-3.5 px-4 text-slate-500 text-xs">{{ $item->deskripsi ?? '-' }}</td>
                        <td class="py-3.5 px-4 text-center font-bold text-slate-800">{{ $item->alat_count }}</td>
                        <td class="py-3.5 px-4 text-center">
                            <span class="px-3 py-1 rounded-full text-xs font-bold uppercase {{ getStatusColor($item->status) }}">{{ $item->status }}</span>
                        </td>
                        <td class="py-3.5 px-4 text-center">
                            <button @click="editMode = true; form = {{ json_encode($item) }}; showModal = true" class="w-8 h-8 rounded-lg bg-amber-100 text-amber-700 hover:bg-amber-200 inline-flex items-center justify-center text-xs">
                                <i class="fa-solid fa-pen"></i>
                            </button>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="py-12 text-center text-slate-400">Belum ada kategori alat.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($kategori->hasPages())
        <div class="px-4 py-3 border-t border-slate-100">{{ $kategori->links() }}</div>
        @endif
    </div>

    <!-- Modal Form -->
    <div x-show="showModal" x-cloak class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl w-full max-w-md shadow-2xl p-6" @click.stop>
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-bold text-slate-900" x-text="editMode ? 'Edit Kategori Alat' : 'Tambah Kategori Alat'"></h3>
                <button @click="showModal = false" class="text-slate-400 hover:text-slate-600"><i class="fa-solid fa-times text-xl"></i></button>
            </div>
            <form action="{{ route('alat.kategori.store') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Nama Kategori *</label>
                    <input type="text" name="nama" x-model="form.nama" required placeholder="Contoh: Mesin & Perkakasan" class="w-full px-3 py-2 rounded-xl border border-slate-200 text-sm">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Kode / Awalan Kode Alat</label>
                    <input type="text" name="kode" x-model="form.kode" placeholder="ALT-MSN..." class="w-full px-3 py-2 rounded-xl border border-slate-200 text-sm">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Deskripsi</label>
                    <textarea name="deskripsi" x-model="form.deskripsi" rows="2" class="w-full px-3 py-2 rounded-xl border border-slate-200 text-sm resize-none"></textarea>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Status</label>
                    <select name="status" x-model="form.status" class="w-full px-3 py-2 rounded-xl border border-slate-200 text-sm bg-white">
                        <option value="aktif">Aktif</option>
                        <option value="nonaktif">Nonaktif</option>
                    </select>
                </div>
                <div class="flex gap-3 justify-end pt-3 border-t border-slate-100">
                    <button type="button" @click="showModal = false" class="px-4 py-2 border border-slate-200 rounded-xl text-sm font-semibold text-slate-600">Batal</button>
                    <button type="submit" class="px-4 py-2 bg-blue-600 text-white font-semibold rounded-xl text-sm hover:bg-blue-700">Simpan Kategori</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
