<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Produk extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'produk';

    protected $fillable = [
        'kategori_produk_id',
        'kode_produk',
        'nama',
        'slug',
        'deskripsi',
        'foto',
        'harga_jual',
        'harga_modal',
        'satuan',
        'stok',
        'stok_minimum',
        'berat',
        'is_ready',
        'spesifikasi',
        'catatan',
        'status',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'harga_jual' => 'decimal:2',
        'harga_modal' => 'decimal:2',
        'berat' => 'decimal:2',
        'is_ready' => 'boolean',
        'spesifikasi' => 'array',
        'stok' => 'integer',
        'stok_minimum' => 'integer',
    ];

    public function getFotoUrlAttribute()
    {
        if ($this->foto) {
            return asset('storage/' . $this->foto);
        }
        return 'https://placehold.co/400x400/e2e8f0/1e293b?text=' . urlencode($this->nama);
    }

    public function scopeAktif($query)
    {
        return $query->where('status', 'aktif');
    }

    public function kategori()
    {
        return $this->belongsTo(KategoriProduk::class, 'kategori_produk_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function stokMasuk()
    {
        return $this->hasMany(StokMasuk::class, 'produk_id');
    }

    public function stokKeluar()
    {
        return $this->hasMany(StokKeluar::class, 'produk_id');
    }

    public function detailPenjualan()
    {
        return $this->hasMany(DetailPenjualan::class, 'produk_id');
    }
}
