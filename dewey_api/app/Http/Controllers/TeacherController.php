<?php

namespace App\Http\Controllers;

use App\Models\Teacher;
use App\Http\Requests\TeacherRequest;

use Illuminate\Http\Request;

class TeacherController extends Controller
{
    public function get_all_teacher(Request $request)
    {
        $campusId = $request->input('campus_id', '*');
        $teacher = Teacher::whereBranch($campusId)->get();
        return response()->json($teacher);
    }

    public function add_teacher(TeacherRequest $request)
    {
        $teacher = Teacher::create($request->validated() + [
            "description" => $request->description,
            "email" => $request->email,
            "facebookName" => $request->facebookName,
            "village_address" => $request->village_address,
            "commune_address" => $request->commune_address,
            "district_address" => $request->district_address,
            "province_address" => $request->province_address,
            "commune_birth" => $request->commune_birth,
            "district_birth" => $request->district_birth,
            "province_birth" => $request->province_birth,
            "village_birth" => $request->village_birth,
            "photo_path" => $request->photo_path,
            "campus_id" => $request->campus_id,
        ]);

        if ($teacher) {
            return response()->json([
                "message" => "គ្រូត្រូវបង្កើតបានជោគជ័យ",
                "status" => 200
            ]);
        }
    }

    public function updateTeacher(TeacherRequest $request)
    {
        $teacher = Teacher::findOrFail($request->id);
        $teacher->update($request->validated() + [
            "description" => $request->description,
            "email" => $request->email,
            "facebookName" => $request->facebookName,
            "village_address" => $request->village_address,
            "commune_address" => $request->commune_address,
            "district_address" => $request->district_address,
            "province_address" => $request->province_address,
            "commune_birth" => $request->commune_birth,
            "district_birth" => $request->district_birth,
            "province_birth" => $request->province_birth,
            "village_birth" => $request->village_birth,
            "photo_path" => $request->photo_path,
        ]);

        if ($request->new_photo_path != null) {
            $teacher->photo_path = $request->new_photo_path;
            $teacher->save();
        }

        if ($teacher) {
            return response()->json([
                "message" => "គ្រូកែប្រែបានជោគជ័យ",
                "status" => 200
            ]);
        }
    }

    public function delete_teacher($id)
    {
        $teacher = Teacher::findOrFail($id);
        $teacher->delete();

        return response()->json([
            "status" => 200,
            "message" => "គ្រូលុបបានដោយជោគជ័យ"
        ]);
    }

    public function getOneTeacher($id)
    {
        $teacher = Teacher::findOrFail($id);
        return response()->json($teacher);
    }
}
