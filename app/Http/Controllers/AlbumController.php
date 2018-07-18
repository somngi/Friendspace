<?php

namespace App\Http\Controllers;

use App\Album;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use JWTAuth;

class AlbumController extends Controller
{
    //
    public function createAlbum(Request $request){
        $user = JWTAuth::parseToken()->toUser($request->bearerToken());
        $validator = Validator::make($request->all(),[
            'album_name' => 'required|max:20|min:2'
        ]);

        if ($validator->fails()){
            return response()->json([
                'success' => false,
                'code' => 1002,
                'error' => $validator->errors()
            ]);
        }
        $album_name = $request->input('album_name');
        $album_caption = !empty($request->input('album_caption')) ? $request->input('album_caption') : null;
        $album = Album::where('album_name',$album_name)
            ->where('user_id',$user->id)
            ->first();
        if ($album){
            return response()->json([
                'success' => false,
                'code' => 1002,
                'error' => [
                    'message' => config('data.message.album_exists')
                ]
            ]);
        }

        $s3 = Storage::disk('s3');
        $dir_name = str_replace(' ','_',strtolower($album_name)).'_'.$user->id.'_'.str_random(16);
        $s3->makeDirectory($user->id.'/'.$dir_name);

        $album = new Album();
        $album->album_name = $album_name;
        $album->album_caption = $album_caption;
        $album->album_dir = $dir_name;
        $album = $user->album()->save($album);

        if (!$album){
            return response()->json([
                'success' => false,
                'code' => 1002,
                'error' => [
                    'message' => config('data.message.album_error')
                ]
            ]);
        }
        return response()->json([
            'success' => false,
            'code' => 1002,
            'message' => config('data.message.album_success')
        ]);
    }

    public function editAlbum(Request $request,$id){
        $validator = Validator::make($request->all(),[
            'album_name' => 'required|max:20|min:2'
        ]);

        if ($validator->fails()){
            return response()->json([
                'success' => false,
                'code' => 1002,
                'error' => $validator->errors()
            ]);
        }

        $album = Album::find($id);
        if (!$album){
            return response()->json([
                'success' => false,
                'code' => 1002,
                'error' => [
                    'message' => config('data.message.album_not_exists')
                ]
            ]);
        }

        $album->album_name = $request->input('album_name');
        $album->album_caption = $request->input('album_caption');
        $album->save();

        return response()->json([
            'success' => false,
            'code' => 1002,
            'message' => config('data.message.album_edit_success')
        ]);


    }

    public function deleteAlbum($id){

    }

    public function getAlbums(Request $request){
        $user = JWTAuth::parseToken()->toUser($request->bearerToken());
        $albums = $user->album;
        return response()->json([
           $albums
        ]);
    }
}
