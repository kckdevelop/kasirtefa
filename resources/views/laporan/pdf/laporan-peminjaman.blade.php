<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Peminjaman Alat</title>
    <style>
        body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 11px; color: #333; line-height: 1.4; margin: 0; padding: 0; }
        .header { text-align: center; border-bottom: 2px solid #1e3a8a; padding-bottom: 10px; margin-bottom: 20px; }
        .header h2 { margin: 0; color: #1e3a8a; font-size: 18px; text-transform: uppercase; }
        .header p { margin: 2px 0; font-size: 10px; color: #64748b; }
        .summary-box { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px; padding: 10px; margin-bottom: 20px; }
        .summary-grid { display: table; width: 100%; }
        .summary-cell { display: table-cell; width: 20%; text-align: center; border-right: 1px solid #cbd5e1; }
        .summary-cell:last-child { border-right: none; }
        .summary-cell span { display: block; font-size: 9px; color: #64748b; text-transform: uppercase; }
        .summary-cell strong { font-size: 13px; color: #1e293b; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th { background-color: #1e3a8a; color: #ffffff; padding: 8px 6px; font-size: 10px; text-align: left; text-transform: uppercase; }
        td { padding: 6px; border-bottom: 1px solid #e2e8f0; font-size: 10px; }
        tr:nth-child(even) td { background-color: #f8fafc; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .badge { padding: 3px 6px; border-radius: 4px; font-size: 8px; font-weight: bold; text-transform: uppercase; }
        .badge-dikembalikan { background: #dcfce7; color: #166534; }
        .badge-dipinjam { background: #dbeafe; color: #1e40af; }
        .badge-terlambat { background: #ffe4e6; color: #9f1239; }
        .signature-table { width: 100%; margin-top: 40px; }
        .signature-cell { width: 50%; text-align: center; }
        .signature-space { height: 60px; }
    </style>
</head>
<body>
    <div class="header">
        <h2>TEACHING FACTORY (TEFA)</h2>
        <p>LAPORAN PEMINJAMAN ALAT & INVENTARIS</p>
        <p>Dicetak Pada: {{ $tanggalCetak }}</p>
    </div>

    <div class="summary-box">
        <div class="summary-grid">
            <div class="summary-cell">
                <span>Total Peminjaman</span>
                <strong>{{ number_format($ringkasan['total_peminjaman']) }}</strong>
            </div>
            <div class="summary-cell">
                <span>Dipinjam</span>
                <strong>{{ number_format($ringkasan['dipinjam']) }}</strong>
            </div>
            <div class="summary-cell">
                <span>Dikembalikan</span>
                <strong>{{ number_format($ringkasan['dikembalikan']) }}</strong>
            </div>
            <div class="summary-cell">
                <span>Terlambat</span>
                <strong>{{ number_format($ringkasan['terlambat']) }}</strong>
            </div>
            <div class="summary-cell">
                <span>Ditolak</span>
                <strong>{{ number_format($ringkasan['ditolak']) }}</strong>
            </div>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th class="text-center" width="5%">No</th>
                <th width="18%">Kode Pinjam</th>
                <th width="20%">Peminjam</th>
                <th width="12%">Tgl Pinjam</th>
                <th width="12%">Rencana Kembali</th>
                <th width="18%">Keperluan</th>
                <th class="text-center" width="15%">Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($data as $index => $row)
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td><strong>{{ $row->kode_peminjaman }}</strong></td>
                <td>{{ $row->peminjam?->nama ?? '-' }}<br><small style="color:#64748b;">{{ $row->peminjam?->kelas }} {{ $row->peminjam?->jurusan }}</small></td>
                <td>{{ formatTanggal($row->tanggal_pinjam) }}</td>
                <td>{{ formatTanggal($row->tanggal_kembali_rencana) }}</td>
                <td>{{ $row->keperluan }}</td>
                <td class="text-center">
                    <span class="badge badge-{{ $row->status }}">{{ str_replace('_', ' ', $row->status) }}</span>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="7" class="text-center" style="padding: 20px;">Tidak ada data peminjaman.</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <table class="signature-table">
        <tr>
            <td class="signature-cell">
                <p>Mengetahui,</p>
                <p><strong>Kepala Laboratorium / TEFa</strong></p>
                <div class="signature-space"></div>
                <p>( _______________________ )</p>
            </td>
            <td class="signature-cell">
                <p>Petugas Alat,</p>
                <div class="signature-space" style="height: 75px;"></div>
                <p>( _______________________ )</p>
            </td>
        </tr>
    </table>
</body>
</html>
