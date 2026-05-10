<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class HelpCenterController extends Controller
{
    public function index(): View
    {
        return view('help.center');
    }
}
