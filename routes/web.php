<?php

use App\Http\Controllers\ArticlesController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\EventsController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

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

Route::get('/', function () {
    return view('welcome');
});

Route::post('register', [AuthController::class, 'register']);
Route::post('login', [AuthController::class, 'login']);
Route::post('forgot-password', [AuthController::class, 'forgotPassword']);
Route::post('reset-password', [AuthController::class, 'resetPassword']);

Route::get('/user', function (Request $request) {
    return $request->user();
});

Route::resource('articles', ArticlesController::class);
Route::get('articles/{id}', [ArticlesController::class, 'show']);
Route::put('articles/{id}', [ArticlesController::class, 'update']);
Route::delete('articles/{id}', [ArticlesController::class, 'destroy']);
Route::post('articles', [ArticlesController::class, 'store']);

Route::resource('events', EventsController::class);
Route::get('events/{id}', [EventsController::class, 'show']);
Route::put('events/{id}', [EventsController::class, 'update']);
Route::delete('events/{id}', [EventsController::class, 'destroy']);
Route::post('events', [EventsController::class, 'store']);

// events-change endpoint
Route::get('/events-change', function () {
    try {
        // Retrieve all documents from the events_changes collection
        $eventsChange = DB::connection('mongodb')->collection('events_changes')->get();
        
        // Return the retrieved documents as a JSON response
        return response()->json($eventsChange);
    } catch (\Exception $e) {
        // Return an error message as a JSON response if an exception occurs
        return response()->json(['error' => $e->getMessage()]);
    }
});

Route::get('/metrics', function () {
    $registry = new \Prometheus\CollectorRegistry(new \Prometheus\Storage\APC());
    $renderer = new \Prometheus\RenderTextFormat();
    $result = $renderer->render($registry->getMetricFamilySamples());
    return response($result, 200, ['Content-Type' => \Prometheus\RenderTextFormat::MIME_TYPE]);
})->middleware('prometheus');
