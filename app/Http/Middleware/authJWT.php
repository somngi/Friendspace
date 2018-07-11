<?php

namespace App\Http\Middleware;

use Closure;
use JWTAuth;

class authJWT
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        try{
            $user = JWTAuth::parseToken()->toUser($request->token);
        }catch (\Tymon\JWTAuth\Exceptions\JWTException $e) {
            if($e instanceof \Tymon\JWTAuth\Exceptions\TokenExpiredException) {
                return response()->json([
                    'success' => False,
                    'code' => 1001,
                    'error' => [
                        'message' => 'Token is Expired',
                    ]
                ]);
            } else if ($e instanceof \Tymon\JWTAuth\Exceptions\TokenInvalidException) {
                return response()->json([
                    'success' => False,
                    'code' => 1001,
                    'error' => [
                        'message' => 'Token is Invalid',
                    ]
                ]);
            } else if($e instanceof \Tymon\JWTAuth\Exceptions\JWTException){
                return response()->json([
                    'success' => False,
                    'code' => 1001,
                    'error' => [
                        'message' => 'Token Not Respond',
                    ]
                ]);
            } else{
                return response()->json([
                    'success' => False,
                    'code' => 1001,
                    'error' => [
                        'message' => 'Token is Required',
                    ]
                ]);
            }
        }
        return $next($request);
    }
}
