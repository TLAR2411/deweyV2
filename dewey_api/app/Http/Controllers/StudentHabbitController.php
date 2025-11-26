<?php

namespace App\Http\Controllers;

use App\Models\StudentHabbit;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StudentHabbitController extends Controller
{
    public function showStudentHabbit(Request $request)
    {
        $checkStudent = DB::table("tbl_study_habits")
            ->where('class_id', $request->class_id)
            ->where('month_id', $request->month_id)
            ->count();

        if ($checkStudent == 0) {
            $student_class = DB::table('view_studentscore')
                ->where('class_id', $request->class_id)
                ->where('deleted', 0)
                ->where('is_transfer', 0)
                ->orderby('sort', 'asc')
                // ->orderby('kh_name')
                ->get();
            return response()->json(
                [
                    "student_class" => $student_class,
                    "status" => 1,
                    'level' => $request->level
                ]
            );
        } else {
            $seachStudent = DB::table('tbl_study_habits')
                ->join('students', 'tbl_study_habits.student_id', '=', 'students.id')
                ->where('class_id', $request->class_id)
                ->where('month_id', $request->month_id)
                ->select(DB::raw('tbl_study_habits.*,students.kh_name'))
                ->get();

            $seachStudent2 = DB::table('student_class')
                ->join('students', 'student_class.student_id', '=', 'students.id')
                ->join('classrooms', 'student_class.class_id', '=', 'classrooms.id')
                ->where('classrooms.id', $request->class_id)
                ->where('student_class.deleted', 0)

                ->whereRaw('student_class.student_id NOT IN (SELECT student_id from tbl_study_habits where tbl_study_habits.class_id=' . $request->class_id . ' and
                        tbl_study_habits.month_id = ' . $request->month_id . ' )')
                ->select(DB::raw('null as respects_school, null as pay_attention,' . $request->class_id . ' as class_id, null as neat_and_tidy,null as
                        work , null as get_along_with_other	,null as take_care,  null as id, students.id as student_id ,
                        ' . $request->month_id . ' as month_id,students.id as student_id,
                        students.kh_name'))
                ->get()->toArray();

            if (count($seachStudent2) > 0) {
                $item = array();
                foreach ($seachStudent as $obj) {
                    $array1[] = (array) $obj;
                }
                // dd($array1);
                $new_data = array_merge($array1, $seachStudent2);
                //   dd($new_data);
                return response()->json(
                    [
                        "student_class" => $new_data,
                        "status" => 2,
                        'level' => $request->level
                    ]
                );
            } else {
                return response()->json(
                    [
                        "student_class" => $seachStudent,
                        "status" => 3,
                        'level' => $request->level
                    ]
                );
            }
        }
    }


    public function saveStudentHabit(Request $request)
    {
        try {
            // Delete existing records if status is not 1
            if ($request->status != 1) {
                $del = DB::table('tbl_study_habits')
                    ->where('class_id', $request->class_id)
                    ->where('month_id', $request->month_id)
                    ->delete();

                if ($del) {
                    $students = array_filter($request->all(), function ($key) {
                        return is_numeric($key);
                    }, ARRAY_FILTER_USE_KEY);

                    // Insert new records
                    foreach ($students as $student) {
                        StudentHabbit::create([
                            'class_id' => $request->class_id,
                            'month_id' => $request->month_id,
                            'student_id' => $student['student_id'],
                            'get_along_with_other' => $student['get_along_with_other'] ?? null,
                            'neat_and_tidy' => $student['neat_and_tidy'] ?? null,
                            'pay_attention' => $student['pay_attention'] ?? null,
                            'respects_school' => $student['respects_school'] ?? null,
                            'take_care' => $student['take_care'] ?? null,
                            'work' => $student['work'] ?? null,
                        ]);
                    }

                    return response()->json([
                        'message' => 'បញ្ចូលបានជោគជ័យ',
                        'status' => 0,
                    ]);
                }
            } else {
                // Filter students with numeric keys
                $students = array_filter($request->all(), function ($key) {
                    return is_numeric($key);
                }, ARRAY_FILTER_USE_KEY);

                // Insert new records
                foreach ($students as $student) {
                    StudentHabbit::create([
                        'class_id' => $request->class_id,
                        'month_id' => $request->month_id,
                        'student_id' => $student['id'],
                        'get_along_with_other' => $student['get_along_with_other'] ?? null,
                        'neat_and_tidy' => $student['neat_and_tidy'] ?? null,
                        'pay_attention' => $student['pay_attention'] ?? null,
                        'respects_school' => $student['respects_school'] ?? null,
                        'take_care' => $student['take_care'] ?? null,
                        'work' => $student['work'] ?? null,
                    ]);
                }

                return response()->json([
                    'message' => 'បញ្ចូលបានជោគជ័យ',
                    'status' => 0,
                ]);
            }
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error: ' . $e->getMessage(),
                'status' => 1,
            ], 500);
        }
    }
}
