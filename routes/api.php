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
Route::GET('activate_account/{token}','AuthController@activateAccount');
Route::POST('forgot_password','AuthController@forgotPassword');
Route::POST('reset_password/{token}','AuthController@resetPassword');

//User
Route::middleware('JWTAuth')->group(function (){
    Route::GET('logout','AuthController@logout');
    Route::POST('change_password','AuthController@changePassword');


    //User
    Route::GET('user_profile','UserController@userProfile');
    Route::POST('update_profile','UserController@updateProfile');
    Route::GET('users_list','UserController@getUsersList');
    Route::GET('users/q={query}','UserController@searchUsers');


    //follow and Unfollow
    Route::POST('follow','UserController@follow');
    Route::POST('un_follow','UserController@follow');
    Route::GET('followers/{$id}','UserController@getFollowers');
    Route::GET('following/{$id}','UserController@getFollowing');
});

//User Without Auth
Route::GET('user/{username}','UserController@userDataByUsername');
