<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class SettingController extends Controller
{
    public function index()
    {
        return view('admins.settings.index');
    }

    public function smtp()
    {
        return view('admins.settings.smtp');
    }

    public function website()
    {
        return view('admins.settings.website');
    }
}
