@extends('layouts.app')

@section('title', 'Reset Data Transaksi')

@section('content')
<div x-data="{
    confirmText: '',
    resetStok: true,
    showConfirmModal: false,
    get isValid() {
        return this.confirmText.trim() === 'RESET TRANSAKSI';
    }
}" class="space-y-6 max-w-4xl mx-auto">

    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-xl font-bold text-slate-900 flex items-center gap-2">
                <i class="fa-solid fa-rotate-left text-rose-600"></i> Reset Data Transaksi
            </h2>
            <p class="text-sm text-slate-500">Hapus dan kosongkan riwayat transaksi penjualan, stok masuk, dan stok keluar TEFa</p>
        </div>
    </div>

    <!-- Alert / Danger Warning Banner -->
    <div class="bg-rose-50 border border-rose-200 rounded-2xl p-5 flex items-start gap-4">
        <div class="w-10 h-10 rounded-xl bg-rose-100 text-rose-600 flex items-center justify-center flex-shrink-0 text-xl">
            <i class="fa-solid fa-triangle-exclamation"></i>
        </div>
        <div class="space-y-1 text-sm text-rose-900">
            <h4 class="font-bold text-base">Peringatan Penting!</h4>
            <p class="text-rose-700 leading-relaxed">
                Tindakan ini akan <strong>menghapus secara permanen</strong> seluruh riwayat transaksi penjualan, stok masuk, dan stok keluar. Data yang sudah di-reset <strong>tidak dapat dikembalikan</strong>.
            </p>
        </div>
    </div>

    <!-- Stats Summary of Data to be Deleted -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm relative overflow-hidden">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Riwayat Penjualan</span>
                <span class="w-8 h-8 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center text-sm">
                    <i class="fa-solid fa-receipt"></i>
                </span>
            </div>
            <p class="text-3xl font-extrabold text-slate-900 mb-1">{{ number_format($countPenjualan) }}</p>
            <p class="text-xs text-slate-500">Total Omset: <strong class="text-emerald-600">Rp {{ number_format($totalOmset, 0, ',', '.') }}</strong></p>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm relative overflow-hidden">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Stok Masuk</span>
                <span class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center text-sm">
                    <i class="fa-solid fa-file-import"></i>
                </span>
            </div>
            <p class="text-3xl font-extrabold text-slate-900 mb-1">{{ number_format($countStokMasuk) }}</p>
            <p class="text-xs text-slate-500">Catatan riwayat stok masuk</p>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm relative overflow-hidden">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Stok Keluar</span>
                <span class="w-8 h-8 rounded-lg bg-amber-50 text-amber-600 flex items-center justify-center text-sm">
                    <i class="fa-solid fa-arrow-up-right-from-square"></i>
                </span>
            </div>
            <p class="text-3xl font-extrabold text-slate-900 mb-1">{{ number_format($countStokKeluar) }}</p>
            <p class="text-xs text-slate-500">Catatan riwayat stok keluar</p>
        </div>
    </div>

    <!-- Reset Form Card -->
    <div class="bg-white rounded-2xl border border-rose-200 shadow-sm overflow-hidden">
        <div class="bg-rose-600 px-6 py-4 text-white">
            <h3 class="font-bold text-base flex items-center gap-2">
                <i class="fa-solid fa-radiation"></i> Konfirmasi Reset Data Transaksi
            </h3>
            <p class="text-xs text-rose-100 mt-0.5">Silakan lakukan konfirmasi di bawah ini untuk memulai proses reset.</p>
        </div>

        <form action="{{ route('tefa.reset-transaksi.store') }}" method="POST" class="p-6 space-y-6">
            @csrf

            <!-- Options -->
            <div class="bg-slate-50 p-4 rounded-xl border border-slate-200 space-y-3">
                <label class="flex items-center gap-3 cursor-pointer">
                    <input type="checkbox" name="reset_stok_produk" value="1" x-model="resetStok"
                        class="w-4 h-4 rounded border-slate-300 text-rose-600 focus:ring-rose-500">
                    <span class="text-sm font-semibold text-slate-800">
                        Reset juga jumlah stok seluruh produk menjadi 0 (Nol)
                    </span>
                </label>
                <p class="text-xs text-slate-500 pl-7">
                    Jika dicentang, angka stok pada katalog seluruh produk TEFa akan diset ulang menjadi 0.
                </p>
            </div>

            <!-- Confirmation Input -->
            <div class="space-y-2">
                <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider">
                    Ketik teks konfirmasi di bawah ini:
                </label>
                <div class="p-3 bg-rose-50/60 rounded-xl border border-rose-200 font-mono text-sm text-rose-800 font-bold select-all">
                    RESET TRANSAKSI
                </div>
                <input type="text" name="konfirmasi" x-model="confirmText" required
                    placeholder="Ketik 'RESET TRANSAKSI' untuk mengonfirmasi..."
                    class="w-full px-4 py-3 rounded-xl border border-slate-300 text-sm font-mono tracking-wider focus:outline-none focus:ring-2 focus:ring-rose-500 bg-white">
                @error('konfirmasi')
                <p class="text-xs font-semibold text-rose-600 mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Submit Button -->
            <div class="pt-4 border-t border-slate-100 flex items-center justify-between gap-4">
                <a href="{{ route('tefa.transaksi.index') }}" class="px-5 py-2.5 border border-slate-200 rounded-xl text-sm font-semibold text-slate-600 hover:bg-slate-50 transition">
                    Batal
                </a>
                <button type="button" @click="if(isValid) showConfirmModal = true" :disabled="!isValid"
                    :class="isValid ? 'bg-rose-600 hover:bg-rose-700 cursor-pointer shadow-lg shadow-rose-600/25' : 'bg-slate-300 cursor-not-allowed opacity-60'"
                    class="px-6 py-3 text-white font-bold rounded-xl text-sm transition-all flex items-center gap-2">
                    <i class="fa-solid fa-trash-can"></i> Jalankan Reset Data Sekarang
                </button>
            </div>

            <!-- Confirmation Modal -->
            <div x-show="showConfirmModal" x-cloak class="fixed inset-0 bg-black/60 backdrop-blur-sm z-50 flex items-center justify-center p-4">
                <div class="bg-white rounded-2xl w-full max-w-md shadow-2xl p-6 text-center space-y-4" @click.stop>
                    <div class="w-14 h-14 rounded-full bg-rose-100 text-rose-600 flex items-center justify-center mx-auto text-2xl">
                        <i class="fa-solid fa-radiation"></i>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-slate-900">Apakah Anda Sudah Yakin 100%?</h3>
                        <p class="text-xs text-slate-500 mt-1">
                            Semua data <strong>stok masuk</strong>, <strong>stok keluar</strong>, dan <strong>riwayat penjualan</strong> akan dihapus selamanya.
                        </p>
                    </div>

                    <div class="flex gap-3 pt-2">
                        <button type="button" @click="showConfirmModal = false" class="w-1/2 py-2.5 border border-slate-200 rounded-xl text-sm font-semibold text-slate-600 hover:bg-slate-50">
                            Batal
                        </button>
                        <button type="submit" class="w-1/2 py-2.5 bg-rose-600 hover:bg-rose-700 text-white font-bold rounded-xl text-sm shadow-md shadow-rose-600/25">
                            Ya, Reset Sekarang!
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
