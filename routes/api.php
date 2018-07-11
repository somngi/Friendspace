<?php

use Illuminate\Http\Request;

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

//Authentication
Route::POST('login','AuthController@login');
Route::POST('register','AuthController@register');
Route::get('activate_account/{token}','AuthController@activateAccount');
Route::POST('forgot_password','AuthController@forgotPassword');
Route::POST('reset_password/{token}','AuthController@resetPassword');

//User
Route::middleware('JWTAuth')->group(function (){
    Route::get('logout','AuthController@logout');
    Route::POST('change_password','AuthController@changePassword');
    Route::get('/user/{username}','AuthController@userDataByUsername');
    Route::get('/user/{id}','AuthController@userDataById');
    Route::get('/user/{email}','AuthController@userDataByEmail');
});

