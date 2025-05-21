<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;

class UserSearchController extends Controller
{
public function suggest(Request $request)
{
    $query = trim($request->query('q'));

    if (!$query) {
        return response()->json([]);
    }

    $users = User::select('id', 'username', 'profilepicture')
        ->where('username', 'LIKE', "%{$query}%")
        ->orderBy('username')
        ->limit(5)
        ->get();

    return response()->json($users);
}
public function getUser($id)
{
    $user = User::find($id);

    if (!$user) {
        return response()->json(['message' => 'User not found'], 404);
    }

    // Nếu bạn muốn trả về một số trường nhất định
    return response()->json([
        'id' => $user->id,
        'username' => $user->username,
        'email' => $user->email,
        'phone' => $user->phone,
        'profilepicture' => $user->profilepicture,
        'fullname' => $user->fullname ?? null, // nếu có trường này
        // Thêm các trường khác nếu cần
    ]);
}

}
