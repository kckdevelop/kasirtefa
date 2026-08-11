@extends('layouts.app')

@section('title', 'Laporan Penjualan')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-xl font-bold text-slate-900">Laporan Penjualan</h2>
            <p class="text-sm text-slate-500">Ringkasan dan analitik transaksi penjualan</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('laporan.penjualan') }}?{{ http_build_query(array_merge(request()->all(), ['export' => 'excel'])) }}"
                class="inline-flex items-center gap-2 px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-semibold rounded-xl text-sm shadow-md shadow-emerald-600/25 transition-all">
                <i class="fa-solid fa-file-excel"></i> Export Excel
            </a>
            <a href="{{ route('laporan.penjualan') }}?{{ http_build_query(array_merge(request()->all(), ['export' => 'pdf'])) }}"
                class="inline-flex items-center gap-2 px-4 py-2.5 bg-rose-600 hover:bg-rose-700 text-white font-semibold rounded-xl text-sm shadow-md shadow-rose-600/25 transition-all">
                <i class="fa-solid fa-file-pdf"></i> Export PDF
            </a>
        </div>
    </div>

    <!-- Filter -->
    <form method="GET" class="bg-white p-4 rounded-2xl border border-slate-200 shadow-sm flex flex-wrap gap-3">
        <div class="flex items-center gap-2">
            <label class="text-xs font-semibold text-slate-600">Dari</label>
            <input type="date" name="tanggal_mulai" value="{{ $filter['tanggal_mulai'] }}"
                class="py-2 px-3 text-sm rounded-lg border border-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>
        <div class="flex items-center gap-2">
            <label class="text-xs font-semibold text-slate-600">Hingga</label>
            <input type="date" name="tanggal_selesai" value="{{ $filter['tanggal_selesai'] }}"
                class="py-2 px-3 text-sm rounded-lg border border-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>
        <select name="periode" class="py-2 px-3 text-sm rounded-lg border border-slate-200 bg-white focus:outline-none focus:ring-2 focus:ring-blue-500">
            <option value="">Custom</option>
            <option value="hari_ini" @selected(request('periode')=='hari_ini')>Hari Ini</option>
            <option value="minggu_ini" @selected(request('periode')=='minggu_ini')>Minggu Ini</option>
            <option value="bulan_ini" @selected(request('periode')=='bulan_ini')>Bulan Ini</option>
            <option value="tahun_ini" @selected(request('periode')=='tahun_ini')>Tahun Ini</option>
        </select>
        <button type="submit" class="px-4 py-2 bg-blue-600 text-white font-semibold rounded-lg text-sm hover:bg-blue-700">Tampilkan</button>
    </form>

    <!-- Summary Cards -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
            <div class="flex items-center gap-3 mb-3">
                <div class="w-10 h-10 rounded-xl bg-blue-100 text-blue-600 flex items-center justify-center">
                    <i class="fa-solid fa-receipt"></i>
                </div>
                <p class="text-xs font-semibold text-slate-500">Total Transaksi</p>
            </div>
            <p class="text-3xl font-bold text-slate-900">{{ $laporan['total_transaksi'] ?? 0 }}</p>
        </div>
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
            <div class="flex items-center gap-3 mb-3">
                <div class="w-10 h-10 rounded-xl bg-emerald-100 text-emerald-600 flex items-center justify-center">
                    <i class="fa-solid fa-money-bills"></i>
                </div>
                <p class="text-xs font-semibold text-slate-500">Total Pendapatan</p>
            </div>
            <p class="text-2xl font-bold text-slate-900">Rp {{ number_format($laporan['total_pendapatan'] ?? 0, 0, ',', '.') }}</p>
        </div>
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
            <div class="flex items-center gap-3 mb-3">
                <div class="w-10 h-10 rounded-xl bg-violet-100 text-violet-600 flex items-center justify-center">
                    <i class="fa-solid fa-trending-up"></i>
                </div>
                <p class="text-xs font-semibold text-slate-500">Laba Kotor</p>
            </div>
            <p class="text-2xl font-bold text-slate-900">Rp {{ number_format($laporan['total_laba'] ?? 0, 0, ',', '.') }}</p>
        </div>
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
            <div class="flex items-center gap-3 mb-3">
                <div class="w-10 h-10 rounded-xl bg-amber-100 text-amber-600 flex items-center justify-center">
                    <i class="fa-solid fa-box"></i>
                </div>
                <p class="text-xs font-semibold text-slate-500">Produk Terjual</p>
            </div>
            <p class="text-3xl font-bold text-slate-900">{{ $laporan['total_item'] ?? 0 }}</p>
        </div>
    </div>

    <!-- Chart & Top Products -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Chart -->
        <div class="lg:col-span-2 bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
            <h3 class="font-bold text-slate-900 mb-4">Grafik Penjualan Harian</h3>
            <canvas id="penjualanChart" height="120"></canvas>
        </div>

        <!-- Top Products -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
            <h3 class="font-bold text-slate-900 mb-4">Produk Terlaris</h3>
            <div class="space-y-3">
                @forelse($laporan['produk_terlaris'] ?? [] as $i => $p)
                <div class="flex items-center gap-3">
                    <div class="w-7 h-7 rounded-lg {{ $i < 3 ? 'bg-amber-400 text-white' : 'bg-slate-100 text-slate-500' }} flex items-center justify-center text-xs font-bold flex-shrink-0">
                        {{ $i + 1 }}
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-semibold text-slate-800 truncate">{{ $p['nama'] }}</p>
                        <div class="w-full bg-slate-100 rounded-full h-1.5 mt-1">
                            <div class="bg-blue-500 h-1.5 rounded-full" style="width: {{ $p['persen'] }}%"></div>
                        </div>
                    </div>
                    <div class="text-right text-xs">
                        <p class="font-bold text-slate-800">{{ $p['total_qty'] }}</p>
                        <p class="text-slate-400">item</p>
                    </div>
                </div>
                @empty
                <p class="text-sm text-slate-400 text-center py-6">Tidak ada data</p>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Detail Table -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between">
            <h3 class="font-bold text-slate-900">Detail Transaksi</h3>
            <p class="text-xs text-slate-400">{{ $transaksi->total() }} transaksi</p>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead class="bg-slate-50 border-b border-slate-200">
                    <tr class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider">
                        <th class="py-3.5 px-4">Kode</th>
                        <th class="py-3.5 px-4">Tanggal</th>
                        <th class="py-3.5 px-4">Kasir</th>
                        <th class="py-3.5 px-4 text-right">Subtotal</th>
                        <th class="py-3.5 px-4 text-right">Diskon</th>
                        <th class="py-3.5 px-4 text-right">Total</th>
                        <th class="py-3.5 px-4 text-center">Metode</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-sm">
                    @forelse($transaksi as $t)
                    <tr class="hover:bg-slate-50">
                        <td class="py-3 px-4 font-mono font-bold text-slate-800">{{ $t->kode_transaksi }}</td>
                        <td class="py-3 px-4 text-slate-600">{{ formatTanggal($t->tanggal) }}</td>
                        <td class="py-3 px-4 text-slate-600">{{ $t->kasir?->nama }}</td>
                        <td class="py-3 px-4 text-right text-slate-800">Rp {{ number_format($t->subtotal, 0, ',', '.') }}</td>
                        <td class="py-3 px-4 text-right text-amber-600">- Rp {{ number_format($t->diskon_nominal, 0, ',', '.') }}</td>
                        <td class="py-3 px-4 text-right font-bold text-slate-900">Rp {{ number_format($t->total_akhir, 0, ',', '.') }}</td>
                        <td class="py-3 px-4 text-center">
                            <span class="px-2.5 py-1 rounded-lg text-xs font-bold uppercase bg-slate-100 text-slate-700">{{ $t->metode_pembayaran }}</span>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="7" class="py-12 text-center text-slate-400 text-sm">Tidak ada data untuk periode ini.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($transaksi->hasPages())
        <div class="px-4 py-3 border-t border-slate-100">{{ $transaksi->appends(request()->all())->links() }}</div>
        @endif
    </div>
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
const chartData = @json($laporan['grafik_harian'] ?? []);
const ctx = document.getElementById('penjualanChart');
if (ctx && chartData.labels) {
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: chartData.labels,
            datasets: [{
                label: 'Pendapatan',
                data: chartData.values,
                borderColor: '#2563eb',
                backgroundColor: 'rgba(37,99,235,0.08)',
                borderWidth: 2.5,
                pointBackgroundColor: '#2563eb',
                pointRadius: 4,
                tension: 0.4,
                fill: true
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label: (ctx) => 'Rp ' + new Intl.NumberFormat('id-ID').format(ctx.raw)
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: (v) => 'Rp ' + new Intl.NumberFormat('id-ID').format(v),
                        font: { size: 11 }
                    },
                    grid: { color: '#f1f5f9' }
                },
                x: {
                    ticks: { font: { size: 11 } },
                    grid: { display: false }
                }
            }
        }
    });
}
</script>
@endsection
