<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class GedungLab extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'gedung_lab';

    protected $fillable = [
        'kode_gedung',
        'nama_gedung',
        'lokasi',
        'kapasitas',
        'harga_sewa_per_hari',
        'deskripsi',
        'foto',
        'status',
    ];

    protected $casts = [
        'kapasitas' => 'integer',
        'harga_sewa_per_hari' => 'decimal:2',
    ];

    public function meFasilitas()
    {
        return $this->hasMany(FasilitasGedung::class, 'gedung_lab_id');
    }

    public function fasilitas()
    {
        return $this->hasMany(FasilitasGedung::class, 'gedung_lab_id');
    }

    public function sewa()
    {
        return $this->hasMany(SewaGedung::class, 'gedung_lab_id');
    }

    public function scopeTersedia($query)
    {
        return $query->where('status', 'tersedia');
    }
}
