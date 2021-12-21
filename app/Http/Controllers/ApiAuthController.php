<?php

namespace App\Http\Controllers;

use App\Http\Requests\UserLoginRequest;
use App\Models\User;
use Firebase\JWT\JWT;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\UserRegistrationRequest;


class ApiAuthController extends Controller
{
    public function register(UserRegistrationRequest $request)
    {
        DB::beginTransaction();

        try {
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'User Registered Succesfully!',
                'data' => [
                    'accessToken' => JWT::encode($user->id, config('jwt.key')),
                ],
            ], 200);
        } catch (\Throwable $th) {
            Log::error($th->getMessage());
            DB::rollback();

            return response()->json([
                'success' => false,
                'message' => 'User Registration Failed!'
            ], 500);
        }
    }

    public function login(UserLoginRequest $request)
    {
        if (User::where('email', '=', $request->email)->exists()) {
            $user = User::where('email', '=', $request->email)->first();

            if (Hash::check($request->password, $user->password)) {
                return response()->json([
                    'success' => true,
                    'message' => 'User Logged In Succesfully!',
                    'data' => [
                        'accessToken' => JWT::encode($user->id, config('jwt.key')),
                    ],
                ], 200);
            }

            return response()->json([
                'success' => true,
                'message' => 'Wrong User Credential!',
                'data' => null,
            ], 400);
        }

        return response()->json([
            'success' => false,
            'message' => 'No User With That Email Address!',
            'data' => null,
        ], 404);
    }

    public function authenticatedUserDetails()
    {
        return response()->json([
            'success' => true,
            'message' => 'Authenticated User Details.',
            'data' => [
                'user' => Auth::guard('api')->user(),
            ],
        ], 200);
    }
}
