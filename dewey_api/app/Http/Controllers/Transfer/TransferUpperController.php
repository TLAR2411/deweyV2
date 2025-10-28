<?php

namespace App\Http\Controllers\Transfer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TransferUpperController extends Controller
{
    public function findStudentTransferUpper(Request $request)
    {
        $studentId = $request->studentId;
        $eduId = $request->eduId;
        $classId = $request->classId;
        $monthId = $request->monthId;

        $check = DB::table('transafer_score_upper')
            ->where('class_id', $classId)
            ->where('student_id', $studentId)
            ->where('month_id', $monthId)
            ->count();
        if ($check == 0) {
            return response()->json([
                "status" => 0
            ]);
        } else {
            $data = DB::table('transafer_score_upper')
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

    public function addScoreStudentTransferUpper(Request $request)
    {
        $check = DB::table('transafer_score_upper')
            ->where('student_id', $request->student_id)
            ->where('class_id', $request->class_id)
            ->where('month_id', $request->month_id)
            ->count();

        if ($check == 0) {
            DB::table('transafer_score_upper')->insert([
                'student_id' => $request->student_id,
                'class_id' => $request->class_id,
                'month_id' => $request->month_id,
                'avg_m' => $request->avg_m,
                'khmer' => $request->khmer,
                'morality' => $request->morality,
                'history' => $request->history,
                'geography' => $request->geography,
                'math' => $request->math,
                'physical' => $request->physical,
                'chemistry' => $request->chemistry,
                'biology' => $request->biology,
                'earth_science' => $request->earth_science,
                'english' => $request->english,
                'pe' => $request->pe,
                'computer' => $request->computer,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            return response()->json([
                'status' => 1,
                'message' => 'ពិន្ទុបញ្ចូលបានជោគជ័យ',
            ]);
        } else {
            DB::table('transafer_score_upper')
                ->where('student_id', $request->student_id)
                ->where('class_id', $request->class_id)
                ->where('month_id', $request->month_id)
                ->update([
                    'avg_m' => $request->avg_m,

                    'khmer' => $request->khmer,
                    'morality' => $request->morality,
                    'history' => $request->history,
                    'geography' => $request->geography,
                    'math' => $request->math,
                    'physical' => $request->physical,
                    'chemistry' => $request->chemistry,
                    'biology' => $request->biology,
                    'earth_science' => $request->earth_science,
                    'english' => $request->english,
                    'pe' => $request->pe,
                    'computer' => $request->computer,
                    'updated_at' => now(),
                ]);

            return response()->json([
                'status' => 1,
                'message' => 'ពិន្ទុកែប្រែបានជោគជ័យ',
            ]);
        }
    }
}
