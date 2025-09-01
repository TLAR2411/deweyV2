<?php

namespace App\Http\Controllers;

use App\Models\Social;
use Illuminate\Http\Request;

class SocialController extends Controller
{
    public function addSocial(Request $request)
    {
        try {
            Social::create($request->all());
            return response()->json([
                "message" => "បង្កើតបានជោគជ័យ",
                "status" => 200
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                "status" => 500,
                "message" => $th->getMessage()
            ], 500);
        }
    }

    public function getSocial($id)
    {
        try {
            $social = Social::where("campus_id", $id)->get();
            return response()->json([
                "data" => $social,
                "status" => 200
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                "status" => 500,
                "message" => $th->getMessage()
            ], 500);
        }
    }

    public function deleteSocial($id)
    {
        try {
            $social = Social::findOrFail($id);
            $social->delete();
            return response()->json([
                "message" => "លុបបានជោគជ័យ !!",
                "status" => 200
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                "status" => 500,
                "message" => $th->getMessage()
            ], 500);
        }
    }

    public function getOneSocial($id)
    {
        try {
            $social = Social::findOrFail($id);
            return response()->json($social);
        } catch (\Throwable $th) {
            return response()->json([
                "status" => 500,
                "message" => $th->getMessage()
            ], 500);
        }
    }

    public function updateSocial(Request $request)
    {
        try {
            $social = Social::findOrFail($request->id);
            $social->update([
                "name" => $request->name,
                "link" => $request->link,
                "campus_id" => $request->campus_id,
                "user_id" => $request->user_id,
            ]);

            if ($request->new_photo_path != null) {
                $social->photo_path = $request->new_photo_path;
                $social->save();
            }

            return response()->json([
                "message" => "កែប្រែបានជោគជ័យ",
                "status" => 200
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                "status" => 500,
                "message" => $th->getMessage()
            ], 500);
        }
    }
}
