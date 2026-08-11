<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DendaPeminjaman extends Model
{
    use HasFactory;

    protected $table = 'denda_peminjaman';

    protected $fillable = [
        'detail_peminjaman_id',
        'peminjaman_alat_id',
        'jenis',
        'jumlah_hari_terlambat',
        'tarif_per_hari',
        'estimasi_kerugian',
        'total_denda',
        'status',
        'metode_pembayaran',
        'bukti_bayar',
        'tanggal_bayar',
        'catatan',
    ];

    protected $casts = [
        'jumlah_hari_terlambat' => 'integer',
        'tarif_per_hari' => 'decimal:2',
        'estimasi_kerugian' => 'decimal:2',
        'total_denda' => 'decimal:2',
        'tanggal_bayar' => 'date',
    ];

    public function getBuktiBayarUrlAttribute()
    {
        if ($this->bukti_bayar) {
            return asset('storage/' . $this->bukti_bayar);
        }
        return null;
    }

    public function peminjaman()
    {
        return $this->belongsTo(PeminjamanAlat::class, 'peminjaman_alat_id');
    }

    public function detailPeminjaman()
    {
        return $this->belongsTo(DetailPeminjaman::class, 'detail_peminjaman_id');
    }
}
