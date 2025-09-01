<?php

namespace App\Http\Controllers;

use App\Models\Day;
use App\Models\ScheduleKhmer;
use App\Models\TimeStudy;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ScheduleKhmerController extends Controller
{
    public function getDay()
    {
        $day = Day::all();
        return response()->json($day);
    }
    public function getTime()
    {
        $time = TimeStudy::all();
        return response()->json($time);
    }

    public function addSchedule(Request $request)
    {
        // Get the array of records from the request
        $records = $request->input('records');

        // Check if the input is a valid array
        if (!is_array($records)) {
            return response()->json([
                "message" => "Invalid data format. Expecting an array of records.",
                "status" => 400
            ], 400);
        }

        // Check if records array is empty
        if (empty($records)) {
            return response()->json([
                "message" => "No records provided to save.",
                "status" => 400
            ], 400);
        }

        // Start a transaction to ensure data consistency
        // \DB::beginTransaction();

        try {
            // Get the classId from the first record
            $classId = $records[0]['classId'];

            // Verify all records have the same classId
            foreach ($records as $record) {
                if ($record['classId'] !== $classId) {
                    throw new \Exception("All records must have the same classId.");
                }
            }

            // Delete existing schedules for the classId (if any)
            ScheduleKhmer::where('classId', $classId)->delete();

            // Insert new records
            $insertedRecords = [];
            foreach ($records as $record) {
                $data = new ScheduleKhmer();
                $data->classId = $record['classId'];
                $data->subjectId = $record['subjectId'];
                $data->dayId = $record['dayId'];
                $data->timeId = $record['timeId'];
                $data->save();
                $insertedRecords[] = $data;
            }

            // \DB::commit();

            return response()->json([
                "message" => "រក្សាទុកបានជោគជ័យ", // "Saved successfully" in Khmer
                "status" => 201,
                "insertedRecords" => $insertedRecords
            ], 201);
        } catch (\Exception $e) {
            // \DB::rollBack();
            return response()->json([
                "message" => "មានបញ្ហាក្នុងការរក្សាទុក: " . $e->getMessage(), // "Error occurred during saving"
                "status" => 500
            ], 500);
        }
    }

    public function getScheduleKhmer($id)
    {
        $query = DB::table('schedule_khmers as sk')
            ->join('subjects as s', 'sk.subjectId', '=', 's.id')
            ->join('days as d', 'sk.dayId', '=', 'd.id')
            ->join('time_study as t', 'sk.timeId', '=', 't.id')
            ->where('sk.classId', $id)
            ->select([
                'sk.*',
                's.subject_name',
                't.time',
                'd.nameKh'
            ])
            ->get();

        return response()->json($query);
    }
}
