<?php

namespace App\Http\Controllers;

use App\Models\Month;
use Illuminate\Http\Request;

class MonthController extends Controller
{
    public function get_all_month()
    {
        $month = Month::all();

        return response()->json($month);
    }
}
