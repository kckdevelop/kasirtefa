<?php

namespace App\Exports;

use App\Models\TransaksiPenjualan;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class PenjualanExport implements FromCollection, WithHeadings, WithMapping, WithStyles
{
    protected $filters;

    public function __construct(array $filters = [])
    {
        $this->filters = $filters;
    }

    public function collection()
    {
        $query = TransaksiPenjualan::with(['items.produk', 'kasir'])
            ->where('status', 'lunas');

        if (!empty($this->filters['tanggal_mulai'])) {
            $query->whereDate('tanggal', '>=', $this->filters['tanggal_mulai']);
        }
        if (!empty($this->filters['tanggal_selesai'])) {
            $query->whereDate('tanggal', '<=', $this->filters['tanggal_selesai']);
        }

        return $query->orderBy('tanggal', 'desc')->get();
    }

    public function headings(): array
    {
        return [
            'Kode Transaksi',
            'Tanggal',
            'Waktu',
            'Kasir',
            'Customer',
            'Metode Pembayaran',
            'Subtotal',
            'Diskon',
            'Total Akhir',
            'Status',
        ];
    }

    public function map($row): array
    {
        return [
            $row->kode_transaksi,
            formatTanggal($row->tanggal),
            $row->waktu,
            $row->kasir?->nama ?? '-',
            $row->customer_nama ?? 'Umum',
            strtoupper($row->metode_pembayaran),
            $row->subtotal,
            $row->diskon_nominal,
            $row->total_akhir,
            strtoupper($row->status),
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
