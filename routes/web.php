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

// Guru Routes
$router->group(['prefix' => 'guru', 'middleware' => 'login'], function () use ($router) {
    $router->get('/', 'GuruController@index');
    $router->post('/', 'GuruController@store');
    $router->get('/{id}', 'GuruController@show');
    $router->put('/{id}', 'GuruController@update');
    $router->delete('/{id}', 'GuruController@destroy');
    $router->get('/{id}/jadwal', 'GuruController@getJadwal');
    $router->get('/{id}/kelas', 'GuruController@getKelas');
    $router->get('/{id}/mapel', 'GuruController@getMapel');
    $router->post('/import', 'GuruController@import');
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

// API Mobile - Guru Routes
$router->group(['prefix' => 'api/guru', 'middleware' => 'login'], function () use ($router) {
    // Profile dan Data Dasar
    $router->get('/profile', 'API\GuruController@getProfile');
    $router->put('/profile', 'API\GuruController@updateProfile');
    
    // Kelas dan Siswa
    $router->get('/kelas', 'API\GuruController@getKelas');
    $router->get('/kelas/{id}/siswa', 'API\GuruController@getSiswaByKelas');
    $router->get('/kelas/{id}/jadwal', 'API\GuruController@getJadwalKelas');
    
    // Mata Pelajaran dan Pembelajaran
    $router->get('/mapel', 'API\GuruController@getMapel');
    $router->get('/mapel/{id}/materi', 'API\GuruController@getMateri');
    $router->get('/cp/{mapelId}', 'API\GuruController@getCapaianPembelajaran');
    $router->get('/tp/{cpId}', 'API\GuruController@getTujuanPembelajaran');
    
    // Penilaian
    $router->post('/nilai/batch', 'API\GuruController@storeNilaiBatch');
    $router->post('/nilai', 'API\GuruController@storeNilai');
    $router->put('/nilai/{id}', 'API\GuruController@updateNilai');
    
    // Pertemuan dan Absensi
    $router->post('/pertemuan', 'API\GuruController@storePertemuan');
    $router->put('/pertemuan/{id}', 'API\GuruController@updatePertemuan');
    $router->post('/absensi/batch', 'API\GuruController@storeAbsensiBatch');
    $router->post('/absensi', 'API\GuruController@storeAbsensi');
    
    // Laporan
    $router->get('/report/nilai/{kelasId}', 'API\GuruController@reportNilaiKelas');
    $router->get('/report/absensi/{kelasId}', 'API\GuruController@reportAbsensiKelas');
    $router->get('/report/pembelajaran/{kelasId}', 'API\GuruController@reportPembelajaran');
    
    // Dashboard
    $router->get('/dashboard/summary', 'API\GuruController@getDashboardSummary');
    $router->get('/dashboard/activities', 'API\GuruController@getRecentActivities');
});

// Admin Routes untuk manajemen guru
$router->group(['prefix' => 'admin/guru', 'middleware' => ['login', 'admin']], function () use ($router) {
    $router->get('/', 'Admin\GuruController@index');
    $router->post('/', 'Admin\GuruController@store');
    $router->get('/{id}', 'Admin\GuruController@show');
    $router->put('/{id}', 'Admin\GuruController@update');
    $router->delete('/{id}', 'Admin\GuruController@destroy');
    $router->post('/reset-password/{id}', 'Admin\GuruController@resetPassword');
    $router->post('/activate/{id}', 'Admin\GuruController@activate');
    $router->post('/deactivate/{id}', 'Admin\GuruController@deactivate');
});


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