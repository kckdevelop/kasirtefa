<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TransaksiPenjualan extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'transaksi_penjualan';

    protected $fillable = [
        'kode_transaksi',
        'tanggal',
        'waktu',
        'user_id',
        'customer_nama',
        'customer_telepon',
        'customer_alamat',
        'subtotal',
        'diskon_persen',
        'diskon_nominal',
        'total_akhir',
        'metode_pembayaran',
        'nominal_bayar',
        'nominal_kembalian',
        'no_referensi',
        'status',
        'catatan',
    ];

    protected $casts = [
        'tanggal' => 'date',
        'subtotal' => 'decimal:2',
        'diskon_persen' => 'decimal:2',
        'diskon_nominal' => 'decimal:2',
        'total_akhir' => 'decimal:2',
        'nominal_bayar' => 'decimal:2',
        'nominal_kembalian' => 'decimal:2',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function kasir()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function items()
    {
        return $this->hasMany(DetailPenjualan::class, 'transaksi_penjualan_id');
    }
}
