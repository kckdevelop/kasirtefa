<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SewaGedung extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'sewa_gedung';

    protected $fillable = [
        'kode_sewa',
        'gedung_lab_id',
        'user_id',
        'pelanggan_id',
        'nama_penyewa',
        'telepon_penyewa',
        'instansi_penyewa',
        'tanggal_mulai',
        'tanggal_selesai',
        'lama_sewa',
        'harga_sewa_gedung',
        'subtotal_gedung',
        'subtotal_fasilitas',
        'total_biaya',
        'jumlah_dibayar',
        'status_pembayaran',
        'status_sewa',
        'catatan',
    ];

    protected $casts = [
        'tanggal_mulai' => 'date',
        'tanggal_selesai' => 'date',
        'lama_sewa' => 'integer',
        'harga_sewa_gedung' => 'decimal:2',
        'subtotal_gedung' => 'decimal:2',
        'subtotal_fasilitas' => 'decimal:2',
        'total_biaya' => 'decimal:2',
        'jumlah_dibayar' => 'decimal:2',
    ];

    public function gedung()
    {
        return $this->belongsTo(GedungLab::class, 'gedung_lab_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function pelanggan()
    {
        return $this->belongsTo(Pelanggan::class, 'pelanggan_id');
    }

    public function details()
    {
        return $this->hasMany(DetailSewaGedung::class, 'sewa_gedung_id');
    }
}
