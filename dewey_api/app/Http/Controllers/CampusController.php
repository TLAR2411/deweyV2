<?php

namespace App\Http\Controllers;

use App\Models\Campus;
use Illuminate\Http\Request;

class CampusController extends Controller
{
    public function getAllCampus()
    {
        $campus = Campus::all()->where("deleted", false);
        return response()->json($campus);
    }
}
