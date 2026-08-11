<?php

namespace App\Http\Requests\Api\V1\Auth;

use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nama' => 'required|string|max:100',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6|confirmed',
            'nomor_induk' => 'nullable|string|max:50',
            'jenis_nomor_induk' => 'nullable|in:siswa,guru,staff',
            'kelas' => 'nullable|string|max:50',
            'jurusan' => 'nullable|string|max:100',
            'no_telepon' => 'nullable|string|max:20',
            'alamat' => 'nullable|string',
        ];
    }
}
