<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Http\Traits\ResponseTrait;
use App\Models\Admin;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    use ResponseTrait;

    public function register(Request $request)
    {
        $validator = Validator::make($request->post(), [
            'first_name' => 'required|string|min:3|max:15',
            'last_name' => 'required|string|min:3|max:15',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:5',
            'confirm_password' => 'required|string|min:5|same:password',
            'type' => 'required|integer|max:1',
        ]);

        if ($validator->fails()) {
            return self::failed($validator->errors()->first());
        } else {
            $user = User::create([
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'email' => $request->email,
                'password' => $request->password,
                'confirm_password' => $request->confirm_password,
                'type' => $request->type,
            ]);

            config(['auth.guards.user-api.provider' => 'user']);
            $token = $user->createToken('MyApp', ['user'])->accessToken;

            $response = [
                'user' => $user,
                'token' => $token
            ];

            $message = 'تم التسجيل بنجاح';
            return $this->success($message, $response);
        }
    }

    public function login(Request $request)
    {
        $validator = Validator::make($request->post(), [
            'email' => 'required|email|exists:users,email',
            'password' => 'required|string|min:5',
        ]);

        if ($validator->fails()) {
            return self::failed($validator->errors()->first());
        } else {
            $data = [
                'email' => $request->email,
                'password' => $request->password
            ];
            config(['auth.guards.user-api.provider' => 'user']);
            if (auth('user')->attempt($data)) {

                $user = User::find(Auth::guard('user')->user()->id);
                $token = $user->createToken('MyApp', ['user'])->accessToken;

                $response = [
                    'user' => $data,
                    'token' => $token
                ];
                $message = 'تم تسجيل الدخول';
                return $this->success($message, $response);
            } else
                return  $this->failed('فشل تسجيل الدخول');
        }
    }

    public function cms_login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:admins,email',
            'password' => 'required|string|min:5',
        ]);

        if ($validator->fails()) {
            return self::failed($validator->errors()->first());
        } else {
            $data = [
                'email' => $request->email,
                'password' => $request->password
            ];
            config(['auth.guards.admin-api.provider' => 'admin']);
            if (auth('admin')->attempt($data)) {

                $admin = Admin::find(Auth::guard('admin')->user()->id);
                $token = $admin->createToken('MyApp', ['admin'])->accessToken;

                $response = [
                    'admin' => $data,
                    'token' => $token
                ];
                $message = 'تم تسجيل الدخول إلى نظام إدارة المحتوى (CMS)';
                return $this->success($message, $response);
            } else
                return  $this->failed('لا يمكنك تسجيل الدخول');
        }
    }

    public function CMS_test()
    {
        return response('This is CMS dashboard');
    }

    public function User_test()
    {
        return response('This is User dashboard');
    }
}
