<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Namshi\JOSE\JWT;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */

    protected $table = 'users';

    protected $fillable = [
        'name', 'email', 'password','username','first_name','last_name','gender','dob','activation_token'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
    ];

    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     *
     * @return mixed
     */
    public function getJWTIdentifier()
    {
        // TODO: Implement getJWTIdentifier() method.
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
        // TODO: Implement getJWTCustomClaims() method.
        return [];
    }

    public function followers()
    {
        return $this->belongsToMany(User::class,'follow','following_id','follower_id')->withTimestamps();
    }

    public function following()
    {
        return $this->belongsToMany(User::class,'follow','follower_id','following_id')->withTimestamps();
    }

    public function countFollowers($user){
        $followers = $user->followers->count();
        return $followers;
    }

    public function countFollowing($user){
        $following = $user->following->count();
        return $following;
    }

    public function album()
    {
        return $this->hasMany(Album::class,'user_id');
    }
}
