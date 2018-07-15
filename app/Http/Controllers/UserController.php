<?php

namespace App\Http\Controllers;

use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use JWTAuth;

class UserController extends Controller
{

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function userProfile(Request $request)
    {
        $auth_user = JWTAuth::parseToken()->toUser($request->token);
        $user = new User();
        $followers = $user->countFollowers($auth_user);
        $following = $user->countFollowing($auth_user);
        $userData = User::find($auth_user->id);
        return response()->json([
            'user' => $userData,
            'followers' => $followers,
            'following' => $following
        ]);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateProfile(Request $request){
        $user_id = JWTAuth::parseToken()->toUser($request->token)->id;
        $validator = Validator::make($request->all(),[
            'mob_no' => 'min:8|max:13|regex:/^\+?\d+$/|unique:users,mob_no,'.$user_id,
            'dob' => 'date_format:d/m/Y',
            'website' => 'url',
            'fb_url' => 'url',
            'linkedin_url' => 'url',
            'twitter_url' => 'url',
            'instagram_url' => 'url',
            'google_plus_url' => 'url'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'code' => 1002,
                'error' => $validator->errors()
            ]);
        }
        $user = User::find($user_id);
        $user->first_name = $request->input('first_name');
        $user->last_name = $request->input('last_name');
        $user->nick_name = $request->input('nick_name');
        $user->gender = $request->input('gender');
        $user->dob = Carbon::createFromFormat('d/m/Y',$request->input('dob'))->toDateTimeString();
        $user->mob_no = $request->input('mob_no');
        $user->website = $request->input('website');
        $user->fb_url = $request->input('fb_url');
        $user->twitter_url = $request->input('twitter_url');
        $user->linkedin_url = $request->input('linkedin_url');
        $user->instagram_url = $request->input('instagram_url');
        $user->google_plus_url = $request->input('google_plus_url');
        $user->save();

        return response()->json([
            'success' => true,
            'code' => '1101',
            'message' => config('data.message.update_success')

        ]);
    }

    /**
     * @param $username
     * @return \Illuminate\Http\JsonResponse
     */
    public function userDataByUsername($username)
    {
        $userData = User::where('username', $username)
            ->where('is_active', 1)
            ->select('id', 'username', 'email', 'first_name', 'last_name', 'nick_name', 'gender', 'dob', 'profile_pic', 'website', 'fb_url', 'twitter_url', 'linkedin_url', 'instagram_url', 'google_plus_url')
            ->first();
        if (!$userData) {
            return response()->json([
                'success' => false,
                'code' => '1101',
                'error' => [
                    'message' => config('data.message.user_not_find')
                ]
            ]);
        }
        $userPhotos =  [
                'album-1' => [
                    'photo1' => 'po.jpg',
                    'photo2' => 'po.jpg',
                    'photo3' => 'po.jpg',
                ],
                'album-2' => [
                    'photo1' => 'po.jpg',
                    'photo2' => 'po.jpg',
                    'photo3' => 'po.jpg',
                ]
        ];
        $userData = array_add($userData,'user_photo',$userPhotos);
        return response()->json([
            'success' => true,
            'code' => '1101',
            'data' => $userData
        ]);

    }

    /**
     * @return \Illuminate\Http\JsonResponse
     */
    public function getUsersList()
    {
        $usersList = User::where('is_active', 1)
            ->where('is_delete', 0)
            ->select('id','username','first_name','last_name','profile_pic')
            ->get();
        return response()->json([
            'success' => true,
            'code' => '1101',
            'data' => $usersList
        ]);

    }

    /**
     * @param $query
     * @return \Illuminate\Http\JsonResponse
     */
    public function searchUsers($query){
        $query = str_replace('+',' ',$query);
        $searchData = User::where('username','LIKE','%'.$query.'%')
            ->orWhere('first_name','LIKE','%'.$query.'%')
            ->orWhere('last_name','LIKE','%'.$query.'%')
            ->orWhere('nick_name','LIKE','%'.$query.'%')
            ->select('id','username','first_name','last_name','profile_pic')
            ->get();
        return response()->json([
            'success' => true,
            'code' => '1101',
            'data' => $searchData
        ]);
    }

}
