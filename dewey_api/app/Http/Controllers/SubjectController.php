<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Subject;

class SubjectController extends Controller
{
    public function addSubject(Request $request)
    {
        $subject = new Subject();
        $subject->subject_name = $request->subject_name;
        $subject->subject_eng = $request->subject_eng;
        $subject->subject_short = $request->subject_short;

        if ($subject->save()) {
            return response()->json([
                "message" => "មុខវិជ្ជាបង្កើតបានជោគជ័យ",
                "status" => 200
            ]);
        }

        // If save fails for some reason
        return response()->json([
            'message' => 'Failed to create subject',
            'status' => 500,
        ], 500);
    }

    public function get_all_subject()
    {
        $s = Subject::all();
        return response()->json($s);
    }

    public function delete_subject($id)
    {
        $s = Subject::findOrFail($id);
        $s->delete();

        return response()->json([
            "message" => "Subject Delete Successful",
            "status" => 200
        ]);
    }

    public function update_subject(Request $request, $id)
    {

        $s = Subject::findOrFail($id);

        $updateData = [
            'subject_name' => $request->subject_name,
            'subject_eng' => $request->subject_eng,
            'subject_short' => $request->subject_short,
        ];
        $s->update($updateData);
        return response()->json([
            "message" => "មុខវិជ្ជាកែប្រែបានជោគជ័យ",
            "status" => 200
        ]);
    }
}
