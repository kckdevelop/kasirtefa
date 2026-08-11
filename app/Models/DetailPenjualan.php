<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DetailPenjualan extends Model
{
    use HasFactory;

    protected $table = 'detail_penjualan';

    protected $fillable = [
        'transaksi_penjualan_id',
        'produk_id',
        'harga_satuan',
        'jumlah',
        'subtotal',
        'catatan',
    ];

    protected $casts = [
        'harga_satuan' => 'decimal:2',
        'jumlah' => 'integer',
        'subtotal' => 'decimal:2',
    ];

    public function transaksi()
    {
        return $this->belongsTo(TransaksiPenjualan::class, 'transaksi_penjualan_id');
    }

    public function produk()
    {
        return $this->belongsTo(Produk::class, 'produk_id');
    }
}
