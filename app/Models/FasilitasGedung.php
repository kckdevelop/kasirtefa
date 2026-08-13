<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FasilitasGedung extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'fasilitas_gedung';

    protected $fillable = [
        'gedung_lab_id',
        'nama_fasilitas',
        'kode_fasilitas',
        'jumlah_tersedia',
        'harga_per_item',
        'satuan',
        'keterangan',
        'status',
    ];

    protected $casts = [
        'jumlah_tersedia' => 'integer',
        'harga_per_item' => 'decimal:2',
    ];

    public function gedung()
    {
        return $this->belongsTo(GedungLab::class, 'gedung_lab_id');
    }

    public function scopeBaik($query)
    {
        return $query->where('status', 'baik');
    }
}
