<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Pelanggan;
use Carbon\Carbon;
use Illuminate\Http\Request;

class PelangganWebController extends Controller
{
    public function index(Request $request)
    {
        $query = Pelanggan::query();

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('nama', 'like', "%{$s}%")
                    ->orWhere('kode_pelanggan', 'like', "%{$s}%")
                    ->orWhere('telepon', 'like', "%{$s}%")
                    ->orWhere('email', 'like', "%{$s}%");
            });
        }

        if ($request->filled('tipe')) {
            $query->where('tipe', $request->tipe);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $pelanggan = $query->latest()->paginate(20)->withQueryString();

        return view('tefa.pelanggan.index', compact('pelanggan'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nama'    => 'required|string|max:200',
            'tipe'    => 'required|in:umum,siswa,guru,instansi',
            'telepon' => 'nullable|string|max:30',
            'email'   => 'nullable|email|max:100',
            'alamat'  => 'nullable|string',
            'status'  => 'required|in:aktif,nonaktif',
            'catatan' => 'nullable|string',
        ]);

        $todayStr = Carbon::now()->format('Ymd');
        $lastCount = Pelanggan::withTrashed()->whereDate('created_at', Carbon::today())->count();
        $data['kode_pelanggan'] = 'PLG-' . $todayStr . '-' . str_pad($lastCount + 1, 4, '0', STR_PAD_LEFT);

        Pelanggan::create($data);

        return redirect()->route('tefa.pelanggan.index')
            ->with('success', 'Data pelanggan berhasil ditambahkan.');
    }

    public function update(Request $request, Pelanggan $pelanggan)
    {
        $data = $request->validate([
            'nama'    => 'required|string|max:200',
            'tipe'    => 'required|in:umum,siswa,guru,instansi',
            'telepon' => 'nullable|string|max:30',
            'email'   => 'nullable|email|max:100',
            'alamat'  => 'nullable|string',
            'status'  => 'required|in:aktif,nonaktif',
            'catatan' => 'nullable|string',
        ]);

        $pelanggan->update($data);

        return redirect()->route('tefa.pelanggan.index')
            ->with('success', 'Data pelanggan berhasil diperbarui.');
    }

    public function destroy(Pelanggan $pelanggan)
    {
        $pelanggan->delete();

        return redirect()->route('tefa.pelanggan.index')
            ->with('success', 'Data pelanggan berhasil dihapus.');
    }
}
