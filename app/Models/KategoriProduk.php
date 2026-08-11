<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class KategoriProduk extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'kategori_produk';

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

    public function produk()
    {
        return $this->hasMany(Produk::class, 'kategori_produk_id');
    }
}
