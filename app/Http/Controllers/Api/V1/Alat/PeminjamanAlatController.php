<?php

namespace App\Http\Controllers\Api\V1\Alat;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Alat\PeminjamanAlatRequest;
use App\Models\PeminjamanAlat;
use App\Services\Alat\PeminjamanService;
use App\Traits\ApiResponse;
use Exception;
use Illuminate\Http\Request;

class PeminjamanAlatController extends Controller
{
    use ApiResponse;

    protected $peminjamanService;

    public function __construct(PeminjamanService $peminjamanService)
    {
        $this->peminjamanService = $peminjamanService;
    }

    public function index(Request $request)
    {
        $query = PeminjamanAlat::with(['peminjam', 'items.alat', 'approver']);

        $user = auth()->user();
        if ($user->hasRole('peminjam') && !$user->hasRole(['super_admin', 'admin_alat'])) {
            $query->where('peminjam_id', $user->id);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('kode_peminjaman', 'like', "%{$search}%")
                    ->orWhere('keperluan', 'like', "%{$search}%")
                    ->orWhereHas('peminjam', function ($uq) use ($search) {
                        $uq->where('nama', 'like', "%{$search}%");
                    });
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('tanggal_mulai')) {
            $query->whereDate('tanggal_pinjam', '>=', $request->tanggal_mulai);
        }

        if ($request->filled('tanggal_selesai')) {
            $query->whereDate('tanggal_pinjam', '<=', $request->tanggal_selesai);
        }

        $perPage = $request->get('per_page', 15);
        $list = $query->orderBy('tanggal_pinjam', 'desc')->paginate($perPage);

        return $this->successResponse($list, 'Daftar peminjaman alat');
    }

    public function store(PeminjamanAlatRequest $request)
    {
        try {
            $peminjaman = $this->peminjamanService->createPeminjaman($request->validated(), auth()->id());
            return $this->successResponse($peminjaman, 'Pengajuan peminjaman berhasil dikirim', 201);
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), 400);
        }
    }

    public function show($id)
    {
        $peminjaman = PeminjamanAlat::with(['peminjam', 'items.alat.kategori', 'approver', 'rejecter', 'denda'])->findOrFail($id);

        $user = auth()->user();
        if ($user->hasRole('peminjam') && !$user->hasRole(['super_admin', 'admin_alat']) && $peminjaman->peminjam_id !== $user->id) {
            return $this->errorResponse('Anda tidak memiliki akses ke data peminjaman ini', 403);
        }

        return $this->successResponse($peminjaman, 'Detail peminjaman alat');
    }

    public function approve(Request $request, $id)
    {
        $request->validate([
            'catatan_admin' => 'nullable|string',
        ]);

        try {
            $peminjaman = $this->peminjamanService->approvePeminjaman($id, auth()->id(), $request->catatan_admin);
            return $this->successResponse($peminjaman, 'Peminjaman alat berhasil disetujui');
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), 400);
        }
    }

    public function reject(Request $request, $id)
    {
        $request->validate([
            'rejection_reason' => 'required|string',
        ]);

        try {
            $peminjaman = $this->peminjamanService->rejectPeminjaman($id, auth()->id(), $request->rejection_reason);
            return $this->successResponse($peminjaman, 'Peminjaman alat berhasil ditolak');
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), 400);
        }
    }

    public function proses(Request $request, $id)
    {
        $request->validate([
            'diterima_oleh' => 'nullable|string|max:200',
        ]);

        try {
            $peminjaman = $this->peminjamanService->prosesPeminjaman($id, $request->diterima_oleh);
            return $this->successResponse($peminjaman, 'Status peminjaman diperbarui menjadi dipinjam');
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), 400);
        }
    }

    public function kembalikan(Request $request, $id)
    {
        $request->validate([
            'dikembalikan_oleh' => 'nullable|string|max:200',
            'diterima_pengembalian_oleh' => 'nullable|string|max:200',
            'items' => 'required|array|min:1',
            'items.*.detail_peminjaman_id' => 'required|exists:detail_peminjaman,id',
            'items.*.jumlah_dikembalikan' => 'required|integer|min:1',
            'items.*.kondisi_saat_dikembalikan' => 'required|in:baik,cukup,rusak_ringan,rusak_berat',
            'items.*.catatan_kerusakan' => 'nullable|string',
        ]);

        try {
            $peminjaman = $this->peminjamanService->prosesPengembalian(
                $id,
                $request->items,
                $request->dikembalikan_oleh,
                $request->diterima_pengembalian_oleh
            );
            return $this->successResponse($peminjaman, 'Proses pengembalian alat berhasil diproses');
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), 400);
        }
    }

    public function findByKode($kode)
    {
        $peminjaman = PeminjamanAlat::with(['peminjam', 'items.alat', 'denda'])
            ->where('kode_peminjaman', $kode)
            ->firstOrFail();

        return $this->successResponse($peminjaman, 'Data peminjaman by kode');
    }

    public function saya(Request $request)
    {
        $list = PeminjamanAlat::with(['items.alat'])
            ->where('peminjam_id', auth()->id())
            ->orderBy('created_at', 'desc')
            ->paginate($request->get('per_page', 15));

        return $this->successResponse($list, 'Daftar peminjaman alat milik user');
    }
}
