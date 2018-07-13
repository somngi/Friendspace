<?php

namespace App\Http\Controllers;

use App\Mail\ActivationMail;
use App\Mail\ResetPasswordMail;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
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
            'gender' => 'required',
            'password'=> 'required|min:6|max:20',
            'confirm_password' => 'required|same:password'
        ]);
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'code' => 1002,
                'error' => $validator->errors()
            ]);
        }

        $username = $request->input('username');
        $email = $request->input('email');
        $first_name = $request->input('first_name');
        $last_name = $request->input('last_name');
        $gender = $request->input('gender');
        $password = Hash::make($request->input('password'));
        $activation_token = str_random(42);

        User::create([
            'username' => $username,
            'email' => $email,
            'password' => $password,
            'first_name' => $first_name,
            'last_name' => $last_name,
            'gender' => $gender,
            'activation_token' => $activation_token
        ]);

        $name = $first_name.' '.$last_name;

        if(Mail::to($email)->send(new ActivationMail($name,$activation_token))){
            return response()->json([
                'success' => false,
                'code' => 1101,
                'message' => config('data.message.register_mail_error')
            ]);
        }

        return response()->json([
            'success' => true,
            'code' => 1101,
            'message' => config('data.message.register_success')
        ]);
    }


    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(Request $request){
        $validator = Validator::make($request->all(), [
            'login' => 'required',
            'password'=> 'required|min:6|max:20',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'code' => 1002,
                'error' => $validator->errors()
            ]);
        }

        $login = $request->input('login');
        $password = $request->input('password');

        $field = filter_var($login,FILTER_VALIDATE_EMAIL) ? 'email' : 'username';

        $credentials = array($field => $login, 'password' => $password);
        try {
            if (! $token = JWTAuth::attempt($credentials)) {
                return response()->json([
                    'success' => false,
                    'code' => 1003,
                    'error' => [
                        'message' => config('data.message.login_error'),
                    ]
                ]);
            }
        } catch (JWTException $e) {
            return response()->json([
                'success' => false,
                'code' => 1003,
                'error' => [
                    'message' => config('data.token.generate'),
                ]
            ]);
        }

        return response()->json([
            'success' => true,
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
                'success' => false,
                'code' => 1003,
                'error' => [
                    'token' => config('data.token.activate_required')
                ]
            ]);
        }

        $user = User::where('activation_token',$token)->first();
        if (!$user){
            return response()->json([
                'success' => false,
                'code' => 1003,
                'error' => [
                    'token' => config('data.token.activate_expire')
                ]
            ]);
        }
        $user->activation_token = null;
        $user->is_active = 1;
        $user->save();

        return response()->json([
            'success' => true,
            'code' => 1102,
            'message' => config('data.message.activate')
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
                'success' => false,
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
                'success' => false,
                'code' => 1002,
                'error' => [
                    'message' => config('data.message.email_not_exist')
                ]
            ]);
        }

        $activation_token = str_random('42');
        $user->activation_token = $activation_token;
        $user->save();

        $name = $user->first_name.' '.$user->last_name;

        if(Mail::to($email)->send(new ResetPasswordMail($name,$activation_token))){
            return response()->json([
                'success' => false,
                'code' => 1104,
                'message' => config('data.message.reset_mail_error')
            ]);
        }

        return response()->json([
            'success' => true,
            'code' => 1104,
            'message' => config('data.message.reset_mail_success')
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
                'success' => false,
                'code' => 1003,
                'error' => [
                    'token' => config('data.token.activate_required')
                ]
            ]);
        }

        $validator = Validator::make($request->all(), [
            'password'=> 'required|min:6|max:20',
            'confirm_password' => 'required|same:password'
        ]);
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'code' => 1002,
                'error' => $validator->errors()
            ]);
        }

        $password = $request->input('password');

        $user = User::where('activation_token',$token)->first();
        if (!$user){
            return response()->json([
                'success' => false,
                'code' => 1003,
                'error' => [
                    'token' => config('data.token.activate_expire')
                ]
            ]);
        }
        $user->activation_token = null;
        $user->password = bcrypt($password);
        $user->save();

        return response()->json([
            'success' => true,
            'code' => 1102,
            'message' => config('data.message.change_pass_success')
        ]);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function changePassword(Request $request){
        $validator = Validator::make($request->all(), [
            'current_password' => 'required|min:6|max:20',
            'password'=> 'required|min:6|max:20',
            'confirm_password' => 'required|same:password'
        ]);
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'code' => 1002,
                'error' => $validator->errors()
            ]);
        }

        $password = $request->input('password');
        $current_password = $request->input('current_password');
        $user = JWTAuth::parseToken()->toUser($request->token);

        if (!Hash::check($current_password,$user->password)){
            return response()->json([
                'success' => false,
                'code' => 1002,
                'error' => [
                    'message' => config('data.message.current_pass_error')
                ]
            ]);
        }

        $user->password = Hash::make($password);
        $user->save();

        return response()->json([
            'success' => true,
            'code' => 1102,
            'message' => config('data.message.change_pass_success')
        ]);
    }

    /**
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function logout(){
        JWTAuth::invalidate();
        return response([
            'success' => true,
            'code' => 1001,
            'message' => config('data.message.logout')
        ]);
    }
}
