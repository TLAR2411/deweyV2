<?php

namespace App\Http\Controllers;

use App\Exports\StudentClassExport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StudentClassExportController extends Controller
{
    public function exportStudentClass($id)
    {

        $classroom = DB::table('classrooms as c')
            ->leftJoin('grades as g', 'c.grade_id', '=', 'g.id')
            ->select(['g.name as grade_name'])
            ->where('c.id', $id)
            ->get();


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
            ->where('c.id', $id)
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
            ->distinct()
            ->get();

        // $studentInClass = clone $s_class;
        // $studentInClass = $studentInClass->where('c.id', $id)->get();

        $total_students = $s_class->count();
        $female_students = $s_class->whereIn('gender', ['F', '2'])->count();

        // Use a safe filename without special characters
        $filename = 'student_class_' . $id . '.xlsx';
        $data = Excel::download(new StudentClassExport($s_class, $classroom, $total_students, $female_students), $filename);
        ob_get_clean();
        return $data;
    }
}
