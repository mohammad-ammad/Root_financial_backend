<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::group(['prefix' => 'v1','middleware'=>'user_auth'],function(){
    Route::get('/me',[App\Http\Controllers\AuthController::class,'profile']);
    Route::get('/logout',[App\Http\Controllers\AuthController::class,'destroy']);

    //assets
    Route::post('/assets',[App\Http\Controllers\AssetController::class,'store']);

    //shares
    Route::post('/shares',[App\Http\Controllers\ShareController::class,'store']);
    Route::get('/shares',[App\Http\Controllers\ShareController::class,'fetch']);
});


Route::post('/v1/register',[App\Http\Controllers\AuthController::class,'store']);
Route::post('/v1/login',[App\Http\Controllers\AuthController::class,'login']);

