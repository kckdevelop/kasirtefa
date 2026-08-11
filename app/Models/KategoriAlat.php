<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class KategoriAlat extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'kategori_alat';

    protected $fillable = [
        'nama',
        'slug',
        'deskripsi',
        'ikon',
        'urutan',
        'status',
    ];

    public function scopeAktif($query)
    {
        return $query->where('status', 'aktif');
    }

    public function alat()
    {
        return $this->hasMany(Alat::class, 'kategori_alat_id');
    }
}
