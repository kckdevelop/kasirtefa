@extends('layouts.app')

@section('title', 'Buat Penyewaan Gedung / Lab')

@section('styles')
<style>
    .fasilitas-card { transition: all 0.2s ease; }
    .fasilitas-card:hover { transform: translateY(-1px); }
    .fasilitas-card.selected { border-color: #3b82f6; background: #eff6ff; }
</style>
@endsection

@section('content')
<div x-data="{
    selectedGedung: null,
    fasilitasList: [],
    selectedFasilitas: [],
    lamaSewa: 1,
    hargaGedungPerHari: 0,
    loadingFasilitas: false,
    statusPembayaran: 'belum_bayar',
    jumlahDibayar: 0,

    get subtotalGedung() {
        return this.hargaGedungPerHari * this.lamaSewa;
    },

    get subtotalFasilitas() {
        return this.selectedFasilitas.reduce((sum, item) => {
            return sum + (item.jumlah * item.harga_per_item * this.lamaSewa);
        }, 0);
    },

    get totalBiaya() {
        return this.subtotalGedung + this.subtotalFasilitas;
    },

    get sisaTagihan() {
        return Math.max(0, this.totalBiaya - (parseFloat(this.jumlahDibayar) || 0));
    },

    get kembalian() {
        return Math.max(0, (parseFloat(this.jumlahDibayar) || 0) - this.totalBiaya);
    },

    onStatusPembayaranChange() {
        if (this.statusPembayaran === 'lunas') {
            this.jumlahDibayar = this.totalBiaya;
        } else if (this.statusPembayaran === 'belum_bayar') {
            this.jumlahDibayar = 0;
        }
    },

    formatRupiah(n) {
        return 'Rp ' + Number(n || 0).toLocaleString('id-ID', { minimumFractionDigits: 0 });
    },

    loadFasilitas(gedungId) {
        if (!gedungId) { this.fasilitasList = []; this.selectedFasilitas = []; return; }
        this.loadingFasilitas = true;
        fetch('/sewa/gedung/' + gedungId + '/fasilitas-json')
            .then(r => r.json())
            .then(data => {
                this.fasilitasList = data.fasilitas;
                this.hargaGedungPerHari = parseFloat(data.gedung.harga_sewa_per_hari);
                this.selectedFasilitas = [];
                this.loadingFasilitas = false;
                this.onStatusPembayaranChange();
            })
            .catch(() => { this.loadingFasilitas = false; });
    },

    toggleFasilitas(fas) {
        const idx = this.selectedFasilitas.findIndex(f => f.id === fas.id);
        if (idx === -1) {
            this.selectedFasilitas.push({ id: fas.id, nama_fasilitas: fas.nama_fasilitas, harga_per_item: parseFloat(fas.harga_per_item), satuan: fas.satuan, jumlah_tersedia: fas.jumlah_tersedia, jumlah: 1 });
        } else {
            this.selectedFasilitas.splice(idx, 1);
        }
        if (this.statusPembayaran === 'lunas') {
            this.jumlahDibayar = this.totalBiaya;
        }
    },

    isFasilitasSelected(id) {
        return this.selectedFasilitas.some(f => f.id === id);
    },

    getSelectedFasilitas(id) {
        return this.selectedFasilitas.find(f => f.id === id);
    },

    hitungLamaSewa() {
        const mulai = document.getElementById('tanggal_mulai').value;
        const selesai = document.getElementById('tanggal_selesai').value;
        if (mulai && selesai) {
            const d1 = new Date(mulai);
            const d2 = new Date(selesai);
            const diff = Math.ceil((d2 - d1) / (1000 * 60 * 60 * 24)) + 1;
            this.lamaSewa = Math.max(1, diff);
            if (this.statusPembayaran === 'lunas') {
                this.jumlahDibayar = this.totalBiaya;
            }
        }
    }
}" class="space-y-6">

    {{-- Header --}}
    <div class="flex items-center gap-3">
        <a href="{{ route('sewa.transaksi.index') }}" class="p-2 text-slate-500 hover:text-slate-800 hover:bg-slate-100 rounded-xl transition-all">
            <i class="fa-solid fa-arrow-left"></i>
        </a>
        <div>
            <h2 class="text-xl font-bold text-slate-900">Buat Penyewaan Gedung / Lab</h2>
            <p class="text-sm text-slate-500">Isi formulir penyewaan gedung, pilih fasilitas, dan kalkulasi biaya</p>
        </div>
    </div>

    @if($errors->any())
    <div class="px-4 py-3 bg-rose-50 border border-rose-200 text-rose-800 rounded-xl text-sm">
        <p class="font-semibold mb-1"><i class="fa-solid fa-circle-exclamation mr-1"></i>Ada kesalahan input:</p>
        <ul class="list-disc list-inside space-y-0.5">
            @foreach($errors->all() as $err)
            <li>{{ $err }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <form method="POST" action="{{ route('sewa.transaksi.store') }}" x-ref="form">
        @csrf
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            {{-- LEFT: Form Fields --}}
            <div class="lg:col-span-2 space-y-5">

                {{-- Pilih Gedung --}}
                <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-sm">
                    <h3 class="font-bold text-slate-800 mb-4 flex items-center gap-2">
                        <span class="w-7 h-7 rounded-lg bg-blue-100 text-blue-600 flex items-center justify-center text-xs font-bold">1</span>
                        Pilih Gedung / Lab
                    </h3>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        @foreach($gedungList as $gedung)
                        <label class="cursor-pointer">
                            <input type="radio" name="gedung_lab_id" value="{{ $gedung->id }}"
                                @change="selectedGedung = {{ $gedung->id }}; hargaGedungPerHari = {{ $gedung->harga_sewa_per_hari }}; loadFasilitas({{ $gedung->id }})"
                                class="peer hidden" required>
                            <div class="peer-checked:ring-2 peer-checked:ring-blue-500 peer-checked:bg-blue-50 border border-slate-200 rounded-xl p-4 transition-all hover:border-blue-300 hover:bg-blue-50/30">
                                <div class="flex items-start justify-between">
                                    <div>
                                        <p class="font-semibold text-slate-800 text-sm">{{ $gedung->nama_gedung }}</p>
                                        <p class="text-xs text-slate-500 mt-0.5">{{ $gedung->kode_gedung }}</p>
                                        @if($gedung->lokasi)
                                        <p class="text-xs text-slate-500 mt-0.5"><i class="fa-solid fa-location-dot mr-1 text-rose-400"></i>{{ $gedung->lokasi }}</p>
                                        @endif
                                    </div>
                                    <div class="text-right">
                                        <p class="text-xs font-bold text-emerald-700">{{ formatRupiah($gedung->harga_sewa_per_hari) }}</p>
                                        <p class="text-xs text-slate-400">/hari</p>
                                    </div>
                                </div>
                                <div class="mt-2 flex items-center gap-3 text-xs text-slate-500">
                                    <span><i class="fa-solid fa-users mr-1 text-blue-400"></i>{{ number_format($gedung->kapasitas) }} org</span>
                                    <span><i class="fa-solid fa-layer-group mr-1 text-violet-400"></i>{{ $gedung->fasilitas->count() }} fasilitas</span>
                                </div>
                            </div>
                        </label>
                        @endforeach
                    </div>
                    @if($gedungList->isEmpty())
                    <div class="text-center py-8 text-slate-400 text-sm">
                        <i class="fa-solid fa-building text-3xl mb-2 block"></i>
                        Belum ada gedung tersedia. <a href="{{ route('sewa.gedung.index') }}" class="text-blue-600 underline">Tambah gedung dulu</a>.
                    </div>
                    @endif
                </div>

                {{-- Pilih Fasilitas --}}
                <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-sm">
                    <h3 class="font-bold text-slate-800 mb-4 flex items-center gap-2">
                        <span class="w-7 h-7 rounded-lg bg-violet-100 text-violet-600 flex items-center justify-center text-xs font-bold">2</span>
                        Pilih Fasilitas / Alat (Opsional)
                    </h3>

                    <div x-show="!selectedGedung" class="text-center py-8 text-slate-400 text-sm">
                        <i class="fa-solid fa-arrow-up text-2xl mb-2 block text-slate-300"></i>
                        Pilih gedung terlebih dahulu untuk melihat daftar fasilitas
                    </div>

                    <div x-show="loadingFasilitas" class="text-center py-8 text-blue-500 text-sm">
                        <i class="fa-solid fa-spinner fa-spin text-2xl mb-2 block"></i>
                        Memuat fasilitas...
                    </div>

                    <div x-show="selectedGedung && !loadingFasilitas">
                        <div x-show="fasilitasList.length === 0" class="text-center py-8 text-slate-400 text-sm">
                            <i class="fa-solid fa-inbox text-2xl mb-2 block"></i>
                            Tidak ada fasilitas yang tersedia untuk gedung ini
                        </div>

                        <div x-show="fasilitasList.length > 0" class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <template x-for="fas in fasilitasList" :key="fas.id">
                                <div
                                    class="fasilitas-card border rounded-xl p-4 cursor-pointer transition-all"
                                    :class="isFasilitasSelected(fas.id) ? 'selected border-blue-400 bg-blue-50' : 'border-slate-200 hover:border-slate-300 bg-white'"
                                    @click="toggleFasilitas(fas)">
                                    <div class="flex items-center justify-between mb-2">
                                        <div class="flex items-center gap-2">
                                            <div class="w-5 h-5 rounded-md flex items-center justify-center transition-all"
                                                :class="isFasilitasSelected(fas.id) ? 'bg-blue-500' : 'bg-slate-200'">
                                                <i class="fa-solid fa-check text-white text-xs" x-show="isFasilitasSelected(fas.id)"></i>
                                            </div>
                                            <p class="font-semibold text-slate-800 text-sm" x-text="fas.nama_fasilitas"></p>
                                        </div>
                                        <span class="text-xs font-bold text-emerald-700" x-text="formatRupiah(fas.harga_per_item)"></span>
                                    </div>
                                    <p class="text-xs text-slate-400 ml-7">Tersedia: <span class="font-semibold text-slate-600" x-text="fas.jumlah_tersedia + ' ' + fas.satuan"></span> · per item/hari</p>

                                    {{-- Jumlah Input --}}
                                    <div x-show="isFasilitasSelected(fas.id)" @click.stop class="mt-3 flex items-center gap-3">
                                        <label class="text-xs text-slate-500 font-medium">Jumlah:</label>
                                        <div class="flex items-center gap-2">
                                            <button type="button"
                                                @click="let sf = getSelectedFasilitas(fas.id); if(sf && sf.jumlah > 1) { sf.jumlah--; if(statusPembayaran==='lunas') jumlahDibayar=totalBiaya; }"
                                                class="w-7 h-7 rounded-lg bg-slate-200 hover:bg-slate-300 text-slate-700 font-bold flex items-center justify-center text-sm transition-all">-</button>
                                            <span x-text="getSelectedFasilitas(fas.id)?.jumlah ?? 1" class="w-8 text-center font-semibold text-slate-700 text-sm"></span>
                                            <button type="button"
                                                @click="let sf = getSelectedFasilitas(fas.id); if(sf && sf.jumlah < fas.jumlah_tersedia) { sf.jumlah++; if(statusPembayaran==='lunas') jumlahDibayar=totalBiaya; }"
                                                class="w-7 h-7 rounded-lg bg-blue-100 hover:bg-blue-200 text-blue-700 font-bold flex items-center justify-center text-sm transition-all">+</button>
                                        </div>
                                        <span class="text-xs text-slate-500" x-text="fas.satuan"></span>
                                    </div>
                                </div>
                            </template>
                        </div>

                        {{-- Hidden inputs for selected fasilitas --}}
                        <template x-for="(item, index) in selectedFasilitas" :key="item.id">
                            <div>
                                <input type="hidden" :name="`fasilitas[${index}][id]`" :value="item.id">
                                <input type="hidden" :name="`fasilitas[${index}][jumlah]`" :value="item.jumlah">
                            </div>
                        </template>
                    </div>
                </div>

                {{-- Data Tanggal & Penyewa --}}
                <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-sm">
                    <h3 class="font-bold text-slate-800 mb-4 flex items-center gap-2">
                        <span class="w-7 h-7 rounded-lg bg-emerald-100 text-emerald-600 flex items-center justify-center text-xs font-bold">3</span>
                        Data Tanggal & Penyewa
                    </h3>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-600 mb-1.5">Tanggal Mulai <span class="text-rose-500">*</span></label>
                            <input type="date" name="tanggal_mulai" id="tanggal_mulai" required value="{{ date('Y-m-d') }}"
                                @change="hitungLamaSewa()"
                                class="w-full px-3 py-2.5 text-sm border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-600 mb-1.5">Tanggal Selesai <span class="text-rose-500">*</span></label>
                            <input type="date" name="tanggal_selesai" id="tanggal_selesai" required value="{{ date('Y-m-d') }}"
                                @change="hitungLamaSewa()"
                                class="w-full px-3 py-2.5 text-sm border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-600 mb-1.5">Nama Penyewa <span class="text-rose-500">*</span></label>
                            <input type="text" name="nama_penyewa" required value="{{ old('nama_penyewa') }}" placeholder="Nama lengkap penyewa"
                                class="w-full px-3 py-2.5 text-sm border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-600 mb-1.5">Telepon</label>
                            <input type="text" name="telepon_penyewa" value="{{ old('telepon_penyewa') }}" placeholder="08xx-xxxx-xxxx"
                                class="w-full px-3 py-2.5 text-sm border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-600 mb-1.5">Instansi / Lembaga</label>
                            <input type="text" name="instansi_penyewa" value="{{ old('instansi_penyewa') }}" placeholder="Nama instansi (opsional)"
                                class="w-full px-3 py-2.5 text-sm border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-600 mb-1.5">Pelanggan (opsional)</label>
                            <select name="pelanggan_id" class="w-full px-3 py-2.5 text-sm border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="">-- Pilih Pelanggan --</option>
                                @foreach($pelangganList as $pl)
                                <option value="{{ $pl->id }}" {{ old('pelanggan_id') == $pl->id ? 'selected' : '' }}>{{ $pl->nama }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-600 mb-1.5">Status Pembayaran <span class="text-rose-500">*</span></label>
                            <select name="status_pembayaran" x-model="statusPembayaran" @change="onStatusPembayaranChange()" required class="w-full px-3 py-2.5 text-sm border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="belum_bayar">Belum Bayar</option>
                                <option value="dp">Uang Muka (DP)</option>
                                <option value="lunas">Lunas</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-600 mb-1.5">Jumlah Dibayar (DP / Nominal)</label>
                            <div class="relative">
                                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm">Rp</span>
                                <input type="number" name="jumlah_dibayar" x-model="jumlahDibayar" min="0" :readonly="statusPembayaran === 'lunas'"
                                    class="w-full pl-10 pr-3 py-2.5 text-sm border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500" :class="statusPembayaran === 'lunas' ? 'bg-slate-100 text-slate-600 font-semibold' : ''">
                            </div>
                        </div>
                        <div class="col-span-2">
                            <label class="block text-xs font-semibold text-slate-600 mb-1.5">Catatan</label>
                            <textarea name="catatan" rows="3" placeholder="Catatan tambahan (opsional)" class="w-full px-3 py-2.5 text-sm border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 resize-none">{{ old('catatan') }}</textarea>
                        </div>
                    </div>
                </div>
            </div>

            {{-- RIGHT: Summary --}}
            <div class="space-y-4">
                <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-sm sticky top-20">
                    <h3 class="font-bold text-slate-800 mb-4 flex items-center gap-2">
                        <i class="fa-solid fa-calculator text-blue-500"></i> Ringkasan Biaya
                    </h3>

                    <div class="space-y-3 text-sm">
                        <div class="flex justify-between text-slate-600">
                            <span>Lama Sewa</span>
                            <span class="font-semibold text-slate-800" x-text="lamaSewa + ' hari'"></span>
                        </div>
                        <div class="flex justify-between text-slate-600">
                            <span>Harga Gedung / Hari</span>
                            <span class="font-semibold text-slate-800" x-text="formatRupiah(hargaGedungPerHari)"></span>
                        </div>
                        <div class="flex justify-between text-slate-600">
                            <span>Subtotal Gedung</span>
                            <span class="font-semibold text-emerald-700" x-text="formatRupiah(subtotalGedung)"></span>
                        </div>

                        <div class="border-t border-dashed border-slate-200 pt-3">
                            <p class="text-xs font-semibold text-slate-500 mb-2">Fasilitas / Alat Dipilih:</p>
                            <template x-for="item in selectedFasilitas" :key="item.id">
                                <div class="flex justify-between text-slate-600 text-xs mb-1">
                                    <span x-text="item.jumlah + 'x ' + item.nama_fasilitas"></span>
                                    <span class="font-semibold" x-text="formatRupiah(item.jumlah * item.harga_per_item * lamaSewa)"></span>
                                </div>
                            </template>
                            <div x-show="selectedFasilitas.length === 0" class="text-xs text-slate-400 italic">Belum ada fasilitas dipilih</div>
                        </div>

                        <div class="flex justify-between text-slate-600 border-t border-dashed border-slate-200 pt-3">
                            <span>Subtotal Fasilitas</span>
                            <span class="font-semibold text-emerald-700" x-text="formatRupiah(subtotalFasilitas)"></span>
                        </div>

                        <div class="flex justify-between text-slate-800 bg-blue-50 rounded-xl p-3 border border-blue-100">
                            <span class="font-bold">Total Biaya</span>
                            <span class="font-bold text-blue-700 text-base" x-text="formatRupiah(totalBiaya)"></span>
                        </div>

                        {{-- Calculation Breakdown for DP / Lunas / Belum Bayar --}}
                        <div class="border-t border-dashed border-slate-200 pt-3 space-y-2">
                            <div class="flex justify-between items-center text-xs">
                                <span class="font-semibold text-slate-600">Status Pembayaran:</span>
                                <span class="px-2 py-0.5 rounded-full text-xs font-bold uppercase"
                                    :class="{
                                        'bg-emerald-100 text-emerald-700 border border-emerald-200': statusPembayaran === 'lunas',
                                        'bg-amber-100 text-amber-700 border border-amber-200': statusPembayaran === 'dp',
                                        'bg-rose-100 text-rose-700 border border-rose-200': statusPembayaran === 'belum_bayar'
                                    }"
                                    x-text="statusPembayaran === 'dp' ? 'DP (Uang Muka)' : (statusPembayaran === 'lunas' ? 'LUNAS' : 'Belum Bayar')">
                                </span>
                            </div>

                            <div class="flex justify-between text-slate-600 text-xs">
                                <span>Jumlah Dibayar</span>
                                <span class="font-semibold text-emerald-700" x-text="formatRupiah(jumlahDibayar)"></span>
                            </div>

                            <template x-if="statusPembayaran === 'dp' && sisaTagihan > 0">
                                <div class="flex justify-between text-rose-700 bg-rose-50 rounded-xl p-2.5 border border-rose-200 text-xs">
                                    <span class="font-semibold">Sisa Tagihan Pelunasan</span>
                                    <span class="font-bold" x-text="formatRupiah(sisaTagihan)"></span>
                                </div>
                            </template>

                            <template x-if="statusPembayaran === 'lunas'">
                                <div class="flex items-center justify-between text-emerald-700 bg-emerald-50 rounded-xl p-2.5 border border-emerald-200 text-xs">
                                    <span class="font-bold flex items-center gap-1.5"><i class="fa-solid fa-circle-check"></i> Status Lunas (Sisa: Rp 0)</span>
                                    <span x-show="kembalian > 0" class="font-semibold text-xs text-blue-700" x-text="'Kembali: ' + formatRupiah(kembalian)"></span>
                                </div>
                            </template>
                        </div>
                    </div>

                    <div class="mt-5 space-y-2">
                        <button type="submit" class="w-full py-3 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-xl text-sm transition-all shadow-md shadow-blue-600/25">
                            <i class="fa-solid fa-floppy-disk mr-2"></i> Simpan Penyewaan
                        </button>
                        <a href="{{ route('sewa.transaksi.index') }}" class="block w-full py-2.5 text-center text-sm text-slate-600 bg-slate-100 hover:bg-slate-200 rounded-xl transition-all">
                            Batal
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection
