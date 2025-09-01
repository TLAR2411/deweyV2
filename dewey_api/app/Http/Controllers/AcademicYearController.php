<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\AcademicYearRequest;
use App\Models\AcademicYear;

class AcademicYearController extends Controller
{
    public function add_year(AcademicYearRequest $request)
    {
        $year = AcademicYear::create($request->validated());

        if ($year) {
            return response()->json([
                "message" => "បង្កើតបានជោគជ័យ",
                "status" => 200
            ]);
        }
    }

    public function getOneYear($id)
    {
        $y = AcademicYear::findOrFail($id);

        return response()->json($y);
    }

    public function updateYear(AcademicYearRequest $request)
    {
        $year = AcademicYear::findOrFail($request->id)->update($request->validated());

        if ($year) {
            return response()->json([
                "message" => "កែប្រែបានជោគជ័យ",
                "status" => 200
            ]);
        }
    }

    public function get_all_year()
    {
        $year = AcademicYear::all();
        return response()->json($year);
    }

    public function delete_year($id)
    {
        $y = AcademicYear::findOrFail($id);
        $y->delete();

        return response()->json([
            "message" => "Delete Success",
            "status" => 200
        ]);
    }
}
