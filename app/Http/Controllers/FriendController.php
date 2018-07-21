<?php

namespace App\Http\Controllers;

use App\User;
use Illuminate\Http\Request;
use JWTAuth;

class FriendController extends Controller
{
    public function sendRequest(Request $request,$id){
        $user = JWTAuth::parseToken()->toUser($request->bearerToken());
        $friend = User::find($id);
        if (!$friend){
            return response()->json([
                'success' => false,
                'code' => 1102,
                'error' => [
                    'message' => config('data.message.user_not_find')
                ]
            ]);
        }
        $friend = $user->friends()->attach($id);
        if (!$friend){
            return response()->json([
                'success' => false,
                'code' => 1102,
                'error' => [
                    'message' => 'friend_request_sent_error'
                ]
            ]);
        }
        return response()->json([
            'success' => true,
            'code' => 1101,
            'message' => 'friend_request_sent_success'
        ]);
    }

    public function acceptRequest(Request $request,$id){
        $user = JWTAuth::parseToken()->toUser($request->bearerToken());
        $friend = Friend::find($id);
        if (!$friend){
            return response()->json([
                'success' => false,
                'code' => 1102,
                'error' => [
                    'message' => 'invalid_id'
                ]
            ]);
        }
        if ($friend->friend_id !== $user->id){
            return response()->json([
                'success' => false,
                'code' => 1102,
                'error' => [
                    'message' => 'invalid_friend_request'
                ]
            ]);
        }
        $friend->status = 1;
        $result = $friend->save();
        if (!$result){
            return response()->json([
                'success' => false,
                'code' => 1102,
                'error' => [
                    'message' => 'friend_request_accept_error'
                ]
            ]);
        }
        return response()->json([
            'success' => true,
            'code' => 1101,
            'message' => 'friend_request_accept_success'
        ]);
    }

    public function deleteRequest(Request $request,$id){
        $user = JWTAuth::parseToken()->toUser($request->bearerToken());
        $friend = Friend::find($id);
        if (!$friend){
            return response()->json([
                'success' => false,
                'code' => 1102,
                'error' => [
                    'message' => 'invalid_id'
                ]
            ]);
        }
        if ($friend->friend_id !== $user->id){
            return response()->json([
                'success' => false,
                'code' => 1102,
                'error' => [
                    'message' => 'invalid_friend_request'
                ]
            ]);
        }
        $friend->status = 1;
        $result = $friend->save();
        if (!$result){
            return response()->json([
                'success' => false,
                'code' => 1102,
                'error' => [
                    'message' => 'friend_request_delete_error'
                ]
            ]);
        }
        return response()->json([
            'success' => true,
            'code' => 1101,
            'message' => 'friend_request_delete_success'
        ]);
    }

    public function blockFriend(Request $request,$id){
        $user = JWTAuth::parseToken()->toUser($request->bearerToken());
        $friend = Friend::find($id);
        if (!$friend){
            return response()->json([
                'success' => false,
                'code' => 1102,
                'error' => [
                    'message' => 'invalid_id'
                ]
            ]);
        }
        if ($friend->friend_id !== $user->id){
            return response()->json([
                'success' => false,
                'code' => 1102,
                'error' => [
                    'message' => 'invalid_friend_request'
                ]
            ]);
        }
        $friend->status = 1;
        $result = $friend->save();
        if (!$result){
            return response()->json([
                'success' => false,
                'code' => 1102,
                'error' => [
                    'message' => 'friend_block_error'
                ]
            ]);
        }
        return response()->json([
            'success' => true,
            'code' => 1101,
            'message' => 'friend_block_success'
        ]);
    }

    public function getFriendRequest(Request $request){

    }

    public function getFriendList(Request $request){

    }

    public function getBlockUsers(Request $request){

    }
}
