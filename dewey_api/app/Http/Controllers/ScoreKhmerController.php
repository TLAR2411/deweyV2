<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx\Rels;
use Illuminate\Support\Facades\Auth;

class ScoreKhmerController extends Controller
{
    public function deleteRecord(Request $request)
    {
        if ($request->edu_id == '3' || $request->edu_id == 3) {
            $record = DB::table('score_upper_cc')->where('id', $request->id)->delete();
            if ($record) {
                return response()->json([
                    "staus" => "200",
                ]);
            }
        } else if ($request->edu_id == '2' || $request->edu_id == 2) {
            $record = DB::table('score_secondary_cc')->where('id', $request->id)->delete();
            if ($record) {
                return response()->json([
                    "staus" => "200",
                ]);
            }
        } else if ($request->edu_id == '1' || $request->edu_id == 1) {
            $record = DB::table('score_primary_cc')->where('id', $request->id)->delete();
            if ($record) {
                return response()->json([
                    "staus" => "200",
                ]);
            }
        }
    }
    public function showstudent(Request $request)
    {
        if ($request->edu_id == '1') {
            $check_score = DB::table('score_primary_cc')
                ->where('class_id', $request->class_id)
                ->where('month_id', $request->month_id)
                ->count();
            if ($check_score == 0) {
                $student_class = DB::table('view_studentscore')
                    ->where('class_id', $request->class_id)
                    ->where('deleted', 0)
                    ->where('is_transfer', 0)
                    ->orderby('sort', 'asc')
                    ->get();
                return response()->json(
                    [
                        "student_class" => $student_class,
                        "status" => 1,
                        'level' => $request->level
                    ]
                );
            } else {
                $seachStudent = DB::table('score_primary_cc')
                    ->join('students', 'score_primary_cc.student_id', '=', 'students.id')
                    // ->join('student_class', 'score_primary_cc.student_id', '=', 'student_class.student_id')
                    ->where('class_id', $request->class_id)
                    ->where('month_id', $request->month_id)
                    ->select(DB::raw('score_primary_cc.*,students.kh_name'))
                    ->get();

                $seachStudent2 = DB::table('student_class')
                    ->join('students', 'student_class.student_id', '=', 'students.id')
                    ->join('classrooms', 'student_class.class_id', '=', 'classrooms.id')
                    ->where('classrooms.id', $request->class_id)
                    ->where('student_class.deleted', 0)
                    // ->whereRaw('student_class.student_id NOT IN (SELECT student_id from score_primary_cc where student_class.class_id=' . $request->class_id . ' and
                    //     score_primary_cc.month_id = ' . $request->month_id . ' )')
                    ->whereRaw('student_class.student_id NOT IN (SELECT student_id from score_primary_cc where score_primary_cc.class_id=' . $request->class_id . ' and
                        score_primary_cc.month_id = ' . $request->month_id . ' )')
                    ->select(DB::raw('null as art, null as chemistry,' . $request->class_id . ' as class_id, null as essay,null as
                        grammar , null as healthy,null as history, null as homework, null as id, students.id as student_id ,null as listent, null as math,
                        ' . $request->month_id . ' as month_id, null as moralty , null as pe, null as physical,null as speaking,students.id as student_id,null as word ,null as writing,
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
                    $avg_m = isset($seachStudent[0]->avg_m) ? $seachStudent[0]->avg_m : null;
                    return response()->json(

                        [
                            "student_class" => $seachStudent,
                            "status" => 3,
                            "avg_m" =>  $avg_m,
                            'level' => $request->level
                        ]
                    );
                }
            }
        }

        // SecondarySchool
        else if ($request->edu_id == '2') {
            $check_score = DB::table('score_secondary_cc')
                ->where('class_id', $request->class_id)
                ->where('month_id', $request->month_id)
                ->count();
            if ($check_score == 0) {
                $student_class = DB::table('view_studentscore')
                    ->where('class_id', $request->class_id)
                    ->where('deleted', 0)
                    ->orderby('sort', 'asc')
                    ->get();
                return response()->json(
                    [
                        "student_class" => $student_class,
                        "status" => 1,
                        "level" => $request->level
                    ]
                );
            } else {
                $seachStudent = DB::table('score_secondary_cc')
                    ->join('students', 'score_secondary_cc.student_id', '=', 'students.id')
                    ->where('class_id', $request->class_id)
                    ->where('month_id', $request->month_id)
                    ->select(DB::raw('score_secondary_cc.*,students.kh_name'))
                    ->get();

                $seachStudent2 = DB::table('student_class')
                    ->join('students', 'student_class.student_id', '=', 'students.id')
                    ->join('classrooms', 'student_class.class_id', '=', 'classrooms.id')
                    ->where('classrooms.id', $request->class_id)
                    ->where('student_class.deleted', 0)
                    // ->whereRaw('student_class.student_id NOT IN (SELECT student_id from score_secondary_cc where student_class.class_id=' . $request->class_id . ' and
                    //     score_secondary_cc.month_id = ' . $request->month_id . ' )')
                    ->whereRaw('student_class.student_id NOT IN (SELECT student_id from score_secondary_cc where score_secondary_cc.class_id=' . $request->class_id . ' and
                        score_secondary_cc.month_id = ' . $request->month_id . ' )')
                    ->select(DB::raw('null as writing, null as essay,' . $request->class_id . ' as class_id, null as khmer,null as
                        morality , null as history,null as geography, null as math, null as id,students.id as student_id, null as lmathistent, null as physical,
                        ' . $request->month_id . ' as month_id, null as chemistry , null as biology, null as geology,null as house_education,students.id as student_id,null as english,
                        students.kh_name'))
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
                            "status" => 2,
                            "level" => $request->level
                        ]
                    );
                } else {

                    $avg_m = isset($seachStudent[0]->avg_m) ? $seachStudent[0]->avg_m : null;

                    return response()->json(
                        [
                            "student_class" => $seachStudent,
                            "status" => 3,
                            "avg_m" =>  $avg_m,
                            "level" => $request->level
                        ]
                    );
                }
            }
        }

        // UpperSecondary
        else {
            $check_score = DB::table('score_upper_cc')
                ->where('class_id', $request->class_id)
                ->where('month_id', $request->month_id)
                ->count();


            if ($check_score == 0) {
                $student_class = DB::table('view_studentscore')
                    ->where('class_id', $request->class_id)
                    ->where('deleted', 0)
                    ->orderby('sort', 'asc')
                    ->get();
                return response()->json(
                    [
                        "student_class" => $student_class,
                        "status" => 1,
                        "level" => $request->level
                    ]
                );
            } else {

                $seachStudent = DB::table('score_upper_cc')
                    ->join('students', 'score_upper_cc.student_id', '=', 'students.id')
                    ->where('class_id', $request->class_id)
                    ->where('month_id', $request->month_id)
                    ->select(DB::raw('score_upper_cc.*,students.kh_name'))
                    ->get();

                $seachStudent2 = DB::table('student_class')
                    ->join('students', 'student_class.student_id', '=', 'students.id')
                    ->join('classrooms', 'student_class.class_id', '=', 'classrooms.id')
                    ->where('classrooms.id', $request->class_id)
                    ->where('student_class.deleted', 0)
                    // ->where('score_upper_cc.month_id',$request->month_id)
                    // ->whereRaw('student_class.student_id NOT IN (SELECT score_upper_cc.student_id from score_upper_cc where student_class.class_id=' . $request->class_id . ' and
                    //     score_upper_cc.month_id = ' . $request->month_id . ' )')
                    ->whereRaw('student_class.student_id NOT IN (SELECT score_upper_cc.student_id from score_upper_cc where score_upper_cc.class_id=' . $request->class_id . ' and
                        score_upper_cc.month_id = ' . $request->month_id . ' )')
                    ->select(DB::raw('null as khmer, null as morality,' . $request->class_id . ' as class_id, null as history,null as
                        geography , null as math,null as physical, null as chemistry, students.id as student_id, null as pe , null as computer ,null as biology, null as earth_sicence,
                        ' . $request->month_id . ' as month_id, null as english ,
                        students.kh_name'))
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
                            "status" => 2,
                            "level" => $request->level
                        ]
                    );
                } else {

                    $avg_m = isset($seachStudent[0]->avg_m) ? $seachStudent[0]->avg_m : null;
                    return response()->json(
                        [
                            "student_class" => $seachStudent,
                            "status" => 3,
                            "avg_m" => $avg_m,
                            "level" => $request->level
                        ]
                    );
                }
            }
        }
    }




    public function add_student_score(Request $request)
    {

        if ($request->role_id == 4) {
            $now = now('Asia/Bangkok');
            $currentDay = $now->format('d');
            $currentMonth = (int) $now->format('m');
            $scoreMonth = (int) $request->month_id;

            if ($scoreMonth !== $currentMonth) {
                return response()->json([
                    "message" => "មិនអាចបញ្ចូលពិន្ទុខែចាស់បានទេ",
                    "status" => 1
                ]);
            }


            if ($currentDay >= 27 || $currentDay >= '27') {
                if ($request->approve == 1) {
                    return response()->json([
                        "message" => "មិនអាចបញ្ចូលពិន្ទុបានទេ ផុតថ្ងៃកំណត់",
                        "status" => 1
                    ]);
                }
            }





            // $day = 28;

            // return response()->json([
            //     "message" => $now->format('m'),
            //     "data" => $request->month_id,
            // ]);
        }

        // if ($request->edu_id == 1) {

        //     if (empty($request->avg_m)) {
        //         return response()->json([
        //             "message" => "សូមបញ្ចូលតួរចែកនៅខាងក្រោម",
        //             "isAlert" => "avg_m"
        //         ]);
        //     }

        //     $students = array_filter($request->all(), function ($key) {
        //         return is_numeric($key);
        //     }, ARRAY_FILTER_USE_KEY);

        //     foreach ($students as $student) {
        //         DB::beginTransaction();
        //         try {
        //             if ($request->status == 1) {
        //                 // Insert new student score
        //                 DB::table('score_primary_cc')->insert([
        //                     'student_id' => $student['id'],
        //                     'class_id' => $student['class_id'],
        //                     'month_id' => $student['month_id'],
        //                     'avg_m' => $student['avg_m'],
        //                     'listent' => $student['listent'] ?? null,
        //                     'speaking' => $student['speaking'] ?? null,
        //                     'writing' => $student['writing'] ?? null,
        //                     'reading' => $student['reading'] ?? null,
        //                     'essay' => $student['essay'] ?? null,
        //                     'grammar' => $student['grammar'] ?? null,
        //                     'math' => $student['math'] ?? null,
        //                     'chemistry' => $student['chemistry'] ?? null,
        //                     'physical' => $student['physical'] ?? null,
        //                     'history' => $student['history'] ?? null,
        //                     'morality' => $student['morality'] ?? null,
        //                     'art' => $student['art'] ?? null,
        //                     'word' => $student['word'] ?? null,
        //                     'pe' => $student['pe'] ?? null,
        //                     'homework' => $student['homework'] ?? null,
        //                     'healthy' => $student['healthy'] ?? null,
        //                     'steam' => $student['steam'] ?? null,
        //                 ]);
        //             } else {
        //                 // Delete existing scores for this student
        //                 // DB::table('score_primary_cc')
        //                 //     ->where('class_id', $request->class_id)
        //                 //     ->where('month_id', $request->month_id)
        //                 //     ->delete();

        //                 // Insert new score
        //                 DB::table('score_primary_cc')
        //                     ->where('class_id', $student['class_id'])
        //                     ->where('month_id', $student['month_id'])
        //                     ->where('student_id', $student['student_id'])
        //                     ->update([
        //                         'student_id' => $student['student_id'],
        //                         // 'class_id' => $student['class_id'],
        //                         // 'month_id' => $student['month_id'],
        //                         'avg_m' => $student['avg_m'],
        //                         'listent' => $student['listent'] ?? null,
        //                         'speaking' => $student['speaking'] ?? null,
        //                         'writing' => $student['writing'] ?? null,
        //                         'reading' => $student['reading'] ?? null,
        //                         'essay' => $student['essay'] ?? null,
        //                         'grammar' => $student['grammar'] ?? null,
        //                         'math' => $student['math'] ?? null,
        //                         'chemistry' => $student['chemistry'] ?? null,
        //                         'physical' => $student['physical'] ?? null,
        //                         'history' => $student['history'] ?? null,
        //                         'morality' => $student['morality'] ?? null,
        //                         'art' => $student['art'] ?? null,
        //                         'word' => $student['word'] ?? null,
        //                         'pe' => $student['pe'] ?? null,
        //                         'homework' => $student['homework'] ?? null,
        //                         'healthy' => $student['healthy'] ?? null,
        //                         'steam' => $student['steam'] ?? null,
        //                     ]);
        //             }

        //             DB::commit();


        //             $time = now()->toDateTimeString();
        //             $userId = Auth::id();
        //             $message = "userId:{$userId} | eduId:{$request->edu_id} | time:{$time} | Path: {$request->path()}";
        //             Storage::append("logs/score.txt", $message);
        //         } catch (\Exception $e) {
        //             DB::rollBack();

        //             // Optional: log error per student
        //             Storage::append(
        //                 "logs/score_error.txt",
        //                 "Error studentId:" . ($student['id'] ?? $student['student_id'] ?? 'N/A') .
        //                     " | " . $e->getMessage()
        //             );

        //             return response()->json([
        //                 "message" => "បញ្ចូលមិនបានជោគជ័យ",
        //                 "status" => 1
        //             ]);
        //         }
        //     }
        //     return response()->json([
        //         "message" => "ពិន្ទុបញ្ចូលបានដោយជោគជ័យ",
        //         "status" => 0
        //     ]);
        // }

        if ($request->edu_id == 1) {

            if ($request->avg_m == '' || $request->avg_m == null) {
                return response()->json(["message" => "សូមបញ្ចូលតួរចែកនៅខាងក្រោម", "isAlert" => "avg_m"]);
            }

            if ($request->status == 1) {
                $array_save = [];

                $students = array_filter($request->all(), function ($key) {
                    return is_numeric($key); // Keep only numeric keys (0, 1, 2, ...)
                }, ARRAY_FILTER_USE_KEY);

                foreach ($students as $student) {
                    $array_save[] = [
                        'student_id' => $student['id'],
                        'class_id' => $student['class_id'],
                        'month_id' => $student['month_id'],
                        'avg_m' => $student['avg_m'],
                        'listent' => $student['listent'] ?? null,
                        'speaking' => $student['speaking'] ?? null,
                        'writing' => $student['writing'] ?? null,
                        'reading' => $student['reading'] ?? null,
                        'essay' => $student['essay'] ?? null,
                        'grammar' => $student['grammar'] ?? null,
                        'math' => $student['math'] ?? null,
                        'chemistry' => $student['chemistry'] ?? null,
                        'physical' => $student['physical'] ?? null,
                        'history' => $student['history'] ?? null,
                        'morality' => $student['morality'] ?? null,
                        'art' => $student['art'] ?? null,
                        'word' => $student['word'] ?? null,
                        'pe' => $student['pe'] ?? null,
                        'homework' => $student['homework'] ?? null,
                        'healthy' => $student['healthy'] ?? null,
                        'steam' => $student['steam'] ?? null,
                        // 'approved'=>$student['approved']??0
                    ];
                    // 
                }


                $data = DB::table('score_primary_cc')->insert($array_save);

                if ($data) {

                    $time = now()->toDateTimeString();
                    $userId = Auth::id();
                    $message = "userId:{$userId} | eduId:{$request->edu_id} | time:{$time} | Path: {$request->path()}";
                    Storage::append("logs/score.txt", $message);
                    return response()->json(
                        [
                            "message" => "ពិន្ទុបញ្ចូលបានដោយជោគជ័យ",
                            "status" => 0
                        ]
                    );
                } else {
                    return response()->json(
                        [
                            "message" => "Something Wrong !",
                            "status" => 1
                        ]
                    );
                }
            } else {
                try {
                    DB::beginTransaction();
                    $del = DB::table('score_primary_cc')
                        ->where('class_id', $request->class_id)
                        ->where('month_id', $request->month_id)
                        ->delete();

                    $array_save2 = [];
                    $students = array_filter($request->all(), function ($key) {
                        return is_numeric($key); // Keep only numeric keys (0, 1, 2, ...)
                    }, ARRAY_FILTER_USE_KEY);
                    foreach ($students as $student) {
                        $array_save2[] = [

                            // change id to student_id
                            'student_id' => $student['student_id'],
                            'class_id' => $student['class_id'],
                            'month_id' => $student['month_id'],
                            'avg_m' => $student['avg_m'],
                            'listent' => $student['listent'] ?? null,
                            'speaking' => $student['speaking'] ?? null,
                            'writing' => $student['writing'] ?? null,
                            'reading' => $student['reading'] ?? null,
                            'essay' => $student['essay'] ?? null,
                            'grammar' => $student['grammar'] ?? null,
                            'math' => $student['math'] ?? null,
                            'chemistry' => $student['chemistry'] ?? null,
                            'physical' => $student['physical'] ?? null,
                            'history' => $student['history'] ?? null,
                            'morality' => $student['morality'] ?? null,
                            'art' => $student['art'] ?? null,
                            'word' => $student['word'] ?? null,
                            'pe' => $student['pe'] ?? null,
                            'homework' => $student['homework'] ?? null,
                            'healthy' => $student['healthy'] ?? null,
                            'steam' => $student['steam'] ?? null,
                        ];
                    }


                    $data = DB::table('score_primary_cc')
                        ->insert($array_save2);
                    if ($data) {

                        $time = now()->toDateTimeString();
                        $userId = Auth::id();
                        $message = "userId:{$userId} | eduId:{$request->edu_id} | time:{$time} | Path: {$request->path()}";
                        Storage::append("logs/score.txt", $message);

                        DB::commit();

                        return response()->json(
                            [
                                "message" => "ពិន្ទុបញ្ចូលបានដោយជោគជ័យ",
                                "status" => 0
                            ]
                        );
                    } else {
                        DB::rollBack();
                        return response()->json(
                            [
                                "message" => "Something Wrong !",
                                "status" => 1
                            ]
                        );
                    }
                } catch (\Throwable $th) {
                    DB::rollBack();
                }
            }
        }

        // Add score Secondary
        elseif ($request->edu_id == 2) {

            if ($request->avg_m == '' || $request->avg_m == null) {
                return response()->json(["message" => "សូមបញ្ចូលតួរចែកនៅខាងក្រោម", "isAlert" => "avg_m"]);
            }
            if ($request->status == 1) {


                $students = array_filter($request->all(), function ($key) {
                    return is_numeric($key);
                }, ARRAY_FILTER_USE_KEY);
                $array_save = [];


                foreach ($students as $student) {
                    $array_save[] = [
                        'student_id' => $student['id'],
                        'class_id' => $student['class_id'],
                        'month_id' => $student['month_id'],
                        'avg_m' => $student['avg_m'],
                        'writing' => $student['writing'] ?? null,
                        'essay' => $student['essay'] ?? null,
                        'khmer' => $student['khmer'] ?? null,
                        'morality' => $student['morality'] ?? null,
                        'history' => $student['history'] ?? null,
                        'geography' => $student['geography'] ?? null,
                        'math' => $student['math'] ?? null,
                        'physical' => $student['physical'] ?? null,
                        'chemistry' => $student['chemistry'] ?? null,
                        'biology' => $student['biology'] ?? null,
                        'geology' => $student['geology'] ?? null,
                        'house_education' => $student['house_education'] ?? null,
                        'english' => $student['english'] ?? null,
                        'pe' => $student['pe'] ?? null,
                        'computer' => $student['computer'] ?? null,
                        'approved' => $student['approved'] ?? 0

                    ];
                    // 
                }

                $data = DB::table('score_secondary_cc')->insert($array_save);

                if ($data) {

                    $time = now()->toDateTimeString();
                    $userId = Auth::id();
                    $message = "userId:{$userId} | eduId:{$request->edu_id} | time:{$time} | Path: {$request->path()}";
                    Storage::append("logs/score.txt", $message);
                    return response()->json(
                        [
                            "message" => "ពិន្ទុបញ្ចូលបានដោយជោគជ័យ",
                            "status" => 0
                        ]
                    );
                } else {
                    return response()->json(
                        [
                            "message" => "Something Wrong !",
                            "status" => 1
                        ]
                    );
                }
            } else {
                $array_save2 = [];
                $del = DB::table('score_secondary_cc')
                    ->where('class_id', $request->class_id)
                    ->where('month_id', $request->month_id)
                    ->delete();

                $students = array_filter($request->all(), function ($key) {
                    return is_numeric($key);
                }, ARRAY_FILTER_USE_KEY);
                if ($del) {
                    foreach ($students as $student) {
                        $array_save2[] = [
                            'student_id' => $student['student_id'],
                            'class_id' => $student['class_id'],
                            'month_id' => $student['month_id'],
                            'avg_m' => $student['avg_m'],
                            'writing' => $student['writing'] ?? null,
                            'essay' => $student['essay'] ?? null,
                            'khmer' => $student['khmer'] ?? null,
                            'morality' => $student['morality'] ?? null,
                            'history' => $student['history'] ?? null,
                            'geography' => $student['geography'] ?? null,
                            'math' => $student['math'] ?? null,
                            'physical' => $student['physical'] ?? null,
                            'chemistry' => $student['chemistry'] ?? null,
                            'biology' => $student['biology'] ?? null,
                            'geology' => $student['geology'] ?? null,
                            'house_education' => $student['house_education'] ?? null,
                            'english' => $student['english'] ?? null,
                            'pe' => $student['pe'] ?? null,
                            'computer' => $student['computer'] ?? null,
                            'approved' => $student['approved'] ?? 0

                        ];
                    }
                }
                $data = DB::table('score_secondary_cc')
                    ->insert($array_save2);

                if ($data) {
                    $time = now()->toDateTimeString();
                    $userId = Auth::id();
                    $message = "userId:{$userId} | eduId:{$request->edu_id} | time:{$time} | Path: {$request->path()}";
                    Storage::append("logs/score.txt", $message);
                    return response()->json(
                        [
                            "message" => "ពិន្ទុបញ្ចូលបានដោយជោគជ័យ",
                            "status" => 0

                        ]
                    );
                } else {
                    return response()->json(
                        [
                            "message" => "Something Wrong !",
                            "status" => 1

                        ]
                    );
                }
            }
        }

        // High school
        else {
            if ($request->avg_m == '' || $request->avg_m == null) {
                return response()->json(["message" => "សូមបញ្ចូលតួរចែកនៅខាងក្រោម", "isAlert" => "avg_m"]);
            }
            if ($request->status == 1) {

                $array_save = [];
                $students = array_filter($request->all(), function ($key) {
                    return is_numeric($key);
                }, ARRAY_FILTER_USE_KEY);

                foreach ($students as $student) {
                    $array_save[] = [
                        'student_id' => $student['id'],
                        'class_id' => $student['class_id'],
                        'month_id' => $student['month_id'],
                        'avg_m' => $student['avg_m'],
                        'khmer' => $student['khmer'] ?? null,
                        'morality' => $student['morality'] ?? null,
                        'history' => $student['history'] ?? null,
                        'geography' => $student['geography'] ?? null,
                        'math' => $student['math'] ?? null,
                        'physical' => $student['physical'] ?? null,
                        'chemistry' => $student['chemistry'] ?? null,
                        'biology' => $student['biology'] ?? null,
                        'earth_science' => $student['earth_science'] ?? null,
                        'english' => $student['english'] ?? null,
                        'pe' => $student['pe'] ?? null,
                        'computer' => $student['computer'] ?? null,
                        'approved' => $student['approved'] ?? 0

                    ];
                }

                $data = DB::table('score_upper_cc')->insert($array_save);

                if ($data) {

                    $time = now()->toDateTimeString();
                    $userId = Auth::id();
                    $message = "userId:{$userId} | eduId:{$request->edu_id} | time:{$time} | Path: {$request->path()} ";
                    Storage::append("logs/score.txt", $message);

                    return response()->json(
                        [
                            "message" => "ពិន្ទុបញ្ចូលបានដោយជោគជ័យ",
                            "status" => 0
                        ]
                    );
                } else {
                    return response()->json(
                        [
                            "message" => "Something Wrong !",
                            "status" => 1
                        ]
                    );
                }
            } else {
                $array_save2 = [];
                $del = DB::table('score_upper_cc')
                    ->where('class_id', $request->class_id)
                    ->where('month_id', $request->month_id)
                    ->delete();

                $students = array_filter($request->all(), function ($key) {
                    return is_numeric($key);
                }, ARRAY_FILTER_USE_KEY);

                if ($del) {
                    foreach ($students as $student) {
                        $array_save2[] = [
                            'student_id' => $student['student_id'],
                            'class_id' => $student['class_id'],
                            'month_id' => $student['month_id'],
                            'avg_m' => $student['avg_m'],
                            'khmer' => $student['khmer'] ?? null,
                            'morality' => $student['morality'] ?? null,
                            'history' => $student['history'] ?? null,
                            'geography' => $student['geography'] ?? null,
                            'math' => $student['math'] ?? null,
                            'physical' => $student['physical'] ?? null,
                            'chemistry' => $student['chemistry'] ?? null,
                            'biology' => $student['biology'] ?? null,
                            'earth_science' => $student['earth_science'] ?? null,
                            'english' => $student['english'] ?? null,
                            'pe' => $student['pe'] ?? null,
                            'computer' => $student['computer'] ?? null,
                            'approved' => $student['approved'] ?? 0
                        ];
                    }
                    $data = DB::table('score_upper_cc')
                        ->insert($array_save2);

                    if ($data) {
                        $time = now()->toDateTimeString();
                        $userId = Auth::id();
                        $message = "userId:{$userId} | eduId:{$request->edu_id} | time:{$time} | Path: {$request->path()} ";
                        Storage::append("logs/score.txt", $message);
                        return response()->json(
                            [
                                "message" => "ពិន្ទុបញ្ចូលបានដោយជោគជ័យ",
                                "status" => 0
                            ]
                        );
                    } else {
                        return response()->json(
                            [
                                "message" => "Something Wrong !",
                                "status" => 1

                            ]
                        );
                    }
                }
            }
        }
    }


    public function approveScore(Request $request)
    {
        if ($request->edu_id == 3) {
            $data = DB::table('score_upper_cc')
                ->where('class_id', $request->class_id)
                ->where('month_id', $request->month_id)
                ->update([
                    'approved' => DB::raw('CASE WHEN approved = 1 THEN 0 ELSE 1 END')
                ]);
            return response()->json([
                "message" => "success",
                "status" => 200
            ]);
        } else if ($request->edu_id == 2) {
            $data = DB::table('score_secondary_cc')
                ->where('class_id', $request->class_id)
                ->where('month_id', $request->month_id)
                ->update([
                    'approved' => DB::raw('CASE WHEN approved = 1 THEN 0 ELSE 1 END')
                ]);
            return response()->json([
                "message" => "success",
                "status" => 200
            ]);
        } else {
            $data = DB::table('score_primary_cc')
                ->where('class_id', $request->class_id)
                ->where('month_id', $request->month_id)
                ->update([
                    'approved' => DB::raw('CASE WHEN approved = 1 THEN 0 ELSE 1 END')
                ]);
            return response()->json([
                "message" => "success",
                "status" => 200
            ]);
        }
    }
}
