<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class UserController extends Controller
{
    //
    function getUser(){
        return "Pieash Ahmed";
    }

    function getUserName($name){
        return "User name is: " . $name;
    }
}
