<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;

class BaseGuruController extends Controller
{
    public function __construct()
    {
        $this->middleware('login');
        $this->middleware('guru');
    }
} 