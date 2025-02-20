<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Sondage;

class AdminController extends Controller
{

    public function manageUsers()
    {
        $users = User::all(); 
        return view('admin.users', compact('users'));
    }


    public function manageSondages()
    {
        $sondages = Sondage::all(); 
        return view('admin.sondages', compact('sondages'));
    }
}

