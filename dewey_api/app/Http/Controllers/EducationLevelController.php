<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\EducationLevel;

class EducationLevelController extends Controller
{
    public function get_all_edu(){
        $edu = EducationLevel::all();
        return response()->json($edu);
    }
}
