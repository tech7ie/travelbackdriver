<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    public function login(Request $request)
    {
        return User::where('email', $request->user['email'])->first();
//        $result = [
//            'status' => false,
//            'message' => 'Неверные данные входа!',
//            'token' => ''
//        ];
//
//        if (Auth::attempt(['email' => $request->user['password'], 'password' => $request->user['password']])) {
//            // Аутентификация успешна...
//            $result['status'] = true;
//            $result['message'] = 'Добро пожаловать';
//            $result['token'] = Auth::user()->api_token;
//        }
//
//        return $result;
    }

    public function auth(Request $request): bool
    {
        $status = false;

        if (User::where('api_token', $request->token)->first()) {
            $status = true;
        }

        return $status;
    }
}
