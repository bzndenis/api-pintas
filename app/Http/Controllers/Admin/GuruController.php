<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Guru;
use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Helper\ResponseBuilder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class GuruController extends Controller
{
    public function __construct()
    {
        // $this->middleware(['login', 'admin']);
    }

    public function index(Request $request)
    {
        try {
            $admin = Auth::user();
            
            $query = Guru::with(['user', 'sekolah', 'mataPelajaran'])
                        ->where('sekolah_id', $admin->sekolah_id);

            if ($request->search) {
                $query->where(function($q) use ($request) {
                    $q->where('nama', 'like', "%{$request->search}%")
                      ->orWhere('nip', 'like', "%{$request->search}%");
                });
            }

            $guru = $query->orderBy('created_at', 'desc')->get();

            return ResponseBuilder::success(200, "Berhasil mendapatkan data", [
                'total' => $guru->count(),
                'guru' => $guru->map(function($item) {
                    return [
                        'id' => $item->id,
                        'nama' => $item->nama,
                        'nip' => $item->nip,
                        'email' => $item->user->email,
                        'is_active' => $item->user->is_active,
                        'mata_pelajaran' => $item->mataPelajaran->map(function($mapel) {
                            return [
                                'id' => $mapel->id,
                                'nama_mapel' => $mapel->nama_mapel
                            ];
                        })
                    ];
                })
            ]);
        } catch (\Exception $e) {
            return ResponseBuilder::error(500, "Gagal mengambil data: " . $e->getMessage());
        }
    }

    public function store(Request $request)
    {
        $this->validate($request, [
            'nama' => 'required|string|max:255',
            'nip' => 'required|string|unique:guru,nip',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6',
            'mata_pelajaran' => 'required|array',
            'mata_pelajaran.*' => 'exists:mata_pelajaran,id'
        ]);

        try {
            DB::beginTransaction();

            $admin = Auth::user();

            // Buat user account
            $user = User::create([
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'role' => 'guru',
                'is_active' => true,
                'sekolah_id' => $admin->sekolah_id
            ]);

            // Buat data guru
            $guru = Guru::create([
                'nama' => $request->nama,
                'nip' => $request->nip,
                'user_id' => $user->id,
                'sekolah_id' => $admin->sekolah_id,
                'created_by' => $admin->id
            ]);

            // Assign mata pelajaran
            $guru->mataPelajaran()->attach($request->mata_pelajaran);

            DB::commit();

            $guru->load(['user', 'mataPelajaran', 'sekolah']);

            return ResponseBuilder::success(201, "Berhasil membuat akun guru", $guru);
        } catch (\Exception $e) {
            DB::rollBack();
            return ResponseBuilder::error(500, "Gagal membuat akun: " . $e->getMessage());
        }
    }

    public function resetPassword(Request $request, $id)
    {
        $this->validate($request, [
            'new_password' => 'required|string|min:6'
        ]);

        try {
            $guru = Guru::with('user')->find($id);
            
            if (!$guru) {
                return ResponseBuilder::error(404, "Data guru tidak ditemukan");
            }

            // Pastikan admin hanya bisa reset password guru di sekolahnya
            if ($guru->sekolah_id != Auth::user()->sekolah_id) {
                return ResponseBuilder::error(403, "Tidak memiliki akses");
            }

            $guru->user->update([
                'password' => Hash::make($request->new_password)
            ]);

            return ResponseBuilder::success(200, "Berhasil reset password");
        } catch (\Exception $e) {
            return ResponseBuilder::error(500, "Gagal reset password: " . $e->getMessage());
        }
    }

    public function activate($id)
    {
        try {
            $guru = Guru::with('user')->find($id);
            
            if (!$guru) {
                return ResponseBuilder::error(404, "Data guru tidak ditemukan");
            }

            if ($guru->sekolah_id != Auth::user()->sekolah_id) {
                return ResponseBuilder::error(403, "Tidak memiliki akses");
            }

            $guru->user->update(['is_active' => true]);

            return ResponseBuilder::success(200, "Berhasil mengaktifkan akun guru");
        } catch (\Exception $e) {
            return ResponseBuilder::error(500, "Gagal mengaktifkan akun: " . $e->getMessage());
        }
    }

    public function deactivate($id)
    {
        try {
            $guru = Guru::with('user')->find($id);
            
            if (!$guru) {
                return ResponseBuilder::error(404, "Data guru tidak ditemukan");
            }

            if ($guru->sekolah_id != Auth::user()->sekolah_id) {
                return ResponseBuilder::error(403, "Tidak memiliki akses");
            }

            $guru->user->update(['is_active' => false]);

            return ResponseBuilder::success(200, "Berhasil menonaktifkan akun guru");
        } catch (\Exception $e) {
            return ResponseBuilder::error(500, "Gagal menonaktifkan akun: " . $e->getMessage());
        }
    }
} 