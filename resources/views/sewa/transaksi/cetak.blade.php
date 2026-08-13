<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kwitansi Sewa A4 - {{ $sewa->kode_sewa }}</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        @page {
            size: A4 portrait;
            margin: 10mm 12mm;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, sans-serif;
            background: #f1f5f9;
            color: #1e293b;
            line-height: 1.4;
        }

        .page {
            width: 210mm;
            min-height: 297mm;
            margin: 15px auto;
            background: #ffffff;
            border-radius: 12px;
            padding: 15mm 15mm;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        .content-wrap {
            flex: 1;
        }

        /* Header / Kop Surat */
        .header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            border-bottom: 2.5px solid #1e3a5f;
            padding-bottom: 14px;
            margin-bottom: 18px;
        }

        .brand {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .brand-icon {
            width: 48px;
            height: 48px;
            background: linear-gradient(135deg, #1e3a5f, #2563eb);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            color: #ffffff;
        }

        .brand-info h1 {
            font-size: 20px;
            font-weight: 800;
            color: #0f172a;
            letter-spacing: -0.5px;
            line-height: 1.2;
        }

        .brand-info p {
            font-size: 11px;
            color: #64748b;
            font-weight: 500;
        }

        .document-title {
            text-align: right;
        }

        .document-title h2 {
            font-size: 18px;
            font-weight: 800;
            color: #1e3a5f;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .document-title .code {
            font-size: 12px;
            font-family: monospace;
            font-weight: 700;
            color: #2563eb;
            background: #eff6ff;
            padding: 3px 10px;
            border-radius: 6px;
            display: inline-block;
            margin-top: 4px;
            border: 1px solid #bfdbfe;
        }

        .document-title .date {
            font-size: 11px;
            color: #64748b;
            margin-top: 4px;
        }

        /* Parties Info Cards */
        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 14px;
            margin-bottom: 16px;
        }

        .info-card {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 12px 14px;
        }

        .info-card-title {
            font-size: 10px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            color: #64748b;
            margin-bottom: 6px;
            border-bottom: 1px solid #e2e8f0;
            padding-bottom: 4px;
        }

        .info-card-content p {
            font-size: 12px;
            color: #334155;
            margin-bottom: 2px;
        }

        .info-card-content strong {
            color: #0f172a;
            font-size: 13px;
        }

        /* Periode Bar */
        .periode-bar {
            background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%);
            border: 1px solid #bfdbfe;
            border-radius: 8px;
            padding: 10px 16px;
            display: flex;
            justify-content: space-around;
            align-items: center;
            margin-bottom: 18px;
        }

        .periode-item {
            text-align: center;
        }

        .periode-item .label {
            font-size: 9px;
            font-weight: 700;
            text-transform: uppercase;
            color: #64748b;
            letter-spacing: 0.5px;
        }

        .periode-item .val {
            font-size: 13px;
            font-weight: 700;
            color: #1e3a5f;
            margin-top: 2px;
        }

        .periode-item .val-big {
            font-size: 18px;
            font-weight: 800;
            color: #2563eb;
        }

        .periode-divider {
            width: 1px;
            height: 30px;
            background: #93c5fd;
        }

        /* Table */
        .section-header {
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            color: #475569;
            margin-bottom: 8px;
        }

        table.detail-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 18px;
        }

        table.detail-table th {
            background: #1e3a5f;
            color: #ffffff;
            font-size: 10px;
            font-weight: 700;
            padding: 8px 12px;
            text-align: left;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        table.detail-table th.right { text-align: right; }
        table.detail-table th.center { text-align: center; }

        table.detail-table td {
            padding: 9px 12px;
            font-size: 12px;
            color: #334155;
            border-bottom: 1px solid #e2e8f0;
        }

        table.detail-table tr:nth-child(even) {
            background: #f8fafc;
        }

        table.detail-table td.right { text-align: right; font-weight: 600; }
        table.detail-table td.center { text-align: center; }

        .gedung-row td {
            font-weight: 700;
            color: #0f172a;
        }

        /* Calculation & Stamp Layout */
        .calc-wrapper {
            display: grid;
            grid-template-columns: 1fr 300px;
            gap: 16px;
            margin-bottom: 20px;
        }

        .stamp-box {
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: flex-start;
            padding: 14px;
            border-radius: 8px;
            background: #f8fafc;
            border: 1px dashed #cbd5e1;
        }

        .stamp-badge {
            display: inline-block;
            padding: 6px 14px;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            margin-bottom: 6px;
        }

        .stamp-lunas {
            background: #dcfce7;
            color: #15803d;
            border: 1.5px solid #22c55e;
        }

        .stamp-dp {
            background: #fef3c7;
            color: #b45309;
            border: 1.5px solid #f59e0b;
        }

        .stamp-belum {
            background: #ffe4e6;
            color: #be123c;
            border: 1.5px solid #f43f5e;
        }

        .calc-table {
            width: 100%;
            border-collapse: collapse;
        }

        .calc-table td {
            padding: 5px 0;
            font-size: 12px;
            color: #475569;
            border-bottom: 1px solid #f1f5f9;
        }

        .calc-table td.val {
            text-align: right;
            font-weight: 600;
            color: #1e293b;
        }

        .calc-table tr.total-row td {
            font-size: 14px;
            font-weight: 800;
            color: #1e3a5f;
            padding-top: 8px;
            border-top: 2px solid #1e3a5f;
        }

        .calc-table tr.total-row td.val {
            color: #2563eb;
        }

        .calc-table tr.dibayar-row td {
            font-weight: 700;
            color: #15803d;
        }

        .calc-table tr.sisa-row td {
            font-weight: 800;
            color: #be123c;
            background: #fff1f2;
            padding: 6px 8px;
            border-radius: 4px;
        }

        /* Signatures */
        .signatures {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 40px;
            margin-top: 24px;
            padding-top: 16px;
            border-top: 1px solid #e2e8f0;
        }

        .sign-box {
            text-align: center;
        }

        .sign-box p.role {
            font-size: 10px;
            font-weight: 700;
            text-transform: uppercase;
            color: #64748b;
            letter-spacing: 0.5px;
            margin-bottom: 50px;
        }

        .sign-box p.name {
            font-size: 12px;
            font-weight: 700;
            color: #0f172a;
            border-top: 1.5px solid #0f172a;
            display: inline-block;
            padding-top: 4px;
            min-width: 150px;
        }

        .sign-box p.sub {
            font-size: 10px;
            color: #94a3b8;
        }

        /* Footer Note */
        .footer-note {
            text-align: center;
            font-size: 9.5px;
            color: #94a3b8;
            border-top: 1px dashed #e2e8f0;
            padding-top: 10px;
            margin-top: 15px;
        }

        /* Media Print */
        @media print {
            body {
                background: #ffffff;
                padding: 0;
                margin: 0;
            }
            .page {
                box-shadow: none;
                margin: 0;
                border-radius: 0;
                padding: 10mm 10mm;
                width: 100%;
                min-height: auto;
            }
            .no-print {
                display: none !important;
            }
        }
    </style>
</head>
<body>

    {{-- Action Bar --}}
    <div class="no-print" style="text-align: center; padding: 14px; background: #ffffff; border-bottom: 1px solid #e2e8f0; sticky; top: 0; z-index: 100;">
        <button onclick="window.print()" style="background: #2563eb; color: #ffffff; border: none; padding: 10px 24px; border-radius: 8px; font-size: 14px; font-weight: 700; cursor: pointer; display: inline-flex; align-items: center; gap: 8px;">
            🖨️ Cetak Kwitansi A4
        </button>
        <a href="{{ route('sewa.transaksi.show', $sewa->id) }}" style="margin-left: 12px; background: #f1f5f9; color: #334155; text-decoration: none; padding: 10px 18px; border-radius: 8px; font-size: 14px; font-weight: 600; display: inline-block;">
            ← Kembali ke Detail
        </a>
    </div>

    <div class="page">
        <div class="content-wrap">
            {{-- Kop Surat --}}
            <div class="header">
                <div class="brand">
                    <div class="brand-icon">🏫</div>
                    <div class="brand-info">
                        <h1>TEFa & INVENTARIS ALAT</h1>
                        <p>Teaching Factory System & Sewa Gedung / Lab</p>
                    </div>
                </div>
                <div class="document-title">
                    <h2>Kwitansi Penyewaan</h2>
                    <div class="code">{{ $sewa->kode_sewa }}</div>
                    <div class="date">Tanggal: {{ formatTanggal($sewa->created_at, 'd F Y') }}</div>
                </div>
            </div>

            {{-- Info Penyewa & Gedung --}}
            <div class="info-grid">
                <div class="info-card">
                    <div class="info-card-title">🏢 Gedung / Lab Yang Disewa</div>
                    <div class="info-card-content">
                        <p><strong>{{ $sewa->gedung?->nama_gedung ?? '-' }}</strong></p>
                        <p>Kode: {{ $sewa->gedung?->kode_gedung ?? '-' }}</p>
                        <p>Lokasi: {{ $sewa->gedung?->lokasi ?? '-' }}</p>
                        <p>Harga Sewa: {{ formatRupiah($sewa->harga_sewa_gedung) }} / hari</p>
                    </div>
                </div>
                <div class="info-card">
                    <div class="info-card-title">👤 Identitas Penyewa</div>
                    <div class="info-card-content">
                        <p>Nama: <strong>{{ $sewa->nama_penyewa }}</strong></p>
                        <p>Instansi: {{ $sewa->instansi_penyewa ?? '-' }}</p>
                        <p>Telepon: {{ $sewa->telepon_penyewa ?? '-' }}</p>
                        <p>Petugas: {{ $sewa->user?->nama ?? $sewa->user?->name ?? 'Admin' }}</p>
                    </div>
                </div>
            </div>

            {{-- Periode Bar --}}
            <div class="periode-bar">
                <div class="periode-item">
                    <div class="label">Tanggal Mulai</div>
                    <div class="val">{{ formatTanggal($sewa->tanggal_mulai, 'd/m/Y') }}</div>
                </div>
                <div class="periode-divider"></div>
                <div class="periode-item">
                    <div class="label">Lama Sewa</div>
                    <div class="val-big">{{ $sewa->lama_sewa }} Hari</div>
                </div>
                <div class="periode-divider"></div>
                <div class="periode-item">
                    <div class="label">Tanggal Selesai</div>
                    <div class="val">{{ formatTanggal($sewa->tanggal_selesai, 'd/m/Y') }}</div>
                </div>
            </div>

            {{-- Table Detail Items --}}
            <div class="section-header">📋 Rincian Sewa Gedung & Fasilitas</div>
            <table class="detail-table">
                <thead>
                    <tr>
                        <th style="width: 40%;">Deskripsi Item</th>
                        <th class="center" style="width: 12%;">Qty</th>
                        <th class="right" style="width: 20%;">Harga Satuan</th>
                        <th class="center" style="width: 10%;">Hari</th>
                        <th class="right" style="width: 18%;">Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    <tr class="gedung-row">
                        <td>Gedung/Lab: {{ $sewa->gedung?->nama_gedung }}</td>
                        <td class="center">1</td>
                        <td class="right">{{ formatRupiah($sewa->harga_sewa_gedung) }}</td>
                        <td class="center">{{ $sewa->lama_sewa }}</td>
                        <td class="right">{{ formatRupiah($sewa->subtotal_gedung) }}</td>
                    </tr>
                    @foreach($sewa->details as $detail)
                    <tr>
                        <td style="padding-left: 24px;">↳ Fasilitas: {{ $detail->nama_fasilitas }}</td>
                        <td class="center">{{ $detail->jumlah }}</td>
                        <td class="right">{{ formatRupiah($detail->harga_per_item) }}</td>
                        <td class="center">{{ $sewa->lama_sewa }}</td>
                        <td class="right">{{ formatRupiah($detail->subtotal) }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>

            {{-- Calculation & Stamp Section --}}
            @php
                $sisa = max(0, $sewa->total_biaya - $sewa->jumlah_dibayar);
                $kembalian = max(0, $sewa->jumlah_dibayar - $sewa->total_biaya);
            @endphp
            <div class="calc-wrapper">
                {{-- Left: Status Stamp & Catatan --}}
                <div class="stamp-box">
                    @if($sewa->status_pembayaran === 'lunas')
                    <div class="stamp-badge stamp-lunas">✓ STAMP LUNAS</div>
                    <p style="font-size: 11px; color: #15803d; font-weight: 600;">Pembayaran telah dilunasi sepenuhnya.</p>
                    @elseif($sewa->status_pembayaran === 'dp')
                    <div class="stamp-badge stamp-dp">⏱ STATUS: UANG MUKA (DP)</div>
                    <p style="font-size: 11px; color: #92400e; font-weight: 600;">Sisa pembayaran sebesar {{ formatRupiah($sisa) }} wajib dilunasi sebelum masa sewa berakhir.</p>
                    @else
                    <div class="stamp-badge stamp-belum">⚠ BELUM DIBAYAR</div>
                    <p style="font-size: 11px; color: #9f1239; font-weight: 600;">Tagihan sewa belum dibayar.</p>
                    @endif

                    @if($sewa->catatan)
                    <div style="margin-top: 10px; font-size: 11px; color: #475569;">
                        <strong>Catatan:</strong> {{ $sewa->catatan }}
                    </div>
                    @endif
                </div>

                {{-- Right: Payment Breakdown Calculations --}}
                <div>
                    <table class="calc-table">
                        <tr>
                            <td>Subtotal Gedung</td>
                            <td class="val">{{ formatRupiah($sewa->subtotal_gedung) }}</td>
                        </tr>
                        <tr>
                            <td>Subtotal Fasilitas</td>
                            <td class="val">{{ formatRupiah($sewa->subtotal_fasilitas) }}</td>
                        </tr>
                        <tr class="total-row">
                            <td>Total Biaya</td>
                            <td class="val">{{ formatRupiah($sewa->total_biaya) }}</td>
                        </tr>
                        <tr class="dibayar-row">
                            <td>
                                @if($sewa->status_pembayaran === 'dp')
                                Jumlah DP / Uang Muka
                                @else
                                Jumlah Dibayar
                                @endif
                            </td>
                            <td class="val">{{ formatRupiah($sewa->jumlah_dibayar) }}</td>
                        </tr>

                        @if($sewa->status_pembayaran === 'dp' || $sisa > 0)
                        <tr class="sisa-row">
                            <td>Sisa Tagihan (Pelunasan)</td>
                            <td class="val" style="color: #be123c;">{{ formatRupiah($sisa) }}</td>
                        </tr>
                        @elseif($kembalian > 0)
                        <tr>
                            <td>Kembalian</td>
                            <td class="val" style="color: #2563eb;">{{ formatRupiah($kembalian) }}</td>
                        </tr>
                        @endif
                    </table>
                </div>
            </div>

            {{-- Signatures --}}
            <div class="signatures">
                <div class="sign-box">
                    <p class="role">Pihak Penyewa</p>
                    <p class="name">{{ $sewa->nama_penyewa }}</p>
                    <p class="sub">{{ $sewa->instansi_penyewa ?? 'Penyewa' }}</p>
                </div>
                <div class="sign-box">
                    <p class="role">Petugas / Pengelola TEFa</p>
                    <p class="name">{{ $sewa->user?->nama ?? $sewa->user?->name ?? 'Petugas TEFa' }}</p>
                    <p class="sub">Petugas Pengelola Gedung</p>
                </div>
            </div>
        </div>

        {{-- Footer Note --}}
        <div class="footer-note">
            Kwitansi ini diterbitkan secara otomatis oleh Sistem TEFa & Inventaris Alat · Dokumen Sah Penyewaan Gedung/Lab · Kode Transaksi: {{ $sewa->kode_sewa }}
        </div>
    </div>

</body>
</html>
