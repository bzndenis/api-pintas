<?php

namespace App\Http\Controllers\Guru;

use App\Models\NilaiSiswa;
use App\Models\Siswa;
use App\Models\TujuanPembelajaran;
use Illuminate\Http\Request;
use App\Http\Helper\ResponseBuilder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Uuid;
use App\Models\CapaianPembelajaran;
use App\Models\Kelas;

class NilaiController extends BaseGuruController
{
    public function index(Request $request)
    {
        try {
            $guru = Auth::user()->guru;
            
            $query = NilaiSiswa::with([
                'siswa.kelas',
                'tujuanPembelajaran.capaianPembelajaran.mataPelajaran'
            ])->whereHas('tujuanPembelajaran.capaianPembelajaran.mataPelajaran', function($q) use ($guru) {
                $q->where('guru_id', $guru->id);
            });
            
            // Filter berdasarkan kelas
            if ($request->kelas_id) {
                $query->whereHas('siswa', function($q) use ($request) {
                    $q->where('kelas_id', $request->kelas_id);
                });
            }
            
            // Filter berdasarkan semester
            if ($request->semester) {
                $query->where('semester', $request->semester);
            }
            
            $nilai = $query->orderBy('created_at', 'desc')->get();
            
            return ResponseBuilder::success(200, "Berhasil mendapatkan data nilai", $nilai);
        } catch (\Exception $e) {
            return ResponseBuilder::error(500, "Gagal mendapatkan data: " . $e->getMessage());
        }
    }

    public function store(Request $request)
    {
        $this->validate($request, [
            'siswa_id' => 'required|exists:siswa,id',
            'tujuan_pembelajaran_id' => 'required|exists:tujuan_pembelajaran,id',
            'nilai' => 'required|numeric|min:0|max:100',
            'semester' => 'required|in:1,2',
            'jenis_nilai' => 'required|in:UH,STS,SAS',
            'nomor_uh' => 'required_if:jenis_nilai,UH|nullable|integer|min:1|max:7',
            'keterangan' => 'nullable|string'
        ]);

        try {
            DB::beginTransaction();
            
            $guru = Auth::user()->guru;
            
            // Tambahan validasi untuk mencegah duplikasi nilai
            $existingNilai = NilaiSiswa::where('siswa_id', $request->siswa_id)
                ->where('tujuan_pembelajaran_id', $request->tujuan_pembelajaran_id)
                ->where('jenis_nilai', $request->jenis_nilai)
                ->where('nomor_uh', $request->nomor_uh)
                ->exists();
                
            if ($existingNilai) {
                return ResponseBuilder::error(400, "Nilai untuk sesi ini sudah ada");
            }

            // Proses penyimpanan nilai
            $nilai = NilaiSiswa::create([
                'siswa_id' => $request->siswa_id,
                'tujuan_pembelajaran_id' => $request->tujuan_pembelajaran_id,
                'nilai' => $request->nilai,
                'semester' => $request->semester,
                'jenis_nilai' => $request->jenis_nilai,
                'nomor_uh' => $request->nomor_uh,
                'guru_id' => $guru->id,
                'sekolah_id' => $guru->sekolah_id
            ]);

            DB::commit();
            return ResponseBuilder::success(201, "Berhasil menambahkan nilai", $nilai);
        } catch (\Exception $e) {
            DB::rollBack();
            return ResponseBuilder::error(500, "Gagal menambahkan nilai: " . $e->getMessage());
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
            
            $nilai = NilaiSiswa::with([
                'siswa.kelas',
                'tujuanPembelajaran.capaianPembelajaran.mataPelajaran'
            ])->whereHas('tujuanPembelajaran.capaianPembelajaran.mataPelajaran', function($q) use ($guru) {
                $q->where('guru_id', $guru->id);
            })->find($id);
            
            if (!$nilai) {
                return ResponseBuilder::error(404, "Data nilai tidak ditemukan");
            }
            
            return ResponseBuilder::success(200, "Berhasil mendapatkan data nilai", ['nilai' => $nilai]);
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
            
            // Validasi input
            $this->validate($request, [
                'nilai' => 'required|numeric|min:0|max:100',
                'keterangan' => 'nullable|string'
            ]);

            $guru = Auth::user()->guru;
            
            $nilai = NilaiSiswa::whereHas('tujuanPembelajaran.capaianPembelajaran.mataPelajaran', 
                function($q) use ($guru) {
                    $q->where('guru_id', $guru->id);
                })->find($id);
            
            if (!$nilai) {
                return ResponseBuilder::error(404, "Data nilai tidak ditemukan");
            }
            
            $nilai->update($request->only(['nilai', 'keterangan']));
            
            return ResponseBuilder::success(200, "Berhasil mengupdate nilai", ['nilai' => $nilai]);
        } catch (\Exception $e) {
            return ResponseBuilder::error(500, "Gagal mengupdate nilai: " . $e->getMessage());
        }
    }

    /**
     * Menyimpan nilai siswa secara batch
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function storeBatch(Request $request)
    {
        $this->validate($request, [
            'nilai_batch' => 'required|array|min:1',
            'nilai_batch.*.siswa_id' => 'required|exists:siswa,id',
            'nilai_batch.*.tujuan_pembelajaran_id' => 'required|exists:tujuan_pembelajaran,id',
            'nilai_batch.*.nilai' => 'required|numeric|min:0|max:100',
            'nilai_batch.*.semester' => 'required|in:1,2',
            'nilai_batch.*.jenis_nilai' => 'required|in:UH,STS,SAS',
            'nilai_batch.*.nomor_uh' => 'required_if:nilai_batch.*.jenis_nilai,UH|nullable|integer|min:1|max:3',
            'nilai_batch.*.keterangan' => 'nullable|string'
        ]);

        try {
            DB::beginTransaction();
            
            $guru = Auth::user()->guru;
            $nilaiBatch = $request->nilai_batch;
            $hasilInput = [];
            $errors = [];
            
            foreach ($nilaiBatch as $index => $nilaiData) {
                try {
                    // Validasi apakah guru mengajar mata pelajaran tersebut
                    $tp = TujuanPembelajaran::with('capaianPembelajaran.mataPelajaran')
                        ->find($nilaiData['tujuan_pembelajaran_id']);
                        
                    if (!$tp || $tp->capaianPembelajaran->mataPelajaran->guru_id !== $guru->id) {
                        $errors[] = [
                            'index' => $index,
                            'message' => "Anda tidak memiliki akses untuk menilai mata pelajaran ini pada data ke-" . ($index + 1)
                        ];
                        continue;
                    }

                    // Validasi jumlah UH per bab
                    if ($nilaiData['jenis_nilai'] === 'UH') {
                        $existingUH = NilaiSiswa::where('siswa_id', $nilaiData['siswa_id'])
                            ->where('semester', $nilaiData['semester'])
                            ->where('jenis_nilai', 'UH')
                            ->where('nomor_uh', $nilaiData['nomor_uh'])
                            ->whereHas('tujuanPembelajaran', function($q) use ($tp) {
                                $q->where('capaian_pembelajaran_id', $tp->capaian_pembelajaran_id);
                            })
                            ->exists();

                        if ($existingUH) {
                            $errors[] = [
                                'index' => $index,
                                'message' => "Nilai UH {$nilaiData['nomor_uh']} untuk siswa ini sudah ada pada data ke-" . ($index + 1)
                            ];
                            continue;
                        }
                    }
                    
                    // Buat nilai baru
                    $nilai = NilaiSiswa::create([
                        'siswa_id' => $nilaiData['siswa_id'],
                        'tujuan_pembelajaran_id' => $nilaiData['tujuan_pembelajaran_id'],
                        'nilai' => $nilaiData['nilai'],
                        'semester' => $nilaiData['semester'],
                        'jenis_nilai' => $nilaiData['jenis_nilai'],
                        'nomor_uh' => $nilaiData['nomor_uh'] ?? null,
                        'keterangan' => $nilaiData['keterangan'] ?? null,
                        'guru_id' => $guru->id,
                        'sekolah_id' => $guru->sekolah_id
                    ]);
                    
                    $hasilInput[] = $nilai;
                    
                } catch (\Exception $e) {
                    $errors[] = [
                        'index' => $index,
                        'message' => "Error pada data ke-" . ($index + 1) . ": " . $e->getMessage()
                    ];
                }
            }
            
            // Jika semua data error, rollback transaksi
            if (count($errors) === count($nilaiBatch)) {
                DB::rollBack();
                return ResponseBuilder::error(400, "Gagal menyimpan semua data nilai", ['errors' => $errors]);
            }
            
            DB::commit();
            
            return ResponseBuilder::success(201, "Berhasil menyimpan data nilai batch", [
                'nilai' => $hasilInput,
                'total_success' => count($hasilInput),
                'total_error' => count($errors),
                'errors' => $errors
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            return ResponseBuilder::error(500, "Gagal menyimpan data nilai batch: " . $e->getMessage());
        }
    }

    /**
     * Menghasilkan template untuk input nilai
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getTemplate(Request $request)
    {
        $this->validate($request, [
            'kelas_id' => 'required|exists:kelas,id',
            'capaian_pembelajaran_id' => 'required|exists:capaian_pembelajaran,id',
            'jenis_nilai' => 'required|in:UH,STS,SAS',
            'nomor_uh' => 'required_if:jenis_nilai,UH|nullable|integer|min:1|max:3',
            'semester' => 'required|in:1,2'
        ]);

        try {
            $guru = Auth::user()->guru;
            
            // Validasi apakah guru mengajar mata pelajaran dari CP ini
            $cp = CapaianPembelajaran::with('mataPelajaran')
                ->find($request->capaian_pembelajaran_id);
                
            if (!$cp || $cp->mataPelajaran->guru_id !== $guru->id) {
                return ResponseBuilder::error(403, "Anda tidak memiliki akses untuk capaian pembelajaran ini");
            }
            
            // Dapatkan semua siswa di kelas
            $siswa = Siswa::where('kelas_id', $request->kelas_id)
                ->where('sekolah_id', Auth::user()->sekolah_id)
                ->where('is_active', 1)
                ->orderBy('nama', 'asc')
                ->get(['id', 'nama', 'nis', 'nisn']);
                
            if ($siswa->isEmpty()) {
                return ResponseBuilder::error(404, "Tidak ada siswa di kelas ini");
            }
            
            // Dapatkan semua tujuan pembelajaran dari CP ini
            $tujuanPembelajaran = TujuanPembelajaran::where('capaian_pembelajaran_id', $request->capaian_pembelajaran_id)
                ->where('sekolah_id', Auth::user()->sekolah_id)
                ->get(['id', 'kode_tp', 'deskripsi']);
                
            if ($tujuanPembelajaran->isEmpty()) {
                return ResponseBuilder::error(404, "Tidak ada tujuan pembelajaran untuk capaian pembelajaran ini");
            }
            
            // Buat template data
            $templateData = [];
            foreach ($siswa as $s) {
                foreach ($tujuanPembelajaran as $tp) {
                    // Cek apakah nilai sudah ada
                    $existingNilai = NilaiSiswa::where('siswa_id', $s->id)
                        ->where('tujuan_pembelajaran_id', $tp->id)
                        ->where('semester', $request->semester)
                        ->where('jenis_nilai', $request->jenis_nilai)
                        ->where(function($query) use ($request) {
                            if ($request->jenis_nilai === 'UH') {
                                $query->where('nomor_uh', $request->nomor_uh);
                            }
                        })
                        ->first();
                    
                    $templateData[] = [
                        'siswa_id' => $s->id,
                        'nama_siswa' => $s->nama,
                        'nis' => $s->nis,
                        'nisn' => $s->nisn,
                        'tujuan_pembelajaran_id' => $tp->id,
                        'kode_tp' => $tp->kode_tp,
                        'deskripsi_tp' => $tp->deskripsi,
                        'nilai' => $existingNilai ? $existingNilai->nilai : null,
                        'semester' => $request->semester,
                        'jenis_nilai' => $request->jenis_nilai,
                        'nomor_uh' => $request->jenis_nilai === 'UH' ? $request->nomor_uh : null,
                        'keterangan' => $existingNilai ? $existingNilai->keterangan : null,
                        'status' => $existingNilai ? 'Sudah ada' : 'Belum ada'
                    ];
                }
            }
            
            // Informasi tambahan untuk template
            $templateInfo = [
                'kelas' => Kelas::find($request->kelas_id)->nama_kelas,
                'capaian_pembelajaran' => $cp->deskripsi,
                'mata_pelajaran' => $cp->mataPelajaran->nama_mapel,
                'jenis_nilai' => $request->jenis_nilai,
                'nomor_uh' => $request->jenis_nilai === 'UH' ? $request->nomor_uh : null,
                'semester' => $request->semester,
                'jumlah_siswa' => $siswa->count(),
                'jumlah_tp' => $tujuanPembelajaran->count()
            ];
            
            return ResponseBuilder::success(200, "Berhasil membuat template nilai", [
                'template_info' => $templateInfo,
                'template_data' => $templateData
            ]);
        } catch (\Exception $e) {
            return ResponseBuilder::error(500, "Gagal membuat template: " . $e->getMessage());
        }
    }

    /**
     * Mengimpor nilai dari template
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function import(Request $request)
    {
        $this->validate($request, [
            'nilai_import' => 'required|array|min:1',
            'nilai_import.*.siswa_id' => 'required|exists:siswa,id',
            'nilai_import.*.tujuan_pembelajaran_id' => 'required|exists:tujuan_pembelajaran,id',
            'nilai_import.*.nilai' => 'required|numeric|min:0|max:100',
            'nilai_import.*.semester' => 'required|in:1,2',
            'nilai_import.*.jenis_nilai' => 'required|in:UH,STS,SAS',
            'nilai_import.*.nomor_uh' => 'required_if:nilai_import.*.jenis_nilai,UH|nullable|integer|min:1|max:3',
            'nilai_import.*.keterangan' => 'nullable|string'
        ]);

        try {
            DB::beginTransaction();
            
            $guru = Auth::user()->guru;
            $nilaiImport = $request->nilai_import;
            $hasilInput = [];
            $hasilUpdate = [];
            $errors = [];
            
            foreach ($nilaiImport as $index => $nilaiData) {
                try {
                    // Validasi apakah guru mengajar mata pelajaran tersebut
                    $tp = TujuanPembelajaran::with('capaianPembelajaran.mataPelajaran')
                        ->find($nilaiData['tujuan_pembelajaran_id']);
                        
                    if (!$tp || $tp->capaianPembelajaran->mataPelajaran->guru_id !== $guru->id) {
                        $errors[] = [
                            'index' => $index,
                            'message' => "Anda tidak memiliki akses untuk menilai mata pelajaran ini pada data ke-" . ($index + 1)
                        ];
                        continue;
                    }

                    // Cek apakah nilai sudah ada
                    $existingNilai = NilaiSiswa::where('siswa_id', $nilaiData['siswa_id'])
                        ->where('tujuan_pembelajaran_id', $nilaiData['tujuan_pembelajaran_id'])
                        ->where('semester', $nilaiData['semester'])
                        ->where('jenis_nilai', $nilaiData['jenis_nilai'])
                        ->where(function($query) use ($nilaiData) {
                            if ($nilaiData['jenis_nilai'] === 'UH') {
                                $query->where('nomor_uh', $nilaiData['nomor_uh']);
                            }
                        })
                        ->first();
                    
                    if ($existingNilai) {
                        // Update nilai yang sudah ada
                        $existingNilai->update([
                            'nilai' => $nilaiData['nilai'],
                            'keterangan' => $nilaiData['keterangan'] ?? null
                        ]);
                        
                        $hasilUpdate[] = $existingNilai;
                    } else {
                        // Buat nilai baru
                        $nilai = NilaiSiswa::create([
                            'siswa_id' => $nilaiData['siswa_id'],
                            'tujuan_pembelajaran_id' => $nilaiData['tujuan_pembelajaran_id'],
                            'nilai' => $nilaiData['nilai'],
                            'semester' => $nilaiData['semester'],
                            'jenis_nilai' => $nilaiData['jenis_nilai'],
                            'nomor_uh' => $nilaiData['nomor_uh'] ?? null,
                            'keterangan' => $nilaiData['keterangan'] ?? null,
                            'guru_id' => $guru->id,
                            'sekolah_id' => $guru->sekolah_id
                        ]);
                        
                        $hasilInput[] = $nilai;
                    }
                    
                } catch (\Exception $e) {
                    $errors[] = [
                        'index' => $index,
                        'message' => "Error pada data ke-" . ($index + 1) . ": " . $e->getMessage()
                    ];
                }
            }
            
            // Jika semua data error, rollback transaksi
            if (count($errors) === count($nilaiImport)) {
                DB::rollBack();
                return ResponseBuilder::error(400, "Gagal menyimpan semua data nilai", ['errors' => $errors]);
            }
            
            DB::commit();
            
            return ResponseBuilder::success(201, "Berhasil mengimpor data nilai", [
                'nilai_baru' => $hasilInput,
                'nilai_diupdate' => $hasilUpdate,
                'total_success' => count($hasilInput) + count($hasilUpdate),
                'total_baru' => count($hasilInput),
                'total_diupdate' => count($hasilUpdate),
                'total_error' => count($errors),
                'errors' => $errors
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            return ResponseBuilder::error(500, "Gagal mengimpor data nilai: " . $e->getMessage());
        }
    }

    public function rekapNilai(Request $request)
    {
        try {
            $guru = Auth::user()->guru;
            
            $nilaiSiswa = NilaiSiswa::with(['siswa'])
                ->where('guru_id', $guru->id)
                ->where('semester', $request->semester)
                ->get()
                ->groupBy('siswa_id')
                ->map(function($nilai) {
                    $siswa = $nilai->first()->siswa;
                    return [
                        'nama' => $siswa->nama,
                        'S-1' => $nilai->where('jenis_nilai', 'S-1')->first()->nilai ?? null,
                        'S-2' => $nilai->where('jenis_nilai', 'S-2')->first()->nilai ?? null,
                        'S-3' => $nilai->where('jenis_nilai', 'S-3')->first()->nilai ?? null,
                        'S-4' => $nilai->where('jenis_nilai', 'S-4')->first()->nilai ?? null,
                        'S-5' => $nilai->where('jenis_nilai', 'S-5')->first()->nilai ?? null,
                        'S-6' => $nilai->where('jenis_nilai', 'S-6')->first()->nilai ?? null,
                        'S-7' => $nilai->where('jenis_nilai', 'S-7')->first()->nilai ?? null,
                    ];
                });
                
            return ResponseBuilder::success(200, "Berhasil mendapatkan rekap nilai", $nilaiSiswa);
        } catch (\Exception $e) {
            return ResponseBuilder::error(500, "Gagal mendapatkan data: " . $e->getMessage());
        }
    }
} 