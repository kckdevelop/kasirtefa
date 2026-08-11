<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Auth\LoginRequest;
use App\Http\Requests\Api\V1\Auth\RegisterRequest;
use App\Models\User;
use App\Traits\ApiResponse;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    use ApiResponse;

    public function register(RegisterRequest $request)
    {
        $user = User::create([
            'nama' => $request->nama,
            'name' => $request->nama,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'nomor_induk' => $request->nomor_induk,
            'jenis_nomor_induk' => $request->jenis_nomor_induk ?? 'siswa',
            'kelas' => $request->kelas,
            'jurusan' => $request->jurusan,
            'no_telepon' => $request->no_telepon,
            'alamat' => $request->alamat,
            'status' => 'aktif',
        ]);

        $user->assignRole('peminjam');

        $token = $user->createToken('auth_token')->plainTextToken;

        return $this->successResponse([
            'user' => $user->load('roles'),
            'access_token' => $token,
            'token_type' => 'Bearer',
        ], 'Registrasi berhasil', 201);
    }

    public function login(LoginRequest $request)
    {
        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return $this->errorResponse('Email atau password salah', 401);
        }

        if ($user->status !== 'aktif') {
            return $this->errorResponse('Akun Anda nonaktif. Silakan hubungi admin.', 403);
        }

        $user->update(['last_login_at' => Carbon::now()]);
        $token = $user->createToken('auth_token')->plainTextToken;

        return $this->successResponse([
            'user' => [
                'id' => $user->id,
                'nama' => $user->nama ?? $user->name,
                'email' => $user->email,
                'nomor_induk' => $user->nomor_induk,
                'jenis_nomor_induk' => $user->jenis_nomor_induk,
                'kelas' => $user->kelas,
                'jurusan' => $user->jurusan,
                'no_telepon' => $user->no_telepon,
                'foto_url' => $user->foto_url,
                'roles' => $user->getRoleNames(),
            ],
            'access_token' => $token,
            'token_type' => 'Bearer',
        ], 'Login berhasil');
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return $this->successResponse(null, 'Logout berhasil');
    }

    public function me(Request $request)
    {
        $user = $request->user()->load('roles');
        return $this->successResponse([
            'user' => $user,
            'roles' => $user->getRoleNames(),
            'permissions' => $user->getAllPermissions()->pluck('name'),
        ], 'Detail profil user');
    }
}
