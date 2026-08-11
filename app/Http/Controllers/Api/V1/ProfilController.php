<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Auth\UpdateProfileRequest;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class ProfilController extends Controller
{
    use ApiResponse;

    public function show()
    {
        $user = auth()->user()->load('roles');
        return $this->successResponse($user, 'Detail profil user');
    }

    public function update(UpdateProfileRequest $request)
    {
        $user = auth()->user();

        if ($request->hasFile('foto')) {
            $user->foto = $request->file('foto')->store('profil', 'public');
        }

        if ($request->has('nama')) {
            $user->nama = $request->nama;
            $user->name = $request->nama;
        }

        $user->update($request->except(['foto', 'nama', 'password']));

        return $this->successResponse($user, 'Profil berhasil diperbarui');
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'password_lama' => 'required|string',
            'password_baru' => 'required|string|min:6|confirmed',
        ]);

        $user = auth()->user();

        if (!Hash::check($request->password_lama, $user->password)) {
            return $this->errorResponse('Password lama tidak sesuai', 400);
        }

        $user->update([
            'password' => Hash::make($request->password_baru),
        ]);

        return $this->successResponse(null, 'Password berhasil diperbarui');
    }
}
