@extends('layouts.app')

@section('title', 'Data Pelanggan')

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
        tipe: 'umum',
        telepon: '',
        email: '',
        alamat: '',
        status: 'aktif',
        catatan: ''
    },
    openAdd() {
        this.editMode = false;
        this.form = { id: null, nama: '', tipe: 'umum', telepon: '', email: '', alamat: '', status: 'aktif', catatan: '' };
        this.showModal = true;
    },
    openEdit(item) {
        this.editMode = true;
        this.form = { ...item };
        this.showModal = true;
    },
    openDelete(url, nama) {
        this.deleteUrl = url;
        this.deleteNama = nama;
        this.deleteModal = true;
    },
    get actionUrl() {
        return this.editMode ? '/tefa/pelanggan/' + this.form.id : '{{ route('tefa.pelanggan.store') }}';
    }
}" class="space-y-6">

    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-xl font-bold text-slate-900">Data Pelanggan</h2>
            <p class="text-sm text-slate-500">Kelola database pelanggan TEFa (Umum, Siswa, Guru, Instansi)</p>
        </div>
        <button @click="openAdd()"
            class="inline-flex items-center gap-2 px-4 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-xl text-sm shadow-md shadow-blue-600/25 transition-all">
            <i class="fa-solid fa-user-plus"></i> Tambah Pelanggan
        </button>
    </div>

    {{-- Stat Cards --}}
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
        @php
            $totalPelanggan = $pelanggan->total();
            $totalUmum      = \App\Models\Pelanggan::where('tipe', 'umum')->count();
            $totalSekolah   = \App\Models\Pelanggan::whereIn('tipe', ['siswa', 'guru'])->count();
            $totalInstansi  = \App\Models\Pelanggan::where('tipe', 'instansi')->count();
        @endphp
        <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-sm">
            <p class="text-xs text-slate-500 font-medium mb-1">Total Pelanggan</p>
            <p class="text-2xl font-bold text-slate-900">{{ $totalPelanggan }}</p>
        </div>
        <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-sm">
            <p class="text-xs text-blue-600 font-medium mb-1">Pelanggan Umum</p>
            <p class="text-2xl font-bold text-blue-700">{{ $totalUmum }}</p>
        </div>
        <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-sm">
            <p class="text-xs text-emerald-600 font-medium mb-1">Siswa / Guru</p>
            <p class="text-2xl font-bold text-emerald-700">{{ $totalSekolah }}</p>
        </div>
        <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-sm">
            <p class="text-xs text-purple-600 font-medium mb-1">Instansi / Mitra</p>
            <p class="text-2xl font-bold text-purple-700">{{ $totalInstansi }}</p>
        </div>
    </div>

    {{-- Filter Bar --}}
    <form method="GET" class="bg-white p-4 rounded-2xl border border-slate-200 shadow-sm flex flex-wrap gap-3">
        <div class="flex-1 min-w-48 relative">
            <i class="fa-solid fa-search absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm"></i>
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama, kode, telepon..."
                class="w-full pl-9 pr-4 py-2 text-sm rounded-lg border border-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>
        <select name="tipe" class="py-2 px-3 text-sm rounded-lg border border-slate-200 bg-white focus:outline-none focus:ring-2 focus:ring-blue-500">
            <option value="">Semua Tipe</option>
            <option value="umum" @selected(request('tipe') == 'umum')>Umum</option>
            <option value="siswa" @selected(request('tipe') == 'siswa')>Siswa</option>
            <option value="guru" @selected(request('tipe') == 'guru')>Guru / Staf</option>
            <option value="instansi" @selected(request('tipe') == 'instansi')>Instansi</option>
        </select>
        <select name="status" class="py-2 px-3 text-sm rounded-lg border border-slate-200 bg-white focus:outline-none focus:ring-2 focus:ring-blue-500">
            <option value="">Semua Status</option>
            <option value="aktif" @selected(request('status') == 'aktif')>Aktif</option>
            <option value="nonaktif" @selected(request('status') == 'nonaktif')>Nonaktif</option>
        </select>
        <button type="submit" class="px-4 py-2 bg-slate-800 text-white font-semibold rounded-lg text-sm hover:bg-slate-700 transition">Filter</button>
        @if(request()->hasAny(['search','tipe','status']))
        <a href="{{ route('tefa.pelanggan.index') }}" class="py-2 px-3 text-sm text-slate-500 hover:text-red-600 font-medium">
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
                        <th class="py-3.5 px-4">Kode</th>
                        <th class="py-3.5 px-4">Nama Pelanggan</th>
                        <th class="py-3.5 px-4">Tipe</th>
                        <th class="py-3.5 px-4">Kontak</th>
                        <th class="py-3.5 px-4">Alamat</th>
                        <th class="py-3.5 px-4 text-center">Status</th>
                        <th class="py-3.5 px-4 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-sm">
                    @forelse($pelanggan as $p)
                    <tr class="hover:bg-slate-50 transition-colors">
                        <td class="py-3.5 px-4 font-mono font-bold text-xs text-slate-700">
                            {{ $p->kode_pelanggan }}
                        </td>
                        <td class="py-3.5 px-4">
                            <p class="font-semibold text-slate-900">{{ $p->nama }}</p>
                            @if($p->catatan)
                            <p class="text-xs text-slate-400 truncate max-w-xs">{{ $p->catatan }}</p>
                            @endif
                        </td>
                        <td class="py-3.5 px-4">
                            @php
                                $tipeColors = [
                                    'umum'     => 'bg-blue-50 text-blue-700 border-blue-200',
                                    'siswa'    => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                                    'guru'     => 'bg-indigo-50 text-indigo-700 border-indigo-200',
                                    'instansi' => 'bg-purple-50 text-purple-700 border-purple-200',
                                ];
                            @endphp
                            <span class="px-2.5 py-1 rounded-lg border text-xs font-semibold uppercase {{ $tipeColors[$p->tipe] ?? 'bg-slate-50 text-slate-700' }}">
                                {{ $p->tipe }}
                            </span>
                        </td>
                        <td class="py-3.5 px-4">
                            @if($p->telepon)
                            <p class="text-xs font-medium text-slate-700"><i class="fa-solid fa-phone text-slate-400 mr-1"></i>{{ $p->telepon }}</p>
                            @endif
                            @if($p->email)
                            <p class="text-xs text-slate-500"><i class="fa-solid fa-envelope text-slate-400 mr-1"></i>{{ $p->email }}</p>
                            @endif
                            @if(!$p->telepon && !$p->email)
                            <span class="text-xs text-slate-400">-</span>
                            @endif
                        </td>
                        <td class="py-3.5 px-4 text-xs text-slate-600 max-w-xs truncate">
                            {{ $p->alamat ?? '-' }}
                        </td>
                        <td class="py-3.5 px-4 text-center">
                            <span class="px-3 py-1 rounded-full text-xs font-bold uppercase {{ $p->status == 'aktif' ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-500' }}">
                                {{ $p->status }}
                            </span>
                        </td>
                        <td class="py-3.5 px-4">
                            <div class="flex items-center justify-center gap-1.5">
                                <button type="button" title="Edit"
                                    @click="openEdit({{ json_encode([
                                        'id'      => $p->id,
                                        'nama'    => $p->nama,
                                        'tipe'    => $p->tipe,
                                        'telepon' => $p->telepon,
                                        'email'   => $p->email,
                                        'alamat'  => $p->alamat,
                                        'status'  => $p->status,
                                        'catatan' => $p->catatan,
                                    ]) }})"
                                    class="w-8 h-8 rounded-lg bg-amber-100 text-amber-700 hover:bg-amber-200 flex items-center justify-center text-xs transition-colors">
                                    <i class="fa-solid fa-pen"></i>
                                </button>
                                <button type="button" title="Hapus"
                                    @click="openDelete('{{ route('tefa.pelanggan.destroy', $p->id) }}', '{{ $p->nama }}')"
                                    class="w-8 h-8 rounded-lg bg-red-100 text-red-600 hover:bg-red-200 flex items-center justify-center text-xs transition-colors">
                                    <i class="fa-solid fa-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="py-16 text-center text-slate-400">
                            <i class="fa-solid fa-users-slash text-5xl mb-3 block opacity-20"></i>
                            <p class="text-sm">Belum ada data pelanggan. Klik "Tambah Pelanggan" untuk membuat baru.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($pelanggan->hasPages())
        <div class="px-4 py-3 border-t border-slate-100">{{ $pelanggan->links() }}</div>
        @endif
    </div>

    {{-- ========= MODAL TAMBAH / EDIT ========= --}}
    <div x-show="showModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" @click="showModal = false"></div>
        <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-lg overflow-hidden"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100">

            <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100 bg-white">
                <div>
                    <h3 class="font-bold text-slate-900 text-base" x-text="editMode ? 'Edit Data Pelanggan' : 'Tambah Pelanggan'"></h3>
                    <p class="text-xs text-slate-500 mt-0.5">Kelola identitas dan kontak pelanggan TEFa</p>
                </div>
                <button @click="showModal = false" class="w-8 h-8 rounded-lg text-slate-400 hover:bg-slate-100 flex items-center justify-center transition">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <form :action="actionUrl" method="POST" class="p-6 space-y-4">
                @csrf
                <template x-if="editMode"><input type="hidden" name="_method" value="PUT"></template>

                <div>
                    <label class="text-xs font-semibold text-slate-600 block mb-1">Nama Pelanggan <span class="text-red-500">*</span></label>
                    <input type="text" name="nama" x-model="form.nama" required
                        class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                        placeholder="Nama lengkap atau nama instansi">
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="text-xs font-semibold text-slate-600 block mb-1">Tipe Pelanggan <span class="text-red-500">*</span></label>
                        <select name="tipe" x-model="form.tipe" required
                            class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">
                            <option value="umum">Umum</option>
                            <option value="siswa">Siswa</option>
                            <option value="guru">Guru / Staf</option>
                            <option value="instansi">Instansi / Mitra</option>
                        </select>
                    </div>
                    <div>
                        <label class="text-xs font-semibold text-slate-600 block mb-1">Status <span class="text-red-500">*</span></label>
                        <select name="status" x-model="form.status" required
                            class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">
                            <option value="aktif">Aktif</option>
                            <option value="nonaktif">Nonaktif</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="text-xs font-semibold text-slate-600 block mb-1">No. Telepon / WhatsApp</label>
                        <input type="text" name="telepon" x-model="form.telepon"
                            class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                            placeholder="081234567890">
                    </div>
                    <div>
                        <label class="text-xs font-semibold text-slate-600 block mb-1">Email</label>
                        <input type="email" name="email" x-model="form.email"
                            class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                            placeholder="email@domain.com">
                    </div>
                </div>

                <div>
                    <label class="text-xs font-semibold text-slate-600 block mb-1">Alamat</label>
                    <textarea name="alamat" x-model="form.alamat" rows="2"
                        class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 resize-none"
                        placeholder="Alamat lengkap"></textarea>
                </div>

                <div>
                    <label class="text-xs font-semibold text-slate-600 block mb-1">Catatan / Keterangan</label>
                    <input type="text" name="catatan" x-model="form.catatan"
                        class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                        placeholder="Catatan tambahan (opsional)">
                </div>

                <div class="flex items-center justify-end gap-3 pt-3 border-t border-slate-100">
                    <button type="button" @click="showModal = false"
                        class="px-5 py-2 text-sm font-semibold text-slate-600 hover:bg-slate-100 rounded-lg transition">Batal</button>
                    <button type="submit"
                        class="px-6 py-2 text-sm font-semibold bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition shadow">
                        <i class="fa-solid fa-save mr-1.5"></i>
                        <span x-text="editMode ? 'Simpan Perubahan' : 'Tambah Pelanggan'"></span>
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
            <h3 class="font-bold text-slate-900 text-base mb-1">Hapus Pelanggan?</h3>
            <p class="text-sm text-slate-500 mb-1">Anda akan menghapus pelanggan:</p>
            <p class="font-semibold text-slate-800 mb-4" x-text="deleteNama"></p>
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
