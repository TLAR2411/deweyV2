<?php

namespace App\Http\Controllers;

use App\Models\Classroom;
use App\Models\Student;
use App\Models\Teacher;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function dashboard(Request $request)
    {
        $campusId = $request->input('campus_id', '*');

        $student = Student::whereBranch($campusId)->count();

        $studentStop = Student::whereBranch($campusId)->where('deleted', '!=', 0)->count();

        $classroom = Classroom::whereBranch($campusId)->where('deleted', '!=', '1')->count();

        $teacher = Teacher::whereBranch($campusId)->count();

        return response()->json([
            "allStudent" => $student,
            "studentStop" => $studentStop,
            "classroom" => $classroom,
            "teacher" => $teacher
        ]);
    }
}
