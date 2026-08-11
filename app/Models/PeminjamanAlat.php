<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PeminjamanAlat extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'peminjaman_alat';

    protected $fillable = [
        'kode_peminjaman',
        'pelanggan_id',
        'peminjam_id',
        'tanggal_pinjam',
        'tanggal_kembali_rencana',
        'tanggal_kembali_aktual',
        'keperluan',
        'tujuan_penggunaan',
        'lokasi_penggunaan',
        'status',
        'catatan_peminjam',
        'catatan_admin',
        'approved_by',
        'approved_at',
        'rejected_by',
        'rejected_at',
        'rejection_reason',
        'diterima_oleh',
        'dikembalikan_oleh',
        'diterima_pengembalian_oleh',
    ];

    protected $casts = [
        'tanggal_pinjam' => 'date',
        'tanggal_kembali_rencana' => 'date',
        'tanggal_kembali_aktual' => 'date',
        'approved_at' => 'datetime',
        'rejected_at' => 'datetime',
    ];

    public function pelanggan()
    {
        return $this->belongsTo(Pelanggan::class, 'pelanggan_id');
    }

    public function userPeminjam()
    {
        return $this->belongsTo(User::class, 'peminjam_id');
    }

    public function getPeminjamAttribute()
    {
        if ($this->relationLoaded('pelanggan') && $this->pelanggan) {
            return $this->pelanggan;
        }
        if ($this->relationLoaded('userPeminjam') && $this->userPeminjam) {
            return $this->userPeminjam;
        }
        return $this->pelanggan ?? $this->userPeminjam;
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function rejecter()
    {
        return $this->belongsTo(User::class, 'rejected_by');
    }

    public function items()
    {
        return $this->hasMany(DetailPeminjaman::class, 'peminjaman_alat_id');
    }

    public function denda()
    {
        return $this->hasMany(DendaPeminjaman::class, 'peminjaman_alat_id');
    }
}
