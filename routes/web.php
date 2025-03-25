<?php

/** @var \Laravel\Lumen\Routing\Router $router */

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/

// Pindahkan fungsi-fungsi ini ke luar loop
function getParametersFromUri($uri) {
    $params = [];
    $parts = explode('/', $uri);
    
    foreach ($parts as $part) {
        if (strpos($part, '{') !== false && strpos($part, '}') !== false) {
            $paramName = trim($part, '{}');
            $params[] = [
                'name' => $paramName,
                'type' => 'path',
                'required' => true,
                'description' => 'ID atau nilai ' . str_replace('_', ' ', $paramName)
            ];
        }
    }
    
    return $params;
}

function getEndpointUsageInfo($method, $uri, $baseUrl) {
    $fullUrl = $baseUrl . '/' . ltrim($uri, '/');
    $params = getParametersFromUri($uri);
    
    // Tambahkan informasi tipe data yang sesuai dengan model
    foreach ($params as $key => $param) {
        $paramName = $param['name'];
        
        // Sesuaikan tipe data berdasarkan nama parameter
        if ($paramName === 'id' || strpos($paramName, '_id') !== false) {
            $params[$key]['type'] = 'string (UUID)';
            $params[$key]['description'] = 'UUID dari ' . str_replace(['_id', '_'], ['', ' '], $paramName);
        }
        
        // Tambahkan informasi spesifik berdasarkan endpoint
        if (strpos($uri, 'sekolah') !== false && $paramName === 'id') {
            $params[$key]['description'] = 'UUID dari sekolah';
        } elseif (strpos($uri, 'guru') !== false && $paramName === 'id') {
            $params[$key]['description'] = 'UUID dari guru';
        } elseif (strpos($uri, 'siswa') !== false && $paramName === 'id') {
            $params[$key]['description'] = 'UUID dari siswa';
        } elseif (strpos($uri, 'kelas') !== false && $paramName === 'id') {
            $params[$key]['description'] = 'UUID dari kelas';
        } elseif (strpos($uri, 'mapel') !== false && $paramName === 'id') {
            $params[$key]['description'] = 'UUID dari mata pelajaran';
        } elseif (strpos($uri, 'cp') !== false && $paramName === 'id') {
            $params[$key]['description'] = 'UUID dari capaian pembelajaran';
        } elseif (strpos($uri, 'tp') !== false && $paramName === 'id') {
            $params[$key]['description'] = 'UUID dari tujuan pembelajaran';
        } elseif (strpos($uri, 'nilai') !== false && $paramName === 'id') {
            $params[$key]['description'] = 'UUID dari nilai siswa';
        } elseif (strpos($uri, 'absensi') !== false && $paramName === 'id') {
            $params[$key]['description'] = 'UUID dari absensi siswa';
        } elseif (strpos($uri, 'pertemuan') !== false && $paramName === 'id') {
            $params[$key]['description'] = 'UUID dari pertemuan bulanan';
        } elseif (strpos($uri, 'tahun-ajaran') !== false && $paramName === 'id') {
            $params[$key]['description'] = 'UUID dari tahun ajaran';
        }
    }
    
    $headers = [
        ['name' => 'Accept', 'value' => 'application/json'],
        ['name' => 'Content-Type', 'value' => 'application/json']
    ];
    
    if (strpos($uri, 'auth/login') === false && strpos($uri, 'auth/register') === false) {
        $headers[] = ['name' => 'Authorization', 'value' => 'Bearer {your_token}'];
    }
    
    $bodyParams = [];
    if ($method === 'POST' || $method === 'PUT' || $method === 'PATCH') {
        // Tambahkan contoh body parameter berdasarkan URI
        if (strpos($uri, 'auth/login') !== false) {
            $bodyParams = [
                ['name' => 'email', 'type' => 'string', 'required' => true, 'description' => 'Email pengguna'],
                ['name' => 'password', 'type' => 'string', 'required' => true, 'description' => 'Password pengguna']
            ];
        } elseif (strpos($uri, 'auth/register') !== false) {
            $bodyParams = [
                ['name' => 'nama_lengkap', 'type' => 'string', 'required' => true, 'description' => 'Nama lengkap'],
                ['name' => 'email', 'type' => 'string', 'required' => true, 'description' => 'Email pengguna'],
                ['name' => 'password', 'type' => 'string', 'required' => true, 'description' => 'Password (min. 8 karakter)'],
                ['name' => 'sekolah_id', 'type' => 'string (UUID)', 'required' => true, 'description' => 'UUID sekolah']
            ];
        } elseif (strpos($uri, 'user/change-password') !== false) {
            $bodyParams = [
                ['name' => 'current_password', 'type' => 'string', 'required' => true, 'description' => 'Password saat ini'],
                ['name' => 'new_password', 'type' => 'string', 'required' => true, 'description' => 'Password baru'],
                ['name' => 'new_password_confirmation', 'type' => 'string', 'required' => true, 'description' => 'Konfirmasi password baru']
            ];
        } elseif (strpos($uri, 'guru') !== false) {
            $bodyParams = [
                ['name' => 'nama', 'type' => 'string', 'required' => true, 'description' => 'Nama guru'],
                ['name' => 'nip', 'type' => 'string', 'required' => false, 'description' => 'NIP guru'],
                ['name' => 'email', 'type' => 'string', 'required' => true, 'description' => 'Email guru'],
                ['name' => 'no_telp', 'type' => 'string', 'required' => false, 'description' => 'Nomor telepon guru'],
                ['name' => 'sekolah_id', 'type' => 'string (UUID)', 'required' => true, 'description' => 'UUID sekolah']
            ];
        } elseif (strpos($uri, 'siswa') !== false) {
            $bodyParams = [
                ['name' => 'nama', 'type' => 'string', 'required' => true, 'description' => 'Nama siswa'],
                ['name' => 'nis', 'type' => 'string', 'required' => true, 'description' => 'NIS siswa'],
                ['name' => 'nisn', 'type' => 'string', 'required' => false, 'description' => 'NISN siswa'],
                ['name' => 'jenis_kelamin', 'type' => 'enum', 'required' => true, 'description' => 'Jenis kelamin (L/P)'],
                ['name' => 'tempat_lahir', 'type' => 'string', 'required' => true, 'description' => 'Tempat lahir'],
                ['name' => 'tanggal_lahir', 'type' => 'date', 'required' => true, 'description' => 'Tanggal lahir (YYYY-MM-DD)'],
                ['name' => 'alamat', 'type' => 'string', 'required' => false, 'description' => 'Alamat siswa'],
                ['name' => 'nama_ortu', 'type' => 'string', 'required' => false, 'description' => 'Nama orang tua'],
                ['name' => 'no_telp_ortu', 'type' => 'string', 'required' => false, 'description' => 'Nomor telepon orang tua'],
                ['name' => 'kelas_id', 'type' => 'string (UUID)', 'required' => true, 'description' => 'UUID kelas'],
                ['name' => 'sekolah_id', 'type' => 'string (UUID)', 'required' => true, 'description' => 'UUID sekolah']
            ];
        } elseif (strpos($uri, 'mapel') !== false) {
            $bodyParams = [
                ['name' => 'kode_mapel', 'type' => 'string', 'required' => true, 'description' => 'Kode mata pelajaran'],
                ['name' => 'nama_mapel', 'type' => 'string', 'required' => true, 'description' => 'Nama mata pelajaran'],
                ['name' => 'tingkat', 'type' => 'string', 'required' => true, 'description' => 'Tingkat kelas (1-12)'],
                ['name' => 'sekolah_id', 'type' => 'string (UUID)', 'required' => true, 'description' => 'UUID sekolah']
            ];
        } elseif (strpos($uri, 'cp') !== false) {
            $bodyParams = [
                ['name' => 'kode_cp', 'type' => 'string', 'required' => true, 'description' => 'Kode capaian pembelajaran'],
                ['name' => 'deskripsi', 'type' => 'string', 'required' => true, 'description' => 'Deskripsi capaian pembelajaran'],
                ['name' => 'mapel_id', 'type' => 'string (UUID)', 'required' => true, 'description' => 'UUID mata pelajaran'],
                ['name' => 'sekolah_id', 'type' => 'string (UUID)', 'required' => true, 'description' => 'UUID sekolah']
            ];
        } elseif (strpos($uri, 'tp') !== false) {
            $bodyParams = [
                ['name' => 'kode_tp', 'type' => 'string', 'required' => true, 'description' => 'Kode tujuan pembelajaran'],
                ['name' => 'deskripsi', 'type' => 'string', 'required' => true, 'description' => 'Deskripsi tujuan pembelajaran'],
                ['name' => 'bobot', 'type' => 'decimal', 'required' => true, 'description' => 'Bobot nilai (0-100)'],
                ['name' => 'cp_id', 'type' => 'string (UUID)', 'required' => true, 'description' => 'UUID capaian pembelajaran'],
                ['name' => 'sekolah_id', 'type' => 'string (UUID)', 'required' => true, 'description' => 'UUID sekolah']
            ];
        } elseif (strpos($uri, 'nilai') !== false) {
            $bodyParams = [
                ['name' => 'siswa_id', 'type' => 'string (UUID)', 'required' => true, 'description' => 'UUID siswa'],
                ['name' => 'tp_id', 'type' => 'string (UUID)', 'required' => true, 'description' => 'UUID tujuan pembelajaran'],
                ['name' => 'nilai', 'type' => 'decimal', 'required' => true, 'description' => 'Nilai (0-100)'],
                ['name' => 'sekolah_id', 'type' => 'string (UUID)', 'required' => true, 'description' => 'UUID sekolah']
            ];
        } elseif (strpos($uri, 'pertemuan') !== false) {
            $bodyParams = [
                ['name' => 'kelas_id', 'type' => 'string (UUID)', 'required' => true, 'description' => 'UUID kelas'],
                ['name' => 'bulan', 'type' => 'integer', 'required' => true, 'description' => 'Bulan (1-12)'],
                ['name' => 'tahun', 'type' => 'integer', 'required' => true, 'description' => 'Tahun (YYYY)'],
                ['name' => 'total_pertemuan', 'type' => 'integer', 'required' => true, 'description' => 'Total pertemuan dalam bulan'],
                ['name' => 'sekolah_id', 'type' => 'string (UUID)', 'required' => true, 'description' => 'UUID sekolah']
            ];
        } elseif (strpos($uri, 'absensi') !== false) {
            $bodyParams = [
                ['name' => 'siswa_id', 'type' => 'string (UUID)', 'required' => true, 'description' => 'UUID siswa'],
                ['name' => 'pertemuan_id', 'type' => 'string (UUID)', 'required' => true, 'description' => 'UUID pertemuan bulanan'],
                ['name' => 'hadir', 'type' => 'integer', 'required' => true, 'description' => 'Jumlah kehadiran'],
                ['name' => 'izin', 'type' => 'integer', 'required' => true, 'description' => 'Jumlah izin'],
                ['name' => 'sakit', 'type' => 'integer', 'required' => true, 'description' => 'Jumlah sakit'],
                ['name' => 'absen', 'type' => 'integer', 'required' => true, 'description' => 'Jumlah absen tanpa keterangan'],
                ['name' => 'sekolah_id', 'type' => 'string (UUID)', 'required' => true, 'description' => 'UUID sekolah']
            ];
        } elseif (strpos($uri, 'tahun-ajaran') !== false) {
            $bodyParams = [
                ['name' => 'nama_tahun_ajaran', 'type' => 'string', 'required' => true, 'description' => 'Nama tahun ajaran (contoh: 2023/2024)'],
                ['name' => 'tanggal_mulai', 'type' => 'date', 'required' => true, 'description' => 'Tanggal mulai (YYYY-MM-DD)'],
                ['name' => 'tanggal_selesai', 'type' => 'date', 'required' => true, 'description' => 'Tanggal selesai (YYYY-MM-DD)'],
                ['name' => 'sekolah_id', 'type' => 'string (UUID)', 'required' => true, 'description' => 'UUID sekolah'],
                ['name' => 'is_active', 'type' => 'boolean', 'required' => false, 'description' => 'Status aktif (true/false)']
            ];
        } elseif (strpos($uri, 'kelas') !== false) {
            $bodyParams = [
                ['name' => 'nama_kelas', 'type' => 'string', 'required' => true, 'description' => 'Nama kelas'],
                ['name' => 'tingkat', 'type' => 'string', 'required' => true, 'description' => 'Tingkat kelas (1-12)'],
                ['name' => 'tahun_ajaran_id', 'type' => 'string (UUID)', 'required' => true, 'description' => 'UUID tahun ajaran'],
                ['name' => 'guru_id', 'type' => 'string (UUID)', 'required' => false, 'description' => 'UUID guru wali kelas'],
                ['name' => 'sekolah_id', 'type' => 'string (UUID)', 'required' => true, 'description' => 'UUID sekolah']
            ];
        } elseif (strpos($uri, 'sekolah') !== false) {
            $bodyParams = [
                ['name' => 'nama_sekolah', 'type' => 'string', 'required' => true, 'description' => 'Nama sekolah'],
                ['name' => 'npsn', 'type' => 'string', 'required' => true, 'description' => 'Nomor Pokok Sekolah Nasional'],
                ['name' => 'alamat', 'type' => 'string', 'required' => true, 'description' => 'Alamat sekolah'],
                ['name' => 'kota', 'type' => 'string', 'required' => true, 'description' => 'Kota'],
                ['name' => 'provinsi', 'type' => 'string', 'required' => true, 'description' => 'Provinsi'],
                ['name' => 'kode_pos', 'type' => 'string', 'required' => false, 'description' => 'Kode pos'],
                ['name' => 'no_telp', 'type' => 'string', 'required' => false, 'description' => 'Nomor telepon'],
                ['name' => 'email', 'type' => 'string', 'required' => false, 'description' => 'Email sekolah'],
                ['name' => 'website', 'type' => 'string', 'required' => false, 'description' => 'Website sekolah'],
                ['name' => 'kepala_sekolah', 'type' => 'string', 'required' => false, 'description' => 'Nama kepala sekolah'],
                ['name' => 'is_active', 'type' => 'boolean', 'required' => false, 'description' => 'Status aktif (true/false)']
            ];
        }
    }
    
    $queryParams = [];
    if ($method === 'GET') {
        $queryParams = [
            ['name' => 'page', 'type' => 'integer', 'required' => false, 'description' => 'Nomor halaman untuk pagination'],
            ['name' => 'per_page', 'type' => 'integer', 'required' => false, 'description' => 'Jumlah item per halaman']
        ];
        
        // Tambahkan filter khusus berdasarkan URI
        if (strpos($uri, 'siswa') !== false) {
            $queryParams[] = ['name' => 'kelas_id', 'type' => 'string (UUID)', 'required' => false, 'description' => 'Filter berdasarkan kelas'];
            $queryParams[] = ['name' => 'nama', 'type' => 'string', 'required' => false, 'description' => 'Filter berdasarkan nama siswa'];
            $queryParams[] = ['name' => 'nis', 'type' => 'string', 'required' => false, 'description' => 'Filter berdasarkan NIS'];
        } elseif (strpos($uri, 'guru') !== false) {
            $queryParams[] = ['name' => 'nama', 'type' => 'string', 'required' => false, 'description' => 'Filter berdasarkan nama guru'];
            $queryParams[] = ['name' => 'nip', 'type' => 'string', 'required' => false, 'description' => 'Filter berdasarkan NIP'];
        } elseif (strpos($uri, 'nilai') !== false) {
            $queryParams[] = ['name' => 'siswa_id', 'type' => 'string (UUID)', 'required' => false, 'description' => 'Filter berdasarkan siswa'];
            $queryParams[] = ['name' => 'tp_id', 'type' => 'string (UUID)', 'required' => false, 'description' => 'Filter berdasarkan tujuan pembelajaran'];
            $queryParams[] = ['name' => 'cp_id', 'type' => 'string (UUID)', 'required' => false, 'description' => 'Filter berdasarkan capaian pembelajaran'];
            $queryParams[] = ['name' => 'mapel_id', 'type' => 'string (UUID)', 'required' => false, 'description' => 'Filter berdasarkan mata pelajaran'];
        } elseif (strpos($uri, 'absensi') !== false) {
            $queryParams[] = ['name' => 'siswa_id', 'type' => 'string (UUID)', 'required' => false, 'description' => 'Filter berdasarkan siswa'];
            $queryParams[] = ['name' => 'pertemuan_id', 'type' => 'string (UUID)', 'required' => false, 'description' => 'Filter berdasarkan pertemuan'];
            $queryParams[] = ['name' => 'bulan', 'type' => 'integer', 'required' => false, 'description' => 'Filter berdasarkan bulan (1-12)'];
            $queryParams[] = ['name' => 'tahun', 'type' => 'integer', 'required' => false, 'description' => 'Filter berdasarkan tahun (YYYY)'];
        } elseif (strpos($uri, 'kelas') !== false) {
            $queryParams[] = ['name' => 'tahun_ajaran_id', 'type' => 'string (UUID)', 'required' => false, 'description' => 'Filter berdasarkan tahun ajaran'];
            $queryParams[] = ['name' => 'tingkat', 'type' => 'string', 'required' => false, 'description' => 'Filter berdasarkan tingkat kelas'];
            $queryParams[] = ['name' => 'guru_id', 'type' => 'string (UUID)', 'required' => false, 'description' => 'Filter berdasarkan guru wali kelas'];
        } elseif (strpos($uri, 'mapel') !== false) {
            $queryParams[] = ['name' => 'tingkat', 'type' => 'string', 'required' => false, 'description' => 'Filter berdasarkan tingkat kelas'];
            $queryParams[] = ['name' => 'nama_mapel', 'type' => 'string', 'required' => false, 'description' => 'Filter berdasarkan nama mata pelajaran'];
        } elseif (strpos($uri, 'cp') !== false) {
            $queryParams[] = ['name' => 'mapel_id', 'type' => 'string (UUID)', 'required' => false, 'description' => 'Filter berdasarkan mata pelajaran'];
        } elseif (strpos($uri, 'tp') !== false) {
            $queryParams[] = ['name' => 'cp_id', 'type' => 'string (UUID)', 'required' => false, 'description' => 'Filter berdasarkan capaian pembelajaran'];
        } elseif (strpos($uri, 'tahun-ajaran') !== false) {
            $queryParams[] = ['name' => 'is_active', 'type' => 'boolean', 'required' => false, 'description' => 'Filter berdasarkan status aktif'];
        }
        
        // Tambahkan filter umum untuk semua endpoint
        $queryParams[] = ['name' => 'sekolah_id', 'type' => 'string (UUID)', 'required' => false, 'description' => 'Filter berdasarkan sekolah'];
        $queryParams[] = ['name' => 'search', 'type' => 'string', 'required' => false, 'description' => 'Pencarian global'];
        $queryParams[] = ['name' => 'sort_by', 'type' => 'string', 'required' => false, 'description' => 'Kolom untuk pengurutan'];
        $queryParams[] = ['name' => 'sort_dir', 'type' => 'string', 'required' => false, 'description' => 'Arah pengurutan (asc/desc)'];
    }
    
    return [
        'url' => $fullUrl,
        'method' => $method,
        'headers' => $headers,
        'path_params' => $params,
        'query_params' => $queryParams,
        'body_params' => $bodyParams
    ];
}

$router->get('/', function () use ($router) {
    $routes = [];
    
    // Dapatkan semua route yang terdaftar
    foreach ($router->getRoutes() as $route) {
        $routes[] = [
            'method' => $route['method'],
            'uri' => $route['uri'],
            'action' => is_callable($route['action']) ? 'Closure' : (is_array($route['action']) ? json_encode($route['action']) : $route['action']),
        ];
    }
    
    // Dapatkan base URL aplikasi
    $baseUrl = url('/');
    
    // Kelompokkan routes berdasarkan prefix
    $groupedRoutes = [];
    foreach ($routes as $route) {
        $uri = $route['uri'];
        $parts = explode('/', trim($uri, '/'));
        $prefix = !empty($parts[0]) ? $parts[0] : 'root';
        
        if (!isset($groupedRoutes[$prefix])) {
            $groupedRoutes[$prefix] = [];
        }
        
        $groupedRoutes[$prefix][] = $route;
    }
    
    // Buat HTML untuk tabel routes
    $routesHtml = '';
    foreach ($groupedRoutes as $prefix => $prefixRoutes) {
        $prefixTitle = $prefix === 'root' ? 'Root Endpoints' : ucfirst($prefix) . ' Endpoints';
        
        $routesHtml .= "
        <div class='endpoint-group'>
            <h3>{$prefixTitle}</h3>
            <div class='endpoint-list'>";
        
        foreach ($prefixRoutes as $route) {
            $method = $route['method'];
            $methodClass = strtolower($method);
            $uri = $route['uri'];
            $action = $route['action'];
            $fullUrl = $baseUrl . '/' . ltrim($uri, '/');
            $usageInfo = getEndpointUsageInfo($method, $uri, $baseUrl);
            
            $routesHtml .= "
            <div class='endpoint-card'>
                <div class='endpoint-header'>
                    <span class='method {$methodClass}'>{$method}</span>
                    <span class='uri'>{$uri}</span>
                </div>
                <div class='endpoint-body'>
                    <div class='action-label'>Controller Action:</div>
                    <div class='action-value'>{$action}</div>
                    <div class='url-label'>Contoh URL:</div>
                    <div class='url-value'>
                        <code>{$fullUrl}</code>
                        <button class='copy-btn' onclick='copyToClipboard(this)' data-url='{$fullUrl}'>
                            <svg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 24 24' fill='none' stroke='currentColor' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'>
                                <rect x='9' y='9' width='13' height='13' rx='2' ry='2'></rect>
                                <path d='M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1'></path>
                            </svg>
                        </button>
                    </div>
                    
                    <div class='usage-info'>
                        <div class='usage-toggle' onclick='toggleUsageInfo(this)'>
                            <span>Cara Penggunaan di Postman</span>
                            <svg class='toggle-icon' xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 24 24' fill='none' stroke='currentColor' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'>
                                <polyline points='6 9 12 15 18 9'></polyline>
                            </svg>
                        </div>
                        <div class='usage-details' style='display: none;'>";
            
            // Headers
            if (!empty($usageInfo['headers'])) {
                $routesHtml .= "
                            <div class='usage-section'>
                                <div class='usage-label'>Headers:</div>
                                <div class='usage-table'>
                                    <table>
                                        <thead>
                                            <tr>
                                                <th>Nama</th>
                                                <th>Nilai</th>
                                            </tr>
                                        </thead>
                                        <tbody>";
                
                foreach ($usageInfo['headers'] as $header) {
                    $routesHtml .= "
                                        <tr>
                                            <td>{$header['name']}</td>
                                            <td>{$header['value']}</td>
                                        </tr>";
                }
                
                $routesHtml .= "
                                    </tbody>
                                </table>
                            </div>
                        </div>";
            }
            
            // Path Parameters
            if (!empty($usageInfo['path_params'])) {
                $routesHtml .= "
                            <div class='usage-section'>
                                <div class='usage-label'>Path Parameters:</div>
                                <div class='usage-table'>
                                    <table>
                                        <thead>
                                            <tr>
                                                <th>Nama</th>
                                                <th>Tipe</th>
                                                <th>Wajib</th>
                                                <th>Deskripsi</th>
                                            </tr>
                                        </thead>
                                        <tbody>";
                
                foreach ($usageInfo['path_params'] as $param) {
                    $required = $param['required'] ? 'Ya' : 'Tidak';
                    $routesHtml .= "
                                        <tr>
                                            <td>{$param['name']}</td>
                                            <td>{$param['type']}</td>
                                            <td>{$required}</td>
                                            <td>{$param['description']}</td>
                                        </tr>";
                }
                
                $routesHtml .= "
                                    </tbody>
                                </table>
                            </div>
                        </div>";
            }
            
            // Query Parameters
            if (!empty($usageInfo['query_params'])) {
                $routesHtml .= "
                            <div class='usage-section'>
                                <div class='usage-label'>Query Parameters:</div>
                                <div class='usage-table'>
                                    <table>
                                        <thead>
                                            <tr>
                                                <th>Nama</th>
                                                <th>Tipe</th>
                                                <th>Wajib</th>
                                                <th>Deskripsi</th>
                                            </tr>
                                        </thead>
                                        <tbody>";
                
                foreach ($usageInfo['query_params'] as $param) {
                    $required = $param['required'] ? 'Ya' : 'Tidak';
                    $routesHtml .= "
                                        <tr>
                                            <td>{$param['name']}</td>
                                            <td>{$param['type']}</td>
                                            <td>{$required}</td>
                                            <td>{$param['description']}</td>
                                        </tr>";
                }
                
                $routesHtml .= "
                                    </tbody>
                                </table>
                            </div>
                        </div>";
            }
            
            // Body Parameters
            if (!empty($usageInfo['body_params'])) {
                $routesHtml .= "
                            <div class='usage-section'>
                                <div class='usage-label'>Body Parameters:</div>
                                <div class='usage-table'>
                                    <table>
                                        <thead>
                                            <tr>
                                                <th>Nama</th>
                                                <th>Tipe</th>
                                                <th>Wajib</th>
                                                <th>Deskripsi</th>
                                            </tr>
                                        </thead>
                                        <tbody>";
                
                foreach ($usageInfo['body_params'] as $param) {
                    $required = $param['required'] ? 'Ya' : 'Tidak';
                    $routesHtml .= "
                                        <tr>
                                            <td>{$param['name']}</td>
                                            <td>{$param['type']}</td>
                                            <td>{$required}</td>
                                            <td>{$param['description']}</td>
                                        </tr>";
                }
                
                $routesHtml .= "
                                    </tbody>
                                </table>
                            </div>
                        </div>";
            
                // Tambahkan contoh JSON body
                $jsonExample = "{\n";
                foreach ($usageInfo['body_params'] as $index => $param) {
                    $exampleValue = '';
                    switch ($param['type']) {
                        case 'string':
                            $exampleValue = '"contoh_' . $param['name'] . '"';
                            break;
                        case 'integer':
                        case 'number':
                            $exampleValue = '1';
                            break;
                        case 'boolean':
                            $exampleValue = 'true';
                            break;
                        case 'date':
                            $exampleValue = '"' . date('Y-m-d') . '"';
                            break;
                        default:
                            $exampleValue = '""';
                    }
                    
                    $jsonExample .= "    \"" . $param['name'] . "\": " . $exampleValue;
                    if ($index < count($usageInfo['body_params']) - 1) {
                        $jsonExample .= ",\n";
                    } else {
                        $jsonExample .= "\n";
                    }
                }
                $jsonExample .= "}";
                
                $routesHtml .= "
                            <div class='usage-section'>
                                <div class='usage-label'>Contoh JSON Body:</div>
                                <div class='usage-code'>
                                    <pre><code class='json'>" . htmlspecialchars($jsonExample) . "</code></pre>
                                    <button class='copy-btn' onclick='copyToClipboard(this)' data-url='" . htmlspecialchars($jsonExample) . "'>
                                        <svg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 24 24' fill='none' stroke='currentColor' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'>
                                            <rect x='9' y='9' width='13' height='13' rx='2' ry='2'></rect>
                                            <path d='M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1'></path>
                                        </svg>
                                    </button>
                                </div>
                            </div>";
            }
            
            // Contoh Response
            $responseExample = "{\n    \"status\": \"success\",\n    \"message\": \"Data berhasil diambil\",\n    \"data\": {}\n}";
            
            $routesHtml .= "
                            <div class='usage-section'>
                                <div class='usage-label'>Contoh Response:</div>
                                <div class='usage-code'>
                                    <pre><code class='json'>" . htmlspecialchars($responseExample) . "</code></pre>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>";
        }
        
        $routesHtml .= "
            </div>
        </div>";
    }
    
    $version = $router->app->version();
    $year = date('Y');
    
    $html = <<<HTML
    <!DOCTYPE html>
    <html lang="id">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>API Documentation</title>
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
        <style>
            :root {
                --primary-color: #3b82f6;
                --primary-dark: #2563eb;
                --secondary-color: #64748b;
                --success-color: #10b981;
                --warning-color: #f59e0b;
                --danger-color: #ef4444;
                --light-color: #f8fafc;
                --dark-color: #1e293b;
                --border-color: #e2e8f0;
                --card-bg: #ffffff;
                --body-bg: #f1f5f9;
                --header-bg: #1e293b;
                --shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
                --shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
                --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
                --radius: 0.5rem;
            }
            
            * {
                margin: 0;
                padding: 0;
                box-sizing: border-box;
            }
            
            body {
                font-family: 'Inter', sans-serif;
                background-color: var(--body-bg);
                color: var(--dark-color);
                line-height: 1.6;
            }
            
            .container {
                max-width: 1200px;
                margin: 0 auto;
                padding: 2rem;
            }
            
            header {
                background-color: var(--header-bg);
                color: white;
                padding: 3rem 0;
                text-align: center;
                border-radius: var(--radius);
                margin-bottom: 2rem;
                box-shadow: var(--shadow-lg);
            }
            
            header h1 {
                font-size: 2.5rem;
                margin-bottom: 0.5rem;
                font-weight: 700;
            }
            
            header p {
                font-size: 1.2rem;
                opacity: 0.9;
                margin-bottom: 1rem;
            }
            
            .version {
                background-color: var(--primary-color);
                color: white;
                padding: 0.5rem 1rem;
                border-radius: 2rem;
                font-size: 0.875rem;
                font-weight: 500;
                display: inline-block;
                box-shadow: var(--shadow);
            }
            
            .content {
                background-color: var(--card-bg);
                border-radius: var(--radius);
                box-shadow: var(--shadow);
                overflow: hidden;
                margin-bottom: 2rem;
            }
            
            .section {
                padding: 2rem;
            }
            
            .section h2 {
                font-size: 1.5rem;
                color: var(--dark-color);
                margin-bottom: 1rem;
                padding-bottom: 0.75rem;
                border-bottom: 2px solid var(--border-color);
            }
            
            .section p {
                margin-bottom: 1.5rem;
                color: var(--secondary-color);
            }
            
            .endpoint-group {
                margin-bottom: 2rem;
                background-color: var(--light-color);
                border-radius: var(--radius);
                overflow: hidden;
                box-shadow: var(--shadow-sm);
            }
            
            .endpoint-group h3 {
                padding: 1rem 1.5rem;
                background-color: var(--header-bg);
                color: white;
                font-size: 1.25rem;
                font-weight: 600;
            }
            
            .endpoint-list {
                padding: 1rem;
                display: grid;
                grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
                gap: 1rem;
            }
            
            .endpoint-card {
                background-color: var(--card-bg);
                border-radius: var(--radius);
                overflow: hidden;
                box-shadow: var(--shadow);
                transition: transform 0.2s, box-shadow 0.2s;
            }
            
            .endpoint-card:hover {
                transform: translateY(-3px);
                box-shadow: var(--shadow-lg);
            }
            
            .endpoint-header {
                padding: 1rem;
                display: flex;
                align-items: center;
                border-bottom: 1px solid var(--border-color);
            }
            
            .endpoint-body {
                padding: 1rem;
            }
            
            .method {
                display: inline-block;
                padding: 0.25rem 0.75rem;
                border-radius: 0.25rem;
                font-size: 0.75rem;
                font-weight: 700;
                color: white;
                text-transform: uppercase;
                margin-right: 0.75rem;
                min-width: 60px;
                text-align: center;
            }
            
            .uri {
                font-family: monospace;
                font-size: 0.875rem;
                font-weight: 500;
                color: var(--dark-color);
                word-break: break-all;
            }
            
            .action-label, .url-label {
                font-size: 0.75rem;
                color: var(--secondary-color);
                margin-bottom: 0.25rem;
                margin-top: 0.75rem;
            }
            
            .action-value, .url-value {
                font-family: monospace;
                font-size: 0.8125rem;
                color: var(--dark-color);
                background-color: var(--light-color);
                padding: 0.5rem;
                border-radius: 0.25rem;
                word-break: break-all;
                position: relative;
            }
            
            .url-value {
                display: flex;
                justify-content: space-between;
                align-items: center;
            }
            
            .copy-btn {
                background: none;
                border: none;
                color: var(--secondary-color);
                cursor: pointer;
                padding: 0.25rem;
                border-radius: 0.25rem;
                transition: all 0.2s;
            }
            
            .copy-btn:hover {
                color: var(--primary-color);
                background-color: rgba(59, 130, 246, 0.1);
            }
            
            .copy-btn.copied {
                color: var(--success-color);
            }
            
            .get { background-color: var(--primary-color); }
            .post { background-color: var(--success-color); }
            .put { background-color: var(--warning-color); }
            .patch { background-color: var(--warning-color); }
            .delete { background-color: var(--danger-color); }
            
            .footer {
                text-align: center;
                padding: 1.5rem;
                color: var(--secondary-color);
                font-size: 0.875rem;
            }
            
            .footer a {
                color: var(--primary-color);
                text-decoration: none;
            }
            
            .footer a:hover {
                text-decoration: underline;
            }
            
            @media (max-width: 768px) {
                .container {
                    padding: 1rem;
                }
                
                header {
                    padding: 2rem 1rem;
                }
                
                .endpoint-list {
                    grid-template-columns: 1fr;
                }
            }
            
            .usage-info {
                margin-top: 1rem;
                border-top: 1px solid var(--border-color);
                padding-top: 1rem;
            }
            
            .usage-toggle {
                display: flex;
                justify-content: space-between;
                align-items: center;
                cursor: pointer;
                padding: 0.5rem;
                background-color: var(--light-color);
                border-radius: 0.25rem;
                font-weight: 500;
                color: var(--primary-color);
                transition: all 0.2s;
            }
            
            .usage-toggle:hover {
                background-color: rgba(59, 130, 246, 0.1);
            }
            
            .toggle-icon {
                transition: transform 0.2s;
            }
            
            .toggle-icon.open {
                transform: rotate(180deg);
            }
            
            .usage-details {
                padding: 1rem;
                background-color: var(--light-color);
                border-radius: 0.25rem;
                margin-top: 0.5rem;
            }
            
            .usage-section {
                margin-bottom: 1rem;
            }
            
            .usage-label {
                font-size: 0.875rem;
                font-weight: 500;
                margin-bottom: 0.5rem;
                color: var(--secondary-color);
            }
            
            .usage-table table {
                width: 100%;
                border-collapse: collapse;
                font-size: 0.8125rem;
            }
            
            .usage-table th,
            .usage-table td {
                padding: 0.5rem;
                text-align: left;
                border: 1px solid var(--border-color);
            }
            
            .usage-table th {
                background-color: var(--light-color);
                font-weight: 500;
            }
            
            .usage-code {
                position: relative;
                background-color: var(--dark-color);
                color: var(--light-color);
                padding: 1rem;
                border-radius: 0.25rem;
                font-family: monospace;
                font-size: 0.8125rem;
                overflow-x: auto;
            }
            
            .usage-code pre {
                margin: 0;
            }
            
            .usage-code .copy-btn {
                position: absolute;
                top: 0.5rem;
                right: 0.5rem;
                color: var(--light-color);
            }
            
            .usage-code .copy-btn:hover {
                color: var(--primary-color);
                background-color: rgba(255, 255, 255, 0.1);
            }
        </style>
    </head>
    <body>
        <div class="container">
            <header>
                <h1>API Documentation</h1>
                <p>Dokumentasi lengkap untuk menggunakan API kami</p>
                <span class="version">{$version}</span>
            </header>

            <div class="content">
                <div class="section">
                    <h2>Daftar Endpoint</h2>
                    <p>Berikut adalah daftar semua endpoint yang tersedia di API, dikelompokkan berdasarkan kategori:</p>
                    
                    {$routesHtml}
                </div>
            </div>
            
            <div class="footer">
                <p>&copy; {$year} API Documentation. Dibuat dengan ❤️ & Gabut.</p>
            </div>
        </div>
        
        <script>
            function copyToClipboard(button) {
                const url = button.getAttribute('data-url');
                navigator.clipboard.writeText(url).then(() => {
                    button.classList.add('copied');
                    
                    // Tampilkan efek visual bahwa URL telah disalin
                    const originalHTML = button.innerHTML;
                    button.innerHTML = `
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="20 6 9 17 4 12"></polyline>
                        </svg>
                    `;
                    
                    setTimeout(() => {
                        button.innerHTML = originalHTML;
                        button.classList.remove('copied');
                    }, 2000);
                });
            }

            function toggleUsageInfo(element) {
                const details = element.nextElementSibling;
                const icon = element.querySelector('.toggle-icon');
                
                if (details.style.display === 'none') {
                    details.style.display = 'block';
                    icon.classList.add('open');
                } else {
                    details.style.display = 'none';
                    icon.classList.remove('open');
                }
            }
        </script>
    </body>
    </html>
    HTML;
    
    return response($html);
});

// Auth Routes
$router->group(['prefix' => 'auth'], function () use ($router) {
    $router->post('/register', 'AuthController@register');
    $router->post('/login', 'AuthController@login');
    $router->post('/logout', 'AuthController@logout');
});

// Tambahkan middleware activity.tracker ke grup route yang memerlukan login
$router->group(['middleware' => ['login', 'activity.tracker']], function () use ($router) {
    // Tambahkan route group untuk admin dashboard
    $router->group(['prefix' => 'admin', 'middleware' => ['login', 'admin']], function () use ($router) {
        // Dashboard
        $router->get('/dashboard', 'Admin\DashboardController@index');
        
        // Manajemen Tahun Ajaran
        $router->get('/tahun-ajaran', 'Admin\TahunAjaranController@index');
        $router->post('/tahun-ajaran', 'Admin\TahunAjaranController@store');
        $router->put('/tahun-ajaran/{id}', 'Admin\TahunAjaranController@update');
        $router->put('/tahun-ajaran/{id}/activate', 'Admin\TahunAjaranController@activate');
        $router->delete('/tahun-ajaran/{id}', 'Admin\TahunAjaranController@destroy');
        
        // Manajemen Data Master
        $router->group(['prefix' => 'master'], function () use ($router) {
            // Mata Pelajaran
            $router->get('/mapel', 'Admin\MataPelajaranController@index');
            $router->post('/mapel', 'Admin\MataPelajaranController@store');
            $router->post('/mapel/batch', 'Admin\MataPelajaranController@storeBatch');
            $router->put('/mapel/{id}', 'Admin\MataPelajaranController@update');
            $router->delete('/mapel/{id}', 'Admin\MataPelajaranController@destroy');
            
            // Tambahkan endpoint untuk ekspor template mapel
            $router->get('/mapel/template', 'Admin\MataPelajaranController@getTemplate');
            // Tambahkan endpoint untuk impor mapel
            $router->post('/mapel/import', 'Admin\MataPelajaranController@import');
            
            // Capaian Pembelajaran
            $router->get('/cp', 'Admin\CapaianPembelajaranController@index');
            $router->post('/cp', 'Admin\CapaianPembelajaranController@store');
            $router->put('/cp/{id}', 'Admin\CapaianPembelajaranController@update');
            $router->delete('/cp/{id}', 'Admin\CapaianPembelajaranController@destroy');
            $router->post('/cp/batch', 'Admin\CapaianPembelajaranController@storeBatch');
            
            // Tujuan Pembelajaran
            $router->get('/tp', 'Admin\TujuanPembelajaranController@index');
            $router->post('/tp', 'Admin\TujuanPembelajaranController@store');
            $router->put('/tp/{id}', 'Admin\TujuanPembelajaranController@update');
            $router->delete('/tp/{id}', 'Admin\TujuanPembelajaranController@destroy');
        });
        
        // Manajemen Guru
        $router->get('/guru', 'Admin\GuruController@index');
        $router->post('/guru', 'Admin\GuruController@store');
        $router->post('/guru/batch', 'Admin\GuruController@storeBatch');
        $router->put('/guru/{id}', 'Admin\GuruController@update');
        $router->delete('/guru/{id}', 'Admin\GuruController@destroy');
        $router->post('/guru/import', 'Admin\GuruController@import');
        $router->get('/guru/template', 'Admin\GuruController@getTemplate');
        $router->post('/guru/{id}/reset-password', 'Admin\GuruController@resetPassword');
        
        // Manajemen Siswa
        $router->get('/siswa', 'Admin\SiswaController@index');
        $router->post('/siswa', 'Admin\SiswaController@store');
        $router->post('/siswa/batch', 'Admin\SiswaController@storeBatch');
        $router->put('/siswa/{id}', 'Admin\SiswaController@update');
        $router->delete('/siswa/{id}', 'Admin\SiswaController@destroy');
        $router->post('/siswa/import', 'Admin\SiswaController@import');
        $router->get('/siswa/template', 'Admin\SiswaController@getTemplate');
        
        // Manajemen Kelas
        $router->get('/kelas', 'Admin\KelasController@index');
        $router->post('/kelas', 'Admin\KelasController@store');
        $router->put('/kelas/{id}', 'Admin\KelasController@update');
        $router->delete('/kelas/{id}', 'Admin\KelasController@destroy');
        $router->get('/kelas/{id}/detail', 'Admin\KelasController@detail');
        $router->post('/kelas/{id}/assign-guru', 'Admin\KelasController@assignGuru');
        $router->post('/kelas/{id}/assign-siswa', 'Admin\KelasController@assignSiswa');
        
        // Laporan
        $router->get('/reports/nilai', 'Admin\ReportController@nilai');
        $router->get('/reports/nilai/export', 'Admin\ReportController@exportNilai');
        $router->get('/reports/absensi', 'Admin\ReportController@absensi');
        $router->get('/reports/aktivitas', 'Admin\ReportController@aktivitas');

        // Tambahkan route ini di dalam grup admin
        $router->get('/storage/link', 'Admin\StorageController@createStorageLink');
    });

    // Guru Routes
    $router->group(['prefix' => 'guru', 'middleware' => ['login', 'guru']], function () use ($router) {
        // Absensi Controller Routes
        $router->group(['prefix' => 'absensi'], function () use ($router) {
            // Route statis terlebih dahulu
            $router->get('/rekap-bulanan', 'Guru\AbsensiController@rekapBulanan');
            $router->get('/export', 'Guru\AbsensiController@export');
            $router->get('/laporan', 'Guru\AbsensiController@laporan');
            $router->post('/import', 'Guru\AbsensiController@import');
            
            // Route variabel setelahnya
            $router->get('/{id}', 'Guru\AbsensiController@show');
            $router->put('/{id}', 'Guru\AbsensiController@update');
            $router->delete('/{id}', 'Guru\AbsensiController@destroy');
        });
        
        // Dashboard
        $router->get('/dashboard', 'Guru\DashboardController@index');
        
        // Nilai Routes
        $router->group(['prefix' => 'nilai'], function () use ($router) {
            $router->get('/', 'Guru\NilaiController@index');
            $router->post('/', 'Guru\NilaiController@store');
            $router->put('/{id}', 'Guru\NilaiController@update');
        });
        
        // Absensi Routes  
        $router->group(['prefix' => 'absensi'], function () use ($router) {
            $router->get('/', 'Guru\AbsensiController@index');
            $router->post('/', 'Guru\AbsensiController@store');
            $router->post('/batch-update', 'Guru\AbsensiController@batchUpdate');
        });
        
        // Rekap Routes
        $router->group(['prefix' => 'rekap'], function () use ($router) {
            $router->get('/nilai', 'Guru\RekapController@nilai');
            $router->get('/absensi', 'Guru\RekapController@absensi');
        });
    });

    // User Routes
    $router->group(['prefix' => 'user', 'middleware' => 'login'], function () use ($router) {
        $router->get('/', 'UserController@index');
        $router->post('/', 'UserController@store');
        
        // Pindahkan static routes ke atas sebelum dynamic routes
        $router->get('/profile', 'UserController@getUserProfile');
        $router->post('/change-password', 'UserController@changePassword');
        $router->get('/pagination', 'UserController@pagenationUser');
        
        // Dynamic routes harus di bawah static routes
        $router->get('/{id}', 'UserController@show');
        $router->put('/{id}', 'UserController@update');
        $router->delete('/{id}', 'UserController@destroy');
    });

    // User Activity Routes
    $router->group(['prefix' => 'activity', 'middleware' => 'login'], function () use ($router) {
        $router->get('/', 'UserActivityController@getAllActivities');
        $router->get('/sessions', 'UserActivityController@getAllSessions');
        $router->get('/logs', 'UserActivityController@getActivityLogs');
        $router->get('/dates', 'UserActivityController@getActivityLogDates');
        $router->get('/statistics', 'UserActivityController@getActivityStatistics');
        $router->get('/usage-time', 'UserActivityController@getUsageTime');
    });

    // Sekolah Routes
    $router->group(['prefix' => 'sekolah', 'middleware' => 'login'], function () use ($router) {
        $router->get('/', 'SekolahController@index');
        $router->post('/', 'SekolahController@store');
        $router->get('/{id}', 'SekolahController@show');
        $router->put('/{id}', 'SekolahController@update');
        $router->delete('/{id}', 'SekolahController@destroy');
        $router->get('/statistics/summary', 'SekolahController@getStatistics');
        $router->get('/{id}/guru', 'SekolahController@getGuru');
        $router->get('/{id}/siswa', 'SekolahController@getSiswa');
        $router->get('/{id}/kelas', 'SekolahController@getKelas');
    });

    // Tahun Ajaran Routes
    $router->group(['prefix' => 'tahun-ajaran', 'middleware' => 'login'], function () use ($router) {
        $router->get('/', 'TahunAjaranController@index');
        $router->post('/', 'TahunAjaranController@store');
        $router->get('/{id}', 'TahunAjaranController@show');
        $router->put('/{id}', 'TahunAjaranController@update');
        $router->delete('/{id}', 'TahunAjaranController@destroy');
        $router->put('/{id}/activate', 'TahunAjaranController@activate');
    });

    // Kelas Routes
    $router->group(['prefix' => 'kelas', 'middleware' => 'login'], function () use ($router) {
        $router->get('/', 'KelasController@index');
        $router->post('/', 'KelasController@store');
        $router->get('/{id}', 'KelasController@show');
        $router->put('/{id}', 'KelasController@update');
        $router->delete('/{id}', 'KelasController@destroy');
        $router->get('/{id}/siswa', 'KelasController@getSiswa');
        $router->get('/{id}/jadwal', 'KelasController@getJadwal');
        $router->get('/{id}/nilai', 'KelasController@getNilai');
        $router->get('/{id}/absensi', 'KelasController@getAbsensi');
        $router->get('/{id}/guru', 'KelasController@getGuru');
    });

    // Siswa Routes
    $router->group(['prefix' => 'siswa', 'middleware' => 'login'], function () use ($router) {
        $router->get('/', 'SiswaController@index');
        $router->post('/', 'SiswaController@store');
        $router->get('/{id}', 'SiswaController@show');
        $router->put('/{id}', 'SiswaController@update');
        $router->delete('/{id}', 'SiswaController@destroy');
        $router->post('/import', 'SiswaController@import');
        $router->get('/{id}/nilai', 'SiswaController@getNilai');
        $router->get('/{id}/absensi', 'SiswaController@getAbsensi');
    });

    // Mata Pelajaran Routes
    $router->group(['prefix' => 'mapel', 'middleware' => 'login'], function () use ($router) {
        $router->get('/', 'MataPelajaranController@index');
        $router->post('/', 'MataPelajaranController@store');
        $router->get('/{id}', 'MataPelajaranController@show');
        $router->put('/{id}', 'MataPelajaranController@update');
        $router->delete('/{id}', 'MataPelajaranController@destroy');
        $router->get('/{id}/cp', 'MataPelajaranController@getCapaianPembelajaran');
    });

    // Capaian Pembelajaran Routes
    $router->group(['prefix' => 'cp', 'middleware' => 'login'], function () use ($router) {
        $router->get('/', 'CapaianPembelajaranController@index');
        $router->post('/', 'CapaianPembelajaranController@store');
        $router->get('/{id}', 'CapaianPembelajaranController@show');
        $router->put('/{id}', 'CapaianPembelajaranController@update');
        $router->delete('/{id}', 'CapaianPembelajaranController@destroy');
        $router->get('/{id}/tp', 'CapaianPembelajaranController@getTujuanPembelajaran');
    });

    // Tujuan Pembelajaran Routes
    $router->group(['prefix' => 'tp', 'middleware' => 'login'], function () use ($router) {
        $router->get('/', 'TujuanPembelajaranController@index');
        $router->post('/', 'TujuanPembelajaranController@store');
        $router->get('/{id}', 'TujuanPembelajaranController@show');
        $router->put('/{id}', 'TujuanPembelajaranController@update');
        $router->delete('/{id}', 'TujuanPembelajaranController@destroy');
    });

    // Nilai Siswa Routes
    $router->group(['prefix' => 'nilai', 'middleware' => 'login'], function () use ($router) {
        $router->get('/', 'NilaiSiswaController@index');
        $router->post('/', 'NilaiSiswaController@store');
        $router->get('/{id}', 'NilaiSiswaController@show');
        $router->put('/{id}', 'NilaiSiswaController@update');
        $router->delete('/{id}', 'NilaiSiswaController@destroy');
        $router->post('/import', 'NilaiSiswaController@import');
        $router->get('/report/siswa/{siswaId}', 'NilaiSiswaController@reportBySiswa');
        $router->get('/report/kelas/{kelasId}', 'NilaiSiswaController@reportByKelas');
    });

    // Pertemuan Bulanan Routes
    $router->group(['prefix' => 'pertemuan', 'middleware' => 'login'], function () use ($router) {
        $router->get('/', 'PertemuanBulananController@index');
        $router->post('/', 'PertemuanBulananController@store');
        $router->get('/{id}', 'PertemuanBulananController@show');
        $router->put('/{id}', 'PertemuanBulananController@update');
        $router->delete('/{id}', 'PertemuanBulananController@destroy');
        
        // New routes
        $router->get('/kelas/{kelasId}', 'PertemuanBulananController@getByKelas');
        $router->get('/bulan/{bulan}', 'PertemuanBulananController@getByBulan');
        $router->get('/tahun/{tahun}', 'PertemuanBulananController@getByTahun');
    });

    // Absensi Siswa Routes
    $router->group(['prefix' => 'absensi', 'middleware' => 'login'], function () use ($router) {
        // Pindahkan rute statis ke atas
        $router->get('/rekap', 'AbsensiSiswaController@getRekapAbsensi');
        $router->get('/report/siswa/{siswaId}', 'AbsensiSiswaController@reportBySiswa');
        $router->get('/report/kelas/{kelasId}', 'AbsensiSiswaController@reportByKelas');
        
        // Kemudian rute CRUD dasar
        $router->get('/', 'AbsensiSiswaController@index');
        $router->post('/', 'AbsensiSiswaController@store');
        $router->post('/import', 'AbsensiSiswaController@import');
        
        // Rute dengan parameter di akhir
        $router->get('/{id}', 'AbsensiSiswaController@show');
        $router->put('/{id}', 'AbsensiSiswaController@update');
        $router->delete('/{id}', 'AbsensiSiswaController@destroy');
    });
});

// Tambahkan route untuk heartbeat
$router->post('/activity/heartbeat', [
    'middleware' => 'login',
    'uses' => 'ActivityController@heartbeat'
]);

// API Mobile - Guru Routes
// $router->group(['prefix' => 'api/guru', 'middleware' => 'login'], function () use ($router) {
//     // Profile dan Data Dasar
//     $router->get('/profile', 'API\GuruController@getProfile');
//     $router->put('/profile', 'API\GuruController@updateProfile');
    
//     // Kelas dan Siswa
//     $router->get('/kelas', 'API\GuruController@getKelas');
//     $router->get('/kelas/{id}/siswa', 'API\GuruController@getSiswaByKelas');
//     $router->get('/kelas/{id}/jadwal', 'API\GuruController@getJadwalKelas');
    
//     // Mata Pelajaran dan Pembelajaran
//     $router->get('/mapel', 'API\GuruController@getMapel');
//     $router->get('/mapel/{id}/materi', 'API\GuruController@getMateri');
//     $router->get('/cp/{mapelId}', 'API\GuruController@getCapaianPembelajaran');
//     $router->get('/tp/{cpId}', 'API\GuruController@getTujuanPembelajaran');
    
//     // Penilaian
//     $router->post('/nilai/batch', 'API\GuruController@storeNilaiBatch');
//     $router->post('/nilai', 'API\GuruController@storeNilai');
//     $router->put('/nilai/{id}', 'API\GuruController@updateNilai');
    
//     // Pertemuan dan Absensi
//     $router->post('/pertemuan', 'API\GuruController@storePertemuan');
//     $router->put('/pertemuan/{id}', 'API\GuruController@updatePertemuan');
//     $router->post('/absensi/batch', 'API\GuruController@storeAbsensiBatch');
//     $router->post('/absensi', 'API\GuruController@storeAbsensi');
    
//     // Laporan
//     $router->get('/report/nilai/{kelasId}', 'API\GuruController@reportNilaiKelas');
//     $router->get('/report/absensi/{kelasId}', 'API\GuruController@reportAbsensiKelas');
//     $router->get('/report/pembelajaran/{kelasId}', 'API\GuruController@reportPembelajaran');
    
//     // Dashboard
//     $router->get('/dashboard/summary', 'API\GuruController@getDashboardSummary');
//     $router->get('/dashboard/activities', 'API\GuruController@getRecentActivities');
// });

// API Admin Routes
// $router->group(['prefix' => 'api/admin', 'middleware' => ['login', 'admin']], function () use ($router) {
//     // Guru Routes
//     $router->group(['prefix' => 'guru'], function () use ($router) {
//         // Route statis terlebih dahulu
//         $router->get('/template', 'API\Admin\GuruController@getTemplate');
//         $router->post('/batch', 'API\Admin\GuruController@storeBatch');
        
//         // Route dasar
//         $router->get('/', 'API\Admin\GuruController@index');
//         $router->post('/', 'API\Admin\GuruController@store');
        
//         // Route variabel
//         $router->get('/{id}', 'API\Admin\GuruController@show');
//         $router->put('/{id}', 'API\Admin\GuruController@update');
//         $router->delete('/{id}', 'API\Admin\GuruController@destroy');
//     });
    
//     // Siswa Routes
//     $router->group(['prefix' => 'siswa'], function () use ($router) {
//         // Route statis terlebih dahulu
//         $router->get('/template', 'API\Admin\SiswaController@getTemplate');
//         $router->post('/batch', 'API\Admin\SiswaController@storeBatch');
        
//         // Route dasar
//         $router->get('/', 'API\Admin\SiswaController@index');
//         $router->post('/', 'API\Admin\SiswaController@store');
        
//         // Route variabel setelahnya
//         $router->get('/{id}', 'API\Admin\SiswaController@show');
//         $router->put('/{id}', 'API\Admin\SiswaController@update');
//         $router->delete('/{id}', 'API\Admin\SiswaController@destroy');
//     });
    
//     // Route lainnya...
// });

// // Admin Routes
// $router->group(['prefix' => 'admin', 'middleware' => ['login', 'admin']], function () use ($router) {
//     $router->get('/sekolah', 'Admin\SekolahController@index');
//     $router->get('/sekolah/{id}', 'Admin\SekolahController@show');
//     $router->post('/sekolah', 'Admin\SekolahController@store');
//     $router->put('/sekolah/{id}', 'Admin\SekolahController@update');
//     $router->delete('/sekolah/{id}', 'Admin\SekolahController@destroy');
//     $router->get('/batch', 'Admin\GuruController@batchForm');
//     $router->post('/batch', 'Admin\GuruController@storeBatch');
// });

// Setting Routes
// $router->group(['prefix' => 'settings', 'middleware' => 'login'], function () use ($router) {
//     $router->get('/', 'SettingController@index');
//     $router->post('/', 'SettingController@store');
//     $router->get('/{id}', 'SettingController@show');
//     $router->put('/{id}', 'SettingController@update');
//     $router->delete('/{id}', 'SettingController@destroy');
//     $router->post('/get-by-key', 'SettingController@getByKey');
//     $router->post('/get-by-group', 'SettingController@getByGroup');
//     $router->post('/bulk-update', 'SettingController@bulkUpdate');
// });