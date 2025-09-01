<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\RoomRequest;
use App\Models\Room;

class RoomController extends Controller
{
    public function add_room(RoomRequest $request)
    {
        $room = Room::create($request->validated() + [
            "description" => $request->description,
            "building" => $request->building,
            "floor" => $request->floor,
            "campus_id" => $request->campus_id

        ]);
        if ($room) {
            return response()->json([
                "message" => "បញ្ចូលបានជោគជ័យ",
                "status" => 200
            ]);
        }
    }

    public function get_all_room(Request $request)
    {
        $campusId = $request->input('campus_id', '*');
        $r = Room::whereNot('deleted', 1)->whereBranch($campusId)->get();
        return response()->json($r);
    }

    public function getOneRoom($id)
    {
        $find = Room::findOrFail($id);
        return response()->json($find);
    }

    public function updateRoom(RoomRequest $request)
    {
        $room = Room::findOrFail($request->id)->update($request->validated() + [
            "description" => $request->description,
            "building" => $request->building,
            "floor" => $request->floor,
            "campus_id" => $request->campus_id
        ]);
        if ($room) {
            return response()->json([
                "message" => "កែប្រែបានជោគជ័យ",
                "status" => 200
            ]);
        }
    }

    public function delete_room($id)
    {
        $r = Room::findOrFail($id);
        $r->delete();
        return response()->json([
            "message" => "Room Delete Success",
            "status" => 200
        ]);
    }
}
