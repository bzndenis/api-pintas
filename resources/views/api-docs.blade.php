<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>API Documentation</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f5f7fa;
            color: #333;
            line-height: 1.6;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        header {
            background-color: #2c3e50;
            color: white;
            padding: 30px 0;
            text-align: center;
            border-radius: 8px 8px 0 0;
            margin-top: 20px;
        }
        header h1 {
            font-size: 2.5rem;
            margin-bottom: 10px;
        }
        header p {
            font-size: 1.2rem;
            opacity: 0.8;
        }
        .version {
            background-color: #3498db;
            color: white;
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 0.8rem;
            margin-top: 10px;
            display: inline-block;
        }
        .content {
            background-color: white;
            padding: 30px;
            border-radius: 0 0 8px 8px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
        }
        .section {
            margin-bottom: 40px;
        }
        .section h2 {
            border-bottom: 2px solid #f1f1f1;
            padding-bottom: 10px;
            margin-bottom: 20px;
            color: #2c3e50;
        }
        .endpoint {
            background-color: #f8f9fa;
            border-left: 4px solid #3498db;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 0 4px 4px 0;
        }
        .method {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 4px;
            margin-right: 10px;
            font-size: 0.8rem;
            font-weight: bold;
            color: white;
        }
        .get { background-color: #61affe; }
        .post { background-color: #49cc90; }
        .put { background-color: #fca130; }
        .delete { background-color: #f93e3e; }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }
        th, td {
            padding: 10px;
            text-align: left;
            border-bottom: 1px solid #e9ecef;
        }
        th {
            background-color: #f8f9fa;
            font-weight: 500;
        }
    </style>
</head>
<body>
    <div class="container">
        <header>
            <h1>API Documentation</h1>
            <p>Dokumentasi lengkap untuk menggunakan API kami</p>
            <span class="version">{{ $version }}</span>
        </header>

        <div class="content">
            <div class="section">
                <h2>Daftar Endpoint</h2>
                <p>Berikut adalah daftar semua endpoint yang tersedia di API:</p>
                
                <table>
                    <thead>
                        <tr>
                            <th>Method</th>
                            <th>URI</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($routes as $route)
                        <tr>
                            <td>
                                <span class="method {{ strtolower($route['method']) }}">
                                    {{ $route['method'] }}
                                </span>
                            </td>
                            <td>{{ $route['uri'] }}</td>
                            <td>{{ $route['action'] }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        
        <div class="footer">
            <p>&copy; {{ date('Y') }} API Documentation. Dibuat dengan ❤️</p>
        </div>
    </div>
</body>
</html>