<?php

namespace App\Http\Controllers;

use App\Models\SettingMonthSemester;
use App\Models\SettingSemesterList;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class SettingMonthSemesterController extends Controller
{
    public function createSemester(Request $request)
    {
        DB::beginTransaction();
        try {
            // Create SettingMonthSemester first
            $data = SettingMonthSemester::create($request->all());

            // Get all grade_level_id for the given edu_id
            $grade_level_ids = DB::table('grade_levels')
                ->where('edu_id', $request->edu_id)
                ->pluck('id');

            // Insert for each grade level

            // ✅ ADD THIS CHECK
            if (!$data || !$data->id) {
                return response()->json([
                    "status" => 500,
                    "message" => "Failed to create SettingMonthSemester record"
                ]);
            }

            foreach ($grade_level_ids as $gl) {
                SettingSemesterList::create([
                    'setting_semester_month_id' => $data->id, // ✅ Correct
                    'grade_level_id' => $gl,
                    'semester_month1' => $request->semester_month1,
                    'semester_month2' => $request->semester_month2,
                    'three_months_semester1' => $request->three_months_semester1,
                    'three_months_semester2' => $request->three_months_semester2,
                    'year_id' => $request->year_id,
                    'edu_id' => $request->edu_id,
                    'campus_id' => $request->campus_id,
                ]);
            }
            DB::commit();

            return response()->json([
                "status" => 200,
                "message" => "បង្កើតបានជោគជ័យ",
                "data" => $data
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json([
                "status" => 500,
                "message" => $th->getMessage()
            ]);
        }
    }



    public function getSemester(Request $request)
    {
        // Step 1: Get months list (id => name)
        $months = DB::table('month')->pluck('name_kh', 'id');

        $campusId = $request->input('campus_id', '*');

        // Step 2: Get semester settings with joins for semester_month1, semester_month2
        $dataSemester = SettingMonthSemester::query()
            ->leftJoin('academicyears as year', 'semester_setting.year_id', '=', 'year.id')
            ->leftJoin('month as mo', 'semester_setting.semester_month1', '=', 'mo.id')
            ->leftJoin('month as m', 'semester_setting.semester_month2', '=', 'm.id')
            ->leftJoin('education_levels as edu', 'semester_setting.edu_id', '=', 'edu.id')
            ->whereBranch($campusId)
            ->select(
                'semester_setting.id',
                'semester_setting.three_months_semester1',
                'semester_setting.three_months_semester2',
                'mo.name_kh as semester_month1',
                'm.name_kh as semester_month2',
                'edu.edu_name',
                'year.name as yearName'
            )
            ->get();

        // Step 3: Convert comma-separated fields into month names
        $result = $dataSemester->map(function ($item) use ($months) {
            return [
                'id' => $item->id,
                'semester_month1' => $item->semester_month1,
                'semester_month2' => $item->semester_month2,
                'edu_name' => $item->edu_name,
                'yearName' => $item->yearName,
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

    public function getOneSemester($id)
    {
        $semesterData = SettingMonthSemester::findOrFail($id);

        $result = [
            'id' => $semesterData->id,
            'semester_month1' => $semesterData->semester_month1,
            'semester_month2' => $semesterData->semester_month2,
            'edu_id' => $semesterData->edu_id,
            'year_id' => $semesterData->year_id,
            'three_months_semester1' => collect(explode(',', $semesterData->three_months_semester1))
                ->map(fn($m) => (int) $m), // convert string to int
            'three_months_semester2' => collect(explode(',', $semesterData->three_months_semester2))
                ->map(fn($m) => (int) $m), // convert string to int
        ];

        return response()->json($result);
    }


    public function deleteSemester($id)
    {
        $semester = SettingMonthSemester::findOrFail($id);
        $semester->delete();

        return response()->json([
            "status" => 200,
            "message" => "លុបបានជោគជ័យ"
        ]);
    }

    public function updateSemester(Request $request)
    {
        $semesterData = SettingMonthSemester::findOrFail($request->id);
        $semesterData->update($request->all());

        return response()->json([
            "status" => 200,
            "message" => "កែប្រែបានជោគជ័យ"
        ]);
    }
}
