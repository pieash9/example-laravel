<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\View;

class UserController extends Controller
{
    //
    function users()
    {
        $users = DB::select('select * from users');
        return view('users', ['users' => $users]);
    }
}
