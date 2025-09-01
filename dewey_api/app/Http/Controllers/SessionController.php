<?php

namespace App\Http\Controllers;

use App\Models\Session;
use Illuminate\Http\Request;

class SessionController extends Controller
{
    public function get_session(){
        $session = Session::all();
        return response()->json($session);
    }
}
