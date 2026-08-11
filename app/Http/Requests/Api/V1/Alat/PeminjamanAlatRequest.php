<?php

namespace App\Http\Requests\Api\V1\Alat;

use Illuminate\Foundation\Http\FormRequest;

class PeminjamanAlatRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'tanggal_pinjam' => 'required|date',
            'tanggal_kembali_rencana' => 'required|date|after_or_equal:tanggal_pinjam',
            'keperluan' => 'required|string',
            'tujuan_penggunaan' => 'nullable|string|max:200',
            'lokasi_penggunaan' => 'nullable|string|max:200',
            'catatan_peminjam' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.alat_id' => 'required|exists:alat,id',
            'items.*.jumlah' => 'required|integer|min:1',
        ];
    }
}
