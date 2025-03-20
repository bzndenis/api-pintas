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
    return $router->app->version();
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