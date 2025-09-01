<?php


use Illuminate\Support\Facades\Route;

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
Route::get('clear-data',function(){
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

