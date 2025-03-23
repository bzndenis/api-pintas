<?php

namespace App\Http\Controllers\Guru;

use App\Models\AbsensiSiswa;
use App\Models\Pertemuan;
use Illuminate\Http\Request;
use App\Http\Helper\ResponseBuilder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Uuid;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Illuminate\Support\Facades\Storage;

class AbsensiController extends BaseGuruController
{
    public function index(Request $request)
    {
        try {
            $guru = Auth::user()->guru;
            
            $query = AbsensiSiswa::with(['siswa.kelas', 'pertemuan.mataPelajaran'])
                ->whereHas('pertemuan.mataPelajaran', function($q) use ($guru) {
                    $q->where('guru_id', $guru->id);
                });
            
            // Filter berdasarkan kelas
            if ($request->kelas_id) {
                $query->whereHas('siswa', function($q) use ($request) {
                    $q->where('kelas_id', $request->kelas_id);
                });
            }
            
            // Filter berdasarkan tanggal
            if ($request->tanggal) {
                $query->whereHas('pertemuan', function($q) use ($request) {
                    $q->whereDate('tanggal', Carbon::parse($request->tanggal));
                });
            }
            
            $absensi = $query->orderBy('created_at', 'desc')->get();
            
            return ResponseBuilder::success(200, "Berhasil mendapatkan data absensi", $absensi);
        } catch (\Exception $e) {
            return ResponseBuilder::error(500, "Gagal mendapatkan data: " . $e->getMessage());
        }
    }

    public function store(Request $request)
    {
        $this->validate($request, [
            'kelas_id' => 'required|exists:kelas,id',
            'mata_pelajaran_id' => 'required|exists:mata_pelajaran,id',
            'tanggal' => 'required|date',
            'pertemuan_ke' => 'required|integer',
            'materi' => 'required|string',
            'absensi' => 'required|array',
            'absensi.*.siswa_id' => 'required|exists:siswa,id',
            'absensi.*.status' => 'required|in:hadir,izin,sakit,alpha'
        ]);

        try {
            DB::beginTransaction();
            
            $guru = Auth::user()->guru;
            
            // Buat pertemuan baru
            $pertemuan = Pertemuan::create([
                'kelas_id' => $request->kelas_id,
                'mata_pelajaran_id' => $request->mata_pelajaran_id,
                'guru_id' => $guru->id,
                'tanggal' => Carbon::parse($request->tanggal),
                'pertemuan_ke' => $request->pertemuan_ke,
                'materi' => $request->materi,
                'sekolah_id' => $guru->sekolah_id
            ]);
            
            // Simpan absensi siswa
            foreach ($request->absensi as $absen) {
                AbsensiSiswa::create([
                    'pertemuan_id' => $pertemuan->id,
                    'siswa_id' => $absen['siswa_id'],
                    'status' => $absen['status'],
                    'keterangan' => $absen['keterangan'] ?? null,
                    'sekolah_id' => $guru->sekolah_id
                ]);
            }
            
            DB::commit();
            
            return ResponseBuilder::success(201, "Berhasil menyimpan absensi");
        } catch (\Exception $e) {
            DB::rollBack();
            return ResponseBuilder::error(500, "Gagal menyimpan absensi: " . $e->getMessage());
        }
    }

    public function show($id)
    {
        try {
            // Validasi format UUID
            if (!Uuid::isValid($id)) {
                return ResponseBuilder::error(400, "Format ID tidak valid");
            }

            $guru = Auth::user()->guru;
            
            $absensi = AbsensiSiswa::with(['siswa.kelas', 'pertemuan.mataPelajaran'])
                ->whereHas('pertemuan.mataPelajaran', function($q) use ($guru) {
                    $q->where('guru_id', $guru->id);
                })->find($id);
            
            if (!$absensi) {
                return ResponseBuilder::error(404, "Data absensi tidak ditemukan");
            }
            
            return ResponseBuilder::success(200, "Berhasil mendapatkan data absensi", ['absensi' => $absensi]);
        } catch (\Exception $e) {
            return ResponseBuilder::error(500, "Gagal mendapatkan data: " . $e->getMessage());
        }
    }

    public function update(Request $request, $id)
    {
        try {
            // Validasi format UUID
            if (!Uuid::isValid($id)) {
                return ResponseBuilder::error(400, "Format ID tidak valid");
            }

            $guru = Auth::user()->guru;
            
            $absensi = AbsensiSiswa::whereHas('pertemuan.mataPelajaran', function($q) use ($guru) {
                $q->where('guru_id', $guru->id);
            })->find($id);
            
            if (!$absensi) {
                return ResponseBuilder::error(404, "Data absensi tidak ditemukan");
            }
            
            $absensi->update($request->only(['status', 'keterangan']));
            
            return ResponseBuilder::success(200, "Berhasil mengupdate absensi", ['absensi' => $absensi]);
        } catch (\Exception $e) {
            return ResponseBuilder::error(500, "Gagal mengupdate absensi: " . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        try {
            // Validasi format UUID
            if (!Uuid::isValid($id)) {
                return ResponseBuilder::error(400, "Format ID tidak valid");
            }

            $guru = Auth::user()->guru;
            
            $absensi = AbsensiSiswa::whereHas('pertemuan.mataPelajaran', function($q) use ($guru) {
                $q->where('guru_id', $guru->id);
            })->find($id);
            
            if (!$absensi) {
                return ResponseBuilder::error(404, "Data absensi tidak ditemukan");
            }
            
            // Hapus data absensi
            $absensi->delete();
            
            return ResponseBuilder::success(200, "Berhasil menghapus data absensi");
        } catch (\Exception $e) {
            return ResponseBuilder::error(500, "Gagal menghapus data: " . $e->getMessage());
        }
    }

    public function export(Request $request)
    {
        try {
            $guru = Auth::user()->guru;
            
            $query = AbsensiSiswa::with(['siswa.kelas', 'pertemuan.mataPelajaran'])
                ->whereHas('pertemuan.mataPelajaran', function($q) use ($guru) {
                    $q->where('guru_id', $guru->id);
                });
            
            // Filter berdasarkan kelas
            if ($request->kelas_id) {
                $query->whereHas('siswa', function($q) use ($request) {
                    $q->where('kelas_id', $request->kelas_id);
                });
            }
            
            // Filter berdasarkan tanggal
            if ($request->start_date && $request->end_date) {
                $query->whereHas('pertemuan', function($q) use ($request) {
                    $q->whereBetween('tanggal', [
                        Carbon::parse($request->start_date),
                        Carbon::parse($request->end_date)
                    ]);
                });
            }
            
            $absensi = $query->orderBy('created_at', 'desc')->get();
            
            // Buat spreadsheet baru
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            
            // Set header
            $sheet->setCellValue('A1', 'No');
            $sheet->setCellValue('B1', 'Nama Siswa');
            $sheet->setCellValue('C1', 'Kelas');
            $sheet->setCellValue('D1', 'Mata Pelajaran');
            $sheet->setCellValue('E1', 'Tanggal');
            $sheet->setCellValue('F1', 'Pertemuan Ke');
            $sheet->setCellValue('G1', 'Status');
            $sheet->setCellValue('H1', 'Keterangan');
            
            // Isi data
            $row = 2;
            foreach ($absensi as $index => $item) {
                $sheet->setCellValue('A' . $row, $index + 1);
                $sheet->setCellValue('B' . $row, $item->siswa->nama);
                $sheet->setCellValue('C' . $row, $item->siswa->kelas->nama_kelas);
                $sheet->setCellValue('D' . $row, $item->pertemuan->mataPelajaran->nama_mapel);
                $sheet->setCellValue('E' . $row, $item->pertemuan->tanggal->format('d-m-Y'));
                $sheet->setCellValue('F' . $row, $item->pertemuan->pertemuan_ke);
                $sheet->setCellValue('G' . $row, ucfirst($item->status));
                $sheet->setCellValue('H' . $row, $item->keterangan);
                $row++;
            }
            
            // Simpan file
            $filename = 'rekap_absensi_' . date('YmdHis') . '.xlsx';
            $writer = new Xlsx($spreadsheet);
            $path = 'exports/' . $filename;
            
            Storage::makeDirectory('exports');
            $writer->save(storage_path('app/public/' . $path));
            
            return ResponseBuilder::success(200, "Berhasil mengekspor data absensi", [
                'file_url' => url('storage/' . $path)
            ]);
        } catch (\Exception $e) {
            return ResponseBuilder::error(500, "Gagal mengekspor data: " . $e->getMessage());
        }
    }

    public function batchUpdate(Request $request)
    {
        $this->validate($request, [
            'absensi' => 'required|array',
            'absensi.*.id' => 'required|exists:absensi_siswa,id',
            'absensi.*.status' => 'required|in:hadir,izin,sakit,alpha',
            'absensi.*.keterangan' => 'nullable|string'
        ]);

        try {
            DB::beginTransaction();
            
            $guru = Auth::user()->guru;
            $updated = 0;
            
            foreach ($request->absensi as $absen) {
                $absensi = AbsensiSiswa::whereHas('pertemuan.mataPelajaran', function($q) use ($guru) {
                    $q->where('guru_id', $guru->id);
                })->find($absen['id']);
                
                if ($absensi) {
                    $absensi->update([
                        'status' => $absen['status'],
                        'keterangan' => $absen['keterangan'] ?? null
                    ]);
                    $updated++;
                }
            }
            
            DB::commit();
            
            return ResponseBuilder::success(200, "Berhasil mengupdate {$updated} data absensi");
        } catch (\Exception $e) {
            DB::rollBack();
            return ResponseBuilder::error(500, "Gagal mengupdate absensi: " . $e->getMessage());
        }
    }

    public function rekapBulanan(Request $request)
    {
        try {
            $guru = Auth::user()->guru;
            
            $bulan = $request->bulan ?? date('m');
            $tahun = $request->tahun ?? date('Y');
            
            $query = AbsensiSiswa::with(['siswa.kelas', 'pertemuan.mataPelajaran'])
                ->whereHas('pertemuan.mataPelajaran', function($q) use ($guru) {
                    $q->where('guru_id', $guru->id);
                })
                ->whereHas('pertemuan', function($q) use ($bulan, $tahun) {
                    $q->whereMonth('tanggal', $bulan)
                      ->whereYear('tanggal', $tahun);
                });
            
            // Filter berdasarkan kelas
            if ($request->kelas_id) {
                $query->whereHas('siswa', function($q) use ($request) {
                    $q->where('kelas_id', $request->kelas_id);
                });
            }
            
            $absensi = $query->get();
            
            // Hitung statistik
            $statistik = [
                'total_pertemuan' => $absensi->pluck('pertemuan_id')->unique()->count(),
                'total_siswa' => $absensi->pluck('siswa_id')->unique()->count(),
                'rekap_status' => [
                    'hadir' => $absensi->where('status', 'hadir')->count(),
                    'izin' => $absensi->where('status', 'izin')->count(),
                    'sakit' => $absensi->where('status', 'sakit')->count(),
                    'alpha' => $absensi->where('status', 'alpha')->count(),
                ]
            ];
            
            // Kelompokkan berdasarkan siswa
            $rekapSiswa = [];
            foreach ($absensi as $absen) {
                $siswaId = $absen->siswa_id;
                
                if (!isset($rekapSiswa[$siswaId])) {
                    $rekapSiswa[$siswaId] = [
                        'siswa' => [
                            'id' => $absen->siswa->id,
                            'nama' => $absen->siswa->nama,
                            'kelas' => $absen->siswa->kelas->nama_kelas
                        ],
                        'rekap' => [
                            'hadir' => 0,
                            'izin' => 0,
                            'sakit' => 0,
                            'alpha' => 0
                        ]
                    ];
                }
                
                $rekapSiswa[$siswaId]['rekap'][$absen->status]++;
            }
            
            return ResponseBuilder::success(200, "Berhasil mendapatkan rekap bulanan", [
                'bulan' => $bulan,
                'tahun' => $tahun,
                'statistik' => $statistik,
                'rekap_siswa' => array_values($rekapSiswa)
            ]);
        } catch (\Exception $e) {
            return ResponseBuilder::error(500, "Gagal mendapatkan data: " . $e->getMessage());
        }
    }
} 