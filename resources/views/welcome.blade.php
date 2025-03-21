<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>API Documentation</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap"
        rel="stylesheet">
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
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
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

        .endpoint h3 {
            margin-bottom: 10px;
            display: flex;
            align-items: center;
        }

        .method {
            padding: 5px 10px;
            border-radius: 4px;
            margin-right: 10px;
            font-size: 0.8rem;
            font-weight: bold;
        }

        .get {
            background-color: #61affe;
            color: white;
        }

        .post {
            background-color: #49cc90;
            color: white;
        }

        .put {
            background-color: #fca130;
            color: white;
        }

        .delete {
            background-color: #f93e3e;
            color: white;
        }

        .endpoint-url {
            font-family: monospace;
            background-color: #e9ecef;
            padding: 5px 10px;
            border-radius: 4px;
            margin-bottom: 10px;
            display: block;
        }

        .params {
            margin-top: 15px;
        }

        .params h4 {
            margin-bottom: 10px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }

        th,
        td {
            padding: 10px;
            text-align: left;
            border-bottom: 1px solid #e9ecef;
        }

        th {
            background-color: #f8f9fa;
            font-weight: 500;
        }

        .response {
            background-color: #282c34;
            color: #abb2bf;
            padding: 15px;
            border-radius: 4px;
            overflow-x: auto;
            font-family: monospace;
        }

        .footer {
            text-align: center;
            margin-top: 40px;
            color: #6c757d;
            font-size: 0.9rem;
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
                <h2>Pengenalan</h2>
                <p>Selamat datang di dokumentasi API kami. API ini menyediakan akses ke berbagai fitur dan data aplikasi
                    melalui HTTP requests.</p>
                <p>Semua endpoint memerlukan autentikasi kecuali dinyatakan lain.</p>
            </div>

            <div class="section">
                <h2>Autentikasi</h2>

                <div class="endpoint">
                    <h3><span class="method post">POST</span> Register</h3>
                    <code class="endpoint-url">/auth/register</code>
                    <p>Mendaftarkan pengguna baru.</p>

                    <div class="params">
                        <h4>Parameter:</h4>
                        <table>
                            <thead>
                                <tr>
                                    <th>Nama</th>
                                    <th>Tipe</th>
                                    <th>Wajib</th>
                                    <th>Deskripsi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>name</td>
                                    <td>string</td>
                                    <td>Ya</td>
                                    <td>Nama lengkap pengguna</td>
                                </tr>
                                <tr>
                                    <td>email</td>
                                    <td>string</td>
                                    <td>Ya</td>
                                    <td>Alamat email pengguna</td>
                                </tr>
                                <tr>
                                    <td>password</td>
                                    <td>string</td>
                                    <td>Ya</td>
                                    <td>Password pengguna (min. 8 karakter)</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="params">
                        <h4>Contoh Response:</h4>
                        <pre class="response">{
  "status": "success",
  "message": "Registrasi berhasil",
  "data": {
    "user": {
      "id": 1,
      "name": "John Doe",
      "email": "john@example.com",
      "created_at": "2023-01-01T00:00:00.000000Z"
    },
    "token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9..."
  }
}</pre>
                    </div>
                </div>

                <div class="endpoint">
                    <h3><span class="method post">POST</span> Login</h3>
                    <code class="endpoint-url">/auth/login</code>
                    <p>Melakukan autentikasi pengguna dan mendapatkan token.</p>

                    <div class="params">
                        <h4>Parameter:</h4>
                        <table>
                            <thead>
                                <tr>
                                    <th>Nama</th>
                                    <th>Tipe</th>
                                    <th>Wajib</th>
                                    <th>Deskripsi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>email</td>
                                    <td>string</td>
                                    <td>Ya</td>
                                    <td>Alamat email pengguna</td>
                                </tr>
                                <tr>
                                    <td>password</td>
                                    <td>string</td>
                                    <td>Ya</td>
                                    <td>Password pengguna</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="params">
                        <h4>Contoh Response:</h4>
                        <pre class="response">{
  "status": "success",
  "message": "Login berhasil",
  "data": {
    "user": {
      "id": 1,
      "name": "John Doe",
      "email": "john@example.com"
    },
    "token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9..."
  }
}</pre>
                    </div>
                </div>

                <div class="endpoint">
                    <h3><span class="method post">POST</span> Logout</h3>
                    <code class="endpoint-url">/auth/logout</code>
                    <p>Menghapus token autentikasi pengguna.</p>

                    <div class="params">
                        <h4>Headers:</h4>
                        <table>
                            <thead>
                                <tr>
                                    <th>Nama</th>
                                    <th>Wajib</th>
                                    <th>Deskripsi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>Authorization</td>
                                    <td>Ya</td>
                                    <td>Bearer {token}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="params">
                        <h4>Contoh Response:</h4>
                        <pre class="response">{
  "status": "success",
  "message": "Logout berhasil"
}</pre>
                    </div>
                </div>
            </div>

            <div class="section">
                <h2>User</h2>

                <div class="endpoint">
                    <h3><span class="method get">GET</span> Daftar Pengguna</h3>
                    <code class="endpoint-url">/user</code>
                    <p>Mendapatkan daftar semua pengguna (hanya admin).</p>

                    <div class="params">
                        <h4>Headers:</h4>
                        <table>
                            <thead>
                                <tr>
                                    <th>Nama</th>
                                    <th>Wajib</th>
                                    <th>Deskripsi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>Authorization</td>
                                    <td>Ya</td>
                                    <td>Bearer {token}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="params">
                        <h4>Contoh Response:</h4>
                        <pre class="response">{
  "status": "success",
  "data": [
    {
      "id": 1,
      "name": "John Doe",
      "email": "john@example.com",
      "role": "admin",
      "created_at": "2023-01-01T00:00:00.000000Z"
    },
    {
      "id": 2,
      "name": "Jane Smith",
      "email": "jane@example.com",
      "role": "user",
      "created_at": "2023-01-02T00:00:00.000000Z"
    }
  ]
}</pre>
                    </div>
                </div>

                <div class="endpoint">
                    <h3><span class="method get">GET</span> Profil Pengguna</h3>
                    <code class="endpoint-url">/user/profile</code>
                    <p>Mendapatkan profil pengguna yang sedang login.</p>

                    <div class="params">
                        <h4>Headers:</h4>
                        <table>
                            <thead>
                                <tr>
                                    <th>Nama</th>
                                    <th>Wajib</th>
                                    <th>Deskripsi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>Authorization</td>
                                    <td>Ya</td>
                                    <td>Bearer {token}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="params">
                        <h4>Contoh Response:</h4>
                        <pre class="response">{
  "status": "success",
  "data": {
    "id": 1,
    "name": "John Doe",
    "email": "john@example.com",
    "role": "admin",
    "created_at": "2023-01-01T00:00:00.000000Z"
  }
}</pre>
                    </div>
                </div>

                <div class="endpoint">
                    <h3><span class="method post">POST</span> Ganti Password</h3>
                    <code class="endpoint-url">/user/change-password</code>
                    <p>Mengubah password pengguna.</p>

                    <div class="params">
                        <h4>Headers:</h4>
                        <table>
                            <thead>
                                <tr>
                                    <th>Nama</th>
                                    <th>Wajib</th>
                                    <th>Deskripsi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>Authorization</td>
                                    <td>Ya</td>
                                    <td>Bearer {token}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="params">
                        <h4>Parameter:</h4>
                        <table>
                            <thead>
                                <tr>
                                    <th>Nama</th>
                                    <th>Tipe</th>
                                    <th>Wajib</th>
                                    <th>Deskripsi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>current_password</td>
                                    <td>string</td>
                                    <td>Ya</td>
                                    <td>Password saat ini</td>
                                </tr>
                                <tr>
                                    <td>new_password</td>
                                    <td>string</td>
                                    <td>Ya</td>
                                    <td>Password baru (min. 8 karakter)</td>
                                </tr>
                                <tr>
                                    <td>new_password_confirmation</td>
                                    <td>string</td>
                                    <td>Ya</td>
                                    <td>Konfirmasi password baru</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="params">
                        <h4>Contoh Response:</h4>
                        <pre class="response">{
  "status": "success",
  "message": "Password berhasil diubah"
}</pre>
                    </div>
                </div>
            </div>

            <div class="footer">
                <p>&copy; {{ date('Y') }} API Documentation. Dibuat dengan ❤️</p>
            </div>
        </div>
    </div>
</body>

</html>