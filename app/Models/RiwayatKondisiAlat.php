<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RiwayatKondisiAlat extends Model
{
    use HasFactory;

    protected $table = 'riwayat_kondisi_alat';

    protected $fillable = [
        'alat_id',
        'kondisi_sebelum',
        'kondisi_sesudah',
        'tanggal_perubahan',
        'keterangan',
        'bukti_foto',
        'dilakukan_oleh',
    ];

    protected $casts = [
        'tanggal_perubahan' => 'date',
    ];

    public function alat()
    {
        return $this->belongsTo(Alat::class, 'alat_id');
    }

    public function actor()
    {
        return $this->belongsTo(User::class, 'dilakukan_oleh');
    }
}
