<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bukti Pembelian Lisensi - {{ $lisensi->nomor_lisensi }}</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap');

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            font-size: 13px;
            color: #1e293b;
            background: #f1f5f9;
            padding: 30px;
            min-height: 100vh;
        }

        /* ── Action Bar (hidden on print) ── */
        .action-bar {
            max-width: 794px;
            margin: 0 auto 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 20px;
            border-radius: 10px;
            font-size: 13px;
            font-weight: 600;
            font-family: 'Inter', sans-serif;
            cursor: pointer;
            border: none;
            transition: all .2s;
            text-decoration: none;
        }

        .btn-print  { background: #2563eb; color: #fff; }
        .btn-print:hover { background: #1d4ed8; }
        .btn-back   { background: #fff; color: #475569; border: 1.5px solid #e2e8f0; }
        .btn-back:hover { background: #f8fafc; }

        /* ── A4 Paper ── */
        .paper {
            width: 794px;
            min-height: 1123px;
            margin: 0 auto;
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 10px 40px rgba(0,0,0,.12);
            overflow: hidden;
            position: relative;
        }

        /* ── Header Band ── */
        .header-band {
            background: linear-gradient(135deg, #1e3a8a 0%, #2563eb 60%, #3b82f6 100%);
            padding: 36px 48px 32px;
            color: #fff;
            position: relative;
            overflow: hidden;
        }

        .header-band::before {
            content: '';
            position: absolute;
            top: -60px; right: -60px;
            width: 260px; height: 260px;
            border-radius: 50%;
            background: rgba(255,255,255,.07);
        }

        .header-band::after {
            content: '';
            position: absolute;
            bottom: -80px; left: -40px;
            width: 220px; height: 220px;
            border-radius: 50%;
            background: rgba(255,255,255,.05);
        }

        .header-inner {
            position: relative;
            z-index: 1;
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
        }

        .header-brand { max-width: 60%; }
        .brand-name {
            font-size: 22px;
            font-weight: 800;
            letter-spacing: -0.5px;
            color: #fff;
        }
        .brand-sub {
            font-size: 11px;
            color: rgba(255,255,255,.7);
            margin-top: 4px;
            line-height: 1.5;
        }

        .header-doc { text-align: right; }
        .doc-title {
            font-size: 28px;
            font-weight: 900;
            letter-spacing: -1px;
            color: #fff;
        }
        .doc-subtitle {
            font-size: 11px;
            color: rgba(255,255,255,.75);
            margin-top: 2px;
        }
        .doc-number {
            margin-top: 10px;
            display: inline-block;
            background: rgba(255,255,255,.15);
            border: 1px solid rgba(255,255,255,.25);
            border-radius: 8px;
            padding: 6px 14px;
            font-size: 12px;
            font-weight: 700;
            font-family: 'Courier New', monospace;
            color: #fff;
            letter-spacing: 1px;
        }

        /* ── Status Banner ── */
        .status-banner {
            padding: 10px 48px;
            font-size: 11.5px;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .status-banner.lunas {
            background: #dcfce7;
            color: #15803d;
            border-bottom: 2px solid #86efac;
        }
        .status-banner.belum {
            background: #fef9c3;
            color: #92400e;
            border-bottom: 2px solid #fde68a;
        }
        .status-dot {
            width: 8px; height: 8px;
            border-radius: 50%;
            flex-shrink: 0;
        }
        .status-dot.lunas { background: #16a34a; }
        .status-dot.belum { background: #d97706; }

        /* ── Body ── */
        .body {
            padding: 36px 48px;
        }

        /* ── Info Grid ── */
        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 24px;
            margin-bottom: 28px;
        }

        .info-box {
            background: #f8fafc;
            border: 1.5px solid #e2e8f0;
            border-radius: 12px;
            padding: 18px 20px;
        }

        .info-box-title {
            font-size: 10px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #64748b;
            margin-bottom: 12px;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .info-row {
            display: flex;
            justify-content: space-between;
            align-items: baseline;
            padding: 5px 0;
            border-bottom: 1px dashed #e2e8f0;
        }
        .info-row:last-child { border-bottom: none; }

        .info-label {
            font-size: 11px;
            color: #64748b;
            font-weight: 500;
        }
        .info-value {
            font-size: 12px;
            color: #1e293b;
            font-weight: 600;
            text-align: right;
            max-width: 60%;
        }

        /* ── Items Table ── */
        .section-title {
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #64748b;
            margin-bottom: 10px;
        }

        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 28px;
        }

        .items-table thead tr {
            background: #f1f5f9;
        }

        .items-table thead th {
            padding: 10px 14px;
            text-align: left;
            font-size: 10.5px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .8px;
            color: #64748b;
            border-bottom: 2px solid #e2e8f0;
        }

        .items-table thead th:last-child { text-align: right; }

        .items-table tbody td {
            padding: 14px;
            border-bottom: 1px solid #f1f5f9;
            font-size: 12.5px;
            color: #1e293b;
        }

        .items-table tbody td:last-child {
            text-align: right;
            font-weight: 700;
        }

        .item-name {
            font-weight: 700;
            color: #1e293b;
        }

        .item-desc {
            font-size: 11px;
            color: #64748b;
            margin-top: 3px;
        }

        /* ── Totals ── */
        .totals-block {
            display: flex;
            justify-content: flex-end;
            margin-bottom: 28px;
        }

        .totals-inner {
            width: 280px;
        }

        .total-row {
            display: flex;
            justify-content: space-between;
            padding: 6px 0;
            font-size: 12.5px;
        }

        .total-row.main {
            border-top: 2px solid #1e293b;
            margin-top: 8px;
            padding-top: 12px;
        }
        .total-row.main .total-label { font-size: 14px; font-weight: 800; color: #1e293b; }
        .total-row.main .total-amount { font-size: 18px; font-weight: 900; color: #1e293b; }

        .total-label { color: #64748b; font-weight: 500; }
        .total-amount { font-weight: 700; color: #1e293b; }

        /* ── Payment Info ── */
        .payment-block {
            background: #f0fdf4;
            border: 1.5px solid #bbf7d0;
            border-radius: 12px;
            padding: 16px 20px;
            margin-bottom: 28px;
            display: flex;
            align-items: center;
            gap: 16px;
        }
        .payment-block.unpaid {
            background: #fffbeb;
            border-color: #fde68a;
        }
        .payment-icon {
            width: 48px; height: 48px;
            border-radius: 12px;
            display: flex; align-items: center; justify-content: center;
            flex-shrink: 0;
            font-size: 20px;
        }
        .payment-icon.lunas { background: #dcfce7; color: #16a34a; }
        .payment-icon.unpaid { background: #fef9c3; color: #d97706; }
        .payment-details { flex: 1; }
        .payment-title { font-size: 13px; font-weight: 700; color: #1e293b; margin-bottom: 4px; }
        .payment-meta { font-size: 11.5px; color: #64748b; line-height: 1.5; }

        /* ── Tipe Badge ── */
        .tipe-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .5px;
        }
        .tipe-badge.beli { background: #dcfce7; color: #15803d; }
        .tipe-badge.berlangganan { background: #ede9fe; color: #7c3aed; }

        /* ── Notes ── */
        .notes-box {
            background: #f8fafc;
            border-left: 4px solid #3b82f6;
            border-radius: 0 10px 10px 0;
            padding: 14px 18px;
            margin-bottom: 28px;
            font-size: 12px;
            color: #475569;
            line-height: 1.6;
        }

        /* ── Validity Period ── */
        .validity-block {
            background: linear-gradient(135deg, #eff6ff, #dbeafe);
            border: 1.5px solid #bfdbfe;
            border-radius: 12px;
            padding: 16px 20px;
            margin-bottom: 28px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .validity-label { font-size: 11px; color: #3b82f6; font-weight: 600; }
        .validity-date  { font-size: 15px; font-weight: 800; color: #1e3a8a; }

        /* ── Footer ── */
        .footer {
            margin-top: 36px;
            padding-top: 20px;
            border-top: 1.5px dashed #e2e8f0;
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
        }

        .footer-left { font-size: 11px; color: #94a3b8; line-height: 1.6; }
        .footer-right { text-align: center; }
        .sign-name  { font-size: 12px; font-weight: 700; color: #1e293b; margin-top: 60px; border-top: 1.5px solid #94a3b8; padding-top: 6px; }
        .sign-title { font-size: 10.5px; color: #64748b; }

        /* ── Watermark ── */
        .watermark {
            position: absolute;
            bottom: 140px;
            right: 50px;
            font-size: 100px;
            font-weight: 900;
            color: rgba(37,99,235,.04);
            transform: rotate(-20deg);
            pointer-events: none;
            user-select: none;
            letter-spacing: -2px;
            line-height: 1;
        }

        /* ── Print Styles ── */
        @media print {
            body {
                background: #fff !important;
                padding: 0 !important;
            }
            .action-bar { display: none !important; }
            .paper {
                width: 100% !important;
                min-height: auto !important;
                border-radius: 0 !important;
                box-shadow: none !important;
            }
            @page {
                size: A4 portrait;
                margin: 0;
            }
        }
    </style>
</head>
<body>

    {{-- Action Bar (screen only) --}}
    <div class="action-bar">
        <a href="{{ route('tefa.lisensi.index') }}" class="btn btn-back">
            ← Kembali
        </a>
        <button onclick="window.print()" class="btn btn-print">
            🖨️ Cetak / Simpan PDF
        </button>
        @if($lisensi->status_pembayaran !== 'lunas')
        <a href="{{ route('tefa.lisensi.index') }}?bayar={{ $lisensi->id }}" class="btn" style="background:#16a34a;color:#fff;">
            💳 Tandai Lunas
        </a>
        @endif
    </div>

    {{-- A4 Paper --}}
    <div class="paper">

        {{-- Watermark --}}
        <div class="watermark">TEFa</div>

        {{-- Header Band --}}
        <div class="header-band">
            <div class="header-inner">
                <div class="header-brand">
                    <div class="brand-name">{{ $pengaturan['nama_aplikasi'] ?? 'Sistem Inventaris & Kasir TEFa' }}</div>
                    <div class="brand-sub">
                        {{ $pengaturan['alamat'] ?? '' }}<br>
                        {{ $pengaturan['telepon'] ?? '' }}
                        @if(!empty($pengaturan['email'])) &nbsp;·&nbsp; {{ $pengaturan['email'] }} @endif
                    </div>
                </div>
                <div class="header-doc">
                    <div class="doc-title">INVOICE</div>
                    <div class="doc-subtitle">Bukti Pembelian Lisensi</div>
                    <div class="doc-number">{{ $lisensi->nomor_lisensi }}</div>
                </div>
            </div>
        </div>

        {{-- Status Banner --}}
        @if($lisensi->status_pembayaran === 'lunas')
        <div class="status-banner lunas">
            <div class="status-dot lunas"></div>
            ✅ PEMBAYARAN LUNAS — {{ $lisensi->tanggal_pembayaran ? $lisensi->tanggal_pembayaran->translatedFormat('d F Y') : '-' }}
            &nbsp;·&nbsp; Metode: {{ strtoupper($lisensi->metode_pembayaran ?? '-') }}
        </div>
        @else
        <div class="status-banner belum">
            <div class="status-dot belum"></div>
            ⏳ MENUNGGU PEMBAYARAN — Belum terbayar
        </div>
        @endif

        {{-- Body --}}
        <div class="body">

            {{-- Info Grid --}}
            <div class="info-grid">
                {{-- Informasi Pembeli --}}
                <div class="info-box">
                    <div class="info-box-title">
                        👤 Informasi Pembeli
                    </div>
                    <div class="info-row">
                        <span class="info-label">Nama</span>
                        <span class="info-value">{{ $lisensi->nama_pembeli }}</span>
                    </div>
                    @if($lisensi->nama_sekolah)
                    <div class="info-row">
                        <span class="info-label">Instansi</span>
                        <span class="info-value">{{ $lisensi->nama_sekolah }}</span>
                    </div>
                    @endif
                    @if($lisensi->email)
                    <div class="info-row">
                        <span class="info-label">Email</span>
                        <span class="info-value">{{ $lisensi->email }}</span>
                    </div>
                    @endif
                    @if($lisensi->telepon)
                    <div class="info-row">
                        <span class="info-label">Telepon</span>
                        <span class="info-value">{{ $lisensi->telepon }}</span>
                    </div>
                    @endif
                </div>

                {{-- Informasi Transaksi --}}
                <div class="info-box">
                    <div class="info-box-title">
                        📋 Informasi Transaksi
                    </div>
                    <div class="info-row">
                        <span class="info-label">No. Lisensi</span>
                        <span class="info-value" style="font-family:monospace;font-size:11px;">{{ $lisensi->nomor_lisensi }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Tipe</span>
                        <span class="info-value">
                            <span class="tipe-badge {{ $lisensi->tipe }}">
                                {{ $lisensi->tipe === 'beli' ? 'Pembelian' : 'Berlangganan' }}
                                @if($lisensi->tipe === 'berlangganan') {{ $lisensi->lama_sewa }} Bulan @endif
                            </span>
                        </span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Tanggal Dibuat</span>
                        <span class="info-value">{{ $lisensi->created_at->translatedFormat('d F Y') }}</span>
                    </div>
                    @if($lisensi->tipe === 'beli' && $lisensi->tanggal_beli)
                    <div class="info-row">
                        <span class="info-label">Tanggal Beli</span>
                        <span class="info-value">{{ $lisensi->tanggal_beli->translatedFormat('d F Y') }}</span>
                    </div>
                    @elseif($lisensi->tipe === 'berlangganan' && $lisensi->tanggal_mulai)
                    <div class="info-row">
                        <span class="info-label">Tanggal Mulai</span>
                        <span class="info-value">{{ $lisensi->tanggal_mulai->translatedFormat('d F Y') }}</span>
                    </div>
                    @endif
                </div>
            </div>

            {{-- Items Table --}}
            <div class="section-title">🛒 Rincian Pembelian</div>
            <table class="items-table">
                <thead>
                    <tr>
                        <th style="width:40px;">#</th>
                        <th>Deskripsi</th>
                        <th style="text-align:center;">Qty</th>
                        <th style="text-align:right;">Harga</th>
                        <th style="text-align:right;">Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td style="color:#94a3b8;font-size:11px;">1</td>
                        <td>
                            <div class="item-name">
                                Lisensi Aplikasi — {{ $pengaturan['nama_aplikasi'] ?? 'Sistem Inventaris & Kasir TEFa' }}
                            </div>
                            <div class="item-desc">
                                @if($lisensi->tipe === 'beli')
                                    Lisensi Pembelian Penuh (Lifetime)
                                    @if($lisensi->tanggal_jatuh_tempo)
                                        · Berlaku s/d {{ $lisensi->tanggal_jatuh_tempo->translatedFormat('d F Y') }}
                                    @endif
                                @else
                                    Lisensi Berlangganan {{ $lisensi->lama_sewa }} Bulan
                                    @if($lisensi->tanggal_mulai && $lisensi->tanggal_berakhir)
                                        · {{ $lisensi->tanggal_mulai->translatedFormat('d M Y') }} s/d {{ $lisensi->tanggal_berakhir->translatedFormat('d M Y') }}
                                    @endif
                                @endif
                            </div>
                        </td>
                        <td style="text-align:center;color:#64748b;">1</td>
                        <td style="text-align:right;">Rp {{ number_format($lisensi->harga, 0, ',', '.') }}</td>
                        <td style="text-align:right;">Rp {{ number_format($lisensi->harga, 0, ',', '.') }}</td>
                    </tr>
                </tbody>
            </table>

            {{-- Totals --}}
            <div class="totals-block">
                <div class="totals-inner">
                    <div class="total-row">
                        <span class="total-label">Subtotal</span>
                        <span class="total-amount">Rp {{ number_format($lisensi->harga, 0, ',', '.') }}</span>
                    </div>
                    <div class="total-row">
                        <span class="total-label">Diskon</span>
                        <span class="total-amount">Rp 0</span>
                    </div>
                    <div class="total-row main">
                        <span class="total-label">TOTAL</span>
                        <span class="total-amount">Rp {{ number_format($lisensi->harga, 0, ',', '.') }}</span>
                    </div>
                </div>
            </div>

            {{-- Validity Period --}}
            @php
                $tanggalAkhir = $lisensi->tanggal_akhir;
            @endphp
            @if($tanggalAkhir)
            <div class="validity-block">
                <div>
                    <div class="validity-label">📅 Mulai Berlaku</div>
                    <div class="validity-date">
                        {{ $lisensi->tipe === 'beli'
                            ? ($lisensi->tanggal_beli ? $lisensi->tanggal_beli->translatedFormat('d F Y') : '-')
                            : ($lisensi->tanggal_mulai ? $lisensi->tanggal_mulai->translatedFormat('d F Y') : '-') }}
                    </div>
                </div>
                <div style="font-size:22px;color:#bfdbfe;">→</div>
                <div style="text-align:right;">
                    <div class="validity-label">📅 Berlaku Hingga</div>
                    <div class="validity-date">{{ $tanggalAkhir->translatedFormat('d F Y') }}</div>
                </div>
            </div>
            @endif

            {{-- Payment Info --}}
            @if($lisensi->status_pembayaran === 'lunas')
            <div class="payment-block">
                <div class="payment-icon lunas">✅</div>
                <div class="payment-details">
                    <div class="payment-title">Pembayaran Lunas</div>
                    <div class="payment-meta">
                        Diterima pada {{ $lisensi->tanggal_pembayaran ? $lisensi->tanggal_pembayaran->translatedFormat('d F Y') : '-' }}
                        · Metode: <strong>{{ strtoupper($lisensi->metode_pembayaran ?? '-') }}</strong>
                        @if($lisensi->catatan_pembayaran)
                            <br>Catatan: {{ $lisensi->catatan_pembayaran }}
                        @endif
                    </div>
                </div>
                <div style="text-align:right;">
                    <div style="font-size:10px;color:#15803d;font-weight:700;text-transform:uppercase;letter-spacing:1px;">Jumlah Diterima</div>
                    <div style="font-size:20px;font-weight:900;color:#15803d;">Rp {{ number_format($lisensi->harga, 0, ',', '.') }}</div>
                </div>
            </div>
            @else
            <div class="payment-block unpaid">
                <div class="payment-icon unpaid">⏳</div>
                <div class="payment-details">
                    <div class="payment-title">Menunggu Pembayaran</div>
                    <div class="payment-meta">
                        Silakan lakukan pembayaran sebesar <strong>Rp {{ number_format($lisensi->harga, 0, ',', '.') }}</strong>
                        melalui metode yang telah disepakati.
                    </div>
                </div>
            </div>
            @endif

            {{-- Keterangan --}}
            @if($lisensi->keterangan)
            <div class="notes-box">
                <strong>Catatan:</strong> {{ $lisensi->keterangan }}
            </div>
            @endif

            {{-- Footer --}}
            <div class="footer">
                <div class="footer-left">
                    Dicetak pada {{ now()->translatedFormat('d F Y, H:i') }} WIB<br>
                    Dokumen ini sah tanpa tanda tangan basah.<br>
                    <span style="color:#3b82f6;font-weight:600;">{{ $lisensi->nomor_lisensi }}</span>
                </div>
                <div class="footer-right">
                    <div class="sign-name">{{ $pengaturan['nama_aplikasi'] ?? 'TEFa' }}</div>
                    <div class="sign-title">Penerbit Lisensi</div>
                </div>
            </div>

        </div>{{-- end body --}}
    </div>{{-- end paper --}}

    <script>
        // Auto-print if URL has ?print=1
        if (new URLSearchParams(window.location.search).get('print') === '1') {
            window.onload = () => setTimeout(() => window.print(), 400);
        }
    </script>

</body>
</html>
