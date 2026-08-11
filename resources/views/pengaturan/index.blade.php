@extends('layouts.app')

@section('title', 'Pengaturan Aplikasi')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <div>
        <h2 class="text-xl font-bold text-slate-900">Pengaturan Aplikasi</h2>
        <p class="text-sm text-slate-500">Konfigurasi parameter sistem, informasi struk kasir, dan info sekolah</p>
    </div>

    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6">
        <form action="{{ route('pengaturan.update') }}" method="POST" class="space-y-6">
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Info Aplikasi & Struk Thermal -->
                <div class="space-y-4 md:col-span-2 border-b border-slate-100 pb-5">
                    <h3 class="font-bold text-slate-900 text-sm flex items-center gap-2">
                        <i class="fa-solid fa-receipt text-blue-600"></i> Identitas Instansi & Header Struk Kasir
                    </h3>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-600 mb-1">Nama Aplikasi</label>
                            <input type="text" name="nama_aplikasi" value="{{ $pengaturan['nama_aplikasi']->nilai ?? 'Sistem Inventaris & Kasir TEFa' }}" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-600 mb-1">Nama TEFa / Unit Produksi</label>
                            <input type="text" name="nama_tefa" value="{{ $pengaturan['nama_tefa']->nilai ?? 'Teaching Factory (TEFa)' }}" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-600 mb-1">Nama Sekolah</label>
                            <input type="text" name="nama_sekolah" value="{{ $pengaturan['nama_sekolah']->nilai ?? 'SMK Negeri 1' }}" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-600 mb-1">Telepon Instansi</label>
                            <input type="text" name="telepon" value="{{ $pengaturan['telepon']->nilai ?? '0812-3456-7890' }}" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                        </div>
                        <div class="sm:col-span-2">
                            <label class="block text-xs font-semibold text-slate-600 mb-1">Alamat Instansi</label>
                            <input type="text" name="alamat_instansi" value="{{ $pengaturan['alamat_instansi']->nilai ?? 'Jl. Pendidikan No. 123' }}" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                        </div>
                        <div class="sm:col-span-2">
                            <label class="block text-xs font-semibold text-slate-600 mb-1">Footer Struk: Ucapan Terima Kasih</label>
                            <input type="text" name="ucapan_terima_kasih" value="{{ $pengaturan['ucapan_terima_kasih']->nilai ?? 'Terima Kasih Atas Kunjungan Anda!' }}" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                        </div>
                    </div>
                </div>

                <!-- Parameter Peminjaman Alat -->
                <div class="space-y-4 md:col-span-2 border-b border-slate-100 pb-4">
                    <h3 class="font-bold text-slate-900 text-sm flex items-center gap-2">
                        <i class="fa-solid fa-clock-rotate-left text-amber-600"></i> Aturan Peminjaman Alat
                    </h3>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-600 mb-1">Denda Keterlambatan Default (Rp / Hari)</label>
                            <input type="number" name="denda_per_hari" value="{{ $pengaturan['denda_per_hari']->nilai ?? 5000 }}" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-600 mb-1">Batas Maksimal Hari Pinjam Standard</label>
                            <input type="number" name="max_hari_pinjam" value="{{ $pengaturan['max_hari_pinjam']->nilai ?? 7 }}" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm">
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex justify-end pt-2">
                <button type="submit" class="px-6 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-xl text-sm shadow-md shadow-blue-600/25 transition-all">
                    <i class="fa-solid fa-save mr-2"></i> Simpan Pengaturan
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
