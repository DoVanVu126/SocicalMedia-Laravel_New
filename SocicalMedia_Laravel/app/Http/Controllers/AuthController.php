<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;
use App\Mail\SendOtpMail;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'username' => 'required|string|unique:users',
            'email' => 'required|email|unique:users',
            'password' => 'required|string|min:6',
            'phone' => 'nullable|string',
            // Không cần kiểm tra profilepicture vì mặc định sẽ là default
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Dữ liệu không hợp lệ', 'errors' => $validator->errors()], 422);
        }

        // Gán ảnh mặc định
        $profilePicturePath = 'default-avatar.png';

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

        // Tạo mã OTP ngẫu nhiên
        $otpCode = rand(100000, 999999);
        $otpExpiry = Carbon::now()->addMinutes(5);

        // Lưu OTP và thời gian hết hạn
        $user->otp_code = $otpCode;
        $user->otp_expires_at = $otpExpiry;
        $user->save();

        // Gửi OTP qua Gmail bằng cách sử dụng lớp SendOtpMail
        Mail::to($user->email)->send(new SendOtpMail($otpCode));

        return response()->json([
            'message' => 'Đăng nhập bước 1 thành công. Vui lòng kiểm tra email để lấy mã OTP.',
            'requires_otp' => true,
            'user_id' => $user->id
        ]);
    }
    public function verifyOtp(Request $request)
    {
        $request->validate([
            'user_id' => 'required|integer',
            'otp_code' => 'required|string',
        ]);

        $user = User::find($request->user_id);

        if (!$user) {
            return response()->json(['message' => 'Không tìm thấy người dùng'], 404);
        }

        if (!$user->otp_code || $user->otp_code !== $request->otp_code) {
            return response()->json(['message' => 'OTP không đúng'], 401);
        }

        if (Carbon::now()->gt($user->otp_expires_at)) {
            return response()->json(['message' => 'OTP đã hết hạn'], 401);
        }

        $user->is_online = true;
        $user->last_online_at = now();

        // Xóa OTP sau khi dùng
        $user->otp_code = null;
        $user->otp_expires_at = null;
        $user->two_factor_enabled = true; // Có thể đánh dấu đã xác thực 2FA
        $user->save();

        return response()->json([
            'message' => 'Xác thực OTP thành công',
            'user' => [
                'id' => $user->id,
                'username' => $user->username,
                'email' => $user->email,
                'profilepicture' => $user->profilepicture,
                'phone' => $user->phone,
                'role' => $user->role,
            ]
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->is_online = false;
        $request->user()->last_online_at = now();
        $request->user()->save();
        return response()->json(['message' => 'Đăng xuat thanh cong']);
    }
}
