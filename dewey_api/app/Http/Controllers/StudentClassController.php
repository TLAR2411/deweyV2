<?php

namespace App\Http\Controllers;

use App\Models\Classroom;
use App\Models\Student;
use App\Models\StudentClass;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx\Rels;

use function Laravel\Prompts\select;

class StudentClassController extends Controller
{

    public function get_one_student_class(Request $request, $id)
    {
        // Get class information

        $classroom = DB::table('classrooms as c')
            ->leftJoin('grades as g', 'c.grade_id', '=', 'g.id')
            ->select(['g.name as grade_name', 'c.id as classId'])
            ->where('c.id', $id)
            ->get();

        // Get all students with class details
        $s_class = DB::table('students as s')
            ->leftJoin('student_class as sc', 's.id', '=', 'sc.student_id')
            ->leftJoin('classrooms as c', 'sc.class_id', '=', 'c.id')
            ->leftJoin('academicyears as y', 'c.year_id', '=', 'y.id')
            ->leftJoin('grades as g', 'c.grade_id', '=', 'g.id')
            ->leftJoin('curriculums as cur', 'g.cur_id', '=', 'cur.id')
            ->leftJoin('rooms as r', 'c.room_id', '=', 'r.id')
            ->leftJoin('sessions as ses', 'c.session_id', '=', 'ses.id')
            ->leftJoin('classtypes as ct', 'c.classtype_id', '=', 'ct.id')
            ->leftJoin('campus as cam', 'c.campus_id', '=', 'cam.id')
            ->where('s.deleted', 0)
            ->where('sc.is_transfer', 0)
            ->select([
                'sc.sort',
                'sc.id',
                's.id as student_id',
                's.student_card_id',
                's.kh_name',
                's.en_name',
                's.gender',
                's.deleted',
                's.dob',
                'c.id as class_id',
                'g.name as grade_name',
                'r.room_number',
                'ses.session_name',
                'ses.time',
                'ct.type',
                'cam.name as campus_name',
                'y.name as year',
                'cur.name as cur_name'
            ])
            ->distinct();

        // Get students in class
        $studentInClass = clone $s_class;
        $studentInClass = $studentInClass->where('c.id', $id)->where('sc.deleted', 0)->get();

        // Get students not in class
        $studentNotInClass = Student::query()
            ->whereNotIn('id', function ($query) use ($id) {
                $query->select('student_class.student_id')->from('student_class')->where('class_id', $id)->where('student_class.deleted', 0);
            })

            ->where('deleted', 0)
            ->get();

        // Get total and female students
        $total_students = $studentInClass->count();
        $female_students = $studentInClass->whereIn('gender', ['F', '2'])->count();

        return response()->json([
            'classInfo' => $classroom,
            'students' => $studentInClass,
            'students_not_in_class' => $studentNotInClass,
            'total_students' => $total_students,
            'female_students' => $female_students
        ]);
    }

    public function studentNotInClass(Request $request, $id)
    {
        $campusId = $request->input('campus_id', '*');
        $studentNotInClass = DB::table('students')
            ->whereNotIn('students.id', function ($query) use ($id) {
                $query->select('student_class.student_id')
                    ->from('student_class')
                    ->where('class_id', $id)
                    ->where('student_class.deleted', 0);
            })
            ->where('deleted', 0)
            ->when($campusId != "*", function ($query) use ($campusId) {
                return $query->where('campus_id', $campusId);
            })
            ->when($campusId == "*", function ($query) {
                return $query->join('user_campus as uc', 'uc.campus_id', '=', 'students.campus_id')
                    ->where('uc.user_id', Auth::id());
            })
            ->get();

        return response()->json($studentNotInClass);
    }


    public function add_student_class(Request $request, $id)
    {
        // Validate the request
        $request->validate([
            'data' => 'required|array', // Expecting an array of student objects
            'data.*.id' => 'required|integer|exists:students,id', // Validate each student ID exists
        ]);

        $students = $request->input('data', []);

        $select_student = StudentClass::where('class_id', $id)->get();
        $count_student = $select_student->count();

        $student_ids = [];  // store student IDs to update their status later

        if ($count_student == 0) {
            $sort = 1;
        } else {
            $sort = $count_student + 1;
        }

        $insert_data = [];
        foreach ($students as $student) {
            $insert_data[] = [
                'student_id' => $student['id'],
                'class_id' => $id,
                'sort' => $sort++,
                'created_at' => now(),
                'updated_at' => now(),
            ];
            $student_ids[] = $student['id'];
        }

        // If no new students to insert, return early (though this won't happen with validation)
        if (empty($insert_data)) {
            return response()->json([
                "message" => "No valid students to add",
                "status" => 400
            ], 400);
        }

        $model = StudentClass::insert($insert_data);

        if ($model) {
            //update status to table students
            Student::whereIn('id', $student_ids)->update(['status' => 1]);
            return response()->json([
                "message" => "សិស្សបញ្ចូលទៅក្នុងថ្នាក់បានដោយជោគជ័យ",
                "status" => 200
            ], 200);
        }
    }

    public function remove_student_class(Request $request, $id)
    {
        // Validate the request
        $request->validate([
            'data' => 'required|array|min:1', // Ensure non-empty array
            // 'data.*.id' => 'required|integer|exists:students,id', // Check IDs exist
        ]);

        $students = $request->input('data', []);

        $student_ids = array_column($students, 'id');

        $updated = StudentClass::whereIn('id', $student_ids)->update(['deleted' => 1]);

        if ($updated) {
            return response()->json([
                'message' => 'សិស្សត្រូវបានដកចេញពីថ្នាក់',
                'status' => 200
            ], 200);
        }

        return response()->json([
            'message' => 'មិនអាចដកសិស្សចេញពីថ្នាក់បានទេ',
            'status' => 400
        ], 400);
    }

    public function remove_student_id($id)
    {
        $updated = StudentClass::where('id', $id)->update(['deleted' => 1]);
        if ($updated) {
            return response()->json([
                'message' => 'សិស្សត្រូវបានដកចេញពីថ្នាក់',
                'status' => 200
            ], 200);
        }
    }

    public function student_change_class(Request $request)
    {
        $request->validate([
            'data' => 'required|array', // Expecting an array of student objects
            'data.*.id' => 'required|integer|exists:students,id', // Validate each student ID exists
        ]);

        $students = $request->input('data', []);

        // $success = StudentClass::whereIn('student_id', $students)->update(['class_id' => $request->new_class_id]);

        // $success = StudentClass::whereIn('student_id', $students)->update([
        //     'is_transfer' => 1
        // ]);

        $success = StudentClass::whereIn('student_id', $students)
            ->where('class_id', '!=', $request->new_class_id) // only old class
            ->update(['is_transfer' => 1]);

        $existStudent = StudentClass::where('class_id', $request->new_class_id)->get();

        $countStudent = $existStudent->count();

        $student_ids = [];

        if ($countStudent == 0) {
            $sort = 1;
        } else {
            $sort = $countStudent + 1;
        }

        $insert_student = [];

        foreach ($students as $student) {
            $insert_student[] = [
                'student_id' => $student['id'],
                'class_id' => $request->new_class_id,
                'sort' => $sort++,
                'is_transfer' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ];
            $student_ids[] = $student['id'];
        }

        if (empty($insert_student)) {
            return response()->json([
                "message" => "No valid students to add",
                "status" => 400
            ], 400);
        }

        $model = StudentClass::insert($insert_student);

        if ($model) {
            if ($request->edu_id == 1 || $request->edu_id == '1') {
                foreach ($student_ids as $student_id) {
                    $scores = DB::table('score_primary_cc')
                        ->where('class_id', $request->old_class_id)
                        ->where('student_id', $student_id)
                        ->get();

                    if ($scores->isNotEmpty()) {
                        $transferScores = [];

                        foreach ($scores as $score) {
                            $transferScores[] = [
                                'student_id' => $score->student_id,
                                'from_class_id' => $score->class_id ?? null,
                                'class_id' => $request->new_class_id,
                                'avg_m' => $score->avg_m ?? null,
                                'month_id' => $score->month_id ?? null,
                                'listent' => $score->listent ?? null,
                                'speaking' => $score->speaking ?? null,
                                'writing' => $score->writing ?? null,
                                'reading' => $score->reading ?? null,
                                'essay' => $score->essay ?? null,
                                'grammar' => $score->grammar ?? null,
                                'math' => $score->math ?? null,
                                'chemistry' => $score->chemistry ?? null,
                                'physical' => $score->physical ?? null,
                                'history' => $score->history ?? null,
                                'morality' => $score->morality ?? null,
                                'art' => $score->art ?? null,
                                'word' => $score->word ?? null,
                                'pe' => $score->pe ?? null,
                                'homework' => $score->homework ?? null,
                                'healthy' => $score->healthy ?? null,
                                'steam' => $score->steam ?? null,
                                'approved' => $score->approved ?? null,
                                'created_at' => now(),
                                'updated_at' => now(),
                            ];
                        }
                        // Insert to transfer_score_primary table
                        DB::table('transafer_score_primary')->insert($transferScores);
                    }
                }
            }



            // $addStudent = StudentClass::create([
            //     'class_id' => $request->new_class_id,
            //     'student_id' => $students

            // ]);

            if ($success && $model) {
                return response()->json([
                    'message' => 'ផ្លាស់ប្ដូរបានជោគជ័យ',
                    'status' => 200
                ]);
            } else {
                return response()->json([
                    'message' => 'បរាជ័យក្នុងការផ្លាស់ប្ដូរ',
                    'status' => 500
                ], 500);
            }
        }
    }
}
