<?php

namespace App\Exports;

use App\Models\PeminjamanAlat;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class PeminjamanExport implements FromCollection, WithHeadings, WithMapping, WithStyles
{
    protected $filters;

    public function __construct(array $filters = [])
    {
        $this->filters = $filters;
    }

    public function collection()
    {
        $query = PeminjamanAlat::with(['peminjam', 'items.alat']);

        if (!empty($this->filters['tanggal_mulai'])) {
            $query->whereDate('tanggal_pinjam', '>=', $this->filters['tanggal_mulai']);
        }
        if (!empty($this->filters['tanggal_selesai'])) {
            $query->whereDate('tanggal_pinjam', '<=', $this->filters['tanggal_selesai']);
        }

        return $query->orderBy('tanggal_pinjam', 'desc')->get();
    }

    public function headings(): array
    {
        return [
            'Kode Peminjaman',
            'Nama Peminjam',
            'Kelas/Jurusan',
            'Tanggal Pinjam',
            'Rencana Kembali',
            'Aktual Kembali',
            'Keperluan',
            'Status',
        ];
    }

    public function map($row): array
    {
        return [
            $row->kode_peminjaman,
            $row->peminjam?->nama ?? '-',
            ($row->peminjam?->kelas ?? '') . ' ' . ($row->peminjam?->jurusan ?? ''),
            formatTanggal($row->tanggal_pinjam),
            formatTanggal($row->tanggal_kembali_rencana),
            formatTanggal($row->tanggal_kembali_aktual),
            $row->keperluan,
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
