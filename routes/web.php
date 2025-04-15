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
    
    // Ambil parameter query dari controller
    $queryParams = [];
    $controllerAction = getControllerFromUri($uri);
    if ($controllerAction) {
        $queryParams = getQueryParamsFromController($controllerAction);
    }
    
    // Tambahkan parameter umum untuk semua endpoint GET
    if ($method === 'GET' && empty($queryParams)) {
        $queryParams = [
            ['name' => 'page', 'type' => 'integer', 'required' => false, 'description' => 'Nomor halaman untuk pagination'],
            ['name' => 'per_page', 'type' => 'integer', 'required' => false, 'description' => 'Jumlah item per halaman'],
            ['name' => 'search', 'type' => 'string', 'required' => false, 'description' => 'Pencarian global'],
            ['name' => 'sort_by', 'type' => 'string', 'required' => false, 'description' => 'Kolom untuk pengurutan'],
            ['name' => 'sort_dir', 'type' => 'string', 'required' => false, 'description' => 'Arah pengurutan (asc/desc)']
        ];
    }
    
    // Ambil parameter body dari controller
    $bodyParams = [];
    if ($method === 'POST' || $method === 'PUT' || $method === 'PATCH') {
        $bodyParams = getBodyParamsFromController($controllerAction);
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

function getControllerFromUri($uri) {
    // Ambil nama controller dan method dari URI
    $parts = explode('/', trim($uri, '/'));
    if (count($parts) > 0) {
        $prefix = ucfirst($parts[0]);
        $controllerName = isset($parts[1]) ? ucfirst($parts[1]) : '';
        return "App\\Http\\Controllers\\{$prefix}\\{$controllerName}Controller";
    }
    return null;
}

function getQueryParamsFromController($controllerClass) {
    if (!class_exists($controllerClass)) {
        return [];
    }
    
    $queryParams = [];
    
    // Refleksi untuk mendapatkan method index
    try {
        $reflection = new \ReflectionClass($controllerClass);
        $method = $reflection->getMethod('index');
        $docComment = $method->getDocComment();
        
        // Parse docblock untuk mendapatkan parameter query
        if ($docComment) {
            preg_match_all('/@param\s+([^\s]+)\s+\$([^\s]+)\s+(.*)/', $docComment, $matches);
            for ($i = 0; $i < count($matches[0]); $i++) {
                $queryParams[] = [
                    'name' => $matches[2][$i],
                    'type' => $matches[1][$i],
                    'required' => false,
                    'description' => trim($matches[3][$i])
                ];
            }
        }
        
        // Jika tidak ada docblock, coba ambil dari validasi rules
        if (empty($queryParams)) {
            $instance = new $controllerClass();
            if (method_exists($instance, 'rules')) {
                $rules = $instance->rules();
                foreach ($rules as $field => $rule) {
                    $type = 'string';
                    $required = false;
                    
                    if (is_string($rule)) {
                        $ruleArray = explode('|', $rule);
                        $required = in_array('required', $ruleArray);
                        if (in_array('integer', $ruleArray)) $type = 'integer';
                        if (in_array('numeric', $ruleArray)) $type = 'number';
                        if (in_array('boolean', $ruleArray)) $type = 'boolean';
                    }
                    
                    $queryParams[] = [
                        'name' => $field,
                        'type' => $type,
                        'required' => $required,
                        'description' => ucfirst(str_replace('_', ' ', $field))
                    ];
                }
            }
        }
    } catch (\Exception $e) {
        // Jika terjadi error, kembalikan array kosong
        return [];
    }
    
    return $queryParams;
}

function getBodyParamsFromController($controllerClass) {
    if (!class_exists($controllerClass)) {
        return [];
    }
    
    $bodyParams = [];
    
    try {
        $instance = new $controllerClass();
        if (method_exists($instance, 'rules')) {
            $rules = $instance->rules();
            foreach ($rules as $field => $rule) {
                $type = 'string';
                $required = false;
                $description = ucfirst(str_replace('_', ' ', $field));
                
                if (is_string($rule)) {
                    $ruleArray = explode('|', $rule);
                    $required = in_array('required', $ruleArray);
                    
                    if (in_array('integer', $ruleArray)) $type = 'integer';
                    if (in_array('numeric', $ruleArray)) $type = 'number';
                    if (in_array('boolean', $ruleArray)) $type = 'boolean';
                    if (in_array('date', $ruleArray)) $type = 'date';
                    if (in_array('email', $ruleArray)) $description .= ' (format email)';
                    if (in_array('url', $ruleArray)) $description .= ' (format URL)';
                    
                    foreach ($ruleArray as $r) {
                        if (strpos($r, 'max:') === 0) {
                            $max = substr($r, 4);
                            $description .= " (maks. $max karakter)";
                        }
                    }
                }
                
                $bodyParams[] = [
                    'name' => $field,
                    'type' => $type,
                    'required' => $required,
                    'description' => $description
                ];
            }
        }
    } catch (\Exception $e) {
        return [];
    }
    
    return $bodyParams;
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
            
            $routesHtml .= "
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
                <h1>Dokumentasi API</h1>
                <p>Dokumentasi lengkap untuk menggunakan API</p>
                <span class="version">{$version}</span>
            </header>

            <div class="content">
                <div class="section">
                    <h2>Daftar Endpoint</h2>
                    <p>Berikut adalah daftar semua endpoint yang tersedia, dikelompokkan berdasarkan kategori:</p>
                    
                    {$routesHtml}
                </div>
            </div>
            
            <div class="footer">
                <p>&copy; {$year} Dokumentasi API. Dibuat dengan ❤️ & Gabut.</p>
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
            
            // // Capaian Pembelajaran
            // $router->get('/cp', 'Admin\CapaianPembelajaranController@index');
            // $router->post('/cp', 'Admin\CapaianPembelajaranController@store');
            // $router->put('/cp/{id}', 'Admin\CapaianPembelajaranController@update');
            // $router->delete('/cp/{id}', 'Admin\CapaianPembelajaranController@destroy');
            // $router->post('/cp/batch', 'Admin\CapaianPembelajaranController@storeBatch');
            
            // // Tujuan Pembelajaran
            // $router->get('/tp', 'Admin\TujuanPembelajaranController@index');
            // $router->post('/tp', 'Admin\TujuanPembelajaranController@store');
            // $router->put('/tp/{id}', 'Admin\TujuanPembelajaranController@update');
            // $router->delete('/tp/{id}', 'Admin\TujuanPembelajaranController@destroy');
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
        $router->get('/kelas/template', 'Admin\KelasController@getTemplate');
        $router->get('/kelas/export', 'Admin\KelasController@export');
        $router->post('/kelas/import', 'Admin\KelasController@import');
        $router->post('/kelas/batch', 'Admin\KelasController@storeBatch');
        
        // Laporan
        $router->get('/reports/nilai', 'Admin\ReportController@nilai');
        $router->get('/reports/nilai/export', 'Admin\ReportController@exportNilai');
        $router->get('/reports/absensi', 'Admin\ReportController@absensi');
        $router->get('/reports/aktivitas', 'Admin\ReportController@aktivitas');

        // Tambahkan route ini di dalam grup admin
        $router->get('/storage/link', 'Admin\StorageController@createStorageLink');
    });

    // Guru Routes
    $router->group(['prefix' => 'guru', 'namespace' => 'Guru', 'middleware' => ['login', 'role:guru']], function () use ($router) {
        // Dashboard
        $router->get('dashboard', ['uses' => 'DashboardController@index']);
        $router->get('dashboard/kelas/{id}', ['uses' => 'DashboardController@detailKelas']);
        
        // Kelas
        $router->get('kelas', ['uses' => 'KelasController@index']);
        $router->get('kelas/{id}', ['uses' => 'KelasController@show']);
        $router->get('kelas/{id}/siswa', ['uses' => 'KelasController@listSiswa']);
        
        // Siswa
        $router->get('siswa', ['uses' => 'SiswaController@index']);
        $router->get('siswa/kelas/{kelasId}', ['uses' => 'SiswaController@getSiswaByKelas']);
        $router->get('siswa/{id}', ['uses' => 'SiswaController@show']);
        $router->post('siswa/kelas', ['uses' => 'SiswaController@addSiswaToKelas']);
        $router->put('siswa/{id}/kelas', ['uses' => 'SiswaController@updateSiswaKelas']);
        $router->delete('siswa/{id}/kelas', ['uses' => 'SiswaController@removeSiswaFromKelas']);
        
        // Mata Pelajaran
        $router->get('mapel', ['uses' => 'MataPelajaranController@index']);
        
        // Capaian Pembelajaran
        $router->group(['prefix' => 'cp'], function () use ($router) {
            $router->get('/', 'CapaianPembelajaranController@index');
            $router->post('/', 'CapaianPembelajaranController@store');
            $router->get('/{id}', 'CapaianPembelajaranController@show');
            $router->put('/{id}', 'CapaianPembelajaranController@update');
            $router->delete('/{id}', 'CapaianPembelajaranController@destroy');
            $router->post('/batch', 'CapaianPembelajaranController@storeBatch');
        });
        
        // Tujuan Pembelajaran
        $router->group(['prefix' => 'tp'], function () use ($router) {
            $router->get('/', 'TujuanPembelajaranController@index');
            $router->post('/', 'TujuanPembelajaranController@store');
            $router->get('/{id}', 'TujuanPembelajaranController@show');
            $router->put('/{id}', 'TujuanPembelajaranController@update');
            $router->delete('/{id}', 'TujuanPembelajaranController@destroy');
            $router->post('/batch', 'TujuanPembelajaranController@storeBatch');
        });

        // Presensi Bulanan
        $router->group(['prefix' => 'absensi'], function () use ($router) {
            $router->get('/', 'AbsensiController@index');
            $router->post('/bulan', 'AbsensiController@createMonth');
            $router->get('/bulan/{id}', 'AbsensiController@getMonthDetail');
            $router->get('/bulan/{id}/siswa', 'AbsensiController@getStudentsByMonth');
            $router->post('/siswa', 'AbsensiController@saveStudentAttendance');
            $router->post('/siswa/batch', 'AbsensiController@storeBatch');
            $router->put('/siswa/{id}', 'AbsensiController@updateStudentAttendance');
            $router->get('/rekap', 'AbsensiController@summary');
        });
        
        // Penilaian Siswa
        $router->group(['prefix' => 'nilai'], function () use ($router) {
            $router->get('/', 'NilaiController@index');
            $router->post('/', 'NilaiController@store');
            $router->post('/batch', 'NilaiController@storeBatchFromUI');
            $router->get('/template', 'NilaiController@getTemplate');
            $router->post('/import', 'NilaiController@import');
            $router->get('/rekap', 'RekapController@nilai');
            $router->get('/export', 'NilaiController@export');
            $router->get('/siswa-tp', 'NilaiController@getSiswaWithTP');
            $router->get('/{id}', 'NilaiController@show');
            $router->put('/{id}', 'NilaiController@update');
        });        
        
    });

    // User Activity Routes
    $router->group(['prefix' => 'activity', 'middleware' => 'login'], function () use ($router) {
        $router->get('/', 'UserActivityController@getAllActivities');
        $router->get('/sessions', 'UserActivityController@getAllSessions');
        $router->get('/logs', 'UserActivityController@getActivityLogs');
        $router->get('/dates', 'UserActivityController@getActivityLogDates');
        $router->get('/statistics', 'UserActivityController@getActivityStatistics');
        $router->get('/usage-time', 'UserActivityController@getUsageTime');
        $router->get('/all-usage-time', 'UserActivityController@getAllUsageTime');
        $router->get('/application-logs', 'UserActivityController@getApplicationLogs');
    });
});

// Tambahkan route untuk heartbeat
$router->post('/activity/heartbeat', [
    'middleware' => 'login',
    'uses' => 'ActivityController@heartbeat'
]);

Route::group(['prefix' => 'admin', 'middleware' => ['auth:api', 'role:admin']], function () {
    // ... existing routes ...
    Route::get('/export-nilai', 'Admin\ReportController@exportNilai');
});