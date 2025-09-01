<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Curriculum;

class CurriculumController extends Controller
{
    public function get_all_curriculum(){
        $cur = Curriculum::all();
        return response()->json($cur);
    }
}
