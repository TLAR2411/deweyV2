<?php

namespace App\Http\Controllers;

use App\Models\GradeLevel;
use App\Models\SettingScoreConfig;
use App\Models\SettingScoreList;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SettingScoreConfigController extends Controller
{
    public function getSettingScoreConfig(Request $request)
    {
        try {
            $campusId = $request->input('campus_id', '*');
            $data = SettingScoreConfig::query()
                ->leftJoin('academicyears as year', 'tbl_config_avg.id_academic', '=', 'year.id')
                ->leftJoin('education_levels as edu', 'tbl_config_avg.edu_id', '=', 'edu.id')
                ->leftJoin('campus', 'tbl_config_avg.campus_id', '=', 'campus.id')
                ->whereBranch($campusId)
                ->select(
                    "tbl_config_avg.*",
                    'edu.edu_name',
                    'year.name as yearName',
                    'campus.name as campusName'
                )
                ->get();

            return response()->json($data);
        } catch (\Throwable $th) {
            //throw $th;
        }
    }

    public function addSettingScoreConfig(Request $request)
    {
        try {

            $grade_level_id = DB::table('grade_levels')
                ->where('edu_id', $request->edu_id)
                ->pluck('id');
            foreach ($grade_level_id as $gl) {
                SettingScoreList::create([
                    'id_config' => $request->id,
                    'grade_level_id' => $gl,
                    'avg_month' => 12,
                    'avg_semester_one' => 12,
                    'avg_semester_two' => 12,
                ]);
            }

            SettingScoreConfig::create([
                'edu_id' => $request->edu_id,
                'id_academic' => $request->id_academic,
                'campus_id' => $request->campus_id
            ]);
            return response()->json([
                "message" => "បង្កើតបានជោគជ័យ",
                "status" => 200

            ]);
        } catch (\Throwable $th) {
            //throw $th;
        }
    }

    public function deleteSettingScoreConfig($id)
    {
        try {
            $setting = SettingScoreConfig::findOrFail($id);
            $setting->delete();
            return response()->json([
                "message" => "លុបបានជោគជ័យ",
                "status" => 200
            ]);
        } catch (\Throwable $th) {
            //throw $th;
        }
    }
}
