<?php

namespace App\Exports;

use App\Models\Alat;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class InventarisExport implements FromCollection, WithHeadings, WithMapping, WithStyles
{
    protected $filters;

    public function __construct(array $filters = [])
    {
        $this->filters = $filters;
    }

    public function collection()
    {
        $query = Alat::with('kategori');
        if (!empty($this->filters['kategori_id'])) {
            $query->where('kategori_alat_id', $this->filters['kategori_id']);
        }

        return $query->orderBy('nama')->get();
    }

    public function headings(): array
    {
        return [
            'Kode Alat',
            'Nama Alat',
            'Kategori',
            'Merek/Tipe',
            'Lokasi Penyimpanan',
            'Jumlah Total',
            'Jumlah Tersedia',
            'Jumlah Baik',
            'Jumlah Rusak',
            'Jumlah Hilang',
            'Status Ketersediaan',
            'Harga Perolehan (Rp)',
        ];
    }

    public function map($row): array
    {
        $jBaik = ($row->jumlah_baik ?? 0) + ($row->jumlah_cukup ?? 0);
        if ($jBaik == 0 && ($row->jumlah_total ?? 0) > 0 && ($row->jumlah_rusak_ringan ?? 0) == 0 && ($row->jumlah_rusak_berat ?? 0) == 0 && ($row->jumlah_hilang ?? 0) == 0) {
            $jBaik = $row->jumlah_total ?? 1;
        }
        $jRusak = ($row->jumlah_rusak_ringan ?? 0) + ($row->jumlah_rusak_berat ?? 0);
        $jHilang = $row->jumlah_hilang ?? 0;

        return [
            $row->kode_alat,
            $row->nama,
            $row->kategori?->nama ?? '-',
            trim(($row->merek ?? '') . ' / ' . ($row->tipe ?? ''), ' /'),
            $row->lokasi_penyimpanan ?? '-',
            $row->jumlah_total,
            $row->jumlah_tersedia,
            $jBaik,
            $jRusak,
            $jHilang,
            strtoupper(str_replace('_', ' ', $row->status_ketersediaan ?? 'tersedia')),
            $row->harga_perolehan ?? 0,
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
