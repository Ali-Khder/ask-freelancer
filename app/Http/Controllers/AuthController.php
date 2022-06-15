<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Http\Traits\ResponseTrait;
use App\Models\Admin;
use App\Models\Skill;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

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
        ]);

        if ($validator->fails()) {
            return self::failed($validator->errors()->first());
        } else {
            $user = User::create([
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'type' => 0,
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

    public function account(Request $request)
    {
        $validator = Validator::make($request->post(), [
            'profissionName' => 'required|string|min:5',
            'speciality' => 'required|string|min:5',
            'bio' => 'string|min:5',
            'type' => 'required|integer|max:3',
            'birthday' => 'required|date|date_format:Y-m-d',
            'phone_number' => 'required|regex:/^([0-9\s\-\+\(\)]*)$/|max:12',
            'skills' => 'required|array|min:1',
            'skills.*' => 'required|integer|min:1|exists:categories,id',
        ]);

        if ($validator->fails()) {
            return self::failed($validator->errors()->first());
        } else {
            $user = User::find(auth()->user()->id);
            $user->birthday = $request->get('birthday');
            $user->phone_number = $request->get('phone_number');
            $user->profissionName = $request->get('profissionName');
            $user->bio = $request->has('bio') ? $request->get('bio') : '';
            $user->speciality = $request->get('speciality');
            $user->type = $request->get('type');
            $skills = $request->get('skills');

            for ($i = 0; $i < count($skills); $i++)
                Skill::create([
                    'user_id' => auth()->user()->id,
                    'category_id' => $skills[$i],
                ]);
            $user->save();
            return $this->success('تم إعداد الحساب', $user);
        }
    }

    public function get_profile()
    {
        $user = User::find(auth()->user()->id);
        return $this->success('معلومات حسابي', $user);
    }

    public function changePassword(Request $request)
    {
        $validator = Validator::make($request->post(), [
            'old_password' => 'required|string|min:5',
            'new_password' => 'required|string|min:5',
            'confirm_password' => 'required|string|min:5|same:new_password',
        ]);

        if ($validator->fails()) {
            return self::failed($validator->errors()->first());
        } else {

            $user = User::find(auth()->user()->id);
            if (!Hash::check($request->old_password, $user->password))
                return $this->failed('Password is wrong');

            $user->password = Hash::make($request->new_password);
            $user->save();
            return $this->success('Change password Success');
        }
    }

    public function cms_login(Request $request)
    {
        $validator = Validator::make($request->post(), [
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

    public function changeCMSPassword(Request $request)
    {
        $validator = Validator::make($request->post(), [
            'old_password' => 'required|string|min:5',
            'new_password' => 'required|string|min:5',
            'confirm_password' => 'required|string|min:5|same:new_password',
        ]);

        if ($validator->fails()) {
            return self::failed($validator->errors()->first());
        } else {

            $user = Admin::find(auth()->user()->id);
            if (!Hash::check($request->old_password, $user->password))
                return $this->failed('Password is wrong');

            $user->password = Hash::make($request->new_password);
            $user->save();
            return $this->success('Change password Success');
        }
    }
}
