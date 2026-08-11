<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PengaturanAplikasi extends Model
{
    use HasFactory;

    protected $table = 'pengaturan_aplikasi';

    protected $fillable = [
        'kunci',
        'nilai',
        'tipe',
        'kategori',
        'deskripsi',
    ];

    public static function get($key, $default = null)
    {
        $setting = static::where('kunci', $key)->first();
        if (!$setting) return $default;

        return match ($setting->tipe) {
            'number' => (float)$setting->nilai,
            'boolean' => filter_var($setting->nilai, FILTER_VALIDATE_BOOLEAN),
            'json' => json_decode($setting->nilai, true),
            default => $setting->nilai,
        };
    }

    public static function set($key, $value, $type = 'text', $kategori = 'umum', $deskripsi = null)
    {
        $val = is_array($value) ? json_encode($value) : (string)$value;
        return static::updateOrCreate(
            ['kunci' => $key],
            [
                'nilai' => $val,
                'tipe' => $type,
                'kategori' => $kategori,
                'deskripsi' => $deskripsi,
            ]
        );
    }
}
