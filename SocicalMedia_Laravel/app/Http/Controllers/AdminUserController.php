<?php

namespace app\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AdminUserController extends Controller
{
    public function list(Request $request)
    {
        $users = User::orderBy('id', 'desc')->get();
        $data = returnMessage(1, $users, 'Get users success!');
        return response($data, 200);
    }

    public function detail($id)
    {
        $user = User::where('id', $id)->first();
        if (!$user) {
            return returnMessage(0, '', 'User not found!');
        }
        $data = returnMessage(1, $user, 'Get user success!');
        return response($data, 200);
    }

    public function create(Request $request)
    {
        try {
            $user = new User();

            $email = $request->input('email');
            $phone = $request->input('phone');
            $password = $request->input('password');
            $password_confirm = $request->input('password_confirm');

            $is_valid = User::checkEmail($email);
            if (!$is_valid) {
                $data = returnMessage(-1, 'Error', 'Email has been used!');
                return response($data, 400);
            }

            $is_valid = User::checkPhone($phone);
            if (!$is_valid) {
                $data = returnMessage(-1, 'Error', 'Phone has been used!');
                return response($data, 400);
            }

            if ($password != $password_confirm) {
                $data = returnMessage(-1, 400, 'Error', 'Passwords do not match!');
                return response($data, 400);
            }

            if (strlen($password) < 5) {
                $data = returnMessage(-1, 'Error', 'Password must be at least 5 characters!');
                return response($data, 400);
            }

            $user = $this->save($user, $request);
            $user->save();

            $data = returnMessage(1, $user, 'Create success');
            return response($data, 200);
        } catch (\Exception $exception) {
            $data = returnMessage(-1, '', $exception->getMessage());
            return response($data, 400);
        }
    }

    public function update($id, Request $request)
    {
        try {
            $user = User::where('id', $id)->first();
            if (!$user) {
                return returnMessage(-1, 400, null, 'User not found!');
            }

            $email = $request->input('email');
            $phone = $request->input('phone');
            $password = $request->input('password');
            $password_confirm = $request->input('password_confirm');

            if ($user->email != $email) {
                $is_valid = User::checkEmail($email);
                if (!$is_valid) {
                    $data = returnMessage(-1, 'Error', 'Email has been used!');
                    return response($data, 400);
                }
            }

            if ($user->phone != $phone) {
                $is_valid = User::checkPhone($phone);
                if (!$is_valid) {
                    $data = returnMessage(-1, 'Error', 'Phone has been used!');
                    return response($data, 400);
                }
            }

            if ($password || $password_confirm) {
                if ($password != $password_confirm) {
                    $data = returnMessage(-1, 'Error', 'Passwords do not match!');
                    return response($data, 400);
                }

                if (strlen($password) < 5) {
                    $data = returnMessage(-1, 'Error', 'Password must be at least 5 characters!');
                    return response($data, 400);
                }
            }

            $user = $this->save($user, $request);
            $user->save();

            $data = returnMessage(1, $user, 'Update success!');
            return response($data, 200);
        } catch (\Exception $exception) {
            $data = returnMessage(-1, '', $exception->getMessage());
            return response($data, 400);
        }
    }

    public function delete($id)
    {
        try {
            $user = User::where('id', $id)->first();
            if (!$user) {
                return returnMessage(-1, null, 'User not found!');
            }

            $user->delete();

            $data = returnMessage(1, $user, 'Delete success!');
            return response($data, 200);
        } catch (\Exception $exception) {
            $data = returnMessage(-1, '', $exception->getMessage());
            return response($data, 400);
        }
    }

    /**
     * Save user data.
     *
     * @param User $user
     * @param Request $request
     * @return User
     */
    private function save(User $user, Request $request)
    {
        $user->username = $request->input('username');
        $user->email = $request->input('email');
        $user->phone = $request->input('phone');

        if ($request->input('password')) {
            $password = $request->input('password');
            $passwordHash = Hash::make($password);
            $user->password = $passwordHash;
        }

        $thumbnail = $user->profilepicture;
        if ($request->hasFile('profilepicture')) {
            $item = $request->file('profilepicture');
            $itemPath = $item->store('avatars', 'public');
            $thumbnail = asset('storage/' . $itemPath);
        }
        $user->profilepicture = $thumbnail;

        return $user;
    }
}
