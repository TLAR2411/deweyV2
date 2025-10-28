<?php

namespace App\Http\Controllers\Transfer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TransferPrimaryController extends Controller
{
    public function findStudentTransfer(Request $request)
    {
        $studentId = $request->studentId;
        $eduId = $request->eduId;
        $classId = $request->classId;
        $monthId = $request->monthId;

        if ($eduId == '1' || $eduId == 1) {
            $check = DB::table('transafer_score_primary')
                ->where('class_id', $classId)
                ->where('student_id', $studentId)
                ->where('month_id', $monthId)
                ->count();
            if ($check == 0) {
                return response()->json([
                    "status" => 0
                ]);
            } else {
                $data = DB::table('transafer_score_primary')
                    ->where('class_id', $classId)
                    ->where('student_id', $studentId)
                    ->where('month_id', $monthId)
                    ->get();

                return response()->json([
                    "status" => 1,
                    "data" => $data
                ]);
            }
        }
    }

    public function addScoreStudentTransfer(Request $request)
    {
        $eduId = 1;
        $avg = $request->avg_m;

        if ($eduId == 1 || $eduId == '1') {
            // First check if record already exists
            $check = DB::table('transafer_score_primary')
                ->where('student_id', $request->student_id)
                ->where('class_id', $request->class_id)
                ->where('month_id', $request->month_id)
                ->count();
            if ($check == 0) {
                DB::table('transafer_score_primary')->insert([
                    'student_id' => $request->student_id,
                    'class_id' => $request->class_id,
                    'month_id' => $request->month_id,
                    'avg_m' => $request->avg_m,
                    'listent' => $request->listent,
                    'speaking' => $request->speaking,
                    'writing' => $request->writing,
                    'reading' => $request->reading,
                    'essay' => $request->essay,
                    'grammar' => $request->grammar,
                    'math' => $request->math,
                    'chemistry' => $request->chemistry,
                    'physical' => $request->physical,
                    'history' => $request->history,
                    'morality' => $request->morality,
                    'art' => $request->art,
                    'word' => $request->word,
                    'pe' => $request->pe,
                    'homework' => $request->homework,
                    'healthy' => $request->healthy,
                    'steam' => $request->steam,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                return response()->json([
                    'status' => 1,
                    'message' => 'ពិន្ទុបញ្ចូលបានជោគជ័យ',
                ]);
            } else {
                DB::table('transafer_score_primary')
                    ->where('student_id', $request->student_id)
                    ->where('class_id', $request->class_id)
                    ->where('month_id', $request->month_id)
                    ->update([
                        'avg_m' => $request->avg_m,
                        'listent' => $request->listent,
                        'speaking' => $request->speaking,
                        'writing' => $request->writing,
                        'reading' => $request->reading,
                        'essay' => $request->essay,
                        'grammar' => $request->grammar,
                        'math' => $request->math,
                        'chemistry' => $request->chemistry,
                        'physical' => $request->physical,
                        'history' => $request->history,
                        'morality' => $request->morality,
                        'art' => $request->art,
                        'word' => $request->word,
                        'pe' => $request->pe,
                        'homework' => $request->homework,
                        'healthy' => $request->healthy,
                        'steam' => $request->steam,
                        'updated_at' => now(),
                    ]);

                return response()->json([
                    'status' => 1,
                    'message' => 'Score updated successfully',
                ]);
            }
        }
    }
}
