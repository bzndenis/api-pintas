<?php

namespace App\Http\Controllers\Admin;

use App\Models\Kelas;
use Illuminate\Http\Request;
use App\Http\Helper\ResponseBuilder;
use Illuminate\Support\Facades\Auth;

class KelasController extends BaseAdminController
{
    public function index(Request $request)
    {
        try {
            $query = Kelas::with(['tahunAjaran', 'guru', 'siswa'])
                ->where('sekolah_id', Auth::user()->sekolah_id);

            if ($request->tahun_ajaran_id) {
                $query->where('tahun_ajaran_id', $request->tahun_ajaran_id);
            }

            if ($request->tingkat) {
                $query->where('tingkat', $request->tingkat);
            }

            $kelas = $query->orderBy('nama_kelas', 'asc')->get();

            return ResponseBuilder::success(200, "Berhasil mendapatkan data kelas", $kelas);
        } catch (\Exception $e) {
            return ResponseBuilder::error(500, "Gagal mendapatkan data: " . $e->getMessage());
        }
    }

    public function store(Request $request)
    {
        $this->validate($request, [
            'nama_kelas' => 'required|string|max:255',
            'tingkat' => 'required|string|max:255',
            'tahun_ajaran_id' => 'required|exists:tahun_ajaran,id',
            'guru_id' => 'required|exists:guru,id'
        ]);

        try {
            $data = $request->all();
            $data['sekolah_id'] = Auth::user()->sekolah_id;

            $kelas = Kelas::create($data);

            return ResponseBuilder::success(201, "Berhasil menambahkan kelas", $kelas);
        } catch (\Exception $e) {
            return ResponseBuilder::error(500, "Gagal menambahkan data: " . $e->getMessage());
        }
    }

    public function update(Request $request, $id)
    {
        $this->validate($request, [
            'nama_kelas' => 'required|string|max:255',
            'tingkat' => 'required|string|max:255',
            'tahun_ajaran_id' => 'required|exists:tahun_ajaran,id',
            'guru_id' => 'required|exists:guru,id'
        ]);

        try {
            $kelas = Kelas::where('sekolah_id', Auth::user()->sekolah_id)
                ->find($id);

            if (!$kelas) {
                return ResponseBuilder::error(404, "Kelas tidak ditemukan");
            }

            $kelas->update($request->all());

            return ResponseBuilder::success(200, "Berhasil mengupdate kelas", $kelas);
        } catch (\Exception $e) {
            return ResponseBuilder::error(500, "Gagal mengupdate data: " . $e->getMessage());
        }
    }
} 