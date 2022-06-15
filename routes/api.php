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
    Route::get('/logout',[App\Http\Controllers\AuthController::class,'destroy']);

    
});

Route::get('v1/me',[App\Http\Controllers\AuthController::class,'profile']);

//assets
Route::post('v1/assets',[App\Http\Controllers\AssetController::class,'store']);

//shares
Route::post('v1/shares',[App\Http\Controllers\ShareController::class,'store']);
Route::get('v1/shares',[App\Http\Controllers\ShareController::class,'fetch']);
Route::get('v1/all_shares',[App\Http\Controllers\ShareController::class,'fetch_all']);


Route::post('/v1/register',[App\Http\Controllers\AuthController::class,'store']);
Route::post('/v1/login',[App\Http\Controllers\AuthController::class,'login']);

