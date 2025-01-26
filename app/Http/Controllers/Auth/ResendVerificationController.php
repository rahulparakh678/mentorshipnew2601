<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use App\User;


class ResendVerificationController extends Controller
{
    public function showVerificationForm()
    {
        return view('auth.resentverification');
    }
    
    public function resendVerificationEmail(Request $request)
    {
        // Validate the email input
        $validated = $request->validate([
            'email' => 'required|email|exists:users,email',
        ]);

        // Retrieve the user by email using query builder
        $user = DB::table('users')->where('email', $validated['email'])->first();

        // Check if the user exists and the email is not already verified
        if ($user && !$user->verified) {

            // Generate a unique token for the verification
            $token = Str::random(60);

            // Check if the email already exists in the email_verifications table
            $existingVerification = DB::table('email_verifications')
                ->where('email', $user->email)
                ->first();

            if ($existingVerification) {
                // Update the existing token
                DB::table('email_verifications')
                    ->where('email', $user->email)
                    ->update([
                        'token' => $token,
                        'updated_at' => now(),
                    ]);
            } else {
                // Insert a new token
                DB::table('email_verifications')->insert([
                    'user_id' => $user->id,
                    'email' => $user->email,
                    'token' => $token,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            // Generate a verification URL
            $verificationUrl = route('verification.verify', ['token' => $token]);

            // Send the email with the verification URL
            Mail::send('emails.resend_verification', [
                'name' => $user->name,
                'verification_url' => $verificationUrl,
            ], function ($message) use ($user) {
                $message->to($user->email)
                    ->subject('Verify Your Email Address');
            });

            return redirect()->back()->with('success', 'A verification email has been sent to your address!');
        }

        return redirect()->back()->with('error', 'The email address is either already verified or does not exist.');
    }

    public function verify($token)
    {
        // Retrieve the verification record using the token
        $verification = DB::table('email_verifications')->where('token', $token)->first();
    
        if ($verification) {
            // Mark the user's email as verified
            DB::table('users')->where('id', $verification->user_id)->update([
                'verified_at' => now(),
            ]);
    
            // Delete the token after successful verification
            DB::table('email_verifications')->where('token', $token)->delete();
    
            return redirect()->route('home')->with('success', 'Your email has been verified successfully!');
        }
    
        return redirect()->route('home')->with('error', 'Invalid verification token.');
    }
    

}
