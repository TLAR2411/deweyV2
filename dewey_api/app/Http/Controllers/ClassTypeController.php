<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Classtype;

class ClassTypeController extends Controller
{
    public function get_all_classtype(){
        $type = Classtype::all();
        return response()->json($type);
    }
}
