<?php

namespace App\Http\Controllers;

use App\Mail\ActivationMail;
use App\Mail\ResetPasswordMail;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use JWTAuth;
use JWTException;


class AuthController extends Controller
{
    //
    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function register(Request $request){
        $validator = Validator::make($request->all(), [
            'username' => 'required|min:6|unique:users',
            'email' => 'required|email|max:255|unique:users',
            'first_name' => 'required|max:20',
            'last_name' => 'max:10',
            'password'=> 'required|min:6|max:20',
            'confirm_password' => 'required|same:password'
        ]);
        if ($validator->fails()) {
            return response()->json([
                'success' => False,
                'code' => 1002,
                'error' => $validator->errors()
            ]);
        }

        $username = $request->input('username');
        $email = $request->input('email');
        $first_name = $request->input('first_name');
        $last_name = $request->input('last_name');
        $password = bcrypt($request->input('password'));
        $activation_token = str_random(42);

        User::create([
            'username' => $username,
            'password' => $password,
            'first_name' => $first_name,
            'last_name' => $last_name,
            'email' => $email,
            'activation_token' => $activation_token
        ]);

        $name = $first_name.' '.$last_name;

        Mail::to($email)->send(new ActivationMail($name,$activation_token));

        return response()->json([
            'success' => True,
            'code' => 1101,
            'message' => "Register Successful"
        ]);
    }


    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(Request $request){
        $validator = Validator::make($request->all(), [
            'username' => 'required|min:6',
            'password'=> 'required|min:6|max:20',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'success' => False,
                'code' => 1002,
                'error' => $validator->errors()
            ]);
        }

        $username = $request->input('username');
        $password = $request->input('password');

        $user = User::where('username',$username)
            ->orWhere('email',$username)
            ->where('password',bcrypt($password))
            ->where('is_active', 1)
            ->where('is_delete',0)
            ->first();

        if (!$user){
            return response()->json([
                'success' => False,
                'code' => 1003,
                'error' => [
                    'message' => 'Invalid Username/Email or Password',
                ]
            ]);
        }

        $credentials = array('email'=>$user->email,'password' => $password);
        try {
            if (! $token = JWTAuth::attempt($credentials)) {
                return response()->json([
                    'success' => False,
                    'code' => 1003,
                    'error' => [
                        'message' => 'Invalid Username/Email or Password',
                    ]
                ]);
            }
        } catch (JWTException $e) {
            return response()->json([
                'success' => False,
                'code' => 1003,
                'error' => [
                    'message' => 'Problem to generate Token',
                ]
            ]);
        }

        return response()->json([
            'success' => True,
            'code' => 1101,
            'token' => $token
        ]);
    }

    /**
     * @param $token
     * @return \Illuminate\Http\JsonResponse
     */
    public function activateAccount($token){
        if (empty($token)){
            return response()->json([
                'success' => False,
                'code' => 1003,
                'error' => [
                    'token' => 'Activation Token is Required'
                ]
            ]);
        }

        $user = User::where('activation_token',$token)->first();
        if (!$user){
            return response()->json([
                'success' => False,
                'code' => 1003,
                'error' => [
                    'token' => 'Activation Code Expires'
                ]
            ]);
        }
        $user->activation_token = null;
        $user->is_active = 1;
        $user->save();

        return response()->json([
            'success' => True,
            'code' => 1102,
            'message' => "Activated Successfully"
        ]);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function forgotPassword(Request $request){
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'success' => False,
                'code' => 1002,
                'error' => $validator->errors()
            ]);
        }

        $email = $request->input('email');
        $user = User::where('email',$email)
            ->where('is_active',1)
            ->where('is_delete',0)
            ->first();

        if (!$user){
            return response()->json([
                'success' => False,
                'code' => 1002,
                'error' => [
                    'message' => 'Email Not Exists'
                ]
            ]);
        }

        $activation_token = str_random('42');
        $user->activation_token = $activation_token;
        $user->save();

        $name = $user->first_name.' '.$user->last_name;

        Mail::to($email)->send(new ResetPasswordMail($name,$activation_token));

        return response()->json([
            'success' => True,
            'code' => 1104,
            'message' => "Reset Password Link sent Successfully"
        ]);
    }

    /**
     * @param Request $request
     * @param $token
     * @return \Illuminate\Http\JsonResponse
     */
    public function resetPassword(Request $request, $token){
        if (empty($token)){
            return response()->json([
                'success' => False,
                'code' => 1003,
                'error' => [
                    'token' => 'Activation Token is Required'
                ]
            ]);
        }

        $validator = Validator::make($request->all(), [
            'password'=> 'required|min:6|max:20',
            'confirm_password' => 'required|same:password'
        ]);
        if ($validator->fails()) {
            return response()->json([
                'success' => False,
                'code' => 1002,
                'error' => $validator->errors()
            ]);
        }

        $password = $request->input('password');

        $user = User::where('activation_token',$token)->first();
        if (!$user){
            return response()->json([
                'success' => False,
                'code' => 1003,
                'error' => [
                    'token' => 'Activation Code Expires'
                ]
            ]);
        }
        $user->activation_token = null;
        $user->password = bcrypt($password);
        $user->save();

        return response()->json([
            'success' => True,
            'code' => 1102,
            'message' => "Password Change Successfully"
        ]);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function changePassword(Request $request){
        $validator = Validator::make($request->all(), [
            'password'=> 'required|min:6|max:20',
            'confirm_password' => 'required|same:password'
        ]);
        if ($validator->fails()) {
            return response()->json([
                'success' => False,
                'code' => 1002,
                'error' => $validator->errors()
            ]);
        }

        $password = $request->input('password');
        $user = JWTAuth::parseToken()->toUser($request->token);
        $current_password = $request->input('current_password');

        $user = User::where('username',$user->username)
            ->orWhere('email',$user->email)
            ->where('password',bcrypt($current_password))
            ->where('is_active', 1)
            ->where('is_delete',0)
            ->first();

        if (!$user){
            return response()->json([
                'success' => False,
                'code' => 1002,
                'error' => [
                    'message' => 'Current Password not Match'
                ]
            ]);
        }

        $user->password = bcrypt($password);
        $user->save();

        return response()->json([
            'success' => True,
            'code' => 1102,
            'message' => "Password Change Successfully"
        ]);
    }

    /**
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function logout(){
        JWTAuth::invalidate();
        return response([
            'success' => True,
            'code' => 1001,
            'message' => "Logout Successfully"
        ]);
    }
}
