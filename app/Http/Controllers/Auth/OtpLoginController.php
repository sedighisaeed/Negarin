<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Log;
use Kavenegar\KavenegarApi;

class OtpLoginController extends Controller
{
    public function __construct()
    {
        $this->middleware('guest');
    }

    public function showOtpLoginForm()
    {
        return view('auth.otp-login');
    }

    public function sendOtp(Request $request)
    {
        $request->validate([
            'phone' => 'required|string|regex:/^\+?[1-9]\d{1,14}$/',
        ]);

        $phone = $request->input('phone');
        $otp = rand(100000, 999999);

        try {
            $apiKey = config('services.kavenegar.api_key');
            $api = new KavenegarApi($apiKey);

            $result = $api->VerifyLookup($phone, $otp, null, null, 'otp-template');

            Session::put('otp', $otp);
            Session::put('otp_phone', $phone);
            Session::put('otp_expires', now()->addMinutes(5));

            return response()->json(['success' => __('auth.otpSent')]);
        } catch (\Kavenegar\Exceptions\ApiException $e) {
            Log::error('Kavenegar API Error: ' . $e->errorMessage());
            return response()->json(['error' => __('auth.failedToSendOtp')], 500);
        } catch (\Kavenegar\Exceptions\HttpException $e) {
            Log::error('Kavenegar HTTP Error: ' . $e->errorMessage());
            return response()->json(['error' => __('auth.networkIssue')], 500);
        }
    }

    public function verifyOtp(Request $request)
    {
        $request->validate([
            'otp_code' => 'required|string|size:6',
        ]);

        $enteredOtp = $request->input('otp_code');
        $storedOtp = Session::get('otp');
        $phone = Session::get('otp_phone');
        $expires = Session::get('otp_expires');

        if (!$storedOtp || !$phone || !$expires) {
            return response()->json(['error' => __('auth.otpSessionExpired')], 400);
        }

        if (now()->greaterThan($expires)) {
            Session::forget(['otp', 'otp_phone', 'otp_expires']);
            return response()->json(['error' => __('auth.otpExpired')], 400);
        }

        if ($enteredOtp !== $storedOtp) {
            return response()->json(['error' => __('auth.invalidOtp')], 400);
        }

        // Find or create user based on phone
        $user = \App\User::where('phone', $phone)->first();

        if (!$user) {
            // For demo, create a new user. In production, you might want to handle registration separately
            $user = \App\User::create([
                'phone' => $phone,
                'username' => 'user_' . rand(10000, 99999), // Generate a username
                'email' => null, // Phone login doesn't require email
                'password' => bcrypt(str_random(16)), // Random password since passwordless
            ]);
        }

        Auth::login($user);

        Session::forget(['otp', 'otp_phone', 'otp_expires']);

        return response()->json(['success' => __('auth.loginSuccessful'), 'redirect' => '/i/web']);
    }
}