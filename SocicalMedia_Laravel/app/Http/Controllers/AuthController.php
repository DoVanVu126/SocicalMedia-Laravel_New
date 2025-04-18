<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Models\User;

class AuthController extends Controller
{
    public function register(Request $request)
{
    $validator = Validator::make($request->all(), [
        'username' => 'required|string|unique:users',
        'email' => 'required|email|unique:users',
        'password' => 'required|string|min:6',
        'phone' => 'nullable|string',
        'profilepicture' => 'nullable|image|mimes:jpg,jpeg,png|max:2048', // tối đa 2MB
    ]);

    if ($validator->fails()) {
        return response()->json(['message' => 'Dữ liệu không hợp lệ', 'errors' => $validator->errors()], 422);
    }

    $profilePicturePath = null;
    if ($request->hasFile('profilepicture')) {
        $profilePicturePath = $request->file('profilepicture')->store('images', 'public');
    }

    $user = User::create([
        'username' => $request->username,
        'email' => $request->email,
        'password' => Hash::make($request->password),
        'phone' => $request->phone,
        'profilepicture' => $profilePicturePath,
    ]);

    return response()->json(['message' => 'Đăng ký thành công', 'user' => $user]);
}

    public function login(Request $request)
    {
        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json(['message' => 'Sai tài khoản hoặc mật khẩu'], 401);
        }

        return response()->json([
            'message' => 'Đăng nhập thành công',
            'user' => [
                'id' => $user->id,
                'username' => $user->username,
                'email' => $user->email,
                'profilepicture' => $user->profilepicture,
                'phone' => $user->phone,
            ]
        ]);
    }
}
