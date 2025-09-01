<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Campus;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Laravel\Sanctum\PersonalAccessToken;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        //Data Validate
        $request->validate([
            "username" => "required",
            "email" => "required|email",
            "password" => 'required|string|min:1|confirmed'
        ]);

        $photo = $request->file('photo_path');

        $campus = is_array($request->campus_ids) ? $request->campus_ids : [$request->campus_ids];

        if (is_array($photo)) {
            $photo = $photo[0]; // Get the first file if it's an array
        }

        $user = User::create([
            "username" => $request->username,
            "email" => $request->email,
            "password" => Hash::make($request->password),
            "photo_path" => $photo,
            "student_id" => $request->student_id,
            "is_AllCampus" => $request->is_AllCampus,
            "role_id" => $request->role_id,
            "teacher_id" => $request->teacher_id,

        ]);

        foreach ($campus as $cam) {
            DB::table('user_campus')->insert([
                'user_id' => $user->id,
                'campus_id' => $cam,
            ]);
        }

        return response()->json([
            "status" => 200,
            "data" => $user,
            "message" => "អ្នកប្រើប្រាស់បង្កើតបានជោគជ័យ"
        ]);
    }
    public function viewprofile($id)
    {
        $user = DB::table('users')->where('id', $id)->first();
        if ($user) {
            $studentidString = $user->student_id;
            $studentIds = array_map('intval', explode(',', $studentidString));
            $students = DB::table('students')
                ->select('id', 'kh_name', 'en_name', 'photo_path')
                ->whereIn('id', $studentIds)
                ->get();

            return response()->json([
                "status" => 0,
                "data" => $user,
                "student" => $students,
                "message" => "Succesfully"
            ], 200);
        } else {

            return response()->json([
                "status" => 1,
                "message" => "UnSuccesfully"
            ], 400);
        }
    }

    public function login(Request $request)
    {
        date_default_timezone_set("Asia/Bangkok");
        // Validate input
        $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);

        // Check if email exists
        $user = User::where('email', $request->email)->first();
        if (!$user) {
            return response()->json([
                "status" => false,
                "message" => "អុីម៉ែលរបស់អ្នកមិនត្រឹមត្រូវទេ :("
            ]);
        }

        // Check login attempts and password
        if (!Hash::check($request->password, $user->password)) {
            // Increment login attempts
            $user->login_attem = ($user->login_attem ?? 0) + 1;
            $user->save();
            if ($user->login_attem >= 3) {
                return response()->json([
                    "status" => false,
                    "message" => "សូមទូរស័ព្ទទៅអ្នកគ្រប់គ្រង (Please call admin)",
                    "alert" => true
                ]);
            }

            return response()->json([
                "status" => false,
                "message" => "ពាក្យសម្ងាត់អ្នកមិនត្រឹមត្រូវ :("
            ]);
        }

        $user->login_attem = 0;
        $user->save();

        Auth::login($user);
        // $tokenResult = $user->createToken("myToken");
        // $token = $tokenResult->plainTextToken;
        // $tokenId = explode('|', $token)[0];
        // $accessToken = PersonalAccessToken::find($tokenId);
        // $accessToken->expires_at = Carbon::now()->addDays(30);
        // $accessToken->save();
        // $dayExpire = Carbon::now()->addDays(30)->format('Y-m-d H:i:s');
        $expiresAt = Carbon::now()->addDays(30);
        $tokenResult = $user->createToken("myToken", ['*'], $expiresAt);
        $token = $tokenResult->plainTextToken;
        return response()->json([
            "status" => true,
            "user" => $user,
            "token" => $token,
            "exp" => $expiresAt->toDateTimeString(),
            "message" => "Login ទទួលបានជោគជ័យ"
        ]);
    }

    public function logout()
    {
        auth()->user()->currentAccessToken()->delete();
        return response()->json([
            "status" => true,
            "message" => "អ្នកបានចាកចេញពីប្រព័ន្ធដោយជោគជ័យ"
        ]);
    }

    public function get_all_user(Request $request)
    {
        $campusId = $request->input('campus_id', '*');
        // $allUser = User::whereBranch($campusId)->get();
        $allUser = User::query()
            ->whereBranch($campusId)
            ->select("users.*")
            ->get();
        return response()->json($allUser);
    }

    public function delete_user($id)
    {
        $delete = User::findOrFail($id);
        $delete->delete();
        return response()->json([
            "message" => "អ្នកប្រើប្រាស់លុបដោយជោគជ័យ",
            'status' => 200
        ]);
    }
    public function profile()
    {
        $user = Auth::user();

        return response()->json([
            "status" => true,
            "message" => "Profile information",
            "data" => $user
        ]);
    }


    public function authCampus()
    {
        try {
            $campus = Campus::query()
                ->join('user_campus as uc', 'uc.campus_id', 'campus.id')
                ->where('uc.user_id', Auth::id())
                ->orderBy('campus.id', 'asc')
                ->select(['campus.*'])
                ->get();

            return response()->json([
                "status" => 200,
                "data" => $campus
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                "status" => 500,
                "message" => $th->getMessage()
            ]);
        }
    }

    public function getclass($student_id)
    {

        $class = DB::table('v_student_class')
            ->join('v_class', 'v_student_class.class_id', '=', 'v_class.id')
            ->where('v_student_class.id', $student_id)
            ->where('v_class.deleted', 0)
            ->where('v_student_class.deleted', 0)
            ->orderByDesc('v_class.yid')
            ->select(
                'v_student_class.*',
                'v_class.grade',
                'v_class.academic_year',
                'v_class.id as clid',
                'v_class.yid',
                'v_class.curriculum',
                'v_class.cur_id as cur_id'
            )
            ->get();

        if ($class) {
            return  response()->json([
                'status' => 0,
                'class' => $class,
                "message" => "Have Class"

            ], 200);
        } else {
            return  response()->json([
                'status' => 1,
                "message" => "No Class"

            ], 200);
        }
    }

    public function deletechild(Request $request)
    {
        $parentId = $request->parentId;
        $idToRemove = $request->idToRemove;

        $findParent = User::findOrFail($parentId);

        if (! $findParent) {
            return response()->json([
                "message" => "គ្មានទិន្នន័យ"
            ]);
        }

        $childIds = explode(',', $findParent->student_id);


        $filterIds = array_filter($childIds, function ($id) use ($idToRemove) {
            return $id != $idToRemove;
        });



        $findParent->student_id = implode(',', $filterIds);

        $findParent->save();

        return response()->json([
            "status" => 200,
            "message" => "សិស្សត្រូវបានលុបចេញពីអាណាព្យាបាលដោយជោគជ័យ",
        ]);
    }

    public function addChild(Request $request)
    {
        $parentId = $request->parentId;
        $studentIdToAdd = $request->studentId;

        $findParent = User::findOrFail($parentId);

        if (! $findParent) {
            return response()->json([
                "message" => "គ្មានទិន្នន័យ"
            ]);
        }

        // Convert the new student IDs to an array
        $newStudentIds = explode(',', $studentIdToAdd);

        // Get existing student IDs as array
        $childIds = $findParent->student_id
            ? explode(',', $findParent->student_id)
            : [];

        // Merge without duplicates
        foreach ($newStudentIds as $id) {
            $id = trim($id); // Clean spaces
            if (!in_array($id, $childIds)) {
                $childIds[] = $id;
            }
        }

        // Save updated student_id string
        $findParent->student_id = implode(',', $childIds);
        $findParent->save();

        return response()->json([
            "status" => 200,
            "message" => "សិស្សត្រូវបានបន្ថែមទៅអាណាព្យាបាលដោយជោគជ័យ",
            "data" => [
                "student_id" => $findParent->student_id
            ],
        ]);
    }
}
