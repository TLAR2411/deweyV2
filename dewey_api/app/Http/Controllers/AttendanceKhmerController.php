<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AttendanceKhmerController extends Controller
{
    public function deleteAttendance($id)
    {
        $delete = DB::table('tbl_attendance')->where('id', $id)->delete();
        if ($delete) {
            return response()->json([
                "status" => 200
            ]);
        }
    }
    public function showStudentAttendance(Request $request)
    {
        $check_attendance = DB::table('tbl_attendance')
            ->where('class_id', $request->class_id)
            ->where('month_id', $request->month_id)
            ->count();

        if ($check_attendance == 0) {
            $student_class = DB::table('view_studentscore')
                ->where('class_id', $request->class_id)
                ->where('deleted', 0)
                ->orderBy('kh_name')
                ->get();

            return response()->json(
                [
                    "student_class" => $student_class,
                    "status" => 1
                ]
            );
        } else {
            //search for student with attendance
            $seachStudent = DB::table('tbl_attendance')
                ->join('students', 'tbl_attendance.student_id', '=', 'students.id')
                ->where('class_id', $request->class_id)
                ->where('month_id', $request->month_id)
                ->select(DB::raw('tbl_attendance.*,students.kh_name,students.gender'))
                ->get();

            // search for student no attendance record
            $seachStudent2 = DB::table('student_class')
                ->join('students', 'student_class.student_id', '=', 'students.id')
                ->join('classrooms', 'student_class.class_id', '=', 'classrooms.id')
                ->where('classrooms.id', $request->class_id)
                ->whereRaw('student_class.student_id NOT IN (SELECT student_id from tbl_attendance where student_class.class_id=' . $request->class_id . ' and
                tbl_attendance.month_id = ' . $request->month_id . ' )')
                ->select(DB::raw('' . $request->class_id . ' as class_id,students.id as student_id ,' . $request->month_id . ' as month_id, 0 as absen , 0 as permission , null as note'))
                ->get()->toArray();

            if (count($seachStudent2) > 0) {
                $item = array();
                foreach ($seachStudent as $obj) {
                    $array1[] = (array) $obj;
                }
                $new_data = array_merge($array1, $seachStudent2);
                return response()->json(
                    [
                        "student_class" => $new_data,
                        "status" => 2
                    ]
                );
            } else {
                return response()->json(
                    [
                        "student_class" => $seachStudent,
                        "status" => 3
                    ]
                );
            }
        }
    }

    // public function save_attendance(Request $request)
    // {


    //     if ($request->status == 1) {
    //         $array_save = [];

    //         $students = array_filter($request->all(), function ($key) {
    //             return is_numeric($key); // Keep only numeric keys (0, 1, 2, ...)
    //         }, ARRAY_FILTER_USE_KEY);

    //         foreach ($students as $s) {
    //             $array_save[] = [
    //                 'class_id' => $request->class_id,
    //                 'month_id' => $request->month_id,
    //                 'student_id' => $s['id'],
    //                 'absen' => $s['absen'] ?? 00,
    //                 'permission' => $s['permission'] ?? 00,
    //                 'note' => $s['note'] ?? null,
    //             ];
    //         }

    //         $data = DB::table('tbl_attendance')->insert($array_save);

    //         if ($data) {
    //             return response()->json(
    //                 [
    //                     "message" => "បញ្ចូលអវត្តមានសិស្សបានដោយជោគជ័យ",
    //                     "status" => 0
    //                 ]
    //             );
    //         } else {
    //             return response()->json(
    //                 [
    //                     "message" => "Something Wrong !",
    //                     "status" => 1
    //                 ]
    //             );
    //         }
    //     } else {

    //         $del = DB::table('tbl_attendance')
    //             ->where('class_id', $request->class_id)
    //             ->where('month_id', $request->month_id)
    //             ->delete();

    //         if ($del) {

    //             $students = array_filter($request->all(), function ($key) {
    //                 return is_numeric($key); // Keep only numeric keys (0, 1, 2, ...)
    //             }, ARRAY_FILTER_USE_KEY);

    //             foreach ($students as $s) {
    //                 $array_save[] = [
    //                     'class_id' => $request->class_id,
    //                     'month_id' => $request->month_id,
    //                     'student_id' => $s['student_id'],
    //                     'absen' => $s['absen'],
    //                     'permission' => $s['permission'],
    //                     'note' => $s['note'],
    //                 ];
    //             }

    //             $data = DB::table('tbl_attendance')->insert($array_save);
    //             if ($data) {
    //                 return response()->json(
    //                     [
    //                         "message" => "បញ្ចូលអវត្តមានសិស្សបានដោយជោគជ័យ",
    //                         "status" => 0
    //                     ]
    //                 );
    //             } else {
    //                 return response()->json(
    //                     [
    //                         "message" => "Something Wrong !",
    //                         "status" => 1
    //                     ]
    //                 );
    //             }
    //         }
    //     }
    // }

    public function save_attendance(Request $request)
    {
        // Helper function to format numbers to two digits
        $formatNumber = function ($value) {
            if (is_null($value) || $value === '') {
                return '00';
            }
            $num = (int) $value;
            if ($num < 0) {
                return '00'; // Handle negative numbers if needed
            }
            return $num < 10 ? '0' . $num : (string) $num;
        };

        if ($request->status == 1) {
            $array_save = [];

            $students = array_filter($request->all(), function ($key) {
                return is_numeric($key); // Keep only numeric keys (0, 1, 2, ...)
            }, ARRAY_FILTER_USE_KEY);

            foreach ($students as $s) {
                $array_save[] = [
                    'class_id' => $request->class_id,
                    'month_id' => $request->month_id,
                    'student_id' => $s['id'],
                    'absen' => $formatNumber($s['absen'] ?? null),
                    'permission' => $formatNumber($s['permission'] ?? null),
                    'note' => $s['note'] ?? null,
                ];
            }

            $data = DB::table('tbl_attendance')->insert($array_save);

            if ($data) {
                return response()->json([
                    "message" => "បញ្ចូលអវត្តមានសិស្សបានដោយជោគជ័យ",
                    "status" => 0
                ]);
            } else {
                return response()->json([
                    "message" => "Something Wrong !",
                    "status" => 1
                ]);
            }
        } else {
            $del = DB::table('tbl_attendance')
                ->where('class_id', $request->class_id)
                ->where('month_id', $request->month_id)
                ->delete();

            if ($del) {
                $array_save = [];

                $students = array_filter($request->all(), function ($key) {
                    return is_numeric($key); // Keep only numeric keys (0, 1, 2, ...)
                }, ARRAY_FILTER_USE_KEY);

                foreach ($students as $s) {
                    $array_save[] = [
                        'class_id' => $request->class_id,
                        'month_id' => $request->month_id,
                        'student_id' => $s['student_id'],
                        'absen' => $formatNumber($s['absen'] ?? null),
                        'permission' => $formatNumber($s['permission'] ?? null),
                        'note' => $s['note'] ?? null,
                    ];
                }

                $data = DB::table('tbl_attendance')->insert($array_save);
                if ($data) {
                    return response()->json([
                        "message" => "បញ្ចូលអវត្តមានសិស្សបានដោយជោគជ័យ",
                        "status" => 0
                    ]);
                } else {
                    return response()->json([
                        "message" => "Something Wrong !",
                        "status" => 1
                    ]);
                }
            } else {
                return response()->json([
                    "message" => "Failed to delete existing attendance records!",
                    "status" => 1
                ]);
            }
        }
    }
}
