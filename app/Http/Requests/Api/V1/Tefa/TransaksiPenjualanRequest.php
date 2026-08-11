<?php

namespace App\Http\Requests\Api\V1\Tefa;

use Illuminate\Foundation\Http\FormRequest;

class TransaksiPenjualanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'customer_nama' => 'nullable|string|max:200',
            'customer_telepon' => 'nullable|string|max:20',
            'customer_alamat' => 'nullable|string',
            'metode_pembayaran' => 'required|in:tunai,transfer,qris',
            'nominal_bayar' => 'required|numeric|min:0',
            'diskon_persen' => 'nullable|numeric|min:0|max:100',
            'no_referensi' => 'nullable|string|max:100',
            'catatan' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.produk_id' => 'required|exists:produk,id',
            'items.*.jumlah' => 'required|integer|min:1',
            'items.*.catatan' => 'nullable|string',
        ];
    }
}
