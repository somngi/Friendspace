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
                        'message' => config('data.token.expire'),
                    ]
                ]);
            } else if ($e instanceof \Tymon\JWTAuth\Exceptions\TokenInvalidException) {
                return response()->json([
                    'success' => False,
                    'code' => 1001,
                    'error' => [
                        'message' => config('data.token.invalid'),
                    ]
                ]);
            } else if($e instanceof \Tymon\JWTAuth\Exceptions\JWTException){
                return response()->json([
                    'success' => False,
                    'code' => 1001,
                    'error' => [
                        'message' => config('data.token.respond'),
                    ]
                ]);
            } else{
                return response()->json([
                    'success' => False,
                    'code' => 1001,
                    'error' => [
                        'message' => config('data.token.required'),
                    ]
                ]);
            }
        }
        return $next($request);
    }
}
