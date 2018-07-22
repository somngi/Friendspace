<?php

namespace App\Http\Controllers;

use App\Friend;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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
        $friend = Friend::where('user_id',$user->id)->where('friend_id',$id)->first();
        if ($friend){
            if ($friend->status == 2){
                $friend->status = 0;
                $friend->save();
                return response()->json([
                    'success' => true,
                    'code' => 1101,
                    'message' => 'friend_request_sent_success'
                ]);
            }
            return response()->json([
                'success' => false,
                'code' => 1102,
                'error' => [
                    'message' => 'request_sent_already'
                ]
            ]);
        }
        $user->friends()->attach($id);
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
        if ($friend->status !== 0){
            return response()->json([
                'success' => false,
                'code' => 1102,
                'error' => [
                    'message' => 'already_accept'
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
        if ($friend->status !== 0){
            return response()->json([
                'success' => false,
                'code' => 1102,
                'error' => [
                    'message' => 'already_delete_or_accept'
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
        $friend->status = 2;
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
        $friend->status = 3;
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

    public function getSentRequest(Request $request){
        $user = JWTAuth::parseToken()->toUser($request->bearerToken());
        $friends = DB::table('friends')
            ->leftJoin('users as friend','friend.id','=','friends.friend_id')
            ->select('friends.*','friend.first_name as friend')
            ->where('friends.user_id',$user->id)
            ->where('status',0)
            ->get();
        if ($friends->count() == 0){
            return response()->json([
                'success' => false,
                'code' => 1102,
                'error' => [
                    'message' => 'no_sent_request'
                ]
            ]);
        }
        return response()->json([
            'success' => true,
            'code' => 1101,
            'data' => $friends
        ]);
    }

    public function getReceiveRequest(Request $request){
        $user = JWTAuth::parseToken()->toUser($request->bearerToken());
        $friends = DB::table('friends')
            ->leftJoin('users as friend','friend.id','=','friends.user_id')
            ->select('friends.*','friend.first_name as friend')
            ->where('friends.friend_id',$user->id)
            ->where('status',0)
            ->get();

        if ($friends->count() == 0){
            return response()->json([
                'success' => false,
                'code' => 1102,
                'error' => [
                    'message' => 'no_receive_request'
                ]
            ]);
        }
        return response()->json([
            'success' => true,
            'code' => 1101,
            'data' => $friends
        ]);
    }

    public function getFriendList(Request $request){
        $user = JWTAuth::parseToken()->toUser($request->bearerToken());
        $friends = DB::table('friends')
            ->leftJoin('users as friend','friend.id','=','friends.friend_id')
            ->select('friends.*','friend.first_name as friend')
            ->where('friends.user_id',$user->id)
            ->where('status',1)
            ->get();

        if ($friends->count() == 0){
            return response()->json([
                'success' => false,
                'code' => 1102,
                'error' => [
                    'message' => 'no_friend'
                ]
            ]);
        }
        return response()->json([
            'success' => true,
            'code' => 1101,
            'data' => $friends
        ]);
    }

    public function getBlockUsers(Request $request){
        $user = JWTAuth::parseToken()->toUser($request->bearerToken());
        $friends = DB::table('friends')
            ->leftJoin('users as friend','friend.id','=','friends.friend_id')
            ->select('friends.*','friend.first_name as friend')
            ->where('friends.user_id',$user->id)
            ->where('status',3)
            ->get();

        if ($friends->count() == 0){
            return response()->json([
                'success' => false,
                'code' => 1102,
                'error' => [
                    'message' => 'no_friend'
                ]
            ]);
        }
        return response()->json([
            'success' => true,
            'code' => 1101,
            'data' => $friends
        ]);
    }
}
