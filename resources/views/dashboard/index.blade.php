@extends('layouts.app')

@section('title', 'Dashboard Overview')

@section('content')
<div class="space-y-6">

    {{-- Stat Cards Grid: Row 1 (TEFa & Alat) --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
        {{-- Card 1: Omzet Bulan Ini --}}
        <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Omzet Penjualan Bln Ini</p>
                <h3 class="text-2xl font-bold text-slate-900 mt-1">Rp {{ number_format($omzetBulanIni, 0, ',', '.') }}</h3>
                <span class="text-xs font-medium inline-flex items-center gap-1 mt-2
                    {{ $pertumbuhanOmzet >= 0 ? 'text-emerald-600' : 'text-red-500' }}">
                    <i class="fa-solid {{ $pertumbuhanOmzet >= 0 ? 'fa-arrow-trend-up' : 'fa-arrow-trend-down' }}"></i>
                    {{ $pertumbuhanOmzet >= 0 ? '+' : '' }}{{ $pertumbuhanOmzet }}% vs bln lalu
                </span>
            </div>
            <div class="w-12 h-12 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center text-xl">
                <i class="fa-solid fa-wallet"></i>
            </div>
        </div>

        {{-- Card 2: Total Produk --}}
        <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Total Produk TEFa</p>
                <h3 class="text-2xl font-bold text-slate-900 mt-1">{{ number_format($totalProduk) }} Item</h3>
                <span class="text-xs font-medium text-amber-600 inline-flex items-center gap-1 mt-2">
                    <i class="fa-solid fa-triangle-exclamation"></i> {{ $stokMenipis->count() }} Stok Menipis
                </span>
            </div>
            <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-xl">
                <i class="fa-solid fa-boxes-stacked"></i>
            </div>
        </div>

        {{-- Card 3: Alat Tersedia --}}
        <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Alat Tersedia</p>
                <h3 class="text-2xl font-bold text-slate-900 mt-1">{{ number_format($alatTersedia) }} Unit</h3>
                <span class="text-xs font-medium text-blue-600 inline-flex items-center gap-1 mt-2">
                    <i class="fa-solid fa-hand-holding"></i> {{ $sedangDipinjam }} Sedang Dipinjam
                </span>
            </div>
            <div class="w-12 h-12 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-xl">
                <i class="fa-solid fa-wrench"></i>
            </div>
        </div>

        {{-- Card 4: Sewa Gedung Aktif --}}
        @php
            $sewaAktifCount = collect($sewaGedungCalendar)->whereIn('status_sewa', ['booking', 'disetujui', 'berlangsung'])->count();
        @endphp
        <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Penyewaan Gedung</p>
                <h3 class="text-2xl font-bold text-blue-600 mt-1">{{ $sewaAktifCount }} Terjadwal</h3>
                <a href="{{ route('sewa.transaksi.index') }}" class="text-xs font-medium text-blue-600 hover:underline inline-flex items-center gap-1 mt-2">
                    <i class="fa-solid fa-building"></i> Kelola Gedung &rarr;
                </a>
            </div>
            <div class="w-12 h-12 rounded-xl bg-violet-50 text-violet-600 flex items-center justify-center text-xl">
                <i class="fa-solid fa-calendar-check"></i>
            </div>
        </div>
    </div>

    {{-- Alert Banners --}}
    @if($stokMenipis->count() > 0)
    <div class="bg-amber-50 border border-amber-200 rounded-2xl p-5">
        <div class="flex items-center gap-3 mb-3">
            <i class="fa-solid fa-triangle-exclamation text-amber-600 text-lg"></i>
            <h4 class="font-bold text-amber-900 text-sm">Peringatan: {{ $stokMenipis->count() }} Produk di Bawah Stok Minimum</h4>
        </div>
        <div class="flex flex-wrap gap-2">
            @foreach($stokMenipis->take(6) as $item)
            <span class="inline-flex items-center gap-2 px-3 py-1 bg-white border border-amber-200 rounded-lg text-xs font-semibold text-amber-800">
                <span>{{ $item->nama }}</span>
                <span class="px-1.5 py-0.5 rounded bg-amber-100 text-amber-900 text-[10px]">{{ $item->stok }} {{ $item->satuan }}</span>
            </span>
            @endforeach
        </div>
    </div>
    @endif

    {{-- ── KALENDER AGENDA PENYEWAAN GEDUNG / LAB ── --}}
    <div x-data="kalenderSewa" class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 space-y-6">

        {{-- Kalender Header Controls --}}
        <div class="flex flex-wrap items-center justify-between gap-4 border-b border-slate-100 pb-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-blue-100 text-blue-600 flex items-center justify-center text-lg font-bold">
                    <i class="fa-solid fa-calendar-days"></i>
                </div>
                <div>
                    <h3 class="font-bold text-slate-800 text-base">Kalender Agenda Penyewaan Gedung & Lab</h3>
                    <p class="text-xs text-slate-500">Jadwal tanggal sewa gedung/ruang lab yang terisi</p>
                </div>
            </div>

            <div class="flex items-center gap-2">
                <button @click="goToday()"
                    class="px-3 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold rounded-lg text-xs transition-all">
                    Hari Ini
                </button>
                <div class="flex items-center bg-slate-100 rounded-lg p-1">
                    <button @click="prevMonth()" class="w-8 h-8 flex items-center justify-center hover:bg-white rounded-md text-slate-600 transition-all">
                        <i class="fa-solid fa-chevron-left text-xs"></i>
                    </button>
                    <span class="px-3 font-bold text-slate-800 text-sm min-w-[130px] text-center" x-text="monthNames[month] + ' ' + year"></span>
                    <button @click="nextMonth()" class="w-8 h-8 flex items-center justify-center hover:bg-white rounded-md text-slate-600 transition-all">
                        <i class="fa-solid fa-chevron-right text-xs"></i>
                    </button>
                </div>
            </div>
        </div>

        {{-- Grid Kalender --}}
        <div>
            {{-- Header Hari --}}
            <div class="grid grid-cols-7 gap-1 text-center font-bold text-xs text-slate-500 uppercase tracking-wider mb-2">
                <template x-for="(d, idx) in dayNames" :key="idx">
                    <div class="py-2 rounded-lg" :class="idx === 0 ? 'text-rose-500' : 'text-slate-600'" x-text="d"></div>
                </template>
            </div>

            {{-- Day Cells --}}
            <div class="grid grid-cols-7 gap-1.5">
                {{-- Empty Padding Cells --}}
                <template x-for="p in firstDayOfWeek" :key="'pad-' + p">
                    <div class="min-h-[75px] bg-slate-50/50 rounded-xl border border-slate-100/50"></div>
                </template>

                {{-- Days of Month --}}
                <template x-for="day in daysInMonth" :key="'day-' + day">
                    <div
                        @click="selectedDay = (selectedDay === day ? null : day)"
                        class="min-h-[75px] p-2 rounded-xl border transition-all cursor-pointer flex flex-col justify-between"
                        :class="{
                            'ring-2 ring-blue-500 bg-blue-50/40': selectedDay === day,
                            'bg-blue-50/20 border-blue-200': isToday(day) && selectedDay !== day,
                            'bg-white border-slate-200 hover:border-blue-300': !isToday(day) && selectedDay !== day
                        }">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-bold w-6 h-6 rounded-full flex items-center justify-center"
                                :class="isToday(day) ? 'bg-blue-600 text-white' : 'text-slate-700'"
                                x-text="day"></span>
                            <template x-if="getRentalsForDay(day).length > 0">
                                <span class="w-2 h-2 rounded-full bg-blue-500"></span>
                            </template>
                        </div>

                        {{-- Rental Color Badges inside Day Cell --}}
                        <div class="space-y-1 mt-1">
                            <template x-for="r in getRentalsForDay(day)" :key="r.id">
                                <div class="px-1.5 py-0.5 rounded text-[10px] font-bold truncate shadow-xs border"
                                    :class="r.color"
                                    :title="r.nama_gedung + ' (' + r.nama_penyewa + ')'">
                                    <span x-text="r.nama_gedung"></span>
                                </div>
                            </template>
                        </div>
                    </div>
                </template>
            </div>
        </div>

        {{-- KETERANGAN & AGENDA KEGIATAN GEDUNG (LIST DI BAWAH KALENDER) --}}
        <div class="border-t border-slate-100 pt-5 space-y-4">
            <div class="flex items-center justify-between">
                <h4 class="font-bold text-slate-800 text-sm flex items-center gap-2">
                    <i class="fa-solid fa-list-check text-blue-600"></i>
                    <span>Keterangan Agenda Kegiatan Gedung</span>
                    <span x-show="selectedDay" class="text-xs font-normal text-blue-600 bg-blue-50 px-2.5 py-0.5 rounded-full border border-blue-200">
                        Tanggal <span x-text="selectedDay + ' ' + monthNames[month] + ' ' + year"></span>
                    </span>
                    <span x-show="!selectedDay" class="text-xs font-normal text-slate-500 bg-slate-100 px-2.5 py-0.5 rounded-full">
                        Bulan <span x-text="monthNames[month] + ' ' + year"></span>
                    </span>
                </h4>

                <button x-show="selectedDay" @click="selectedDay = null" class="text-xs text-slate-500 hover:text-slate-800 underline">
                    Tampilkan Semua Bulan Ini
                </button>
            </div>

            {{-- Event Items Cards --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                <template x-for="r in (selectedDay ? getRentalsForDay(selectedDay) : getRentalsForCurrentMonth())" :key="'evt-' + r.id">
                    <div class="p-4 rounded-xl border border-slate-200 bg-slate-50/50 hover:bg-white hover:shadow-md transition-all flex items-start justify-between gap-3">
                        <div class="flex items-start gap-3">
                            {{-- Color Marker Badge --}}
                            <div class="w-4 h-12 rounded-lg shrink-0 mt-0.5" :class="r.color"></div>
                            <div>
                                <div class="flex items-center gap-2 flex-wrap">
                                    <h5 class="font-bold text-slate-900 text-sm" x-text="r.nama_gedung"></h5>
                                    <span class="text-[10px] font-mono px-2 py-0.5 bg-slate-200 text-slate-600 rounded-md font-semibold" x-text="r.kode_gedung"></span>
                                </div>
                                <p class="text-xs font-semibold text-slate-700 mt-1">
                                    <i class="fa-solid fa-user mr-1 text-slate-400"></i> Penyewa: <span x-text="r.nama_penyewa"></span>
                                    <span x-show="r.instansi_penyewa" x-text="'(' + r.instansi_penyewa + ')'" class="text-slate-500 font-normal"></span>
                                </p>
                                <p class="text-xs text-blue-700 font-bold mt-1">
                                    <i class="fa-solid fa-clock mr-1"></i>
                                    <span x-text="formatDateRange(r.tanggal_mulai, r.tanggal_selesai)"></span>
                                    <span class="text-slate-500 font-normal" x-text="'(' + r.lama_sewa + ' Hari)'"></span>
                                </p>
                            </div>
                        </div>

                        <div class="text-right shrink-0 flex flex-col justify-between items-end h-full">
                            <span class="text-[10px] px-2 py-0.5 rounded-full font-bold uppercase border"
                                :class="{
                                    'bg-amber-100 text-amber-700 border-amber-200': r.status_sewa === 'booking',
                                    'bg-blue-100 text-blue-700 border-blue-200': r.status_sewa === 'disetujui',
                                    'bg-violet-100 text-violet-700 border-violet-200': r.status_sewa === 'berlangsung',
                                    'bg-emerald-100 text-emerald-700 border-emerald-200': r.status_sewa === 'selesai'
                                }"
                                x-text="r.status_sewa">
                            </span>
                            <a :href="'/sewa/transaksi/' + r.id" class="text-xs text-blue-600 hover:text-blue-800 font-semibold mt-3 block">
                                Detail &rarr;
                            </a>
                        </div>
                    </div>
                </template>
            </div>

            {{-- Empty State --}}
            <div x-show="(selectedDay ? getRentalsForDay(selectedDay) : getRentalsForCurrentMonth()).length === 0"
                class="text-center py-8 bg-slate-50 rounded-xl border border-dashed border-slate-200 text-slate-400 text-xs">
                <i class="fa-solid fa-calendar-xmark text-3xl mb-2 block text-slate-300"></i>
                Tidak ada agenda penyewaan gedung pada tanggal/bulan yang dipilih.
            </div>
        </div>
    </div>

    {{-- Tables Grid: Penjualan & Peminjaman Terbaru --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {{-- Recent Sales Table --}}
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="font-bold text-slate-800 text-base">Transaksi Penjualan Terbaru</h3>
                <a href="{{ route('tefa.transaksi.index') }}" class="text-xs font-semibold text-blue-600 hover:text-blue-700">Lihat Semua &rarr;</a>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b border-slate-100 text-slate-400 text-[11px] font-semibold uppercase tracking-wider">
                            <th class="py-3 px-2">Kode</th>
                            <th class="py-3 px-2">Tanggal</th>
                            <th class="py-3 px-2 text-right">Total</th>
                            <th class="py-3 px-2 text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-xs">
                        @forelse($recentTransactions as $trx)
                        <tr class="hover:bg-slate-50">
                            <td class="py-3 px-2 font-bold text-slate-900">{{ $trx->kode_transaksi }}</td>
                            <td class="py-3 px-2 text-slate-600">{{ formatTanggal($trx->tanggal) }}</td>
                            <td class="py-3 px-2 text-right font-semibold text-slate-800">Rp {{ number_format($trx->total_akhir, 0, ',', '.') }}</td>
                            <td class="py-3 px-2 text-center">
                                <span class="px-2.5 py-1 rounded-md text-[10px] font-bold uppercase {{ getStatusColor($trx->status) }}">
                                    {{ $trx->status }}
                                </span>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="text-center py-6 text-slate-400">Belum ada transaksi.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Recent Tool Borrowing Table --}}
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="font-bold text-slate-800 text-base">Peminjaman Alat Terbaru</h3>
                <a href="{{ route('alat.peminjaman.index') }}" class="text-xs font-semibold text-blue-600 hover:text-blue-700">Lihat Semua &rarr;</a>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b border-slate-100 text-slate-400 text-[11px] font-semibold uppercase tracking-wider">
                            <th class="py-3 px-2">Kode</th>
                            <th class="py-3 px-2">Peminjam</th>
                            <th class="py-3 px-2">Tgl Pinjam</th>
                            <th class="py-3 px-2 text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-xs">
                        @forelse($recentPeminjaman as $pnm)
                        <tr class="hover:bg-slate-50">
                            <td class="py-3 px-2 font-bold text-slate-900">{{ $pnm->kode_peminjaman }}</td>
                            <td class="py-3 px-2 text-slate-700 font-medium">{{ $pnm->peminjam?->nama ?? '-' }}</td>
                            <td class="py-3 px-2 text-slate-600">{{ formatTanggal($pnm->tanggal_pinjam) }}</td>
                            <td class="py-3 px-2 text-center">
                                <span class="px-2.5 py-1 rounded-md text-[10px] font-bold uppercase {{ getStatusColor($pnm->status) }}">
                                    {{ str_replace('_', ' ', $pnm->status) }}
                                </span>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="text-center py-6 text-slate-400">Belum ada peminjaman.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Lisensi Terbaru --}}
    @if($recentLisensi->count() > 0)
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="font-bold text-slate-800 text-base flex items-center gap-2">
                <i class="fa-solid fa-key text-violet-500"></i> Lisensi Terbaru
            </h3>
            <a href="{{ route('tefa.lisensi.index') }}" class="text-xs font-semibold text-blue-600 hover:text-blue-700">Lihat Semua &rarr;</a>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-slate-100 text-slate-400 text-[11px] font-semibold uppercase tracking-wider">
                        <th class="py-3 px-2">Nomor Lisensi</th>
                        <th class="py-3 px-2">Pembeli</th>
                        <th class="py-3 px-2">Tipe</th>
                        <th class="py-3 px-2">Berakhir</th>
                        <th class="py-3 px-2 text-center">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-xs">
                    @foreach($recentLisensi as $lis)
                    <tr class="hover:bg-slate-50">
                        <td class="py-3 px-2 font-mono font-bold text-slate-800 text-[11px]">{{ $lis->nomor_lisensi }}</td>
                        <td class="py-3 px-2 text-slate-700">
                            <span class="font-medium">{{ $lis->nama_pembeli }}</span>
                            @if($lis->nama_sekolah)
                            <span class="block text-slate-400 text-[10px]">{{ $lis->nama_sekolah }}</span>
                            @endif
                        </td>
                        <td class="py-3 px-2">
                            @if($lis->tipe === 'beli')
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-700">Beli</span>
                            @else
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-purple-100 text-purple-700">Langganan {{ $lis->lama_sewa }}bln</span>
                            @endif
                        </td>
                        <td class="py-3 px-2 text-slate-600">
                            {{ $lis->tanggal_akhir ? $lis->tanggal_akhir->format('d M Y') : '-' }}
                        </td>
                        <td class="py-3 px-2 text-center">
                            @php
                                $badge = match($lis->status) {
                                    'aktif' => 'bg-emerald-100 text-emerald-700',
                                    'kadaluarsa' => 'bg-red-100 text-red-700',
                                    default => 'bg-slate-100 text-slate-500'
                                };
                            @endphp
                            <span class="px-2.5 py-1 rounded-full text-[10px] font-bold uppercase {{ $badge }}">
                                {{ $lis->status }}
                            </span>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

</div>
@endsection

@section('scripts')
<script>
    // Pass PHP data to JS before Alpine initializes
    window.__sewaGedungData = @json($sewaGedungCalendar);

    document.addEventListener('alpine:init', function () {
        Alpine.data('kalenderSewa', function () {
            return {
                year: new Date().getFullYear(),
                month: new Date().getMonth(),
                monthNames: ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'],
                dayNames: ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'],
                rentals: window.__sewaGedungData || [],
                selectedDay: null,

                nextMonth() {
                    if (this.month === 11) { this.month = 0; this.year++; }
                    else { this.month++; }
                    this.selectedDay = null;
                },

                prevMonth() {
                    if (this.month === 0) { this.month = 11; this.year--; }
                    else { this.month--; }
                    this.selectedDay = null;
                },

                goToday() {
                    this.year = new Date().getFullYear();
                    this.month = new Date().getMonth();
                    this.selectedDay = new Date().getDate();
                },

                get daysInMonth() {
                    return new Date(this.year, this.month + 1, 0).getDate();
                },

                get firstDayOfWeek() {
                    return new Date(this.year, this.month, 1).getDay();
                },

                getRentalsForDay(dayNum) {
                    if (!dayNum) return [];
                    var m = String(this.month + 1).padStart(2, '0');
                    var d = String(dayNum).padStart(2, '0');
                    var dateStr = this.year + '-' + m + '-' + d;
                    return this.rentals.filter(function(r) {
                        return dateStr >= r.tanggal_mulai && dateStr <= r.tanggal_selesai;
                    });
                },

                getRentalsForCurrentMonth() {
                    var m = String(this.month + 1).padStart(2, '0');
                    var startStr = this.year + '-' + m + '-01';
                    var endStr = this.year + '-' + m + '-' + String(this.daysInMonth).padStart(2, '0');
                    return this.rentals.filter(function(r) {
                        return (r.tanggal_mulai <= endStr && r.tanggal_selesai >= startStr);
                    });
                },

                isToday(dayNum) {
                    var now = new Date();
                    return now.getDate() === dayNum && now.getMonth() === this.month && now.getFullYear() === this.year;
                },

                formatDateIndo(dateStr) {
                    if (!dateStr) return '-';
                    var parts = dateStr.split('-');
                    if (parts.length < 3) return dateStr;
                    var y = parts[0];
                    var mn = this.monthNames[parseInt(parts[1], 10) - 1];
                    var d = parseInt(parts[2], 10);
                    return d + ' ' + mn + ' ' + y;
                },

                formatDateRange(startStr, endStr) {
                    if (startStr === endStr) return this.formatDateIndo(startStr);
                    var p1 = startStr.split('-');
                    var p2 = endStr.split('-');
                    var m1 = this.monthNames[parseInt(p1[1], 10) - 1];
                    var m2 = this.monthNames[parseInt(p2[1], 10) - 1];
                    if (p1[0] === p2[0] && p1[1] === p2[1]) {
                        return parseInt(p1[2], 10) + ' - ' + parseInt(p2[2], 10) + ' ' + m1 + ' ' + p1[0];
                    }
                    return parseInt(p1[2], 10) + ' ' + m1 + ' - ' + parseInt(p2[2], 10) + ' ' + m2 + ' ' + p2[0];
                }
            };
        });
    });
</script>
@endsection
