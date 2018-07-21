<?php

namespace App\Http\Controllers;

use App\Album;
use App\Photo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Intervention\Image\Facades\Image;
use JWTAuth;

class PhotoController extends Controller
{
    //
    public function uploadPhoto(Request $request){
        $user = JWTAuth::parseToken()->toUser($request->bearerToken());
        $validator = Validator::make($request->all(),[
            'album_id' => 'required|exists:user_photo_album,id',
            'photo' => 'required'
        ]);

        if ($validator->fails()){
            return response()->json([
                'success' => false,
                'code' => 1002,
                'error' => $validator->errors()
            ]);
        }
        $album_id = $request->input('album_id');
        $photo_caption = $request->input('photo_caption');

        $album = Album::find($album_id);
        if ($album->user_id !== $user->id){
            return response()->json([
                'success' => false,
                'code' => 1002,
                'error' => [
                    'message' => config('data.message.invalid_album')
                ]
            ]);
        }

        $photo = $request->file('photo');
        $extension = $photo->getClientOriginalExtension();
        $photo_name = str_random(16).'_'.$user->id.$album_id.'_'.time().'.'.$extension;
        $dir = $user->id.'/'.$album->album_dir;

        $s3 = Storage::disk('s3');
        $s3->put($dir.'/original/'.$photo_name,file_get_contents($photo),'public');

        $photo_thumb = Image::make($photo)->resize(null,config('data.image.album_photo_thumb_height'),function ($contstraint) {
            $contstraint->aspectRatio();
        })->crop(config('data.image.album_photo_thumb_width'),config('data.image.album_photo_thumb_height'))->encode($extension);
        $s3->put($dir.'/thumb/'.$photo_name,(string) $photo_thumb,'public');
        $url = $s3->url($dir);

        $photo = new Photo();
        $photo->user_id = $user->id;
        $photo->album_id = $album->id;
        $photo->photo = $photo_name;
        $photo->photo_caption = $photo_caption;
        $photo->save();

        if ($album->album_cove == null){
            $album->album_cover = $photo_name;
            $album->save();
        }

        return response()->json([
            'success' => true,
            'code' => 1101,
            'data' => [
                'photo' => $photo_name,
                'photo_thumb_url' => $url.'/thumb/'.$photo_name,
                'photo_original_url' => $url.'/thumb/'.$photo_name,
            ]
        ]);
    }

    public function getPhoto(Request $request,$id){
        $user = JWTAuth::parseToken()->toUser($request->bearerToken());
        $photo = DB::table('user_photos')
            ->leftJoin('user_photo_album','user_photo_album.id', '=', 'user_photos.album_id')
            ->where('user_photos.id',$id)
            ->first();
        if (!$photo){
            return response()->json([
                'success' => false,
                'code' => 1101,
                'message' => 'photo_not_found'
            ]);
        }
        $s3 = Storage::disk('s3');
        $url = $s3->url($user->id.'/'.$photo->album_dir);
        $photo->photo_thumb_url = $url.'/thumb/'.$photo->photo;
        $photo->photo_original_url = $url.'/thumb/'.$photo->photo;

        return response()->json([
            'success' => true,
            'code' => 1101,
            'data' => $photo
        ]);
    }

    public function deletePhoto(Request $request,$id){
        $user = JWTAuth::parseToken()->toUser($request->bearerToken());
        $photo = DB::table('user_photos')
            ->leftJoin('user_photo_album','user_photo_album.id', '=', 'user_photos.album_id')
            ->where('user_photos.id',$id)
            ->first();
        if (!$photo){
            return response()->json([
                'success' => false,
                'code' => 1101,
                'message' => 'photo_not_found'
            ]);
        }
        if ($photo->user_id !== $user->id){
            return response()->json([
                'success' => false,
                'code' => 1101,
                'message' => config('data.message.invalid_photo')
            ]);
        }
        $s3 = Storage::disk('s3');
        $s3->delete($user->id.'/'.$photo->album_dir.'/thumb/'.$photo->photo);
        $s3->delete($user->id.'/'.$photo->album_dir.'/original/'.$photo->photo);
        $result_photo = Photo::destroy($id);
        if (!$result_photo){
            return response()->json([
                'success' => false,
                'code' => 1101,
                'message' => 'photo_delete_error'
            ]);
        }

        return response()->json([
            'success' => true,
            'code' => 1101,
            'message' => 'photo_delete_success'
        ]);
    }
}
