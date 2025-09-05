<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Carbon;

class AuthController extends Controller
{
    // Register user
    public function register(Request $request)
    {
        $request->validate([
            'name'=>'required|string|max:255',
            'email'=>'required|email|unique:users',
            'password'=>'required|string|min:6|confirmed'
        ]);

        $user = User::create([
            'name'=>$request->name,
            'email'=>$request->email,
            'password'=>Hash::make($request->password),
        ]);

        $token = $user->createToken('api_token')->plainTextToken;

        return response()->json(['user'=>$user,'token'=>$token], 201);
    }

    // Login user
    public function login(Request $request)
    {
        $request->validate([
            'email'=>'required|email',
            'password'=>'required'
        ]);

        $user = User::where('email',$request->email)->first();

        if(!$user || !Hash::check($request->password,$user->password)){
            throw ValidationException::withMessages([
                'email'=>['The provided credentials are incorrect.']
            ]);
        }

        $token = $user->createToken('api_token')->plainTextToken;

        return response()->json(['user'=>$user,'token'=>$token]);
    }

    // Logout user
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message'=>'Logged out successfully']);
    }

    // Send OTP
    public function sendOtp(Request $request)
    {
        $request->validate([
            'email'=>'required|email|exists:users,email'
        ]);

        $user = User::where('email',$request->email)->first();

        $otp = rand(100000,999999);
        $user->otp = $otp;
        $user->otp_expires_at = Carbon::now()->addMinutes(5);
        $user->save();

        // Send OTP via email
        Mail::raw("Your OTP is: $otp", function($message) use($user){
            $message->to($user->email)
                    ->subject('Your OTP Code');
        });

        return response()->json(['message'=>'OTP sent successfully']);
    }

    // Verify OTP
    public function verifyOtp(Request $request)
    {
        $request->validate([
            'email'=>'required|email|exists:users,email',
            'otp'=>'required|digits:6'
        ]);

        $user = User::where('email',$request->email)
                    ->where('otp',$request->otp)
                    ->where('otp_expires_at','>',Carbon::now())
                    ->first();

        if(!$user){
            return response()->json(['message'=>'Invalid or expired OTP'],422);
        }

        // OTP verified, clear OTP
        $user->otp = null;
        $user->otp_expires_at = null;
        $user->save();

        return response()->json(['message'=>'OTP verified successfully']);
    }

    // Reset password
    public function resetPassword(Request $request)
    {
        $request->validate([
            'email'=>'required|email|exists:users,email',
            'otp'=>'required|digits:6',
            'password'=>'required|string|min:6|confirmed'
        ]);

        $user = User::where('email',$request->email)
                    ->where('otp',$request->otp)
                    ->where('otp_expires_at','>',Carbon::now())
                    ->first();

        if(!$user){
            return response()->json(['message'=>'Invalid or expired OTP'],422);
        }

        $user->password = Hash::make($request->password);
        $user->otp = null;
        $user->otp_expires_at = null;
        $user->save();

        return response()->json(['message'=>'Password reset successfully']);
    }
}
