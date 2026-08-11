@extends('layouts.app')

@section('title', 'Denda Peminjaman')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-xl font-bold text-slate-900">Denda Peminjaman Alat</h2>
            <p class="text-sm text-slate-500">Kelola catatan dan pembayaran denda keterlambatan / kerusakan alat</p>
        </div>
    </div>

    <!-- Table -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead class="bg-slate-50 border-b border-slate-200">
                    <tr class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider">
                        <th class="py-3.5 px-4">Kode Peminjaman</th>
                        <th class="py-3.5 px-4">Peminjam</th>
                        <th class="py-3.5 px-4">Jenis Denda</th>
                        <th class="py-3.5 px-4 text-right">Nominal Denda</th>
                        <th class="py-3.5 px-4 text-center">Status</th>
                        <th class="py-3.5 px-4 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-sm">
                    @forelse($denda as $item)
                    <tr class="hover:bg-slate-50">
                        <td class="py-3.5 px-4 font-mono text-xs text-slate-700 font-semibold">
                            {{ $item->peminjaman?->kode_peminjaman ?? '-' }}
                        </td>
                        <td class="py-3.5 px-4 font-semibold text-slate-900">
                            {{ $item->peminjaman?->peminjam?->nama ?? $item->peminjaman?->peminjam?->name }}
                        </td>
                        <td class="py-3.5 px-4">
                            <span class="px-2.5 py-1 rounded bg-slate-100 text-slate-700 text-xs font-semibold uppercase">
                                {{ str_replace('_', ' ', $item->jenis_denda ?? 'Keterlambatan') }}
                            </span>
                        </td>
                        <td class="py-3.5 px-4 text-right font-bold text-rose-600">
                            Rp {{ number_format($item->jumlah_denda, 0, ',', '.') }}
                        </td>
                        <td class="py-3.5 px-4 text-center">
                            <span class="px-3 py-1 rounded-full text-xs font-bold uppercase {{ getStatusColor($item->status) }}">
                                {{ $item->status }}
                            </span>
                        </td>
                        <td class="py-3.5 px-4 text-center">
                            @if($item->status === 'belum_dibayar')
                            <form action="{{ route('alat.denda.bayar', $item->id) }}" method="POST" class="inline">
                                @csrf
                                @method('PUT')
                                <button type="submit" class="px-3 py-1 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white font-semibold text-xs shadow-sm">
                                    Bayar Denda
                                </button>
                            </form>
                            @else
                            <span class="text-xs text-emerald-600 font-semibold"><i class="fa-solid fa-check-circle"></i> Lunas</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="py-12 text-center text-slate-400">Belum ada data denda peminjaman.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($denda->hasPages())
        <div class="px-4 py-3 border-t border-slate-100">{{ $denda->links() }}</div>
        @endif
    </div>
</div>
@endsection
