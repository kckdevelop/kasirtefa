@extends('layouts.app')

@section('title', 'POS Kasir - Transaksi Penjualan')

@section('styles')
<style>
    .product-card:hover { transform: translateY(-2px); }
    .product-card { transition: all 0.2s ease; }
    .cart-item-enter { animation: slideIn 0.2s ease; }
    @keyframes slideIn { from { opacity: 0; transform: translateX(10px); } to { opacity: 1; transform: translateX(0); } }
</style>
@endsection

@section('content')
<div x-data="kasirApp()" class="flex flex-col lg:flex-row gap-6 h-full">

    <!-- LEFT: Product Catalog -->
    <div class="flex-1 min-w-0 space-y-4">
        <!-- Search & Filter Bar -->
        <div class="flex gap-3">
            <div class="relative flex-1">
                <i class="fa-solid fa-magnifying-glass absolute left-4 top-1/2 -translate-y-1/2 text-slate-400"></i>
                <input type="text" x-model="searchQuery" @input="filterProducts()"
                    placeholder="Cari produk berdasarkan nama atau kode..."
                    class="w-full pl-11 pr-4 py-3 rounded-xl border border-slate-200 bg-white text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 shadow-sm">
            </div>
        </div>

        <!-- Product Grid -->
        <div class="grid grid-cols-2 sm:grid-cols-3 xl:grid-cols-4 gap-3">
            <template x-for="produk in filteredProducts" :key="produk.id">
                <div class="product-card bg-white rounded-xl border border-slate-200 p-4 cursor-pointer hover:border-blue-500 hover:shadow-md shadow-sm"
                     :class="{ 'opacity-50 pointer-events-none': produk.stok == 0 }"
                     @click="addToCart(produk)">
                    <div class="w-full aspect-square rounded-lg bg-slate-100 mb-3 overflow-hidden flex items-center justify-center">
                        <template x-if="produk.foto">
                            <img :src="'/storage/' + produk.foto" class="w-full h-full object-cover" :alt="produk.nama">
                        </template>
                        <template x-if="!produk.foto">
                            <i class="fa-solid fa-box text-slate-300 text-3xl"></i>
                        </template>
                    </div>
                    <h4 class="text-xs font-semibold text-slate-800 leading-tight line-clamp-2" x-text="produk.nama"></h4>
                    <div class="mt-2 flex items-center justify-between">
                        <span class="text-blue-700 font-bold text-sm" x-text="'Rp ' + formatNumber(produk.harga_jual)"></span>
                        <span class="text-[10px] font-medium px-2 py-0.5 rounded-full" :class="produk.stok > produk.stok_minimum ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-700'" x-text="produk.stok + ' ' + produk.satuan"></span>
                    </div>
                </div>
            </template>

            <template x-if="filteredProducts.length === 0">
                <div class="col-span-4 text-center py-16 text-slate-400">
                    <i class="fa-solid fa-box-open text-5xl mb-3 block opacity-30"></i>
                    <p class="text-sm">Tidak ada produk ditemukan</p>
                </div>
            </template>
        </div>
    </div>

    <!-- RIGHT: Cart Panel -->
    <div class="w-full lg:w-96 flex flex-col gap-4">
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm flex flex-col h-fit lg:sticky lg:top-20">
            <!-- Cart Header -->
            <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <i class="fa-solid fa-shopping-cart text-blue-600"></i>
                    <h3 class="font-bold text-slate-800">Keranjang Belanja</h3>
                </div>
                <button @click="clearCart()" class="text-xs text-rose-500 hover:text-rose-700 font-semibold" x-show="cart.length > 0">
                    <i class="fa-solid fa-trash mr-1"></i> Kosongkan
                </button>
            </div>

            <!-- Cart Items -->
            <div class="flex-1 overflow-y-auto max-h-64 px-4 py-3 space-y-2">
                <template x-for="(item, index) in cart" :key="index">
                    <div class="cart-item-enter flex items-center gap-3 py-2 border-b border-slate-50 last:border-0">
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-semibold text-slate-800 truncate" x-text="item.nama"></p>
                            <p class="text-xs text-slate-500" x-text="'Rp ' + formatNumber(item.harga_jual) + ' / ' + item.satuan"></p>
                        </div>
                        <div class="flex items-center gap-2">
                            <button @click="decreaseQty(index)" class="w-7 h-7 rounded-lg bg-slate-100 hover:bg-rose-100 text-slate-600 hover:text-rose-600 flex items-center justify-center text-sm transition-colors">
                                <i class="fa-solid fa-minus"></i>
                            </button>
                            <input type="number" x-model.number="item.jumlah" @change="updateQty(index, item.jumlah)"
                                :max="item.stok" min="1"
                                class="w-12 text-center py-1 text-sm font-bold border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <button @click="increaseQty(index)" class="w-7 h-7 rounded-lg bg-slate-100 hover:bg-blue-100 text-slate-600 hover:text-blue-600 flex items-center justify-center text-sm transition-colors">
                                <i class="fa-solid fa-plus"></i>
                            </button>
                        </div>
                        <div class="text-right min-w-16">
                            <p class="text-xs font-bold text-slate-800" x-text="'Rp ' + formatNumber(item.harga_jual * item.jumlah)"></p>
                            <button @click="removeFromCart(index)" class="text-rose-400 hover:text-rose-600 text-[10px] mt-0.5">Hapus</button>
                        </div>
                    </div>
                </template>

                <template x-if="cart.length === 0">
                    <div class="text-center py-8 text-slate-400">
                        <i class="fa-solid fa-cart-shopping text-4xl mb-2 block opacity-20"></i>
                        <p class="text-xs">Klik produk untuk menambahkan ke keranjang</p>
                    </div>
                </template>
            </div>

            <!-- Calculation Section -->
            <div class="px-5 py-4 border-t border-slate-100 space-y-3">
                <!-- Customer Info -->
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Nama Pelanggan (opsional)</label>
                    <input type="text" x-model="customerNama" list="listPelanggan" placeholder="Pilih / ketik nama pelanggan..."
                        class="w-full px-3 py-2 text-sm rounded-lg border border-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <datalist id="listPelanggan">
                        @foreach($pelanggan as $plg)
                        <option value="{{ $plg->nama }}">{{ $plg->kode_pelanggan }} - {{ $plg->telepon }} ({{ strtoupper($plg->tipe) }})</option>
                        @endforeach
                    </datalist>
                </div>

                <!-- Discount -->
                <div class="flex gap-2 items-end">
                    <div class="flex-1">
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Diskon (%)</label>
                        <input type="number" x-model.number="diskonPersen" @input="hitungTotal()" min="0" max="100" placeholder="0"
                            class="w-full px-3 py-2 text-sm rounded-lg border border-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div class="flex-1">
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Metode Bayar</label>
                        <select x-model="metodePembayaran" class="w-full px-3 py-2 text-sm rounded-lg border border-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="tunai">Tunai</option>
                            <option value="transfer">Transfer</option>
                            <option value="qris">QRIS</option>
                        </select>
                    </div>
                </div>

                <!-- Summary -->
                <div class="bg-slate-50 rounded-xl p-4 space-y-2 text-sm">
                    <div class="flex justify-between text-slate-600">
                        <span>Subtotal</span>
                        <span class="font-medium" x-text="'Rp ' + formatNumber(subtotal)"></span>
                    </div>
                    <div class="flex justify-between text-amber-600" x-show="diskonNominal > 0">
                        <span>Diskon (<span x-text="diskonPersen"></span>%)</span>
                        <span class="font-medium" x-text="'- Rp ' + formatNumber(diskonNominal)"></span>
                    </div>
                    <div class="border-t border-slate-200 pt-2 flex justify-between font-bold text-lg text-slate-900">
                        <span>TOTAL</span>
                        <span class="text-blue-700" x-text="'Rp ' + formatNumber(totalAkhir)"></span>
                    </div>
                </div>

                <!-- Cash Input -->
                <div x-show="metodePembayaran === 'tunai'">
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Nominal Bayar</label>
                    <input type="number" x-model.number="nominalBayar" @input="hitungKembalian()"
                        :min="totalAkhir" placeholder="Masukkan jumlah uang"
                        class="w-full px-3 py-2 text-sm rounded-lg border border-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <div class="mt-2 p-3 bg-emerald-50 rounded-lg flex justify-between" x-show="nominalBayar >= totalAkhir">
                        <span class="text-emerald-700 text-sm font-semibold">Kembalian</span>
                        <span class="text-emerald-700 font-bold" x-text="'Rp ' + formatNumber(kembalian)"></span>
                    </div>
                </div>

                <!-- Quick Cash Buttons -->
                <div class="flex flex-wrap gap-2" x-show="metodePembayaran === 'tunai'">
                    <template x-for="nominal in [5000, 10000, 20000, 50000, 100000]">
                        <button @click="nominalBayar += nominal; hitungKembalian()"
                            class="px-2.5 py-1.5 bg-white border border-slate-200 rounded-lg text-xs font-semibold text-slate-600 hover:bg-blue-50 hover:border-blue-300 hover:text-blue-700 transition-colors"
                            x-text="'+' + formatNumber(nominal)">
                        </button>
                    </template>
                    <button @click="nominalBayar = totalAkhir; hitungKembalian()"
                        class="px-2.5 py-1.5 bg-emerald-50 border border-emerald-200 rounded-lg text-xs font-semibold text-emerald-700 hover:bg-emerald-100 transition-colors">
                        Uang Pas
                    </button>
                </div>

                <!-- Proses Bayar Button -->
                <button @click="prosesBayar()" :disabled="cart.length === 0 || loading"
                    class="w-full py-3.5 bg-blue-600 hover:bg-blue-700 disabled:bg-slate-300 disabled:cursor-not-allowed text-white font-bold rounded-xl transition-all shadow-lg shadow-blue-600/30 flex items-center justify-center gap-2">
                    <template x-if="!loading">
                        <span><i class="fa-solid fa-cash-register mr-2"></i>Proses Pembayaran</span>
                    </template>
                    <template x-if="loading">
                        <span><i class="fa-solid fa-spinner fa-spin mr-2"></i>Memproses...</span>
                    </template>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Success Modal -->
<div x-show="showSuccessModal" x-cloak
    class="fixed inset-0 bg-black/50 backdrop-blur-sm z-[200] flex items-center justify-center p-4"
    @keydown.escape.window="showSuccessModal = false">
    <div class="bg-white rounded-2xl p-8 max-w-sm w-full shadow-2xl text-center" @click.stop>
        <div class="w-16 h-16 bg-emerald-100 rounded-full flex items-center justify-center mx-auto mb-4">
            <i class="fa-solid fa-check text-emerald-600 text-3xl"></i>
        </div>
        <h3 class="text-xl font-bold text-slate-900 mb-2">Transaksi Berhasil!</h3>
        <p class="text-slate-500 text-sm mb-6">Pembayaran telah diterima dan stok telah diperbarui.</p>
        <div class="flex gap-3">
            <button @click="cetakStruk()" class="flex-1 py-2.5 border border-blue-200 rounded-xl text-blue-700 font-semibold text-sm hover:bg-blue-50 transition-colors">
                <i class="fa-solid fa-print mr-1"></i> Cetak Struk
            </button>
            <button @click="resetAll()" class="flex-1 py-2.5 bg-blue-600 rounded-xl text-white font-semibold text-sm hover:bg-blue-700 transition-colors">
                Transaksi Baru
            </button>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
const allProducts = @json($produk);

function kasirApp() {
    return {
        searchQuery: '',
        filteredProducts: allProducts,
        cart: [],
        customerNama: '',
        metodePembayaran: 'tunai',
        diskonPersen: 0,
        nominalBayar: 0,
        subtotal: 0,
        diskonNominal: 0,
        totalAkhir: 0,
        kembalian: 0,
        loading: false,
        showSuccessModal: false,
        lastTransaksiId: null,

        filterProducts() {
            const q = this.searchQuery.toLowerCase();
            this.filteredProducts = q
                ? allProducts.filter(p => p.nama.toLowerCase().includes(q) || (p.kode_produk || '').toLowerCase().includes(q))
                : allProducts;
        },

        addToCart(produk) {
            const existing = this.cart.find(i => i.id === produk.id);
            if (existing) {
                if (existing.jumlah < produk.stok) existing.jumlah++;
                else alert(`Stok ${produk.nama} tidak mencukupi!`);
            } else {
                this.cart.push({ ...produk, jumlah: 1 });
            }
            this.hitungTotal();
        },

        removeFromCart(index) { this.cart.splice(index, 1); this.hitungTotal(); },

        increaseQty(index) {
            if (this.cart[index].jumlah < this.cart[index].stok) {
                this.cart[index].jumlah++;
                this.hitungTotal();
            }
        },

        decreaseQty(index) {
            if (this.cart[index].jumlah > 1) {
                this.cart[index].jumlah--;
            } else {
                this.removeFromCart(index);
            }
            this.hitungTotal();
        },

        updateQty(index, val) {
            const max = this.cart[index].stok;
            this.cart[index].jumlah = Math.max(1, Math.min(parseInt(val) || 1, max));
            this.hitungTotal();
        },

        clearCart() { this.cart = []; this.hitungTotal(); },

        hitungTotal() {
            this.subtotal = this.cart.reduce((sum, i) => sum + (i.harga_jual * i.jumlah), 0);
            this.diskonNominal = Math.round(this.subtotal * (this.diskonPersen / 100));
            this.totalAkhir = Math.max(0, this.subtotal - this.diskonNominal);
            this.hitungKembalian();
        },

        hitungKembalian() {
            this.kembalian = Math.max(0, this.nominalBayar - this.totalAkhir);
        },

        formatNumber(n) {
            return new Intl.NumberFormat('id-ID').format(n || 0);
        },

        async prosesBayar() {
            if (this.cart.length === 0) return;
            if (this.metodePembayaran === 'tunai' && this.nominalBayar < this.totalAkhir) {
                alert('Nominal bayar kurang dari total belanja!');
                return;
            }
            if (!this.metodePembayaran) { alert('Pilih metode pembayaran!'); return; }

            this.loading = true;

            // Pre-open print window to bypass browser popup blocker
            const printWin = window.open('about:blank', '_blank');

            try {
                const resp = await fetch('{{ route("tefa.kasir.store") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                    },
                    body: JSON.stringify({
                        customer_nama: this.customerNama || null,
                        metode_pembayaran: this.metodePembayaran,
                        nominal_bayar: this.metodePembayaran === 'tunai' ? this.nominalBayar : this.totalAkhir,
                        diskon_persen: this.diskonPersen,
                        items: this.cart.map(i => ({ produk_id: i.id, jumlah: i.jumlah }))
                    })
                });
                const data = await resp.json();
                if (data.success) {
                    this.lastTransaksiId = data.transaksi_id;
                    this.lastCetakUrl = data.cetak_url;
                    this.showSuccessModal = true;
                    
                    // Direct print window to thermal receipt URL
                    if (printWin && data.cetak_url) {
                        printWin.location.href = data.cetak_url;
                    }
                } else {
                    if (printWin) printWin.close();
                    alert('Gagal: ' + data.message);
                }
            } catch (e) {
                if (printWin) printWin.close();
                alert('Terjadi kesalahan koneksi.');
            } finally {
                this.loading = false;
            }
        },

        cetakStruk() {
            if (this.lastCetakUrl) {
                window.open(this.lastCetakUrl, '_blank');
            }
        },

        resetAll() {
            this.cart = [];
            this.customerNama = '';
            this.diskonPersen = 0;
            this.nominalBayar = 0;
            this.subtotal = 0;
            this.diskonNominal = 0;
            this.totalAkhir = 0;
            this.kembalian = 0;
            this.showSuccessModal = false;
            this.lastTransaksiId = null;
        }
    }
}
</script>
@endsection
