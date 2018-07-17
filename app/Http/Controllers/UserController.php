<?php

namespace App\Http\Controllers;

use App\Album;
use App\Photo;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Intervention\Image\Facades\Image;
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
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function uploadProfilePic(Request $request){
        $user = JWTAuth::parseToken()->toUser($request->bearerToken());
        $validator = Validator::make($request->all(),[
            'profile_pic' => 'required|image|mimes:jpeg,png,jpg|max:2048'
        ]);

        if ($validator->fails()){
            return response()->json([
                'success' => false,
                'code' => 1002,
                'error' => $validator->errors()
            ]);
        }

        $s3 = Storage::disk('s3');

        $album = Album::where('album_name','Profile Picture')
            ->where('user_id',$user->id)
            ->first();

        if (!$album){
            $s3->makeDirectory($user->id.'/profile_picture');
            $album = new Album();
            $album->album_name = 'Profile Picture';
            $album->album_dir = 'profile_picture';
            $album->album_caption = 'Album for Profile Pictures';
            $album = $user->album()->save($album);
        }

        $profile_pic = $request->file('profile_pic');
        $extension = $profile_pic->getClientOriginalExtension();
        $profile_pic_name = str_random(16).'_'.$user->id.'_'.time().'.'.$extension;
        $dir = $user->id.'/profile_picture';

        $s3->put($dir.'/original/'.$profile_pic_name,file_get_contents($profile_pic),'public');
        $profile_pic_icon = Image::make($profile_pic)->resize(null,config('data.image.profile_pic_icon_height'),function ($contstraint){
            $contstraint->aspectRatio();
        })->crop(config('data.image.profile_pic_icon_width'),config('data.image.profile_pic_icon_height'))->encode($extension);
        $s3->put($dir.'/icon/'.$profile_pic_name,(string) $profile_pic_icon,'public');

        $profile_pic_thumb = Image::make($profile_pic)->resize(null,config('data.image.profile_pic_thumb_height'),function ($contstraint) {
            $contstraint->aspectRatio();
        })->crop(config('data.image.profile_pic_thumb_width'),config('data.image.profile_pic_thumb_height'))->encode($extension);
        $s3->put($dir.'/thumb/'.$profile_pic_name,(string) $profile_pic_thumb,'public');
        $url = $s3->url($dir);

        $user->profile_pic = $profile_pic_name;
        $user->save();

        $photo = new Photo();
        $photo->user_id = $user->id;
        $photo->album_id = $album->id;
        $photo->photo = $profile_pic_name;
        $photo->save();

        return response()->json([
            'success' => true,
            'code' => 1101,
            'data' => [
                'profile_pic' => $profile_pic_name,
                'profile_pic_icon_url' => $url.'/icon/'.$profile_pic_name,
                'profile_pic_thumb_url' => $url.'/thumb/'.$profile_pic_name,
                'profile_pic_original_url' => $url.'/original/'.$profile_pic_name,
            ]
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
