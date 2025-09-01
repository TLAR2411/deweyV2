<?php

namespace App\Http\Controllers;

use App\Http\Requests\StudentRequest;

use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StudentController extends Controller
{
    // public function get_all_student(Request $request)
    // {

    //     $perPage = $request->query('per_page', 10);
    //     $page = $request->query('page', 1);
    //     $search = $request->query('search', '');
    //     $year = $request->query('year', '');
    //     if ($year == '') {
    //         $student = Student::where('kh_name', 'LIKE', "%{$search}%")
    //             ->paginate($perPage, ['*'], 'page', $page);

    //         return response()->json($student);
    //     } else if ($year == "All") {
    //         $student = Student::paginate(10);

    //         return response()->json($student);
    //     }else if($year == "all_student_name")
    //     {
    //           $students = DB::table('students')
    //             ->select('id', 'kh_name')
    //             ->get();
    //             return response()->json($students);
    //     }
    //     else {
    //         $student = Student::whereYear('created_at', $year)
    //             ->paginate($perPage, ['*'], 'page', $page);

    //         return response()->json($student);
    //     }
    // }

    public function get_all_student(Request $request)
    {
        $perPage = $request->query('per_page', 10);
        $page = $request->query('page', 1);
        $search = $request->query('search', '');
        $year = $request->query('year', '');
        $campusId = $request->input('selectedCampusId', '*'); // Add campus_id parameter

        // Start building the query
        $query = Student::query();

        // Apply campus filter using the scope
        $query->whereBranch($campusId);

        if ($year == '') {
            $student = $query->where('kh_name', 'LIKE', "%{$search}%")
                ->paginate($perPage, ['*'], 'page', $page);

            return response()->json($student);
        } else if ($year == "All") {
            $student = $query->paginate($perPage, ['*'], 'page', $page);

            return response()->json($student);
        } else if ($year == "all_student_name") {
            $students = Student::query()
                ->whereBranch($campusId)
                ->select('students.id', 'students.kh_name')
                ->get();
            return response()->json($students);
        } else {
            $student = $query->whereYear('created_at', $year)
                ->paginate($perPage, ['*'], 'page', $page);

            return response()->json($student);
        }
    }

    public function add_student(StudentRequest $request)
    {
        $model = Student::create($request->validated() + [
            "student_card_id" => $request->student_card_id,
            "description" => $request->description,
            "phone" => $request->phone,
            "email" => $request->email,
            "old_grade" => $request->old_grade,
            "old_school" => $request->old_school,
            "old_school_english" => $request->old_school_english,
            "photo_path" => $request->photo_path,
            "m_phone" => $request->m_phone,
            "f_phone" => $request->f_phone,
            "village_birth" => $request->village_birth,
            "commune_birth" => $request->commune_birth,
            "district_birth" => $request->district_birth,
            "province_birth" => $request->province_birth,
            "village_address" => $request->village_address,
            "commune_address" => $request->commune_address,
            "district_address" => $request->district_address,
            "province_address" => $request->province_address,
            "m_name" => $request->m_name,
            "m_national" => $request->m_national,
            "m_job" => $request->m_job,
            "m_address" => $request->m_address,
            "f_name" => $request->f_name,
            "f_national" => $request->f_national,
            "f_job" => $request->f_job,
            "f_address" => $request->f_address,
            "campus_id" => $request->campus_id,

        ]);
        if ($model) {
            return response()->json([
                "message" => "សិស្សត្រូវបានបង្កើតដោយជោគជ័យ",
                "status" => 200
            ]);
        }
    }

    public function get_student_delete()
    {
        $student = Student::where('deleted', true)->get();
        return response()->json($student);
    }

    public function delete_student($id)
    {
        $student = Student::findOrFail($id);
        $student->update([
            "deleted" => true
        ]);
        return response()->json("Student Delete Success");
    }

    public function backToStudy($id)
    {
        $student = Student::findOrFail($id);
        $student->update([
            "deleted" => false
        ]);
        return response()->json("Student Back To Study");
    }

    public function final_delete_student($id)
    {
        $s = Student::findOrFail($id);
        $s->delete();
        return response()->json([
            "message" => "លុបបានជោគជ័យ",
            "status" => 200
        ]);
    }
    public function get_one_student($id)
    {
        $student = Student::findOrFail($id);
        return response()->json($student);
    }

    public function updateStudent(StudentRequest $request)
    {
        $student = Student::findOrFail($request->id);
        $student->update($request->validated() + [
            "student_card_id" => $request->student_card_id,
            "description" => $request->description,
            "phone" => $request->phone,
            "email" => $request->email,
            "old_grade" => $request->old_grade,
            "old_school" => $request->old_school,
            "old_school_english" => $request->old_school_english,
            "photo_path" => $request->photo_path,
            "m_phone" => $request->m_phone,
            "f_phone" => $request->f_phone,
            "village_birth" => $request->village_birth,
            "commune_birth" => $request->commune_birth,
            "district_birth" => $request->district_birth,
            "province_birth" => $request->province_birth,
            "village_address" => $request->village_address,
            "commune_address" => $request->commune_address,
            "district_address" => $request->district_address,
            "province_address" => $request->province_address,
            "m_name" => $request->m_name,
            "m_national" => $request->m_national,
            "m_job" => $request->m_job,
            "m_address" => $request->m_address,
            "f_name" => $request->f_name,
            "f_national" => $request->f_national,
            "f_job" => $request->f_job,
            "f_address" => $request->f_address,
        ]);

        if ($request->new_photo_path != null) {
            $student->photo_path = $request->new_photo_path;
            $student->save();
        }

        if ($student) {
            return response()->json([
                "message" => "កែប្រែបានជោគជ័យ",
                "status" => 200
            ]);
        }
    }

    public function getStudentByYear($id, Request $request)
    {
        $perPage = $request->query('per_page', 10);
        $page = $request->query('page', 1);
        $student = DB::table('students')->whereYear('created_at', $id)
            ->paginate($perPage, ['*'], 'page', $page);
        return response()->json($student);
    }

    public function getyear()
    {
        $data = DB::table('students')
            ->select(DB::raw('YEAR(created_at) as year'))
            ->groupBy('year')
            ->get();

        return response()->json($data);
    }
}
