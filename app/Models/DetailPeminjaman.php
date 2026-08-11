<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DetailPeminjaman extends Model
{
    use HasFactory;

    protected $table = 'detail_peminjaman';

    protected $fillable = [
        'peminjaman_alat_id',
        'alat_id',
        'jumlah_pinjam',
        'jumlah_dikembalikan',
        'kondisi_saat_dipinjam',
        'kondisi_saat_dikembalikan',
        'catatan_kerusakan',
        'foto_pengembalian',
        'status_item',
    ];

    protected $casts = [
        'jumlah_pinjam' => 'integer',
        'jumlah_dikembalikan' => 'integer',
    ];

    public function getFotoPengembalianUrlAttribute()
    {
        if ($this->foto_pengembalian) {
            return asset('storage/' . $this->foto_pengembalian);
        }
        return null;
    }

    public function peminjaman()
    {
        return $this->belongsTo(PeminjamanAlat::class, 'peminjaman_alat_id');
    }

    public function alat()
    {
        return $this->belongsTo(Alat::class, 'alat_id');
    }

    public function denda()
    {
        return $this->hasMany(DendaPeminjaman::class, 'detail_peminjaman_id');
    }
}
