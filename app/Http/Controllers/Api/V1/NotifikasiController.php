<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Notifikasi;
use App\Traits\ApiResponse;
use Carbon\Carbon;
use Illuminate\Http\Request;

class NotifikasiController extends Controller
{
    use ApiResponse;

    public function index(Request $request)
    {
        $list = Notifikasi::where('user_id', auth()->id())
            ->latest()
            ->paginate($request->get('per_page', 15));

        return $this->successResponse($list, 'Daftar notifikasi user');
    }

    public function read($id)
    {
        $notif = Notifikasi::where('user_id', auth()->id())->findOrFail($id);
        $notif->update([
            'is_read' => true,
            'read_at' => Carbon::now(),
        ]);

        return $this->successResponse($notif, 'Notifikasi telah dibaca');
    }

    public function readAll()
    {
        Notifikasi::where('user_id', auth()->id())
            ->where('is_read', false)
            ->update([
                'is_read' => true,
                'read_at' => Carbon::now(),
            ]);

        return $this->successResponse(null, 'Semua notifikasi ditandai telah dibaca');
    }

    public function unreadCount()
    {
        $count = Notifikasi::where('user_id', auth()->id())
            ->where('is_read', false)
            ->count();

        return $this->successResponse(['unread_count' => $count], 'Jumlah notifikasi belum dibaca');
    }
}
