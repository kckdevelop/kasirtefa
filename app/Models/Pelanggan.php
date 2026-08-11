<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Pelanggan extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'pelanggan';

    protected $fillable = [
        'kode_pelanggan',
        'nama',
        'tipe',
        'telepon',
        'email',
        'alamat',
        'status',
        'catatan',
    ];

    public function scopeAktif($query)
    {
        return $query->where('status', 'aktif');
    }
}
