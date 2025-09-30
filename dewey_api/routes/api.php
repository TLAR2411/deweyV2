<?php

use App\Http\Controllers\AcademicYearController;
use App\Http\Controllers\ClassroomController;
use App\Http\Controllers\ClassTypeController;
use App\Http\Controllers\CurriculumController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EducationLevelController;
use App\Http\Controllers\GradeConroller;
use App\Http\Controllers\MonthController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\TeacherController;
use App\Http\Controllers\SubjectController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RoomController;
use App\Http\Controllers\ScoreKhmerController;
use App\Http\Controllers\SessionController;
use App\Http\Controllers\StudentClassController;
use App\Http\Controllers\SubjectGradeController;
use App\Models\StudentClass;
use App\Http\Controllers\StudentClassExportController;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\AttendanceKhmerController;
use App\Http\Controllers\CampusController;
use App\Http\Controllers\ExportReport\PrimaryMonthExportController;
use App\Http\Controllers\ExportReport\SecondaryExportController;
use App\Http\Controllers\ReportScoreKhmerController;
use App\Http\Controllers\ReportScoreUpperController;
use App\Http\Controllers\ReportSecondaryScoreController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\ScheduleKhmerController;
use App\Http\Controllers\SettingMonthSemesterController;
use App\Http\Controllers\SettingScoreConfigController;
use App\Http\Controllers\SettingSemesterListController;
use App\Http\Controllers\SocialController;
use App\Http\Controllers\StudentHabbitController;
use App\Http\Controllers\TeacherClassController;
use App\Models\SettingSemesterList;
use App\Models\TeacherClass;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;

use Illuminate\Support\Facades\Storage;

// use App\Http\Controllers\ExportController\StudentClassExportController;
// use App\Http\Controllers\ExportController\StudentClassExportController;
// use App\Http\Controllers\StudentClassExportController

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });

Route::group([
    "middleware" => ["auth:sanctum"]
], function () {
    Route::get("/logout", [AuthController::class, "logout"]);
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/profile', [AuthController::class, 'profile']);
    Route::post("/get_all_user", [AuthController::class, 'get_all_user']);
    Route::post('/delete_user/{id}', [AuthController::class, 'delete_user']);
    Route::post('/deletechild', [AuthController::class, 'deletechild']);

    Route::post('authCampus', [AuthController::class, "authCampus"]);

    // student
    Route::post("/get_all_student", [StudentController::class, "get_all_student"]);
    Route::post("/get_student_delete", [StudentController::class, "get_student_delete"]);
    Route::post("/add_student", [StudentController::class, "add_student"]);
    Route::post("/updateStudent", [StudentController::class, "updateStudent"]);
    Route::post("/delete_student/{id}", [StudentController::class, "delete_student"]);
    Route::post("/getStudentByYear/{id}", [StudentController::class, "getStudentByYear"]);
    Route::post("/final_delete_student/{id}", [StudentController::class, "final_delete_student"]);
    Route::post('/backToStudy/{id}', [StudentController::class, "backToStudy"]);
    Route::post('/get_one_student/{id}', [StudentController::class, "get_one_student"]);
    Route::post('/getyear', [StudentController::class, 'getyear']);

    //teacher
    Route::post("/get_all_teacher", [TeacherController::class, "get_all_teacher"]);
    Route::post("/add_teacher", [TeacherController::class, "add_teacher"]);
    Route::post("/updateTeacher", [TeacherController::class, "updateTeacher"]);
    Route::post("/delete_teacher/{id}", [TeacherController::class, "delete_teacher"]);
    Route::post("/getOneTeacher/{id}", [TeacherController::class, "getOneTeacher"]);

    //teacherToClass
    Route::post("/addTeacherToClass", [TeacherClassController::class, "addTeacherToClass"]);
    Route::post("/updateTeacherClass", [TeacherClassController::class, "updateTeacherClass"]);
    Route::post("/getTeacherInClass/{id}", [TeacherClassController::class, "getTeacherInClass"]);
    Route::post("/getOneTeacherClass/{id}", [TeacherClassController::class, "getOneTeacherClass"]);
    Route::post("/deleteTeacherFromClass/{id}", [TeacherClassController::class, "deleteTeacherFromClass"]);

    //year
    Route::post("/get_all_year", [AcademicYearController::class, "get_all_year"]);
    Route::post("/add_year", [AcademicYearController::class, "add_year"]);
    Route::post("/updateYear", [AcademicYearController::class, "updateYear"]);
    Route::post("/delete_year/{id}", [AcademicYearController::class, "delete_year"]);
    Route::post("/getOneYear/{id}", [AcademicYearController::class, "getOneYear"]);

    // grade
    Route::post('/get_all_grade', [GradeConroller::class, 'get_all_grade']);
    Route::post('/get_grade_level', [GradeConroller::class, 'get_grade_level']);
    Route::post('/add_grade', [GradeConroller::class, 'add_grade']);
    Route::post('/updateGrade', [GradeConroller::class, 'updateGrade']);
    Route::post('/delete_grade/{id}', [GradeConroller::class, 'delete_grade']);
    Route::post('/getOneGrade/{id}', [GradeConroller::class, 'getOneGrade']);

    //room
    Route::post('/add_room', [RoomController::class, 'add_room']);
    Route::post('/get_all_room', [RoomController::class, 'get_all_room']);
    Route::post('/getOneRoom/{id}', [RoomController::class, 'getOneRoom']);
    Route::post('/updateRoom', [RoomController::class, 'updateRoom']);
    Route::post('/delete_room/{id}', [RoomController::class, 'delete_room']);

    // classroom
    Route::post('/get_all_classroom', [ClassroomController::class, 'get_all_classroom']);
    Route::post('add_classroom', [ClassroomController::class, 'add_classroom']);
    Route::post('getOneClass/{id}', [ClassroomController::class, 'getOneClass']);
    Route::post('/delete_classroom/{id}', [ClassroomController::class, 'delete_classroom']);
    Route::post('/backClass/{id}', [ClassroomController::class, 'backClass']);
    Route::post('/updateClassroom', [ClassroomController::class, 'updateClassroom']);
    Route::post('/getClassroomByYear/{id}', [ClassroomController::class, 'getClassroomByYear']);

    // session classtype edu curriculum
    Route::post('/get_all_classtype', [ClassTypeController::class, 'get_all_classtype']);
    Route::post('/get_session', [SessionController::class, 'get_session']);
    Route::post('/get_all_edu', [EducationLevelController::class, 'get_all_edu']);
    Route::post('/get_all_curriculum', [CurriculumController::class, 'get_all_curriculum']);

    // Student class
    Route::post('/get_one_student_class/{id}', [StudentClassController::class, 'get_one_student_class']);
    Route::post('/student_not_in_class/{id}', [StudentClassController::class, 'studentNotInClass']);
    // Route::post('/get_studen_class/{id}', [StudentClassController::class, 'get_studen_class']);
    Route::post('/exportStudentClass/{id}', [StudentClassExportController::class, 'exportStudentClass']);
    Route::post('/add_student_class/{id}', [StudentClassController::class, 'add_student_class']);
    Route::post('/remove_student_class/{id}', [StudentClassController::class, 'remove_student_class']);
    Route::post('/remove_student_id/{id}', [StudentClassController::class, 'remove_student_id']);
    Route::post('/student_change_class', [StudentClassController::class, 'student_change_class']);

    // Subject
    Route::post('/addSubject', [SubjectController::class, 'addSubject']);
    Route::post('/get_all_subject', [SubjectController::class, 'get_all_subject']);
    Route::post('/delete_subject/{id}', [SubjectController::class, 'delete_subject']);
    Route::post('/update_subject/{id}', [SubjectController::class, 'update_subject']);

    // Exam Khmer controller
    Route::post('/showstudent', [ScoreKhmerController::class, 'showstudent']);
    Route::post('/get_all_month', [MonthController::class, 'get_all_month']);
    Route::post('/add_student_score', [ScoreKhmerController::class, 'add_student_score']);
    Route::post('/deleteRecord', [ScoreKhmerController::class, 'deleteRecord']);

    // ScheduleKhmer

    Route::post('/getDay', [ScheduleKhmerController::class, 'getDay']);
    Route::post('/getTime', [ScheduleKhmerController::class, 'getTime']);
    Route::post('/addSchedule', [ScheduleKhmerController::class, 'addSchedule']);

    //subject_grade
    Route::post('/add_subject_grade', [SubjectGradeController::class, 'add_subject_grade']);
    Route::post('/get_subject_grade', [SubjectGradeController::class, 'get_subject_grade']);
    Route::post('/update_subject_grade/{id}', [SubjectGradeController::class, 'update_subject_grade']);
    Route::post('/delete_subject_grade/{id}', [SubjectGradeController::class, 'delete_subject_grade']);

    //report
    Route::post('/getSemesterOnePrimary', [ReportScoreKhmerController::class, 'getSemesterOnePrimary']);
    Route::post('/viewPrimary', [ReportScoreKhmerController::class, 'viewPrimary']);
    Route::post('/viewPrimaryExportExcel', [PrimaryMonthExportController::class, 'exportPrimary']);
    Route::post('/viewSecondary', [ReportSecondaryScoreController::class, 'viewSecondary']);
    Route::post('/viewSecondaryExportExcel', [SecondaryExportController::class, 'viewSecondary']);
    Route::post('/viewUpper', [ReportScoreUpperController::class, 'viewUpper']);


    Route::post('/dashboard', [DashboardController::class, 'dashboard']);

    Route::post('/deleteAttendance/{id}', [AttendanceKhmerController::class, 'deleteAttendance']);
    Route::post('/showStudentAttendance', [AttendanceKhmerController::class, 'showStudentAttendance']);
    Route::post('/save_attendance', [AttendanceKhmerController::class, 'save_attendance']);
    Route::post('/getScheduleKhmer/{id}', [ScheduleKhmerController::class, 'getScheduleKhmer']);


    // settingSemester


    Route::post("/getSemester", [SettingMonthSemesterController::class, 'getSemester']);
    Route::post("/deleteSemester/{id}", [SettingMonthSemesterController::class, 'deleteSemester']);
    Route::post("/getOneSemester/{id}", [SettingMonthSemesterController::class, 'getOneSemester']);
    Route::post("/updateSemester", [SettingMonthSemesterController::class, 'updateSemester']);

    Route::post('/getSettingScoreConfig', [SettingScoreConfigController::class, "getSettingScoreConfig"]);
});

Route::post("/createSemester", [SettingMonthSemesterController::class, 'createSemester']);

Route::post('/addSettingScoreConfig', [SettingScoreConfigController::class, "addSettingScoreConfig"]);

Route::post('/getSemesterList/{id}', [SettingSemesterListController::class, "getSemesterList"]);
Route::post('/getOneSemesterList/{id}', [SettingSemesterListController::class, "getOneSemesterList"]);
Route::post('/updateSemesterlist', [SettingSemesterListController::class, "updateSemesterlist"]);



Route::get('/image', function (Request $request) {
    $relativePath = $request->query('path');

    if (!$relativePath || !Storage::disk('public')->exists($relativePath)) {
        return response()->json(['error' => 'Image not found'], 404);
    }

    $path = Storage::disk('public')->path($relativePath);

    return response()->file($path, [
        'Access-Control-Allow-Origin' => '*',
    ]);
});

Route::post('/getClassHaveTeacher', [TeacherClassController::class, "getClassHaveTeacher"]);

Route::post('/login', [AuthController::class, 'login']);
Route::get('viewporfile/{student_id}', [AuthController::class, 'viewprofile']);
Route::get('/getclass/{student_id}', [AuthController::class, 'getclass']);
Route::post('/deletechild', [AuthController::class, 'deletechild']);
Route::post("/addChild", [AuthController::class, 'addChild']);

Route::post('/getRole', [RoleController::class, 'getRole']);

Route::post("/getAllCampus", [CampusController::class, "getAllCampus"]);
// social
Route::post("/addSocial", [SocialController::class, "addSocial"]);
Route::post("/updateSocial", [SocialController::class, "updateSocial"]);
Route::post("/getSocial/{id}", [SocialController::class, "getSocial"]);
Route::post("/deleteSocial/{id}", [SocialController::class, "deleteSocial"]);
Route::post("/getOneSocial/{id}", [SocialController::class, "getOneSocial"]);


Route::post("/showStudentHabbit", [StudentHabbitController::class, "showStudentHabbit"]);
Route::post("/saveStudentHabit", [StudentHabbitController::class, "saveStudentHabit"]);
Route::get('clear-data', function () {
    Artisan::call('route:clear');
    Artisan::call('cache:clear');
    Artisan::call('optimize');
    Artisan::call('view:clear');
    Artisan::call('config:clear');
    Artisan::call('clear-compiled');
    return "Clear Complete";
});

// Route::post("/getTeacherInClass/{id}", [TeacherClassController::class, "getTeacherInClass"]);

// Route::get('/proxy-image', function () {
   
//     // header('Access-Control-Allow-Origin', '*');
  
//     $url = 'https://iconic.disreportcard.com/storage/user/May2025/LzFF4HDjbHS67trgM1Bb1QOol9pqVz1JxcZgH3VG.jpg';

//     return response()->json([
//         'image_url' => $url
//     ])->header('Access-Control-Allow-Origin', '*');
        
// });

// Route::get('/api/proxy-image', function () {
//     $url = 'https://iconic.disreportcard.com/storage/user/May2025/LzFF4HDjbHS67trgM1Bb1QOol9pqVz1JxcZgH3VG.jpg';
//     $response = Http::get($url);
//     if ($response->failed()) {
//         abort(404);
//     }
//     dd($response);
//     return response($response->body(), 200)
//         ->header('Content-Type', $response->header('Content-Type') ?? 'image/jpeg')
//         ->header('Access-Control-Allow-Origin', '*');
// });