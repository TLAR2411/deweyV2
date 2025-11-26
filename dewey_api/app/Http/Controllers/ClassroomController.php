<?php

namespace App\Http\Controllers;

use App\Models\Classroom;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Requests\ClassroomRequest;

class ClassroomController extends Controller
{
    public function add_classroom(ClassroomRequest $request)
    {

        // $existingClass = DB::table('classrooms')
        //     // ->where('grade_id', $request->grade_id)
        //     ->where('year_id', $request->year_id)
        //     ->where('campus_id', $request->campus_id)
        //     ->exists();
        // if ($existingClass) {
        //     return response()->json([
        //         'message' => "ថ្នាក់នេះបានបង្កើតម្ដងរួចហើយ"
        //     ], 400);
        // }

        $data = Classroom::create($request->validated() + [
            "description" => $request->description,
            "classtype_id" => $request->classtype_id,
            "session_id" => $request->session_id,
            "campus_id" => $request->campus_id,
        ]);

        if ($data) {
            return response()->json([
                "message" => "បង្កើតបានជោគជ័យ",
                "status" => 200
            ]);
        }
    }

    // public function get_all_classroom()
    // {
    //     $class = DB::table("classrooms as c")
    //         ->leftJoin("rooms as r", "c.room_id", "=", "r.id")
    //         ->leftJoin("academicyears as y", "c.year_id", "=", "y.id")
    //         ->leftJoin("classtypes as t", "c.classtype_id", "=", "t.id")
    //         ->leftJoin("sessions as ss", "c.session_id", "=", "ss.id")
    //         ->leftJoin("grades as g", "c.grade_id", "=", "g.id")
    //         ->leftJoin("grade_levels as gl", "g.grade_level_id", "=", "gl.id")
    //         ->leftJoin("education_levels as e", "g.edu_id", "=", "e.id")
    //         ->leftJoin("curriculums as cur", "g.cur_id", "=", "cur.id")
    //         ->select(
    //             "c.id",
    //             "c.deleted",
    //             "c.description",
    //             "r.room_number",
    //             "y.name as year",
    //             "y.id as year_id",
    //             DB::raw("COALESCE(t.type, 'N/A') as classtype"),
    //             "ss.session_name",
    //             "ss.time",
    //             "g.name as grade",
    //             "e.edu_name",
    //             "e.id as education_id",
    //             "cur.name as cur_name",
    //             "gl.level"
    //         )
    //         // ->where('c.deleted', '!=', 1)
    //         ->get();

    //     return response()->json($class);
    // }

    public function get_all_classroom(Request $request)
    {
        $campusId = $request->input('campus_id', '*'); // Add campus_id parameter

        $class = Classroom::query()
            ->leftJoin('rooms as r', 'classrooms.room_id', '=', 'r.id')
            ->leftJoin('academicyears as y', 'classrooms.year_id', '=', 'y.id')
            ->leftJoin('classtypes as t', 'classrooms.classtype_id', '=', 't.id')
            ->leftJoin('sessions as ss', 'classrooms.session_id', '=', 'ss.id')
            ->leftJoin('grades as g', 'classrooms.grade_id', '=', 'g.id')
            ->leftJoin('grade_levels as gl', 'g.grade_level_id', '=', 'gl.id')
            ->leftJoin('education_levels as e', 'g.edu_id', '=', 'e.id')
            ->leftJoin('curriculums as cur', 'g.cur_id', '=', 'cur.id')
            ->select(
                'classrooms.id',
                'classrooms.deleted',
                'classrooms.description',
                'r.room_number',
                'y.name as year',
                'y.id as year_id',
                DB::raw("COALESCE(t.type, 'N/A') as classtype"),
                'ss.session_name',
                'ss.time',
                'g.name as grade',
                'e.edu_name',
                'e.id as education_id',
                'cur.name as cur_name',
                'gl.level'
            )
            ->whereBranch($campusId) // Apply the scope
            // ->where('classrooms.deleted', '!=', 1) // Uncomment if needed
            ->get();

        return response()->json($class);
    }

    public function delete_classroom($id)
    {
        $classroom = Classroom::findOrFail($id);

        $model =  $classroom->update([
            "deleted" => true
        ]);
        if ($model) {
            return response()->json([
                "message" => "លុបបានជោគជ័យ",
                "status" => 200
            ]);
        } else {
            return response()->json([
                "message" => "លុបបរាជ័យ",
                "status" => 400
            ]);
        }
        // $classroom->delete();


    }

    public function getOneClass($id)
    {
        $c = Classroom::findOrFail($id);

        return response()->json($c);
    }
    public function updateClassroom(ClassroomRequest $request)
    {
        // $existingClass = DB::table('classrooms')
        //     ->where('grade_id', $request->grade_id)
        //     ->where('year_id', $request->year_id)
        //     ->whereNot('id', $request->id)
        //     ->exists();
        // if ($existingClass) {
        //     return response()->json([
        //         'message' => "ថ្នាក់នេះបានបង្កើតម្ដងរួចហើយ"
        //     ], 400);
        // }

        $data = Classroom::findOrFail($request->id)->update($request->validated() + [
            "description" => $request->description,
            "classtype_id" => $request->classtype_id,
            "session_id" => $request->session_id,
            "campus_id" => $request->campus_id,
        ]);

        if ($data) {
            return response()->json([
                "message" => "កែប្រែបានជោគជ័យ",
                "status" => 200
            ]);
        }
    }

    public function backClass($id)
    {
        $find = Classroom::findOrFail($id);
        $find->update([
            "deleted" => false,
        ]);

        return response()->json([
            "message" => "ថ្នាក់រៀនបានត្រលប់មកវិញ",
            "status" => 200
        ]);
    }

    public function getClassroomByYear(Request $request, $id)
    {
        try {
            // Get campus_id parameter
            $campusId = $request->input('campus_id', '*');

            // Build the query using Eloquent
            $classrooms = Classroom::query()
                ->leftJoin('rooms as r', 'classrooms.room_id', '=', 'r.id')
                ->leftJoin('academicyears as y', 'classrooms.year_id', '=', 'y.id')
                ->leftJoin('classtypes as t', 'classrooms.classtype_id', '=', 't.id')
                ->leftJoin('sessions as ss', 'classrooms.session_id', '=', 'ss.id')
                ->leftJoin('grades as g', 'classrooms.grade_id', '=', 'g.id')
                ->leftJoin('grade_levels as gl', 'g.grade_level_id', '=', 'gl.id')
                ->leftJoin('education_levels as e', 'g.edu_id', '=', 'e.id')
                ->leftJoin('curriculums as cur', 'g.cur_id', '=', 'cur.id')
                ->select(
                    'classrooms.id',
                    'classrooms.deleted',
                    'classrooms.description',
                    'r.room_number',
                    'y.name as year',
                    'y.id as year_id',
                    DB::raw("COALESCE(t.type, 'N/A') as classtype"),
                    'ss.session_name',
                    'ss.time',
                    'g.name as grade',
                    'e.edu_name',
                    'e.id as education_id',
                    'cur.name as cur_name',
                    'gl.level'
                )
                ->where('classrooms.year_id', $id)
                ->whereBranch($campusId)
                ->where('classrooms.deleted', '!=', 1) // Filter out soft-deleted records
                ->get();

            return response()->json([
                'data' => $classrooms,
                'message' => 'ស្វែងរកបានជោគជ័យ',
                'status' => 200,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to retrieve classrooms: ' . $e->getMessage(),
                'status' => 500,
            ], 500);
        }
    }
}
