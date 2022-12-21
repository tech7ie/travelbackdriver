<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function login(Request $request): array
    {
        $result = [
            'status' => false,
            'message' => 'Invalid login details!',
            'token' => '',
            'user' => []
        ];

        $user = User::where('email', $request->email)->first();

        if ($user && empty($user->email_verified_at)) {
            $result['message'] = 'Email not verified!';
            return $result;
        }

        if (Auth::attempt(['email' => $request->email, 'password' => $request->password], true)) {
            // Аутентификация успешна...
            $result['status'] = true;
            $result['message'] = 'Welcome';
            $result['token'] = Auth::user()->remember_token;
            $result['user'] = $user;
        }

        return $result;
    }

    public function auth(Request $request): array
    {
        $result = [
            'status' => false,
            'user' => []
        ];
        $user = User::where('remember_token', $request->token)->first();

        if (!empty($request->token) && $user) {
            $result['status'] = true;
            $result['user'] = $user;
        }

        return $result;
    }

    public function verify(Request $request): \Exception|array
    {
        $result = [
            'status' => false,
            'message' => 'Incorrect email, please use the same email that was used for registration.',
            'user' => []
        ];

        $user = User::where('email', $request->email)->first();

        if (!$user)
        {
            return $result;
        }

        if (!empty($user->email_verified_at)) {
            $result['message'] = 'The user with this email has already been verified!';
            return  $result;
        }

        try {
            $user->update([
                'password' => Hash::make($request->password),
                'email_verified_at' => date('Y-m-d H:i:s')
            ]);

            if (Auth::attempt(['email' => $request->email, 'password' => $request->password], true)) {
                // Аутентификация успешна...
                $result['status'] = true;
                $result['message'] = 'Success!';
                $result['token'] = Auth::user()->remember_token;
                $result['user'] = $user;
            }
        } catch (\Exception $exception) {
            return $exception;
        }

        return $result;
    }

    public function checkEmail(Request $request)
    {
        $result = [
          'status' => false,
          'email' => []
        ];

        $user = User::where('email', $request->email)->first();

        if ($user) {
            $result['status'] = true;
            $result['email'] = $user->email;
        } else {
            $result['message'] = 'The specified email was not found.';
        }

        return $result;
    }

    public function updatePassword(Request $request)
    {
        $result = [
            'status' => false,
            'message' => 'Failed to change the password for the specified user.'
        ];

        $user = User::where('email', $request->email);

        if ($user && $request->password) {
            $user->update([
                'password' => Hash::make($request->password),
            ]);

            $result['status'] = true;
            $result['message'] = 'Success!';
        }

        return $result;
    }
}
