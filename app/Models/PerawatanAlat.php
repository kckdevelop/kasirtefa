<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PerawatanAlat extends Model
{
    use HasFactory;

    protected $table = 'perawatan_alat';

    protected $fillable = [
        'alat_id',
        'kode_perawatan',
        'jenis',
        'tanggal_mulai',
        'tanggal_selesai',
        'biaya',
        'pelaksana',
        'deskripsi_pekerjaan',
        'hasil',
        'status',
        'bukti_foto',
        'created_by',
    ];

    protected $casts = [
        'tanggal_mulai' => 'date',
        'tanggal_selesai' => 'date',
        'biaya' => 'decimal:2',
    ];

    public function alat()
    {
        return $this->belongsTo(Alat::class, 'alat_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
