<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Alat extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'alat';

    protected $fillable = [
        'kategori_alat_id',
        'kode_alat',
        'nama',
        'slug',
        'merek',
        'tipe',
        'serial_number',
        'tahun_perolehan',
        'kondisi',
        'status_ketersediaan',
        'lokasi_penyimpanan',
        'jumlah_total',
        'jumlah_tersedia',
        'jumlah_baik',
        'jumlah_cukup',
        'jumlah_rusak_ringan',
        'jumlah_rusak_berat',
        'jumlah_hilang',
        'satuan',
        'harga_perolehan',
        'sumber_perolehan',
        'foto',
        'spesifikasi_teknis',
        'cara_penggunaan',
        'peringatan_keamanan',
        'umur_pakai',
        'kalibrasi_terakhir',
        'kalibrasi_berikutnya',
        'catatan',
        'status',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'spesifikasi_teknis'  => 'array',
        'harga_perolehan'     => 'decimal:2',
        'jumlah_total'        => 'integer',
        'jumlah_tersedia'     => 'integer',
        'jumlah_baik'         => 'integer',
        'jumlah_cukup'        => 'integer',
        'jumlah_rusak_ringan' => 'integer',
        'jumlah_rusak_berat'  => 'integer',
        'jumlah_hilang'       => 'integer',
        'umur_pakai'          => 'integer',
        'kalibrasi_terakhir'  => 'date',
        'kalibrasi_berikutnya'=> 'date',
    ];

    public function getFotoUrlAttribute()
    {
        if ($this->foto) {
            return asset('storage/' . $this->foto);
        }
        return 'https://placehold.co/400x400/e2e8f0/1e293b?text=' . urlencode($this->nama);
    }

    public function kategori()
    {
        return $this->belongsTo(KategoriAlat::class, 'kategori_alat_id');
    }

    public function dokumentasi()
    {
        return $this->hasMany(DokumentasiAlat::class, 'alat_id')->orderBy('urutan');
    }

    public function riwayatKondisi()
    {
        return $this->hasMany(RiwayatKondisiAlat::class, 'alat_id')->latest();
    }

    public function riwayatPerawatan()
    {
        return $this->hasMany(PerawatanAlat::class, 'alat_id')->latest();
    }

    public function detailPeminjaman()
    {
        return $this->hasMany(DetailPeminjaman::class, 'alat_id');
    }

    public function peminjamanAktif()
    {
        return $this->hasMany(DetailPeminjaman::class, 'alat_id')
            ->whereHas('peminjaman', fn($q) => $q->whereIn('status', ['disetujui', 'dipinjam']));
    }
}
