<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DokumentasiAlat extends Model
{
    use HasFactory;

    protected $table = 'dokumentasi_alat';

    protected $fillable = [
        'alat_id',
        'jenis',
        'judul',
        'deskripsi',
        'file_path',
        'file_nama_asli',
        'file_ukuran',
        'file_tipe',
        'thumbnail',
        'urutan',
        'uploaded_by',
    ];

    protected $casts = [
        'file_ukuran' => 'integer',
        'urutan' => 'integer',
    ];

    public function getFileUrlAttribute()
    {
        return asset('storage/' . $this->file_path);
    }

    public function getThumbnailUrlAttribute()
    {
        if ($this->thumbnail) {
            return asset('storage/' . $this->thumbnail);
        }
        return $this->file_url;
    }

    public function alat()
    {
        return $this->belongsTo(Alat::class, 'alat_id');
    }

    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}
