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
});

// Sekolah Routes
$router->group(['prefix' => 'sekolah', 'middleware' => 'login'], function () use ($router) {
    $router->get('/', 'SekolahController@index');
    $router->post('/', 'SekolahController@store');
    $router->get('/{id}', 'SekolahController@show');
    $router->put('/{id}', 'SekolahController@update');
    $router->delete('/{id}', 'SekolahController@destroy');
});

// Guru Routes
$router->group(['prefix' => 'guru', 'middleware' => 'login'], function () use ($router) {
    $router->get('/', 'GuruController@index');
    $router->post('/', 'GuruController@store');
    $router->get('/{id}', 'GuruController@show');
    $router->put('/{id}', 'GuruController@update');
    $router->delete('/{id}', 'GuruController@destroy');
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
});

// Absensi Siswa Routes
$router->group(['prefix' => 'absensi', 'middleware' => 'login'], function () use ($router) {
    $router->get('/', 'AbsensiSiswaController@index');
    $router->post('/', 'AbsensiSiswaController@store');
    $router->get('/{id}', 'AbsensiSiswaController@show');
    $router->put('/{id}', 'AbsensiSiswaController@update');
    $router->delete('/{id}', 'AbsensiSiswaController@destroy');
    $router->post('/import', 'AbsensiSiswaController@import');
    $router->get('/report/siswa/{siswaId}', 'AbsensiSiswaController@reportBySiswa');
    $router->get('/report/kelas/{kelasId}', 'AbsensiSiswaController@reportByKelas');
});

// Setting Routes
$router->group(['prefix' => 'settings', 'middleware' => 'login'], function () use ($router) {
    $router->get('/', 'SettingController@index');
    $router->post('/', 'SettingController@store');
    $router->get('/{id}', 'SettingController@show');
    $router->put('/{id}', 'SettingController@update');
    $router->delete('/{id}', 'SettingController@destroy');
    $router->post('/get-by-key', 'SettingController@getByKey');
    $router->post('/get-by-group', 'SettingController@getByGroup');
    $router->post('/bulk-update', 'SettingController@bulkUpdate');
});

// API Mobile - Guru Routes
$router->group(['prefix' => 'api/guru', 'middleware' => 'login'], function () use ($router) {
    $router->get('/profile', 'API\GuruController@getProfile');
    $router->get('/kelas', 'API\GuruController@getKelas');
    $router->get('/kelas/{id}/siswa', 'API\GuruController@getSiswaByKelas');
    $router->get('/mapel', 'API\GuruController@getMapel');
    $router->get('/cp/{mapelId}', 'API\GuruController@getCapaianPembelajaran');
    $router->get('/tp/{cpId}', 'API\GuruController@getTujuanPembelajaran');
    $router->post('/nilai', 'API\GuruController@storeNilai');
    $router->post('/pertemuan', 'API\GuruController@storePertemuan');
    $router->post('/absensi', 'API\GuruController@storeAbsensi');
    $router->get('/report/nilai/{kelasId}', 'API\GuruController@reportNilaiKelas');
    $router->get('/report/absensi/{kelasId}', 'API\GuruController@reportAbsensiKelas');
});

// Public Routes
// $router->group(['prefix' => 'open'], function () use ($router) {
//     $router->get('/token', 'PublicController@tokenview');
//     $router->get('/news', 'PublicController@index');
//     $router->get('/news/{id}', 'PublicController@show');
//     $router->get('/newspagenation', 'PublicController@pagenationNews');
//     $router->get('/cctv', 'PublicController@getcctv');
//     $router->get('/cctv/{id}', 'PublicController@showcctvbyid');
//     $router->get('/polantas', 'PublicController@get_polantas');
//     $router->get('/polantas/{id}', 'PublicController@get_polantasbyid');
//     $router->get('/polantas_category', 'PublicController@get_polantascate');
//     $router->get('/polantas_category/{id}', 'PublicController@get_polantascatebyid');
//     $router->get('/fasum', 'PublicController@get_fasum');
//     $router->get('/fasum/{id}', 'PublicController@get_fasumbyid');
//     $router->get('/fasum_category', 'PublicController@get_fasumcate');
//     $router->get('/fasum_category/{id}', 'PublicController@get_fasumcatebyid');
//     $router->get('/trayek', 'PublicController@get_trayek');
//     $router->get('/trayek/{id}', 'PublicController@get_trayekbyid');
//     $router->get('/trayek_category', 'PublicController@get_trayekcate');
//     $router->get('/trayek_category/{id}', 'PublicController@get_trayekcatebyid');
//     $router->get('/count_pelayanan', 'PublicController@count_total_data');
// });