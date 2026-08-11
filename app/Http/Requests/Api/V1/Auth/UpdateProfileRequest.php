<?php

namespace App\Http\Requests\Api\V1\Auth;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $userId = $this->user()?->id;
        return [
            'nama' => 'sometimes|required|string|max:100',
            'email' => 'sometimes|required|email|unique:users,email,' . $userId,
            'nomor_induk' => 'nullable|string|max:50',
            'jenis_nomor_induk' => 'nullable|in:siswa,guru,staff',
            'kelas' => 'nullable|string|max:50',
            'jurusan' => 'nullable|string|max:100',
            'no_telepon' => 'nullable|string|max:20',
            'alamat' => 'nullable|string',
            'foto' => 'nullable|image|max:2048',
        ];
    }
}
