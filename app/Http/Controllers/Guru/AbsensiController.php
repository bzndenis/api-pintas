<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\PertemuanBulanan;
use App\Models\AbsensiSiswa;
use App\Models\Siswa;
use App\Models\Kelas;
use App\Models\MataPelajaran;
use App\Models\User;
use Ramsey\Uuid\Uuid;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

class AbsensiController extends Controller
{
    /**
     * Menampilkan daftar data pertemuan bulanan
     */
    public function index(Request $request)
    {
        try {
            $user = Auth::user();
            $sekolahId = $user->sekolah_id;
            $userId = $user->id;
            
            $kelasId = $request->input('kelas_id');
            $mapelId = $request->input('mapel_id');
            $bulan = $request->input('bulan');
            $tahun = $request->input('tahun');
            $limit = $request->input('limit', 10);
            $page = $request->input('page', 1);

            $query = PertemuanBulanan::where('sekolah_id', $sekolahId);

            if ($kelasId) {
                $query->where('kelas_id', $kelasId);
            }
            
            if ($mapelId) {
                $query->where('mata_pelajaran_id', $mapelId);
            }

            if ($bulan) {
                $query->where('bulan', $bulan);
            }

            if ($tahun) {
                $query->where('tahun', $tahun);
            }

            $total = $query->count();
            $pertemuan = $query->skip(($page - 1) * $limit)
                            ->take($limit)
                            ->with(['kelas', 'mataPelajaran'])
                            ->orderBy('created_at', 'desc')
                            ->get();

            return response()->json([
                'status' => 'success',
                'data' => $pertemuan,
                'meta' => [
                    'total' => $total,
                    'page' => (int)$page,
                    'limit' => (int)$limit
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Membuat data pertemuan bulanan baru
     */
    public function createMonth(Request $request)
    {
        try {
            $user = Auth::user();
            $sekolahId = $user->sekolah_id;
            $userId = $user->id;

            $validator = Validator::make($request->all(), [
                'kelas_id' => 'required|string|exists:kelas,id',
                'mata_pelajaran_id' => 'required|string|exists:mata_pelajaran,id',
                'bulan' => 'required|integer|min:1|max:12',
                'tahun' => 'required|integer|min:2000|max:2099',
                'total_pertemuan' => 'required|integer|min:1|max:31'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Validasi gagal',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Cek apakah sudah ada pertemuan untuk bulan dan kelas yang sama
            $exists = PertemuanBulanan::where('kelas_id', $request->kelas_id)
                        ->where('bulan', $request->bulan)
                        ->where('tahun', $request->tahun)
                        ->where('sekolah_id', $sekolahId)
                        ->first();
                        
            if ($exists) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Pertemuan untuk bulan ini sudah ada'
                ], 422);
            }

            $pertemuan = new PertemuanBulanan();
            $pertemuan->id = Uuid::uuid4()->toString();
            $pertemuan->kelas_id = $request->kelas_id;
            $pertemuan->mata_pelajaran_id = $request->mata_pelajaran_id;
            $pertemuan->bulan = $request->bulan;
            $pertemuan->tahun = $request->tahun;
            $pertemuan->total_pertemuan = $request->total_pertemuan;
            $pertemuan->created_by = $userId;
            $pertemuan->sekolah_id = $sekolahId;
            $pertemuan->save();

            return response()->json([
                'status' => 'success',
                'message' => 'Pertemuan bulanan berhasil dibuat',
                'data' => $pertemuan
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mendapatkan detail pertemuan bulanan
     */
    public function getMonthDetail(Request $request, $id)
    {
        try {
            $user = Auth::user();
            $sekolahId = $user->sekolah_id;

            $pertemuan = PertemuanBulanan::where('id', $id)
                            ->where('sekolah_id', $sekolahId)
                            ->with(['kelas', 'mataPelajaran'])
                            ->first();

            if (!$pertemuan) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Data tidak ditemukan'
                ], 404);
            }

            return response()->json([
                'status' => 'success',
                'data' => $pertemuan
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mendapatkan daftar siswa berdasarkan pertemuan bulanan
     */
    public function getStudentsByMonth(Request $request, $id)
    {
        try {
            $user = Auth::user();
            $sekolahId = $user->sekolah_id;

            $pertemuan = PertemuanBulanan::where('id', $id)
                            ->where('sekolah_id', $sekolahId)
                            ->first();

            if (!$pertemuan) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Data pertemuan tidak ditemukan'
                ], 404);
            }

            // Dapatkan daftar siswa di kelas tersebut
            $siswa = Siswa::where('kelas_id', $pertemuan->kelas_id)
                        ->where('sekolah_id', $sekolahId)
                        ->where('deleted_at', null)
                        ->orderBy('nama', 'asc')
                        ->get();

            // Dapatkan data absensi yang sudah ada
            $absensi = AbsensiSiswa::where('pertemuan_id', $id)
                        ->where('sekolah_id', $sekolahId)
                        ->get()
                        ->keyBy('siswa_id');

            $result = [];
            foreach ($siswa as $s) {
                $absensiData = isset($absensi[$s->id]) ? $absensi[$s->id] : null;
                
                $result[] = [
                    'id' => $absensiData ? $absensiData->id : null,
                    'siswa_id' => $s->id,
                    'nama' => $s->nama,
                    'nisn' => $s->nisn,
                    'hadir' => $absensiData ? $absensiData->hadir : 0,
                    'izin' => $absensiData ? $absensiData->izin : 0,
                    'sakit' => $absensiData ? $absensiData->sakit : 0,
                    'absen' => $absensiData ? $absensiData->absen : 0,
                    'keterangan' => $absensiData ? $absensiData->keterangan : null
                ];
            }

            return response()->json([
                'status' => 'success',
                'data' => $result,
                'bulan' => [
                    'id' => $pertemuan->id,
                    'bulan' => $pertemuan->bulan,
                    'tahun' => $pertemuan->tahun,
                    'total_pertemuan' => $pertemuan->total_pertemuan
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Menyimpan data absensi siswa
     */
    public function saveStudentAttendance(Request $request)
    {
        try {
            $user = Auth::user();
            $sekolahId = $user->sekolah_id;
            $userId = $user->id;

            $validator = Validator::make($request->all(), [
                'pertemuan_id' => 'required|string|exists:pertemuan_bulanan,id',
                'siswa_id' => 'required|string|exists:siswa,id',
                'hadir' => 'required|integer|min:0',
                'izin' => 'required|integer|min:0',
                'sakit' => 'required|integer|min:0',
                'absen' => 'required|integer|min:0',
                'keterangan' => 'nullable|string|max:255'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Validasi gagal',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Cek apakah sudah ada data absensi untuk siswa dan pertemuan ini
            $exists = AbsensiSiswa::where('siswa_id', $request->siswa_id)
                        ->where('pertemuan_id', $request->pertemuan_id)
                        ->where('sekolah_id', $sekolahId)
                        ->first();
                        
            if ($exists) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Data absensi untuk siswa ini sudah ada'
                ], 422);
            }

            $absensi = new AbsensiSiswa();
            $absensi->id = Uuid::uuid4()->toString();
            $absensi->siswa_id = $request->siswa_id;
            $absensi->pertemuan_id = $request->pertemuan_id;
            $absensi->hadir = $request->hadir;
            $absensi->izin = $request->izin;
            $absensi->sakit = $request->sakit;
            $absensi->absen = $request->absen;
            $absensi->keterangan = $request->keterangan;
            $absensi->created_by = $userId;
            $absensi->sekolah_id = $sekolahId;
            $absensi->save();

            return response()->json([
                'status' => 'success',
                'message' => 'Data absensi berhasil disimpan',
                'data' => $absensi
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    public function storeBatch(Request $request)
    {
        try {
            $user = Auth::user();
            $sekolahId = $user->sekolah_id;

            $validator = Validator::make($request->all(), [
                'absensi' => 'required|array',
                'absensi.*.pertemuan_id' => 'required|string|exists:pertemuan_bulanan,id',
                'absensi.*.siswa_id' => 'required|string|exists:siswa,id',
                'absensi.*.hadir' => 'required|integer|min:0',
                'absensi.*.izin' => 'required|integer|min:0',
                'absensi.*.sakit' => 'required|integer|min:0',
                'absensi.*.absen' => 'required|integer|min:0',
                'absensi.*.keterangan' => 'nullable|string|max:255'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Validasi gagal',
                    'errors' => $validator->errors()
                ], 422);
            }

            $absensiData = $request->input('absensi');
            $absensiList = [];

            foreach ($absensiData as $data) {
                $exists = AbsensiSiswa::where('siswa_id', $data['siswa_id'])
                            ->where('pertemuan_id', $data['pertemuan_id'])
                            ->where('sekolah_id', $sekolahId)
                            ->first();

                if ($exists) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Data absensi untuk siswa dengan pertemuan_id ' . $data['pertemuan_id'] . ' sudah ada'
                    ], 422);
                }

                $absensi = new AbsensiSiswa();
                $absensi->id = Uuid::uuid4()->toString();
                $absensi->siswa_id = $data['siswa_id'];
                $absensi->pertemuan_id = $data['pertemuan_id'];
                $absensi->hadir = $data['hadir'];
                $absensi->izin = $data['izin'];
                $absensi->sakit = $data['sakit'];
                $absensi->absen = $data['absen'];
                $absensi->keterangan = $data['keterangan'];
                $absensi->created_by = $user->id;
                $absensi->sekolah_id = $sekolahId;
                $absensiList[] = $absensi;
            }

            foreach ($absensiList as $absensi) {
                $absensi->save();
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Data absensi batch berhasil disimpan',
                'data' => $absensiList
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    

    /**
     * Memperbaharui data absensi siswa
     */
    public function updateStudentAttendance(Request $request, $id)
    {
        try {
            $user = Auth::user();
            $sekolahId = $user->sekolah_id;

            $validator = Validator::make($request->all(), [
                'hadir' => 'required|integer|min:0',
                'izin' => 'required|integer|min:0',
                'sakit' => 'required|integer|min:0',
                'absen' => 'required|integer|min:0',
                'keterangan' => 'nullable|string|max:255'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Validasi gagal',
                    'errors' => $validator->errors()
                ], 422);
            }

            $absensi = AbsensiSiswa::where('id', $id)
                        ->where('sekolah_id', $sekolahId)
                        ->first();

            if (!$absensi) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Data absensi tidak ditemukan'
                ], 404);
            }

            $absensi->hadir = $request->hadir;
            $absensi->izin = $request->izin;
            $absensi->sakit = $request->sakit;
            $absensi->absen = $request->absen;
            $absensi->keterangan = $request->keterangan;
            $absensi->save();

            return response()->json([
                'status' => 'success',
                'message' => 'Data absensi berhasil diperbarui',
                'data' => $absensi
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Menampilkan rekap absensi
     */
    public function summary(Request $request)
    {
        try {
            $user = Auth::user();
            $sekolahId = $user->sekolah_id;
            
            $kelasId = $request->input('kelas_id');
            $bulan = $request->input('bulan');
            $tahun = $request->input('tahun');
            
            if (!$kelasId) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Parameter kelas_id diperlukan'
                ], 422);
            }

            $query = DB::table('absensi_siswa')
                    ->join('siswa', 'absensi_siswa.siswa_id', '=', 'siswa.id')
                    ->join('pertemuan_bulanan', 'absensi_siswa.pertemuan_id', '=', 'pertemuan_bulanan.id')
                    ->where('siswa.kelas_id', $kelasId)
                    ->where('absensi_siswa.sekolah_id', $sekolahId)
                    ->select(
                        'siswa.id as siswa_id',
                        'siswa.nama',
                        'siswa.nisn',
                        DB::raw('SUM(absensi_siswa.hadir) as total_hadir'),
                        DB::raw('SUM(absensi_siswa.izin) as total_izin'),
                        DB::raw('SUM(absensi_siswa.sakit) as total_sakit'),
                        DB::raw('SUM(absensi_siswa.absen) as total_absen'),
                        DB::raw('GROUP_CONCAT(DISTINCT pertemuan_bulanan.bulan) as bulan_list')
                    )
                    ->groupBy('siswa.id', 'siswa.nama', 'siswa.nisn');

            if ($bulan) {
                $query->where('pertemuan_bulanan.bulan', $bulan);
            }

            if ($tahun) {
                $query->where('pertemuan_bulanan.tahun', $tahun);
            }

            $rekap = $query->get();

            // Dapatkan detail kelas
            $kelas = Kelas::find($kelasId);

            return response()->json([
                'status' => 'success',
                'data' => $rekap,
                'kelas' => $kelas ? ['id' => $kelas->id, 'nama' => $kelas->nama_kelas, 'tingkat' => $kelas->tingkat] : null
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }
}
