<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;

class UserWebController extends Controller
{
    public function index()
    {
        $users = User::with('roles')->latest()->paginate(15);
        $roles = Role::all();
        return view('users.index', compact('users', 'roles'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nama' => 'required|string|max:100',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6',
            'role' => 'required|exists:roles,name',
            'nomor_induk' => 'nullable|string',
            'kelas' => 'nullable|string',
            'jurusan' => 'nullable|string',
        ]);

        $user = User::create([
            'nama' => $data['nama'],
            'name' => $data['nama'],
            'email' => $data['email'],
            'password' => bcrypt($data['password']),
            'nomor_induk' => $data['nomor_induk'],
            'kelas' => $data['kelas'],
            'jurusan' => $data['jurusan'],
            'status' => 'aktif',
        ]);

        $user->assignRole($data['role']);

        return back()->with('success', 'User berhasil ditambahkan');
    }

    public function toggleStatus($id)
    {
        $user = User::findOrFail($id);
        $user->status = $user->status === 'aktif' ? 'nonaktif' : 'aktif';
        $user->save();

        return back()->with('success', 'Status user berhasil diperbarui');
    }
}
