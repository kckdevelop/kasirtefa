<?php

use Carbon\Carbon;

if (!function_exists('formatRupiah')) {
    function formatRupiah($angka, $withPrefix = true)
    {
        $val = number_format((float)($angka ?? 0), 0, ',', '.');
        return $withPrefix ? 'Rp ' . $val : $val;
    }
}

if (!function_exists('getBulanIndonesia')) {
    function getBulanIndonesia($bulan)
    {
        $bulanList = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
        ];
        return $bulanList[(int)$bulan] ?? '';
    }
}

if (!function_exists('getHariIndonesia')) {
    function getHariIndonesia($hari)
    {
        $hariList = [
            'Sunday' => 'Minggu', 'Monday' => 'Senin', 'Tuesday' => 'Selasa',
            'Wednesday' => 'Rabu', 'Thursday' => 'Kamis', 'Friday' => 'Jumat',
            'Saturday' => 'Sabtu'
        ];
        return $hariList[$hari] ?? $hari;
    }
}

if (!function_exists('formatTanggal')) {
    function formatTanggal($date, $format = 'd/m/Y')
    {
        if (!$date) return '-';
        $c = Carbon::parse($date);
        if ($format === 'indonesia' || $format === 'indo') {
            return $c->format('d') . ' ' . getBulanIndonesia($c->format('n')) . ' ' . $c->format('Y');
        }
        return $c->format($format);
    }
}

if (!function_exists('formatTanggalWaktu')) {
    function formatTanggalWaktu($datetime)
    {
        if (!$datetime) return '-';
        $c = Carbon::parse($datetime);
        return $c->format('d') . ' ' . getBulanIndonesia($c->format('n')) . ' ' . $c->format('Y') . ', ' . $c->format('H:i');
    }
}

if (!function_exists('generateKode')) {
    function generateKode($prefix, $date = null)
    {
        $dateStr = $date ? Carbon::parse($date)->format('Ymd') : Carbon::now()->format('Ymd');
        $random = strtoupper(substr(uniqid(), -4));
        return "{$prefix}-{$dateStr}-{$random}";
    }
}

if (!function_exists('getStatusColor')) {
    function getStatusColor($status)
    {
        return match (strtolower($status)) {
            'aktif', 'lunas', 'disetujui', 'dikembalikan', 'selesai', 'sudah_bayar', 'sukses' => 'bg-emerald-100 text-emerald-800 border-emerald-300',
            'pending', 'menunggu_persetujuan', 'direncanakan', 'berlangsung', 'belum_bayar', 'info' => 'bg-amber-100 text-amber-800 border-amber-300',
            'dipinjam', 'dikembalikan_sebagian', 'peringatan' => 'bg-blue-100 text-blue-800 border-blue-300',
            'nonaktif', 'batal', 'ditolak', 'terlambat', 'rusak', 'hilang', 'kesalahan' => 'bg-rose-100 text-rose-800 border-rose-300',
            'dibebaskan' => 'bg-purple-100 text-purple-800 border-purple-300',
            default => 'bg-slate-100 text-slate-800 border-slate-300',
        };
    }
}

if (!function_exists('getKondisiColor')) {
    function getKondisiColor($kondisi)
    {
        return match (strtolower($kondisi)) {
            'baik' => 'bg-emerald-100 text-emerald-800 border-emerald-300',
            'cukup' => 'bg-blue-100 text-blue-800 border-blue-300',
            'rusak_ringan' => 'bg-amber-100 text-amber-800 border-amber-300',
            'rusak_berat' => 'bg-rose-100 text-rose-800 border-rose-300',
            default => 'bg-slate-100 text-slate-800 border-slate-300',
        };
    }
}

if (!function_exists('hitungSelisihHari')) {
    function hitungSelisihHari($startDate, $endDate)
    {
        if (!$startDate || !$endDate) return 0;
        $start = Carbon::parse($startDate)->startOfDay();
        $end = Carbon::parse($endDate)->startOfDay();
        return (int) $start->diffInDays($end, false);
    }
}

if (!function_exists('isWeekend')) {
    function isWeekend($date)
    {
        if (!$date) return false;
        return Carbon::parse($date)->isWeekend();
    }
}
