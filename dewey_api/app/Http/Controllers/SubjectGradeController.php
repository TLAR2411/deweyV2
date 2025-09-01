<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use App\Http\Requests\SubjectGradeRequest;
use App\Models\SubjectGrade;
use Illuminate\Http\Request;

class SubjectGradeController extends Controller
{
    public function add_subject_grade(SubjectGradeRequest $request)
    {
        $model = SubjectGrade::create($request->validated() + [
            "full_score" => $request->full_score,
            "average_score" => $request->average_score
        ]);

        if ($model) {
            return response()->json([
                "message" => "មុខវិជ្ជាបង្កើតបានជោគជ័យ",
                "status" => 200
            ]);
        }
    }

    public function get_subject_grade()
    {
        $sg = DB::table("subject_grades as sg")
            ->leftJoin("subjects as sub", "sg.subject_id", "=", "sub.id")
            ->leftJoin("classtypes as c", "sg.class_type_id", "=", "c.id")
            ->leftJoin("grade_levels as gl", "sg.grade_level_id", "=", "gl.id")
            ->leftJoin("education_levels as edu", "gl.edu_id", "=", "edu.id")
            ->select([
                "sg.*",
                // "sg.full_score",
                // "sg.average_score",
                // "sg.*",
                // "sub.*",
                "sub.subject_name",
                "c.type",
                "edu.edu_name",
                "gl.level"
            ])
            ->get();
        return response()->json($sg);
    }

    public function update_subject_grade(Request $request, $id)
    {

        $up = SubjectGrade::find($id);

        $updateSubjectGrade = [
            'full_score' => $request->full_score,
            'average_score' => $request->average_score,
            'subject_id' => $request->subject_id,
            'class_type_id' => $request->class_type_id,
            'grade_level_id' => $request->grade_level_id,

        ];
        $up->update($updateSubjectGrade);
        if ($up) {
            return response()->json([
                "message" => "មុខវិជ្ជាកែប្រែបានជោគជ័យ",
                "status" => 200
            ]);
        }
    }

    public function delete_subject_grade($id)
    {
        $s = SubjectGrade::findOrFail($id);
        $s->delete();

        return response()->json([
            "message" => "Subject Delete Successful",
            "status" => 200
        ]);
    }
}
