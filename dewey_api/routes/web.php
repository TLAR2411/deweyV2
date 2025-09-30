<?php


use Illuminate\Support\Facades\Route;

use function PHPSTORM_META\type;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/



// Route::get('/exportStudentClass/{id}', [StudentClassExportController::class, 'exportStudentClass']);

Route::get('/', function () {
    return view('welcome');
});
Route::get('clear-data', function () {
    Artisan::call('route:clear');
    Artisan::call('cache:clear');
    Artisan::call('optimize');
    Artisan::call('view:clear');
    Artisan::call('config:clear');
    Artisan::call('clear-compiled');
    return "Clear Complete";
});

Route::get('/proxy-image', function () {
    $url = 'https://iconic.disreportcard.com/storage/user/May2025/LzFF4HDjbHS67trgM1Bb1QOol9pqVz1JxcZgH3VG.jpg';

    $response = Http::get($url);

    if ($response->failed()) {
        abort(404);
    }

    return response($response->body(), 200)
        ->header('Content-Type', $response->header('Content-Type'))
        ->header('Access-Control-Allow-Origin', '*');
});

Route::get('/viewprimaryExport', function () {
    // $url = "http://127.0.0.1:8000/api/viewPrimary";

    // $data = [
    //     "campus_id" => "*",
    //     "class_id"  => 34,
    //     "level"     => 6,
    //     "month_id"  => null,
    //     "type"      => "semester1",
    //     "year_id"   => 1
    // ];

    // $ch = curl_init($url);

    // $payload = json_encode($data);

    // curl_setopt($ch, CURLOPT_POST, true);
    // curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
    // curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    // curl_setopt($ch, CURLOPT_HTTPHEADER, [
    //     "Content-Type: application/json",
    //     "Accept: application/json",
    //     "Authorization: Bearer 214|OGzuzxqV61g1aUxjiTheu0bpq6yTe1sPzVufspVF2faad283",
    //     "Connection: keep-alive"
    // ]);

    // $response = curl_exec($ch);

    // if (curl_errno($ch)) {
    //     echo "Error: " . curl_error($ch);
    // } else {
    //     echo "Response:\n" . $response;
    // }

    // curl_close($ch);

    // if (curl_errno($ch)) {
    //     echo "Error: " . curl_error($ch);
    // } else {
    //     return view('primaryReport')->with('type', $response);
    // }

    //    curl_close($ch);
    $response = [];
    return view('primaryReport')->with('type', $response);
});


Route::get('test_route', function () {
    return 1;
});
