<?php

namespace App\Http\Controllers;

use App\Http\Requests\TeacherClassRequest;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\TeacherClass;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx\Rels;

class TeacherClassController extends Controller
{
    public function addTeacherToClass(TeacherClassRequest $request)
    {
        $data = TeacherClass::create($request->validated() + [
            "campus_id" => $request->campus_id
        ]);

        Teacher::where('id', $request->validated()['teacherId'])->update(['status' => 1]);

        if ($data) {
            return response()->json([
                "message" => "គ្រូបញ្ចូលទៅក្នុងថ្នាក់បានដោយជោគជ័យ",
                "status" => 200
            ]);
        }
    }

    public function getTeacherInClass($id)
    {
        $data = DB::table('teachers as t')
            ->leftJoin('teacher_class as tc', 't.id', '=', 'tc.teacherId')
            ->leftJoin('classrooms as c', 'c.id', '=', 'tc.classId')
            ->leftJoin('subjects as sub', 'sub.id', '=', 'tc.subjectId')
            ->where('c.id', $id)
            ->select([
                'tc.*',
                't.kh_name',
                't.gender',
                't.phone',
                't.photo_path',
                'sub.subject_name'
            ])
            ->get();

        return response()->json($data);
    }

    public function getClassHaveTeacher(Request $request)
    {
        $campusId = $request->input('campus_id', '*');

        $teacherId = $request->input('teacherId');

        $data = TeacherClass::query()

            ->from('teacher_class as tc') // Use alias directly
            ->where('tc.teacherId', $teacherId)
            ->leftJoin('classrooms as c', 'c.id', '=', 'tc.classId')
            ->leftJoin('academicyears as y', 'c.year_id', '=', 'y.id')
            ->leftJoin('grades as g', 'g.id', '=', 'c.grade_id')
            ->leftJoin('grade_levels as gl', 'g.grade_level_id', '=', 'gl.id')
            ->leftJoin('education_levels as e', 'g.edu_id', '=', 'e.id')
            // ->leftJoin('curriculums as cur', 'g.cur_id', '=', 'cur.id')
            // ->leftJoin('education_levels as e', 'g.edu_id', '=', 'e.id')
            ->select([
                "c.id",
                'g.name as grade',
                // "g.id as grade_id"
                'gl.level',
                'e.id as education_id',
                'y.name as year',
                'y.id as year_id',
                'e.id as education_id',
            ])
            ->groupBy([
                'c.id',
                'g.name',
                'gl.level',
                'e.id',
                'y.name',
                'y.id'
            ])
            // ->whereBranch($campusId)
            ->get();

        return response()->json($data);
    }


    public function getOneTeacherClass($id)
    {
        $data = TeacherClass::findOrFail($id);
        return response()->json($data);
    }
    public function updateTeacherClass(TeacherClassRequest $request)
    {
        $tc = TeacherClass::findOrFail($request->id);
        $tc->update($request->validated());
        if ($tc) {
            return response()->json([
                "message" => "កែប្រែបានជោគជ័យ",
                "status" => 200
            ]);
        }
    }
    public function deleteTeacherFromClass($id)
    {
        $teacher = TeacherClass::findorFail($id);
        $teacher->delete();

        return response()->json([
            "messsage" => "គ្រូត្រូវបានលុបដោយជោគជ័យ",
            "status" => 200
        ]);
    }

    public function getSubject(Request $request)
    {
        $data = TeacherClass::query()
            ->from('teacher_class as tc')
            ->where('teacherId', $request->teacherId)
            ->where('classId', $request->classId)
            ->leftJoin('subjects as sub', 'sub.id', '=', 'tc.subjectId')
            ->select('sub.subject_name', 'sub.id')
            ->get();
        return response()->json($data);
    }
}
