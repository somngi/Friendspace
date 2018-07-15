<?php

namespace App\Http\Controllers;

use App\User;
use App\Follow;
use Illuminate\Http\Request;
use JWTAuth;

class FollowController extends Controller
{
    //
    public function follow(Request $request,$id){
        $follower = JWTAuth::parseToken()->toUser($request->token);
        $following = User::find($id);
        if (!$following){
            return response()->json([
                'success' => false,
                'code' => 1102,
                'error' => [
                    'message' => config('data.message.user_not_find')
                ]
            ]);
        }

        if ($follower->id == $id){
            return response()->json([
                'success' => false,
                'code' => 1102,
                'error' => [
                    'message' => config('data.message.follow_error')
                ]
            ]);
        }

        $follow = Follow::where('follower_id',$follower->id)
            ->where('following_id',$id)
            ->first();

        if ($follow){
            return response()->json([
                'success' => false,
                'code' => 1102,
                'error' => [
                    'message' => config('data.message.follow_exists')
                ]
            ]);
        }

        $following->followers()->attach($follower->id);
        return response()->json([
            'success' => true,
            'code' => 1101,
            'message' => config('data.message.follow_success')
        ]);
    }

    public function unFollow(Request $request,$id){
        $follower = JWTAuth::parseToken()->toUser($request->token);
        $following = User::find($id);
        if (!$following){
            return response()->json([
                'success' => false,
                'code' => 1102,
                'error' => [
                    'message' => config('data.message.user_not_find')
                ]
            ]);
        }

        if ($follower->id == $id){
            return response()->json([
                'success' => false,
                'code' => 1102,
                'error' => [
                    'message' => config('data.message.follow_error')
                ]
            ]);
        }

        $follow = Follow::where('follower_id',$follower->id)
            ->where('following_id',$id)
            ->first();

        if (!$follow){
            return response()->json([
                'success' => false,
                'code' => 1102,
                'error' => [
                    'message' => config('data.message.follow_not_exists')
                ]
            ]);
        }

        $following->followers()->detach($follower->id);
        return response()->json([
            'success' => true,
            'code' => 1101,
            'message' => config('data.message.unfollow_success')
        ]);
    }

    public function getFollowers($id){
        $user = User::find($id);
        $followers = $user->followers;

        return response()->json([
            $followers
        ]);
    }

    public function getFollowing($id){
        $user = User::find($id);
        $followers = $user->following;

        return response()->json([
            $followers
        ]);
    }
}
