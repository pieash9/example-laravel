<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\View;

class UserController extends Controller
{
    //
    function getUser()
    {
        $response = Http::get('https://jsonplaceholder.typicode.com/users/1');
        $data = $response->body();
       return view('users', ['data' => json_decode($data)]);
    }
}
