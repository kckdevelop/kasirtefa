<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Inventaris Alat</title>
    <style>
        body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 10px; color: #333; line-height: 1.4; margin: 0; padding: 0; }
        .header { text-align: center; border-bottom: 2px solid #1e3a8a; padding-bottom: 10px; margin-bottom: 15px; }
        .header h2 { margin: 0; color: #1e3a8a; font-size: 16px; text-transform: uppercase; }
        .header p { margin: 2px 0; font-size: 9px; color: #64748b; }
        .summary-box { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px; padding: 8px; margin-bottom: 15px; }
        .summary-grid { display: table; width: 100%; }
        .summary-cell { display: table-cell; width: 20%; text-align: center; border-right: 1px solid #cbd5e1; }
        .summary-cell:last-child { border-right: none; }
        .summary-cell span { display: block; font-size: 8px; color: #64748b; text-transform: uppercase; }
        .summary-cell strong { font-size: 12px; color: #1e293b; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th { background-color: #1e3a8a; color: #ffffff; padding: 6px 4px; font-size: 9px; text-align: left; text-transform: uppercase; }
        td { padding: 5px 4px; border-bottom: 1px solid #e2e8f0; font-size: 9px; }
        tr:nth-child(even) td { background-color: #f8fafc; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .signature-table { width: 100%; margin-top: 30px; }
        .signature-cell { width: 50%; text-align: center; }
        .signature-space { height: 50px; }
        .text-emerald { color: #047857; font-weight: bold; }
        .text-amber { color: #b45309; font-weight: bold; }
        .text-rose { color: #be123c; font-weight: bold; }
    </style>
</head>
<body>
    <div class="header">
        <h2>TEACHING FACTORY (TEFA)</h2>
        <p>LAPORAN DAFTAR INVENTARIS ALAT & RINCIAN KONDISI</p>
        <p>Dicetak Pada: {{ $tanggalCetak }}</p>
    </div>

    <div class="summary-box">
        <div class="summary-grid">
            <div class="summary-cell">
                <span>Total Unit</span>
                <strong>{{ number_format($ringkasan['total_unit'] ?? 0) }}</strong>
            </div>
            <div class="summary-cell">
                <span>Unit Baik</span>
                <strong class="text-emerald">{{ number_format($ringkasan['total_baik'] ?? 0) }}</strong>
            </div>
            <div class="summary-cell">
                <span>Unit Rusak</span>
                <strong class="text-amber">{{ number_format($ringkasan['total_rusak'] ?? 0) }}</strong>
            </div>
            <div class="summary-cell">
                <span>Unit Hilang</span>
                <strong class="text-rose">{{ number_format($ringkasan['total_hilang'] ?? 0) }}</strong>
            </div>
            <div class="summary-cell">
                <span>Total Nilai Aset</span>
                <strong>Rp {{ number_format($ringkasan['total_aset_nilai'] ?? 0, 0, ',', '.') }}</strong>
            </div>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th class="text-center" width="4%">No</th>
                <th width="14%">Kode Alat</th>
                <th width="24%">Nama Alat</th>
                <th width="14%">Kategori</th>
                <th class="text-center" width="8%">Total</th>
                <th class="text-center" width="8%">Tersedia</th>
                <th class="text-center" width="8%">Baik</th>
                <th class="text-center" width="8%">Rusak</th>
                <th class="text-center" width="8%">Hilang</th>
                <th class="text-center" width="10%">Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($data as $index => $row)
            @php
                $jBaik = ($row->jumlah_baik ?? 0) + ($row->jumlah_cukup ?? 0);
                if ($jBaik == 0 && ($row->jumlah_total ?? 0) > 0 && ($row->jumlah_rusak_ringan ?? 0) == 0 && ($row->jumlah_rusak_berat ?? 0) == 0 && ($row->jumlah_hilang ?? 0) == 0) {
                    $jBaik = $row->jumlah_total ?? 1;
                }
                $jRusak = ($row->jumlah_rusak_ringan ?? 0) + ($row->jumlah_rusak_berat ?? 0);
                $jHilang = $row->jumlah_hilang ?? 0;
            @endphp
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td><strong>{{ $row->kode_alat }}</strong></td>
                <td>{{ $row->nama }}<br><small style="color:#64748b;">{{ $row->merek }} {{ $row->tipe }}</small></td>
                <td>{{ $row->kategori?->nama ?? '-' }}</td>
                <td class="text-center"><strong>{{ $row->jumlah_total }}</strong></td>
                <td class="text-center" style="color:#047857;"><strong>{{ $row->jumlah_tersedia }}</strong></td>
                <td class="text-center text-emerald">{{ $jBaik }}</td>
                <td class="text-center text-amber">{{ $jRusak }}</td>
                <td class="text-center text-rose">{{ $jHilang }}</td>
                <td class="text-center">{{ strtoupper(str_replace('_', ' ', $row->status_ketersediaan ?? $row->status ?? 'tersedia')) }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="10" class="text-center" style="padding: 20px;">Tidak ada data alat.</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <table class="signature-table">
        <tr>
            <td class="signature-cell">
                <p>Mengetahui,</p>
                <p><strong>Kepala Bengkel / Lab</strong></p>
                <div class="signature-space"></div>
                <p>( _______________________ )</p>
            </td>
            <td class="signature-cell">
                <p>Pengelola Inventaris,</p>
                <div class="signature-space"></div>
                <p>( _______________________ )</p>
            </td>
        </tr>
    </table>
</body>
</html>
