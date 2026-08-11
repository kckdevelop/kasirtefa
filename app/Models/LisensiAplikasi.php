<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LisensiAplikasi extends Model
{
    use HasFactory;

    protected $table = 'lisensi_aplikasi';

    protected $fillable = [
        'nomor_lisensi',
        'tipe',
        'nama_pembeli',
        'email',
        'telepon',
        'nama_sekolah',
        'harga',
        'tanggal_beli',
        'tanggal_jatuh_tempo',
        'tanggal_mulai',
        'lama_sewa',
        'tanggal_berakhir',
        'status',
        'status_pembayaran',
        'tanggal_pembayaran',
        'metode_pembayaran',
        'catatan_pembayaran',
        'keterangan',
        'created_by',
    ];

    protected $casts = [
        'tanggal_beli'        => 'date',
        'tanggal_jatuh_tempo' => 'date',
        'tanggal_mulai'       => 'date',
        'tanggal_berakhir'    => 'date',
        'tanggal_pembayaran'  => 'date',
        'harga'               => 'decimal:2',
        'lama_sewa'           => 'integer',
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Tanggal akhir berlaku (jatuh tempo atau tanggal berakhir)
     */
    public function getTanggalAkhirAttribute(): ?Carbon
    {
        if ($this->tipe === 'beli') {
            return $this->tanggal_jatuh_tempo;
        }
        return $this->tanggal_berakhir;
    }

    /**
     * Sisa hari sampai jatuh tempo
     */
    public function getSisaHariAttribute(): ?int
    {
        $akhir = $this->tanggal_akhir;
        if (!$akhir) return null;
        return max(0, Carbon::today()->diffInDays($akhir, false));
    }

    /**
     * Apakah lisensi sudah expired
     */
    public function getIsExpiredAttribute(): bool
    {
        $akhir = $this->tanggal_akhir;
        if (!$akhir) return false;
        return $akhir->isPast();
    }

    /**
     * Apakah lisensi akan berakhir dalam N hari
     */
    public function isMenujuBerakhir(int $hari = 30): bool
    {
        $akhir = $this->tanggal_akhir;
        if (!$akhir) return false;
        $sisaHari = Carbon::today()->diffInDays($akhir, false);
        return $sisaHari >= 0 && $sisaHari <= $hari;
    }

    /**
     * Scope: hanya lisensi aktif
     */
    public function scopeAktif($query)
    {
        return $query->where('status', 'aktif');
    }

    /**
     * Scope: lisensi yang mendekati kadaluarsa (dalam N hari)
     */
    public function scopeMendekatiKadaluarsa($query, int $hari = 30)
    {
        return $query->where('status', 'aktif')
            ->where(function ($q) use ($hari) {
                $q->whereBetween('tanggal_jatuh_tempo', [now()->toDateString(), now()->addDays($hari)->toDateString()])
                  ->orWhereBetween('tanggal_berakhir', [now()->toDateString(), now()->addDays($hari)->toDateString()]);
            });
    }
}
