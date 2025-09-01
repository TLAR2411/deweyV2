<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Grade;
use App\Http\Requests\GradeRequest;
use App\Models\GradeLevel;

class GradeConroller extends Controller
{
    public function get_all_grade()
    {
        $grade = DB::table("grades as g")
            ->leftJoin("curriculums as c", "g.cur_id", "=", "c.id")
            ->leftJoin("education_levels as edu", "g.edu_id", "=", "edu.id")
            ->select([
                "g.id",
                "g.name",
                'c.name as curriculum_name',
                'edu.id as edu_id',
                'edu.edu_name',
                DB::raw("COALESCE(edu.edu_name, 'N/A') as edu_name")

            ])
            ->where("g.deleted", "!=", 1)
            ->get();
        return response()->json($grade);
    }

    public function add_grade(GradeRequest $request)
    {
        $g = $request->grade_level_id;
        $edu_id = $request->edu_id;

        if ($edu_id) {
            if ($g >= 1 && $g <= 6 && $edu_id != 1) {
                return response()->json(
                    ['message' => " ថ្នាក់ទី $g ត្រូវស្ថិតនៅក្នុងកម្រិតសិក្សាថ្នាក់បឋម"],
                    400
                );
            } else if ($g >= 7 && $g <= 9 && $edu_id != 2) {
                return response()->json(
                    ['message' => " ថ្នាក់ទី $g ត្រូវស្ថិតនៅក្នុងកម្រិតសិក្សាថ្នាក់អនុវិទ្យាល័យ"],
                    400
                );
            } else if ($g >= 10 && $g <= 12 && $edu_id != 3) {
                return response()->json(
                    ['message' => " ថ្នាក់ទី $g ត្រូវស្ថិតនៅក្នុងកម្រិតសិក្សាថ្នាក់វិទ្យាល័យ"],
                    400
                );
            }
        }

        $gr = $request->name;
        // Check for duplicate
        $existingGrade = DB::table('grades')
            ->where('name', $request->name)
            ->where('cur_id', $request->cur_id)
            ->exists();

        // If grade already exists, return a message
        if ($existingGrade) {
            return response()->json([
                'message' => "កម្រិតថ្នាក់ទី $gr បានបង្កើតម្ដងរួចហើយ"
            ], 400); // 400 is Bad Request
        }

        $grade = Grade::create($request->validated() + [
            'edu_id' => $request->edu_id,
            'name' => $request->name,
            'symbol' => $request->symbol
        ]);
        if ($grade) {
            return response()->json([
                'message' => 'បង្កើតបានជោគជ័យ',
                'status' => 200
            ]);
        }
    }

    public function delete_grade($id)
    {
        $g = Grade::findOrFail($id);
        $g->delete();

        return response()->json("Grade Delete Success");
    }

    public function get_grade_level()
    {
        $level = GradeLevel::all();
        return response()->json($level);
    }

    public function getOneGrade($id)
    {
        $g = Grade::findOrFail($id);

        return response()->json($g);
    }

    public function updateGrade(GradeRequest $request)
    {
        $g = $request->grade_level_id;
        $edu_id = $request->edu_id;

        if ($edu_id) {
            if ($g >= 1 && $g <= 6 && $edu_id != 1) {
                return response()->json(
                    ['message' => " ថ្នាក់ទី $g ត្រូវស្ថិតនៅក្នុងកម្រិតថ្នាក់បឋម"],
                    400
                );
            } else if ($g >= 7 && $g <= 9 && $edu_id != 2) {
                return response()->json(
                    ['message' => " ថ្នាក់ទី $g ត្រូវស្ថិតនៅក្នុងកម្រិតថ្នាក់អនុវិទ្យាល័យ"],
                    400
                );
            } else if ($g >= 10 && $g <= 12 && $edu_id != 3) {
                return response()->json(
                    ['message' => " ថ្នាក់ទី $g ត្រូវស្ថិតនៅក្នុងកម្រិតសិក្សាវិទ្យាល័យ"],
                    400
                );
            }

            $gr = $request->name;
            // Check for duplicate
            $existingGrade = DB::table('grades')
                ->where('name', $request->name)
                ->where('cur_id', $request->cur_id)
                ->exists();

            // If grade already exists, return a message
            if ($existingGrade) {
                return response()->json([
                    'message' => "ថ្នាក់ទី $gr បានបង្កើតម្ដងរួចហើយ"
                ], 400); // 400 is Bad Request
            }

            $grade = Grade::findOrFail($request->id)->update($request->validated() + [
                'edu_id' => $request->edu_id,
                'name' => $request->name,
                'symbol' => $request->symbol
            ]);
            if ($grade) {
                return response()->json([
                    'message' => 'កែប្រែបានជោគជ័យ',
                    'status' => 200
                ]);
            }
        }
    }
}
