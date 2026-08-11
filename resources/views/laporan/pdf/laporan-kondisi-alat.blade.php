<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Kondisi Alat</title>
    <style>
        body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 11px; color: #333; line-height: 1.4; margin: 0; padding: 0; }
        .header { text-align: center; border-bottom: 2px solid #1e3a8a; padding-bottom: 10px; margin-bottom: 20px; }
        .header h2 { margin: 0; color: #1e3a8a; font-size: 18px; text-transform: uppercase; }
        .header p { margin: 2px 0; font-size: 10px; color: #64748b; }
        .summary-box { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px; padding: 10px; margin-bottom: 20px; }
        .summary-grid { display: table; width: 100%; }
        .summary-cell { display: table-cell; width: 25%; text-align: center; border-right: 1px solid #cbd5e1; }
        .summary-cell:last-child { border-right: none; }
        .summary-cell span { display: block; font-size: 9px; color: #64748b; text-transform: uppercase; }
        .summary-cell strong { font-size: 13px; color: #1e293b; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th { background-color: #1e3a8a; color: #ffffff; padding: 8px 6px; font-size: 10px; text-align: left; text-transform: uppercase; }
        td { padding: 6px; border-bottom: 1px solid #e2e8f0; font-size: 10px; }
        tr:nth-child(even) td { background-color: #f8fafc; }
        .text-center { text-align: center; }
    </style>
</head>
<body>
    <div class="header">
        <h2>TEACHING FACTORY (TEFA)</h2>
        <p>LAPORAN REKAPITULASI KONDISI ALAT</p>
        <p>Dicetak Pada: {{ $tanggalCetak }}</p>
    </div>

    <div class="summary-box">
        <div class="summary-grid">
            <div class="summary-cell">
                <span>Kondisi Baik</span>
                <strong style="color: #166534;">{{ number_format($rekap['baik']) }} Unit</strong>
            </div>
            <div class="summary-cell">
                <span>Kondisi Cukup</span>
                <strong style="color: #1e40af;">{{ number_format($rekap['cukup']) }} Unit</strong>
            </div>
            <div class="summary-cell">
                <span>Rusak Ringan</span>
                <strong style="color: #b45309;">{{ number_format($rekap['rusak_ringan']) }} Unit</strong>
            </div>
            <div class="summary-cell">
                <span>Rusak Berat</span>
                <strong style="color: #9f1239;">{{ number_format($rekap['rusak_berat']) }} Unit</strong>
            </div>
        </div>
    </div>

    <h3 style="color: #1e3a8a; font-size: 12px; margin-bottom: 8px;">Daftar Alat Perlu Perhatian / Perbaikan</h3>
    <table>
        <thead>
            <tr>
                <th class="text-center" width="5%">No</th>
                <th width="15%">Kode Alat</th>
                <th width="25%">Nama Alat</th>
                <th width="15%">Kategori</th>
                <th width="15%">Lokasi</th>
                <th class="text-center" width="10%">Kondisi</th>
                <th width="15%">Catatan</th>
            </tr>
        </thead>
        <tbody>
            @forelse($detail as $index => $row)
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td><strong>{{ $row->kode_alat }}</strong></td>
                <td>{{ $row->nama }}</td>
                <td>{{ $row->kategori?->nama ?? '-' }}</td>
                <td>{{ $row->lokasi_penyimpanan ?? '-' }}</td>
                <td class="text-center"><strong>{{ strtoupper($row->kondisi) }}</strong></td>
                <td>{{ $row->catatan ?? '-' }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="7" class="text-center" style="padding: 15px;">Semua alat dalam kondisi baik.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
