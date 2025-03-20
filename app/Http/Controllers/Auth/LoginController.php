<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use App\Http\Helper\ResponseBuilder;
use Carbon\Carbon;

class LoginController extends Controller
{
    public function login(Request $request)
    {
        $this->validate($request, [
            'email' => 'required|email',
            'password' => 'required'
        ]);

        try {
            $user = User::where('email', $request->email)->first();

            if (!$user || !Hash::check($request->password, $user->password)) {
                return ResponseBuilder::error(401, "Email atau password salah");
            }

            if (!$user->is_active) {
                return ResponseBuilder::error(403, "Akun anda tidak aktif");
            }

            // Generate token
            $token = Str::random(80);
            
            // Update user dengan token baru di remember_token
            $user->update([
                'remember_token' => $token,
                'last_login' => Carbon::now()
            ]);

            // Load relasi yang diperlukan
            $user->load('sekolah');

            return ResponseBuilder::success(200, "Login berhasil", [
                'user' => $user,
                'token' => $token
            ]);

        } catch (\Exception $e) {
            return ResponseBuilder::error(500, "Gagal melakukan login: " . $e->getMessage());
        }
    }

    public function logout(Request $request)
    {
        try {
            $user = auth()->user();
            
            // Hapus token
            $user->update(['remember_token' => null]);

            return ResponseBuilder::success(200, "Logout berhasil");
        } catch (\Exception $e) {
            return ResponseBuilder::error(500, "Gagal melakukan logout: " . $e->getMessage());
        }
    }
} 