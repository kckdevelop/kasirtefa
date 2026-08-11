@extends('layouts.app')

@section('title', 'Manajemen User')

@section('content')
<div x-data="{ showModal: false }" class="space-y-6">

    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-xl font-bold text-slate-900">Manajemen User</h2>
            <p class="text-sm text-slate-500">Kelola pengguna sistem, peran, dan hak akses</p>
        </div>
        <button @click="showModal = true"
            class="inline-flex items-center gap-2 px-4 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-xl text-sm shadow-md shadow-blue-600/25 transition-all">
            <i class="fa-solid fa-user-plus"></i> Tambah User Baru
        </button>
    </div>

    <!-- Table -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead class="bg-slate-50 border-b border-slate-200">
                    <tr class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider">
                        <th class="py-3.5 px-4">Nama Pengguna</th>
                        <th class="py-3.5 px-4">Email</th>
                        <th class="py-3.5 px-4">Role / Peran</th>
                        <th class="py-3.5 px-4">Identitas (NIP/NIS)</th>
                        <th class="py-3.5 px-4 text-center">Status</th>
                        <th class="py-3.5 px-4 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-sm">
                    @forelse($users as $user)
                    <tr class="hover:bg-slate-50 transition-colors">
                        <td class="py-3.5 px-4">
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 rounded-full bg-blue-100 text-blue-700 flex items-center justify-center font-bold text-xs uppercase">
                                    {{ substr($user->nama ?? $user->name, 0, 2) }}
                                </div>
                                <div>
                                    <p class="font-semibold text-slate-900">{{ $user->nama ?? $user->name }}</p>
                                    <p class="text-xs text-slate-400">{{ $user->jurusan ? $user->jurusan.' - '.$user->kelas : '' }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="py-3.5 px-4 text-slate-600">{{ $user->email }}</td>
                        <td class="py-3.5 px-4">
                            <span class="px-2.5 py-1 rounded-lg bg-purple-50 text-purple-700 text-xs font-semibold uppercase">
                                {{ $user->roles->first()?->name ?? 'User' }}
                            </span>
                        </td>
                        <td class="py-3.5 px-4 font-mono text-xs text-slate-600">{{ $user->nomor_induk ?? '-' }}</td>
                        <td class="py-3.5 px-4 text-center">
                            <span class="px-3 py-1 rounded-full text-xs font-bold uppercase {{ $user->status === 'aktif' ? 'bg-emerald-100 text-emerald-700' : 'bg-rose-100 text-rose-700' }}">
                                {{ $user->status ?? 'aktif' }}
                            </span>
                        </td>
                        <td class="py-3.5 px-4 text-center">
                            <form action="{{ route('users.toggle-status', $user->id) }}" method="POST" class="inline">
                                @csrf
                                @method('PUT')
                                <button type="submit" class="px-3 py-1.5 rounded-lg text-xs font-semibold transition-colors {{ $user->status === 'aktif' ? 'bg-rose-50 text-rose-600 hover:bg-rose-100' : 'bg-emerald-50 text-emerald-600 hover:bg-emerald-100' }}">
                                    {{ $user->status === 'aktif' ? 'Nonaktifkan' : 'Aktifkan' }}
                                </button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="py-12 text-center text-slate-400">Belum ada data user.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($users->hasPages())
        <div class="px-4 py-3 border-t border-slate-100">{{ $users->links() }}</div>
        @endif
    </div>

    <!-- Modal Form Tambah User -->
    <div x-show="showModal" x-cloak class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl w-full max-w-lg shadow-2xl p-6" @click.stop>
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-bold text-slate-900">Tambah User Baru</h3>
                <button @click="showModal = false" class="text-slate-400 hover:text-slate-600"><i class="fa-solid fa-times text-xl"></i></button>
            </div>
            <form action="{{ route('users.store') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Nama Lengkap *</label>
                    <input type="text" name="nama" required placeholder="Nama user..." class="w-full px-3 py-2 rounded-xl border border-slate-200 text-sm">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Email *</label>
                    <input type="email" name="email" required placeholder="user@domain.com" class="w-full px-3 py-2 rounded-xl border border-slate-200 text-sm">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Password *</label>
                    <input type="password" name="password" required minlength="6" placeholder="Minimal 6 karakter" class="w-full px-3 py-2 rounded-xl border border-slate-200 text-sm">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Role / Peran *</label>
                    <select name="role" required class="w-full px-3 py-2 rounded-xl border border-slate-200 text-sm bg-white">
                        @foreach($roles as $role)
                        <option value="{{ $role->name }}">{{ ucfirst($role->name) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">NIP / NIS</label>
                        <input type="text" name="nomor_induk" placeholder="12345..." class="w-full px-3 py-2 rounded-xl border border-slate-200 text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Kelas</label>
                        <input type="text" name="kelas" placeholder="XII RPL 1..." class="w-full px-3 py-2 rounded-xl border border-slate-200 text-sm">
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Jurusan</label>
                    <input type="text" name="jurusan" placeholder="Rekayasa Perangkat Lunak..." class="w-full px-3 py-2 rounded-xl border border-slate-200 text-sm">
                </div>
                <div class="flex gap-3 justify-end pt-3 border-t border-slate-100">
                    <button type="button" @click="showModal = false" class="px-4 py-2 border border-slate-200 rounded-xl text-sm font-semibold text-slate-600">Batal</button>
                    <button type="submit" class="px-4 py-2 bg-blue-600 text-white font-semibold rounded-xl text-sm hover:bg-blue-700">Simpan User</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
