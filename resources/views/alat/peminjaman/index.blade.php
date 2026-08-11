@extends('layouts.app')

@section('title', 'Daftar Peminjaman Alat')

@section('content')
<div x-data="{
    showAddModal: false,
    showEditModal: false,
    showDeleteModal: false,
    showApproveModal: false,
    showRejectModal: false,

    selectedId: null,
    deleteUrl: '',
    deleteKode: '',

    // Form Tambah
    addForm: {
        pelanggan_id: '',
        tanggal_pinjam: '{{ date('Y-m-d') }}',
        tanggal_kembali_rencana: '{{ date('Y-m-d', strtotime('+7 days')) }}',
        keperluan: '',
        tujuan_penggunaan: '',
        lokasi_penggunaan: '',
        catatan_peminjam: '',
        auto_approve: '1',
        items: [
            { alat_id: '', jumlah: 1 }
        ]
    },

    // Form Edit
    editForm: {
        id: null,
        kode_peminjaman: '',
        pelanggan_id: '',
        tanggal_pinjam: '',
        tanggal_kembali_rencana: '',
        keperluan: '',
        tujuan_penggunaan: '',
        lokasi_penggunaan: '',
        status: '',
        catatan_peminjam: ''
    },

    addItem() {
        this.addForm.items.push({ alat_id: '', jumlah: 1 });
    },

    removeItem(index) {
        if (this.addForm.items.length > 1) {
            this.addForm.items.splice(index, 1);
        }
    },

    openEdit(item) {
        this.editForm = {
            id: item.id,
            kode_peminjaman: item.kode_peminjaman,
            pelanggan_id: item.pelanggan_id || item.peminjam_id,
            tanggal_pinjam: item.tanggal_pinjam ? item.tanggal_pinjam.substring(0,10) : '',
            tanggal_kembali_rencana: item.tanggal_kembali_rencana ? item.tanggal_kembali_rencana.substring(0,10) : '',
            keperluan: item.keperluan || '',
            tujuan_penggunaan: item.tujuan_penggunaan || '',
            lokasi_penggunaan: item.lokasi_penggunaan || '',
            status: item.status || 'menunggu_persetujuan',
            catatan_peminjam: item.catatan_peminjam || ''
        };
        this.showEditModal = true;
    },

    openDelete(url, kode) {
        this.deleteUrl = url;
        this.deleteKode = kode;
        this.showDeleteModal = true;
    }
}" class="space-y-6">

    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-xl font-bold text-slate-900">Peminjaman Alat</h2>
            <p class="text-sm text-slate-500">Kelola pengajuan, persetujuan, dan pengembalian alat</p>
        </div>
        <button @click="showAddModal = true"
            class="inline-flex items-center gap-2 px-4 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-xl text-sm shadow-md shadow-blue-600/25 transition-all">
            <i class="fa-solid fa-plus"></i> Tambah Peminjaman
        </button>
    </div>

    {{-- Summary Badges --}}
    <div class="flex flex-wrap gap-3">
        @php
        $statusCounts = $peminjaman->groupBy('status');
        @endphp
        @foreach(['menunggu_persetujuan' => 'Menunggu', 'disetujui' => 'Disetujui', 'dipinjam' => 'Dipinjam', 'terlambat' => 'Terlambat', 'dikembalikan' => 'Selesai'] as $key => $label)
        <div class="px-4 py-2 bg-white border border-slate-200 rounded-xl text-sm font-semibold text-slate-700 shadow-sm flex items-center gap-2">
            <span class="inline-block px-2 py-0.5 rounded-md {{ getStatusColor($key) }} text-xs font-bold">{{ $statusCounts->get($key, collect())->count() }}</span>
            <span>{{ $label }}</span>
        </div>
        @endforeach
    </div>

    {{-- Filter Bar --}}
    <form method="GET" class="bg-white p-4 rounded-2xl border border-slate-200 shadow-sm flex flex-wrap gap-3">
        <input type="date" name="tanggal_mulai" value="{{ request('tanggal_mulai') }}"
            class="py-2 px-3 text-sm rounded-lg border border-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-500">
        <input type="date" name="tanggal_selesai" value="{{ request('tanggal_selesai') }}"
            class="py-2 px-3 text-sm rounded-lg border border-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-500">
        <select name="status" class="py-2 px-3 text-sm rounded-lg border border-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">
            <option value="">Semua Status</option>
            <option value="menunggu_persetujuan" @selected(request('status')=='menunggu_persetujuan')>Menunggu Persetujuan</option>
            <option value="disetujui" @selected(request('status')=='disetujui')>Disetujui</option>
            <option value="dipinjam" @selected(request('status')=='dipinjam')>Dipinjam</option>
            <option value="dikembalikan" @selected(request('status')=='dikembalikan')>Dikembalikan</option>
            <option value="terlambat" @selected(request('status')=='terlambat')>Terlambat</option>
            <option value="ditolak" @selected(request('status')=='ditolak')>Ditolak</option>
        </select>
        <button type="submit" class="px-4 py-2 bg-slate-800 text-white font-semibold rounded-lg text-sm hover:bg-slate-700 transition">Filter</button>
        @if(request()->hasAny(['tanggal_mulai','tanggal_selesai','status']))
        <a href="{{ route('alat.peminjaman.index') }}" class="py-2 px-3 text-sm text-slate-500 hover:text-red-600 font-medium">
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
                        <th class="py-3.5 px-4">Peminjam</th>
                        <th class="py-3.5 px-4">Tgl Pinjam</th>
                        <th class="py-3.5 px-4">Rencana Kembali</th>
                        <th class="py-3.5 px-4">Alat (Qty)</th>
                        <th class="py-3.5 px-4 text-center">Status</th>
                        <th class="py-3.5 px-4 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-sm">
                    @forelse($peminjaman as $p)
                    <tr class="hover:bg-slate-50 transition-colors {{ $p->status == 'terlambat' ? 'bg-rose-50/50' : '' }}">
                        <td class="py-3.5 px-4">
                            <p class="font-bold text-slate-900 font-mono">{{ $p->kode_peminjaman }}</p>
                        </td>
                        <td class="py-3.5 px-4">
                            <p class="font-medium text-slate-800">{{ $p->peminjam?->nama ?? '-' }}</p>
                            <p class="text-xs text-slate-400">
                                {{ $p->peminjam?->tipe ? strtoupper($p->peminjam->tipe) : ($p->peminjam?->kelas ?? '-') }}
                                {{ $p->peminjam?->telepon ? '• '.$p->peminjam->telepon : '' }}
                            </p>
                        </td>
                        <td class="py-3.5 px-4 text-slate-600">{{ formatTanggal($p->tanggal_pinjam) }}</td>
                        <td class="py-3.5 px-4 {{ \Carbon\Carbon::parse($p->tanggal_kembali_rencana)->isPast() && !in_array($p->status, ['dikembalikan','ditolak']) ? 'text-rose-600 font-bold' : 'text-slate-600' }}">
                            {{ formatTanggal($p->tanggal_kembali_rencana) }}
                        </td>
                        <td class="py-3.5 px-4">
                            @foreach($p->items->take(2) as $item)
                            <p class="text-xs text-slate-600">• {{ $item->alat?->nama }} ({{ $item->jumlah_pinjam }})</p>
                            @endforeach
                            @if($p->items->count() > 2)
                            <p class="text-xs text-slate-400">+{{ $p->items->count() - 2 }} lainnya</p>
                            @endif
                        </td>
                        <td class="py-3.5 px-4 text-center">
                            <span class="px-3 py-1 rounded-full text-xs font-bold uppercase {{ getStatusColor($p->status) }}">
                                {{ str_replace('_', ' ', $p->status) }}
                            </span>
                        </td>
                        <td class="py-3.5 px-4">
                            <div class="flex items-center justify-center gap-1.5">
                                <a href="{{ route('alat.peminjaman.show', $p->id) }}" title="Detail"
                                    class="w-8 h-8 rounded-lg bg-blue-100 text-blue-700 hover:bg-blue-200 flex items-center justify-center text-xs transition-colors">
                                    <i class="fa-solid fa-eye"></i>
                                </a>

                                <button type="button" title="Edit"
                                    @click="openEdit({{ json_encode([
                                        'id' => $p->id,
                                        'kode_peminjaman' => $p->kode_peminjaman,
                                        'pelanggan_id' => $p->pelanggan_id ?? $p->peminjam_id,
                                        'tanggal_pinjam' => $p->tanggal_pinjam,
                                        'tanggal_kembali_rencana' => $p->tanggal_kembali_rencana,
                                        'keperluan' => $p->keperluan,
                                        'tujuan_penggunaan' => $p->tujuan_penggunaan,
                                        'lokasi_penggunaan' => $p->lokasi_penggunaan,
                                        'status' => $p->status,
                                        'catatan_peminjam' => $p->catatan_peminjam,
                                    ]) }})"
                                    class="w-8 h-8 rounded-lg bg-amber-100 text-amber-700 hover:bg-amber-200 flex items-center justify-center text-xs transition-colors">
                                    <i class="fa-solid fa-pen"></i>
                                </button>

                                @if($p->status === 'menunggu_persetujuan')
                                <button @click="selectedId = {{ $p->id }}; showApproveModal = true" title="Setujui"
                                    class="w-8 h-8 rounded-lg bg-emerald-100 text-emerald-700 hover:bg-emerald-200 flex items-center justify-center text-xs transition-colors">
                                    <i class="fa-solid fa-check"></i>
                                </button>
                                <button @click="selectedId = {{ $p->id }}; showRejectModal = true" title="Tolak"
                                    class="w-8 h-8 rounded-lg bg-rose-100 text-rose-700 hover:bg-rose-200 flex items-center justify-center text-xs transition-colors">
                                    <i class="fa-solid fa-times"></i>
                                </button>
                                @endif

                                <button type="button" title="Hapus"
                                    @click="openDelete('{{ route('alat.peminjaman.destroy', $p->id) }}', '{{ $p->kode_peminjaman }}')"
                                    class="w-8 h-8 rounded-lg bg-red-100 text-red-600 hover:bg-red-200 flex items-center justify-center text-xs transition-colors">
                                    <i class="fa-solid fa-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="py-16 text-center text-slate-400">
                            <i class="fa-solid fa-hand-holding-hand text-5xl mb-3 block opacity-20"></i>
                            <p class="text-sm">Belum ada data peminjaman.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($peminjaman->hasPages())
        <div class="px-4 py-3 border-t border-slate-100">{{ $peminjaman->links() }}</div>
        @endif
    </div>

    {{-- ========= MODAL TAMBAH PEMINJAMAN ========= --}}
    <div x-show="showAddModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" @click="showAddModal = false"></div>
        <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-2xl max-h-[92vh] overflow-y-auto"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100">

            <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100 sticky top-0 bg-white z-10">
                <div>
                    <h3 class="font-bold text-slate-900 text-base">Tambah Peminjaman Alat</h3>
                    <p class="text-xs text-slate-500 mt-0.5">Input transaksi peminjaman alat baru</p>
                </div>
                <button @click="showAddModal = false" class="w-8 h-8 rounded-lg text-slate-400 hover:bg-slate-100 flex items-center justify-center transition">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <form action="{{ route('alat.peminjaman.store') }}" method="POST" class="p-6 space-y-5">
                @csrf

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="md:col-span-2">
                        <label class="text-xs font-semibold text-slate-600 block mb-1">Peminjam (Data Pelanggan) <span class="text-red-500">*</span></label>
                        <select name="pelanggan_id" x-model="addForm.pelanggan_id" required
                            class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">
                            <option value="">-- Pilih Peminjam (Pelanggan) --</option>
                            @foreach($pelangganList as $plg)
                            <option value="{{ $plg->id }}">{{ $plg->nama }} ({{ strtoupper($plg->tipe) }}{{ $plg->telepon ? ' - '.$plg->telepon : '' }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="text-xs font-semibold text-slate-600 block mb-1">Tanggal Pinjam <span class="text-red-500">*</span></label>
                        <input type="date" name="tanggal_pinjam" x-model="addForm.tanggal_pinjam" required
                            class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>

                    <div>
                        <label class="text-xs font-semibold text-slate-600 block mb-1">Rencana Kembali <span class="text-red-500">*</span></label>
                        <input type="date" name="tanggal_kembali_rencana" x-model="addForm.tanggal_kembali_rencana" required
                            class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>

                    <div class="md:col-span-2">
                        <label class="text-xs font-semibold text-slate-600 block mb-1">Keperluan <span class="text-red-500">*</span></label>
                        <input type="text" name="keperluan" x-model="addForm.keperluan" required
                            class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                            placeholder="Contoh: Praktikum Pemrograman Web, Proyek Tugas Akhir, dst.">
                    </div>

                    <div>
                        <label class="text-xs font-semibold text-slate-600 block mb-1">Tujuan Penggunaan</label>
                        <input type="text" name="tujuan_penggunaan" x-model="addForm.tujuan_penggunaan"
                            class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                            placeholder="Tujuan">
                    </div>

                    <div>
                        <label class="text-xs font-semibold text-slate-600 block mb-1">Lokasi Penggunaan</label>
                        <input type="text" name="lokasi_penggunaan" x-model="addForm.lokasi_penggunaan"
                            class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                            placeholder="Lab Komputer 1, Ruang Teori, dll.">
                    </div>
                </div>

                {{-- Opsi Auto Approve --}}
                <div class="p-3.5 bg-blue-50 rounded-xl border border-blue-100 flex items-center justify-between">
                    <div>
                        <p class="text-xs font-bold text-blue-900">Setujui Langsung (Auto Approve)</p>
                        <p class="text-[11px] text-blue-700">Peminjaman langsung berstatus 'Disetujui' dan mengurangi stok alat.</p>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" name="auto_approve" value="1" x-model="addForm.auto_approve" class="sr-only peer">
                        <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                    </label>
                </div>

                {{-- Daftar Alat Dipinjam --}}
                <div class="p-4 bg-slate-50 rounded-xl border border-slate-200 space-y-3">
                    <div class="flex items-center justify-between">
                        <label class="text-xs font-bold text-slate-700">Daftar Alat Dipinjam <span class="text-red-500">*</span></label>
                        <button type="button" @click="addItem()" class="text-xs font-semibold text-blue-600 hover:text-blue-800">
                            <i class="fa-solid fa-plus mr-1"></i> Tambah Item
                        </button>
                    </div>

                    <template x-for="(item, index) in addForm.items" :key="index">
                        <div class="flex items-center gap-3 bg-white p-3 rounded-lg border border-slate-200">
                            <div class="flex-1">
                                <select :name="'items['+index+'][alat_id]'" x-model="item.alat_id" required
                                    class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">
                                    <option value="">-- Pilih Alat --</option>
                                    @foreach($alatList as $alt)
                                    <option value="{{ $alt->id }}">{{ $alt->nama }} (Tersedia: {{ $alt->jumlah_tersedia }} {{ $alt->satuan }})</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="w-28">
                                <input type="number" :name="'items['+index+'][jumlah]'" x-model.number="item.jumlah" min="1" required placeholder="Jumlah"
                                    class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm text-center focus:outline-none focus:ring-2 focus:ring-blue-500">
                            </div>
                            <button type="button" @click="removeItem(index)" x-show="addForm.items.length > 1"
                                class="text-red-500 hover:text-red-700 text-sm p-2">
                                <i class="fa-solid fa-trash"></i>
                            </button>
                        </div>
                    </template>
                </div>

                {{-- Catatan --}}
                <div>
                    <label class="text-xs font-semibold text-slate-600 block mb-1">Catatan Peminjam</label>
                    <textarea name="catatan_peminjam" x-model="addForm.catatan_peminjam" rows="2"
                        class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 resize-none"
                        placeholder="Catatan tambahan (opsional)"></textarea>
                </div>

                <div class="flex items-center justify-end gap-3 pt-2 border-t border-slate-100">
                    <button type="button" @click="showAddModal = false"
                        class="px-5 py-2 text-sm font-semibold text-slate-600 hover:bg-slate-100 rounded-lg transition">Batal</button>
                    <button type="submit"
                        class="px-6 py-2 text-sm font-semibold bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition shadow">
                        <i class="fa-solid fa-save mr-1.5"></i> Simpan Peminjaman
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- ========= MODAL EDIT PEMINJAMAN ========= --}}
    <div x-show="showEditModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" @click="showEditModal = false"></div>
        <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-xl max-h-[92vh] overflow-y-auto"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100">

            <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100 sticky top-0 bg-white z-10">
                <div>
                    <h3 class="font-bold text-slate-900 text-base">Edit Peminjaman Alat</h3>
                    <p class="text-xs text-slate-500 mt-0.5">Kode: <span class="font-mono font-bold text-blue-600" x-text="editForm.kode_peminjaman"></span></p>
                </div>
                <button @click="showEditModal = false" class="w-8 h-8 rounded-lg text-slate-400 hover:bg-slate-100 flex items-center justify-center transition">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <form :action="'/alat/peminjaman/' + editForm.id" method="POST" class="p-6 space-y-4">
                @csrf
                @method('PUT')

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="md:col-span-2">
                        <label class="text-xs font-semibold text-slate-600 block mb-1">Peminjam (Data Pelanggan) <span class="text-red-500">*</span></label>
                        <select name="pelanggan_id" x-model="editForm.pelanggan_id" required
                            class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">
                            <option value="">-- Pilih Peminjam (Pelanggan) --</option>
                            @foreach($pelangganList as $plg)
                            <option value="{{ $plg->id }}">{{ $plg->nama }} ({{ strtoupper($plg->tipe) }}{{ $plg->telepon ? ' - '.$plg->telepon : '' }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="text-xs font-semibold text-slate-600 block mb-1">Tanggal Pinjam <span class="text-red-500">*</span></label>
                        <input type="date" name="tanggal_pinjam" x-model="editForm.tanggal_pinjam" required
                            class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>

                    <div>
                        <label class="text-xs font-semibold text-slate-600 block mb-1">Rencana Kembali <span class="text-red-500">*</span></label>
                        <input type="date" name="tanggal_kembali_rencana" x-model="editForm.tanggal_kembali_rencana" required
                            class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>

                    <div>
                        <label class="text-xs font-semibold text-slate-600 block mb-1">Status Peminjaman <span class="text-red-500">*</span></label>
                        <select name="status" x-model="editForm.status" required
                            class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">
                            <option value="menunggu_persetujuan">Menunggu Persetujuan</option>
                            <option value="disetujui">Disetujui</option>
                            <option value="dipinjam">Dipinjam</option>
                            <option value="dikembalikan">Dikembalikan</option>
                            <option value="terlambat">Terlambat</option>
                            <option value="ditolak">Ditolak</option>
                        </select>
                    </div>

                    <div>
                        <label class="text-xs font-semibold text-slate-600 block mb-1">Lokasi Penggunaan</label>
                        <input type="text" name="lokasi_penggunaan" x-model="editForm.lokasi_penggunaan"
                            class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>

                    <div class="md:col-span-2">
                        <label class="text-xs font-semibold text-slate-600 block mb-1">Keperluan <span class="text-red-500">*</span></label>
                        <input type="text" name="keperluan" x-model="editForm.keperluan" required
                            class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>

                    <div class="md:col-span-2">
                        <label class="text-xs font-semibold text-slate-600 block mb-1">Tujuan Penggunaan</label>
                        <input type="text" name="tujuan_penggunaan" x-model="editForm.tujuan_penggunaan"
                            class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                </div>

                <div>
                    <label class="text-xs font-semibold text-slate-600 block mb-1">Catatan Peminjam</label>
                    <textarea name="catatan_peminjam" x-model="editForm.catatan_peminjam" rows="2"
                        class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 resize-none"></textarea>
                </div>

                <div class="flex items-center justify-end gap-3 pt-2 border-t border-slate-100">
                    <button type="button" @click="showEditModal = false"
                        class="px-5 py-2 text-sm font-semibold text-slate-600 hover:bg-slate-100 rounded-lg transition">Batal</button>
                    <button type="submit"
                        class="px-6 py-2 text-sm font-semibold bg-amber-600 text-white rounded-lg hover:bg-amber-700 transition shadow">
                        <i class="fa-solid fa-save mr-1.5"></i> Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- ========= MODAL KONFIRMASI HAPUS ========= --}}
    <div x-show="showDeleteModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" @click="showDeleteModal = false"></div>
        <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-sm p-6 text-center"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100">
            <div class="w-14 h-14 rounded-full bg-red-100 flex items-center justify-center mx-auto mb-4">
                <i class="fa-solid fa-trash text-red-600 text-xl"></i>
            </div>
            <h3 class="font-bold text-slate-900 text-base mb-1">Hapus Peminjaman?</h3>
            <p class="text-sm text-slate-500 mb-1">Kode Transaksi:</p>
            <p class="font-semibold text-slate-800 font-mono mb-4" x-text="deleteKode"></p>
            <p class="text-xs text-rose-600 bg-rose-50 rounded-lg p-2.5 mb-5">
                <i class="fa-solid fa-triangle-exclamation mr-1"></i>
                Stok alat akan dikembalikan jika transaksi ini berstatus disetujui / dipinjam.
            </p>
            <div class="flex gap-3">
                <button type="button" @click="showDeleteModal = false"
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

    {{-- Approve Modal --}}
    <div x-show="showApproveModal" x-cloak class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl w-full max-w-md shadow-2xl p-6" @click.stop>
            <div class="text-center mb-5">
                <div class="w-14 h-14 bg-emerald-100 rounded-full flex items-center justify-center mx-auto mb-3">
                    <i class="fa-solid fa-check text-emerald-600 text-2xl"></i>
                </div>
                <h3 class="text-lg font-bold text-slate-900">Setujui Peminjaman</h3>
                <p class="text-sm text-slate-500 mt-1">Stok alat akan langsung dikurangi setelah disetujui</p>
            </div>
            <form :action="'/alat/peminjaman/' + selectedId + '/approve'" method="POST" class="space-y-3">
                @csrf
                <textarea name="catatan_admin" rows="2" placeholder="Catatan persetujuan (opsional)..."
                    class="w-full px-3 py-2 text-sm rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-emerald-500"></textarea>
                <div class="flex gap-3">
                    <button type="button" @click="showApproveModal = false" class="flex-1 py-2.5 border border-slate-200 rounded-xl text-sm font-semibold text-slate-600">Batal</button>
                    <button type="submit" class="flex-1 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-semibold rounded-xl text-sm">Setujui</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Reject Modal --}}
    <div x-show="showRejectModal" x-cloak class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl w-full max-w-md shadow-2xl p-6" @click.stop>
            <div class="text-center mb-5">
                <div class="w-14 h-14 bg-rose-100 rounded-full flex items-center justify-center mx-auto mb-3">
                    <i class="fa-solid fa-times text-rose-600 text-2xl"></i>
                </div>
                <h3 class="text-lg font-bold text-slate-900">Tolak Peminjaman</h3>
            </div>
            <form :action="'/alat/peminjaman/' + selectedId + '/reject'" method="POST" class="space-y-3">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Alasan Penolakan *</label>
                    <textarea name="rejection_reason" rows="3" required placeholder="Jelaskan alasan penolakan..."
                        class="w-full px-3 py-2 text-sm rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-rose-500"></textarea>
                </div>
                <div class="flex gap-3">
                    <button type="button" @click="showRejectModal = false" class="flex-1 py-2.5 border border-slate-200 rounded-xl text-sm font-semibold text-slate-600">Batal</button>
                    <button type="submit" class="flex-1 py-2.5 bg-rose-600 hover:bg-rose-700 text-white font-semibold rounded-xl text-sm">Tolak</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
