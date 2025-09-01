<?php

namespace App\Http\Controllers;

use App\Models\SettingSemesterList;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SettingSemesterListController extends Controller
{
    public function getSemesterList(Request $request, $id)
    {
        $months = DB::table('month')->pluck('name_kh', 'id');

        $dataSemester = SettingSemesterList::query()
            ->leftJoin('semester_setting as ss', 'setting_semester_list.setting_semester_month_id', '=', 'ss.id')
            ->leftJoin('academicyears as year', 'setting_semester_list.year_id', '=', 'year.id')
            ->leftJoin('month as mo', 'setting_semester_list.semester_month1', '=', 'mo.id')
            ->leftJoin('month as m', 'setting_semester_list.semester_month2', '=', 'm.id')
            ->leftJoin('education_levels as edu', 'setting_semester_list.edu_id', '=', 'edu.id')
            ->leftJoin('grade_levels as gl', 'setting_semester_list.grade_level_id', '=', 'gl.id')
            ->where('setting_semester_list.setting_semester_month_id', '=', $id)
            ->select(
                'setting_semester_list.id_setting_list',
                'setting_semester_list.three_months_semester1',
                'setting_semester_list.three_months_semester2',
                'setting_semester_list.semester_month1',
                'setting_semester_list.semester_month2',
                'mo.name_kh as semester_month1',
                'm.name_kh as semester_month2',
                'edu.edu_name',
                'year.name as yearName',
                "gl.level as level",
                "ss.id as setting_semester_month_id"
            )
            ->get();

        $result = $dataSemester->map(function ($item) use ($months) {
            return [
                'id_setting_list' => $item->id_setting_list,
                'semester_month1' => $item->semester_month1,
                'semester_month2' => $item->semester_month2,
                'edu_name' => $item->edu_name,
                'level' => $item->level,
                'yearName' => $item->yearName,
                'setting_semester_month_id' => $item->setting_semester_month_id,
                'three_months_semester1' => collect(explode(',', $item->three_months_semester1))
                    ->map(fn($m) => $months[(int)$m] ?? null)
                    ->implode(', '),
                'three_months_semester2' => collect(explode(',', $item->three_months_semester2))
                    ->map(fn($m) => $months[(int)$m] ?? null)
                    ->implode(', '),
            ];
        });

        return response()->json($result);
    }

    public function getOneSemesterList($id)
    {
        $semesterData = DB::table('setting_semester_list')->where('id_setting_list', $id)->first();

        // Check if record exists
        if (!$semesterData) {
            return response()->json([
                'status' => 404,
                'message' => 'Semester data not found'
            ], 404);
        }

        $result = [
            'id' => $semesterData->id_setting_list,
            'semester_month1' => $semesterData->semester_month1,
            'semester_month2' => $semesterData->semester_month2,
            'edu_id' => $semesterData->edu_id,
            'level' => $semesterData->grade_level_id,
            'year_id' => $semesterData->year_id,
            'three_months_semester1' => collect(explode(',', $semesterData->three_months_semester1))
                ->map(fn($m) => (int) $m)->filter()->values(),
            'three_months_semester2' => collect(explode(',', $semesterData->three_months_semester2))
                ->map(fn($m) => (int) $m)->filter()->values(),
        ];

        return response()->json($result);
    }

    public function updateSemesterlist(Request $request)
    {
        try {
            // Check if record exists first
            $semesterData = DB::table('setting_semester_list')
                ->where('id_setting_list', $request->id)
                ->first();

            if (!$semesterData) {
                return response()->json([
                    "status" => 404,
                    "message" => "ទិន្នន័យមិនត្រូវបានរកឃើញ"
                ], 404);
            }

            // Use DB query builder to update (not the result object)
            $updated = DB::table('setting_semester_list')
                ->where('id_setting_list', $request->id)
                ->update([
                    "semester_month1" => $request->semester_month1,
                    "semester_month2" => $request->semester_month2,
                    "three_months_semester1" => $request->three_months_semester1,
                    "three_months_semester2" => $request->three_months_semester2,
                    "edu_id" => $request->edu_id,
                    "campus_id" => $request->campus_id,
                    "year_id" => $request->year_id,
                    'grade_level_id' => $request->level
                ]);

            if ($updated) {
                return response()->json([
                    "status" => 200,
                    "message" => "កែប្រែបានជោគជ័យ",
                    "data" => $request->all()
                ]);
            } else {
                return response()->json([
                    "status" => 500,
                    "message" => "ការកែប្រែបរាជ័យ - គ្មានទិន្នន័យត្រូវបានកែប្រែ"
                ]);
            }
        } catch (\Throwable $th) {
            return response()->json([
                "status" => 500,
                "message" => $th->getMessage()
            ]);
        }
    }
}
