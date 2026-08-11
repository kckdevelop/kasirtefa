<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Struk - {{ $transaksi->kode_transaksi }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            font-size: 12px;
            line-height: 1.4;
            color: #0f172a;
            background-color: #f1f5f9;
            padding: 24px 16px;
            display: flex;
            flex-direction: column;
            align-items: center;
            min-height: 100vh;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        /* ── Action Bar (Screen Only) ── */
        .no-print {
            margin-bottom: 16px;
            display: flex;
            gap: 10px;
            width: 100%;
            max-width: 300px;
        }

        .btn-print {
            background-color: #059669;
            color: #ffffff;
            border: none;
            padding: 9px 16px;
            border-radius: 6px;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 6px;
            flex: 1;
            justify-content: center;
            transition: background-color 0.15s ease;
        }

        .btn-print:hover { background-color: #047857; }

        .btn-close {
            background-color: #64748b;
            color: #ffffff;
            border: none;
            padding: 9px 14px;
            border-radius: 6px;
            font-size: 13px;
            font-weight: 500;
            cursor: pointer;
            transition: background-color 0.15s ease;
        }

        .btn-close:hover { background-color: #475569; }

        /* ── Thermal Paper Container ── */
        .receipt-container {
            width: 100%;
            max-width: 300px;
            background: #ffffff;
            padding: 16px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
            border-radius: 8px;
            border: 1px solid #e2e8f0;
            word-break: normal;
            overflow-wrap: break-word;
        }

        .text-center { text-align: center; }
        .text-right  { text-align: right; }
        .bold        { font-weight: 700; }

        /* ── Header ── */
        .header-title {
            font-size: 13px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            color: #0f172a;
            margin-bottom: 2px;
            line-height: 1.3;
        }

        .header-sub {
            font-size: 11.5px;
            font-weight: 600;
            color: #1e293b;
            margin-bottom: 2px;
        }

        .header-school {
            font-size: 11px;
            color: #334155;
            margin-bottom: 3px;
        }

        .header-info {
            font-size: 10px;
            color: #475569;
            line-height: 1.35;
        }

        /* ── Dividers ── */
        .divider {
            border-top: 1px dashed #cbd5e1;
            margin: 8px 0;
        }

        .divider-double {
            border-top: 2px solid #0f172a;
            margin: 10px 0;
        }

        /* ── Info Rows ── */
        .row {
            display: flex;
            justify-content: space-between;
            align-items: baseline;
            gap: 8px;
            margin: 3.5px 0;
            font-size: 11.5px;
        }

        .row-label {
            color: #475569;
            white-space: nowrap;
            flex-shrink: 0;
        }

        .row-val {
            color: #0f172a;
            text-align: right;
            font-variant-numeric: tabular-nums;
            word-break: normal;
            overflow-wrap: break-word;
        }

        .row-total {
            font-size: 13.5px;
            font-weight: 800;
            color: #0f172a;
            margin: 6px 0;
        }

        /* ── Items ── */
        .item-group {
            margin: 6px 0;
        }

        .item-name {
            font-weight: 600;
            font-size: 11.5px;
            color: #0f172a;
            line-height: 1.3;
            margin-bottom: 2px;
        }

        .item-calc {
            display: flex;
            justify-content: space-between;
            align-items: baseline;
            font-size: 11px;
            color: #334155;
            font-variant-numeric: tabular-nums;
        }

        /* ── Footer ── */
        .footer {
            margin-top: 12px;
            text-align: center;
        }

        .footer-thanks {
            font-size: 11px;
            font-weight: 700;
            color: #0f172a;
            margin-bottom: 3px;
        }

        .footer-contact {
            font-size: 10px;
            color: #475569;
            line-height: 1.4;
        }

        .footer-note {
            font-size: 9.5px;
            color: #64748b;
            font-style: italic;
            margin-top: 6px;
            border-top: 1px dashed #cbd5e1;
            padding-top: 6px;
        }

        .barcode {
            margin-top: 10px;
            font-family: 'Courier New', Courier, monospace;
            font-size: 11px;
            letter-spacing: 1.5px;
            font-weight: 700;
            color: #0f172a;
        }

        /* ── Print Styles ── */
        @media print {
            body {
                background: #ffffff !important;
                padding: 0 !important;
                margin: 0 !important;
                display: block;
            }

            .no-print {
                display: none !important;
            }

            .receipt-container {
                width: 100% !important;
                max-width: 100% !important;
                box-shadow: none !important;
                border: none !important;
                border-radius: 0 !important;
                padding: 2mm 2mm !important;
            }

            @page {
                size: 80mm auto;
                margin: 0;
            }
        }
    </style>
</head>
<body>

    <!-- Action Buttons -->
    <div class="no-print">
        <button class="btn-print" onclick="window.print()">
            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M6 9V2h12v7M6 18H4a2 2 0 01-2-2v-5a2 2 0 012-2h16a2 2 0 012 2v5a2 2 0 01-2 2h-2m-4 0h-8v4h8v-4z"></path></svg>
            Cetak Struk
        </button>
        <button class="btn-close" onclick="window.close()">Tutup</button>
    </div>

    <div class="receipt-container">
        <!-- HEADER STRUK -->
        <div class="text-center">
            <div class="header-title">{{ $pengaturan['nama_aplikasi'] ?? 'Sistem Inventaris & Kasir TEFa' }}</div>
            <div class="header-sub">{{ $pengaturan['nama_tefa'] ?? 'Teaching Factory (TEFa)' }}</div>
            <div class="header-school">{{ $pengaturan['nama_sekolah'] ?? 'SMK Muhammadiyah 1 Bantul' }}</div>
            @if(!empty($pengaturan['alamat_instansi']) || !empty($pengaturan['alamat']))
                <div class="header-info">{{ $pengaturan['alamat_instansi'] ?? $pengaturan['alamat'] }}</div>
            @endif
            @if(!empty($pengaturan['telepon']))
                <div class="header-info">Telp: {{ $pengaturan['telepon'] }}</div>
            @endif
        </div>

        <div class="divider-double"></div>

        <!-- TRANSAKSI INFO -->
        <div class="row">
            <span class="row-label">No. Struk</span>
            <span class="row-val bold">{{ $transaksi->kode_transaksi }}</span>
        </div>
        <div class="row">
            <span class="row-label">Tanggal</span>
            <span class="row-val">{{ \Carbon\Carbon::parse($transaksi->tanggal ?? $transaksi->created_at)->format('d/m/Y H:i') }}</span>
        </div>
        <div class="row">
            <span class="row-label">Kasir</span>
            <span class="row-val">{{ $transaksi->kasir?->nama ?? $transaksi->kasir?->name ?? 'Admin' }}</span>
        </div>
        @if(!empty($transaksi->customer_nama))
        <div class="row">
            <span class="row-label">Pelanggan</span>
            <span class="row-val">{{ $transaksi->customer_nama }}</span>
        </div>
        @endif
        <div class="row">
            <span class="row-label">Metode Bayar</span>
            <span class="row-val bold">{{ strtoupper($transaksi->metode_pembayaran) }}</span>
        </div>

        <div class="divider"></div>

        <!-- DAFTAR ITEM BARANG -->
        @foreach($transaksi->items as $item)
        <div class="item-group">
            <div class="item-name">{{ $item->nama_produk ?? $item->produk?->nama ?? 'Produk' }}</div>
            <div class="item-calc">
                <span>{{ $item->jumlah }} x {{ number_format($item->harga_satuan, 0, ',', '.') }}</span>
                <span class="bold">{{ number_format($item->subtotal ?? ($item->jumlah * $item->harga_satuan), 0, ',', '.') }}</span>
            </div>
        </div>
        @endforeach

        <div class="divider"></div>

        <!-- TOTAL & PERHITUNGAN -->
        <div class="row">
            <span class="row-label">Subtotal</span>
            <span class="row-val">Rp {{ number_format($transaksi->subtotal ?? $transaksi->total_bayar ?? $transaksi->total_akhir, 0, ',', '.') }}</span>
        </div>

        @if(($transaksi->diskon_nominal ?? $transaksi->diskon ?? 0) > 0)
        <div class="row">
            <span class="row-label">Diskon {{ !empty($transaksi->diskon_persen) ? '('.$transaksi->diskon_persen.'%)' : '' }}</span>
            <span class="row-val">- Rp {{ number_format($transaksi->diskon_nominal ?? $transaksi->diskon, 0, ',', '.') }}</span>
        </div>
        @endif

        <div class="divider"></div>

        <div class="row row-total">
            <span>TOTAL</span>
            <span class="row-val">Rp {{ number_format($transaksi->total_akhir ?? $transaksi->total_bayar, 0, ',', '.') }}</span>
        </div>

        <div class="row">
            <span class="row-label">Bayar</span>
            <span class="row-val">Rp {{ number_format($transaksi->nominal_bayar ?? $transaksi->jumlah_dibayar ?? $transaksi->total_akhir, 0, ',', '.') }}</span>
        </div>

        @if(($transaksi->kembalian ?? 0) > 0)
        <div class="row bold">
            <span class="row-label" style="color: #0f172a;">Kembalian</span>
            <span class="row-val">Rp {{ number_format($transaksi->kembalian, 0, ',', '.') }}</span>
        </div>
        @endif

        <div class="divider-double"></div>

        <!-- FOOTER STRUK -->
        <div class="footer">
            <div class="footer-thanks">
                {{ $pengaturan['ucapan_terima_kasih'] ?? 'Terima Kasih Atas Kunjungan Anda!' }}
            </div>

            @if(!empty($pengaturan['telepon']) || !empty($pengaturan['email']))
            <div class="footer-contact">
                @if(!empty($pengaturan['telepon']))
                    Telp/WA: {{ $pengaturan['telepon'] }}<br>
                @endif
                @if(!empty($pengaturan['email']))
                    {{ $pengaturan['email'] }}
                @endif
            </div>
            @endif

            <div class="footer-note">
                Simpan struk ini untuk klaim garansi.
            </div>

            <div class="barcode">
                *{{ $transaksi->kode_transaksi }}*
            </div>
        </div>
    </div>

    <script>
        window.onload = function() {
            setTimeout(function() {
                window.print();
            }, 300);
        };
    </script>
</body>
</html>

