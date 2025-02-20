<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Sondage;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $sondages = Sondage::where('user_id', Auth::id())->get();
        return view('dashboard', compact('sondages'));
    }
}

