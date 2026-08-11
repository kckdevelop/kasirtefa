<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\PengaturanAplikasi;
use Illuminate\Http\Request;

class PengaturanWebController extends Controller
{
    public function index()
    {
        $pengaturan = PengaturanAplikasi::all()->keyBy('kunci');
        return view('pengaturan.index', compact('pengaturan'));
    }

    public function update(Request $request)
    {
        foreach ($request->except('_token') as $key => $val) {
            PengaturanAplikasi::updateOrCreate(['kunci' => $key], ['nilai' => $val]);
        }

        return back()->with('success', 'Pengaturan berhasil disimpan');
    }
}
