<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::middleware('auth:sanctum')->get('/projects', [\App\Http\Controllers\ProjectController::class, 'apiIndex']);
Route::middleware('auth:sanctum')->get('/time-entries', 'TimeEntryController@apiIndex');
Route::middleware('auth:sanctum')->post('/time-entries/add', [\App\Http\Controllers\TimeEntryController::class, 'store'])->name('time_entries.store');
Route::middleware('auth:sanctum')->get('/reports', [\App\Http\Controllers\ReportController::class, 'reportProjects']);
Route::middleware('auth:sanctum')->get('/reports/week-breakdown', [\App\Http\Controllers\ReportController::class, 'reportWeekBreakdown']);
Route::middleware('auth:sanctum')->delete('/projects/{project}', [\App\Http\Controllers\ProjectController::class, 'destroy']);
Route::middleware('auth:sanctum')->get('/time-entries/day/{day}', [\App\Http\Controllers\TimeEntryController::class, 'getEntriesForDay']);
Route::middleware('auth:sanctum')->get('/time-entries/week-view', [\App\Http\Controllers\TimeEntryController::class, 'getWeekView']);
Route::middleware('auth:sanctum')->put('/projects/{id}', [\App\Http\Controllers\ProjectController::class, 'update']);
Route::middleware('auth:sanctum')->post('/projects/{project}/add-users', [\App\Http\Controllers\ProjectController::class, 'addUsers']);
Route::middleware('auth:sanctum')->get('/team-users', [\App\Http\Controllers\TeamController::class, 'getUsers']);

