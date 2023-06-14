<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\StaffController;
use App\Http\Controllers\LeaveTypeManagerController;
use App\Http\Controllers\LeaveManagerController;

use App\Http\Middleware\Authentication;
use App\Http\Middleware\ValidateLeaveInput;

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

Route::get( 'v1/employees',[StaffController::class, 'getAllStaff'])->middleware([Authentication::class]);
Route::get( 'v1/leave-types',[LeaveTypeManagerController::class, 'getLeaveTypes'])->middleware([Authentication::class]);
Route::get( 'v1/leave',[LeaveManagerController::class, 'getAllLeave'])->middleware([Authentication::class]);
Route::get('v1/calculate/{startDate}/{endDate}',[LeaveManagerController::class, 'calculateLeave'])->middleware([Authentication::class]);

Route::get('v1/search',[LeaveManagerController::class, 'search'])->middleware([Authentication::class]);


Route::post( 'v1/leave',[LeaveManagerController::class, 'store'])->middleware([Authentication::class,ValidateLeaveInput::class]);

/**
 * put v1/leave
 * get v1/leave
 * post v1/filter-leave
 */
