<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DetailSewaGedung extends Model
{
    use HasFactory;

    protected $table = 'detail_sewa_gedung';

    protected $fillable = [
        'sewa_gedung_id',
        'fasilitas_gedung_id',
        'nama_fasilitas',
        'jumlah',
        'harga_per_item',
        'subtotal',
    ];

    protected $casts = [
        'jumlah' => 'integer',
        'harga_per_item' => 'decimal:2',
        'subtotal' => 'decimal:2',
    ];

    public function sewa()
    {
        return $this->belongsTo(SewaGedung::class, 'sewa_gedung_id');
    }

    public function fasilitas()
    {
        return $this->belongsTo(FasilitasGedung::class, 'fasilitas_gedung_id');
    }
}
