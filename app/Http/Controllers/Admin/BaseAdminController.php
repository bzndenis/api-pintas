<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

class BaseAdminController extends Controller
{
    public function __construct()
    {
        $this->middleware('login');
        $this->middleware('admin');
        $this->middleware('autologout:15');
    }
} 