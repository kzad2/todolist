<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Plan;
use Illuminate\Support\Facades\Hash;
use Laravel\Socialite\Facades\Socialite;

class AuthController extends Controller
{
    public function registrasi(Request $request)
    {
        $validator = validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|min:8',
        ]);

        if ($validator->fails()) {
            return response()->json([$validator->errors()], 422);
        }

        $FreePlan = Plan::where('name', 'Free')->first();
        if(!$FreePlan){
            return response()->json(['message' => 'Default Plan not found'], 500);
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'plan_id' => $FreePlan->id
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Success',
            'user' => $user,
            'token' => $token
        ],);
    }

    public function login(Request $request)
    {
        $validator = validator::make($request->all(), [
            'email' => 'required|string|email|max:255',
            'password' => 'required|min:8',
        ]);

        if ($validator->fails()) {
            return response()->json([$validator->errors()], 422);
        }

        $user = User::where('email', $request->email)->first();

        if (!Auth::attempt($request->only('email', 'password'))) {
            return response()->json(['message' => 'invalit loginn details'], 401);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Success',
            'user' => $user,
            'token' => $token
        ],200);
    }

    public function me()
    {
        return response()->json(Auth::user());
    }

     public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json([
            'message' => 'Successfully logged out',
        ],);
    }
    public function oAuthUrl()
    {
        $url = Socialite::driver('google')->redirect()->getTargetUrl();
        // $url = Socialite::driver('google')->stateless()->redirect()->getTargetUrl();
        return response()->json(['url' => $url]);
    }

    public function oAuthCallback(Request $request)
    {
        $user = Socialite::driver('google')->user();
        // $user = Socialite::driver('google')->stateless()->user();
        //dd('Auth callback received: ', $user);
        $existingUser = User::where('email', $user->getEmail())->first();
        if ($existingUser) {
            $token = $existingUser->createToken('auth_token')->plainTextToken;
            $existingUser->update([
                'avatar' => $user->getAvatar() ?? $user->getAvatar()
            ]);
            return response()->json([
                'message' => 'Login successful',
                'user' => $existingUser,
                'token' => $token,
            ]);
        } else {
            $freePlan = Plan::where('name', 'Free')->first();
            if (!$freePlan) {
                return response()->json(['message' => 'Default plan not found.'], 500);
            }
            // dd($freePlan->id);
            $newUser = User::create([
                'name' => $user->getName(),
                'email' => $user->getEmail(),
                'password' => null,
                'plan_id' => $freePlan->id,
                'avatar' => $user->getAvatar()
            ]);
            $token = $newUser->createToken('auth_token')->plainTextToken;
            return response()->json([
                'message' => 'User created and logged in successfully',
                'user' => $newUser,
                'token' => $token
            ], 201);
        }
    }
}
