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
    Route::POST('profile_pic','UserController@uploadProfilePic');


    //follow and Unfollow
    Route::PUT('user/{id}/follow','FollowController@follow');
    Route::DELETE('user/{id}/follow','FollowController@unFollow');
    Route::GET('followers/{id}','FollowController@getFollowers');
    Route::GET('following/{id}','FollowController@getFollowing');

    //Album
    Route::GET('album','AlbumController@getAlbums');
    Route::POST('album','AlbumController@createAlbum');
    Route::POST('album/{id}','AlbumController@editAlbum');
    Route::DELETE('album/{id}','AlbumController@deleteAlbum');
    Route::GET('album/{id}','AlbumController@getAlbumPhoto');
    Route::GET('all_album','AlbumController@getAllAlbumWithPhoto');

    //Photo
    Route::get('photo/{id}','PhotoController@getPhoto');
    Route::POST('photo','PhotoController@uploadPhoto');
    Route::DELETE('photo/{id}','PhotoController@deletePhoto');

});

//User Without Auth
Route::GET('user/{username}','UserController@userDataByUsername');
