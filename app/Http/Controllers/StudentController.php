<?php

namespace App\Http\Controllers;

use App\Models\Student;
use Illuminate\Http\Request;

class StudentController extends Controller
{
    //
    function getStudent()
    {
        $students = Student::all();
        return response()->json($students);
    }
}
